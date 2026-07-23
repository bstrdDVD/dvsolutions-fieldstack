<?php
/**
 * Tabla de reservas y lógica de disponibilidad.
 *
 * Modelo:
 *   - Cada tour se ofrece ciertos días de la semana (config en DVS_TR_Tours):
 *       · Sábado  → Termas + Embalse
 *       · Domingo → solo Termas
 *       · Festivos habilitados → solo Termas
 *   - Cada tour tiene un cupo de motos por día (3 por defecto). Varias reservas
 *     comparten ese cupo hasta agotarlo.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DVS_TR_DB {

	const DB_VERSION_KEY = 'dvs_tr_db_version';
	const DB_VERSION     = '3';

	const ESTADO_PENDIENTE = 'pendiente';
	const ESTADO_PAGADA    = 'pagada';
	const ESTADO_CANCELADA = 'cancelada';

	public static function tabla() {
		global $wpdb;
		return $wpdb->prefix . 'dvs_tr_reservas';
	}

	public static function install() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$tabla   = self::tabla();
		$charset = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$tabla} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			tour VARCHAR(20) NOT NULL,
			fecha DATE NOT NULL,
			nombre VARCHAR(120) NOT NULL,
			email VARCHAR(120) NOT NULL,
			telefono VARCHAR(40) NOT NULL DEFAULT '',
			motos SMALLINT UNSIGNED NOT NULL DEFAULT 1,
			personas SMALLINT UNSIGNED NOT NULL DEFAULT 1,
			estado VARCHAR(20) NOT NULL DEFAULT 'pendiente',
			codigo VARCHAR(20) NOT NULL,
			order_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
			creado DATETIME NOT NULL,
			PRIMARY KEY (id),
			KEY fecha_estado (fecha, estado),
			KEY tour_fecha (tour, fecha),
			KEY codigo (codigo),
			KEY order_id (order_id)
		) {$charset};";

		dbDelta( $sql );
		update_option( self::DB_VERSION_KEY, self::DB_VERSION );
	}

	public static function maybe_upgrade() {
		if ( get_option( self::DB_VERSION_KEY ) !== self::DB_VERSION ) {
			self::install();
		}
	}

	/**
	 * Expira reservas pendientes que superaron el tiempo de retención de cupo.
	 */
	public static function expirar_pendientes() {
		$minutos = (int) DVS_TR_Tours::opcion( 'minutos_retencion' );
		if ( $minutos <= 0 ) {
			return;
		}
		global $wpdb;
		$limite = gmdate( 'Y-m-d H:i:s', time() - $minutos * MINUTE_IN_SECONDS );
		$wpdb->query( $wpdb->prepare(
			'UPDATE ' . self::tabla() . ' SET estado = %s WHERE estado = %s AND creado < %s',
			self::ESTADO_CANCELADA,
			self::ESTADO_PENDIENTE,
			$limite
		) );
	}

	/**
	 * Motos activas (pendientes o pagadas) reservadas por fecha y tour,
	 * en un rango. Devuelve mapa: fecha => ( tour => motos_reservadas ).
	 */
	public static function motos_por_fecha( $desde, $hasta ) {
		global $wpdb;
		self::expirar_pendientes();

		$filas = $wpdb->get_results( $wpdb->prepare(
			'SELECT fecha, tour, SUM(motos) AS motos FROM ' . self::tabla()
			. ' WHERE fecha BETWEEN %s AND %s AND estado IN (%s, %s) GROUP BY fecha, tour',
			$desde,
			$hasta,
			self::ESTADO_PENDIENTE,
			self::ESTADO_PAGADA
		) );

		$mapa = array();
		foreach ( $filas as $fila ) {
			$mapa[ $fila->fecha ][ $fila->tour ] = (int) $fila->motos;
		}
		return $mapa;
	}

	/**
	 * Motos disponibles de un tour en una fecha, dado el mapa de reservadas
	 * de ese día (tour => motos). Considera si el tour se ofrece ese día.
	 *
	 * @return int Motos disponibles (0 si no se ofrece o está lleno).
	 */
	public static function motos_disponibles( $tour, $fecha, $reservadas_del_dia ) {
		if ( ! in_array( $tour, DVS_TR_Tours::tours_ofrecidos( $fecha ), true ) ) {
			return 0;
		}
		$capacidad  = DVS_TR_Tours::capacidad( $tour );
		$reservadas = isset( $reservadas_del_dia[ $tour ] ) ? (int) $reservadas_del_dia[ $tour ] : 0;
		return max( 0, $capacidad - $reservadas );
	}

	/**
	 * Crea una reserva de forma segura contra reservas simultáneas, validando
	 * que el tour se ofrezca ese día y que queden motos disponibles.
	 *
	 * @return array|WP_Error La reserva creada o un error.
	 */
	public static function crear_reserva( $tour, $fecha, $nombre, $email, $telefono, $motos, $order_id = 0 ) {
		global $wpdb;
		self::expirar_pendientes();

		$motos = max( 1, (int) $motos );

		// El tour debe ofrecerse ese día (día de la semana / festivo).
		if ( ! in_array( $tour, DVS_TR_Tours::tours_ofrecidos( $fecha ), true ) ) {
			return new WP_Error(
				'dvs_tr_no_disponible',
				__( 'Ese tour no está disponible en la fecha seleccionada.', 'dvs-tour-reservas' )
			);
		}

		$tabla = self::tabla();
		$wpdb->query( 'START TRANSACTION' );

		// Bloqueo de las filas del tour+día para evitar sobreventa simultánea.
		$reservadas = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COALESCE(SUM(motos),0) FROM {$tabla} WHERE tour = %s AND fecha = %s AND estado IN (%s, %s) FOR UPDATE",
			$tour,
			$fecha,
			self::ESTADO_PENDIENTE,
			self::ESTADO_PAGADA
		) );

		$capacidad = DVS_TR_Tours::capacidad( $tour );
		if ( $reservadas + $motos > $capacidad ) {
			$wpdb->query( 'ROLLBACK' );
			$restantes = max( 0, $capacidad - $reservadas );
			return new WP_Error(
				'dvs_tr_sin_cupo',
				sprintf(
					/* translators: %d: motos disponibles */
					_n( 'Lo sentimos, solo queda %d moto disponible para esa fecha.', 'Lo sentimos, solo quedan %d motos disponibles para esa fecha.', $restantes, 'dvs-tour-reservas' ),
					$restantes
				)
			);
		}

		$codigo = strtoupper( wp_generate_password( 8, false, false ) );
		$ok     = $wpdb->insert(
			$tabla,
			array(
				'tour'     => $tour,
				'fecha'    => $fecha,
				'nombre'   => $nombre,
				'email'    => $email,
				'telefono' => $telefono,
				'motos'    => $motos,
				'personas' => $motos * 2,
				'estado'   => self::ESTADO_PENDIENTE,
				'codigo'   => $codigo,
				'order_id' => (int) $order_id,
				'creado'   => current_time( 'mysql', true ),
			),
			array( '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%d', '%s' )
		);

		if ( ! $ok ) {
			$wpdb->query( 'ROLLBACK' );
			return new WP_Error( 'dvs_tr_error_bd', __( 'No se pudo registrar la reserva. Inténtalo nuevamente.', 'dvs-tour-reservas' ) );
		}

		$wpdb->query( 'COMMIT' );

		return array(
			'id'     => (int) $wpdb->insert_id,
			'codigo' => $codigo,
			'tour'   => $tour,
			'fecha'  => $fecha,
			'motos'  => $motos,
		);
	}

	public static function cambiar_estado( $id, $estado ) {
		global $wpdb;
		return (bool) $wpdb->update(
			self::tabla(),
			array( 'estado' => $estado ),
			array( 'id' => (int) $id ),
			array( '%s' ),
			array( '%d' )
		);
	}

	/**
	 * Asocia una reserva a un pedido de WooCommerce.
	 */
	public static function vincular_order( $reserva_id, $order_id ) {
		global $wpdb;
		return (bool) $wpdb->update(
			self::tabla(),
			array( 'order_id' => (int) $order_id ),
			array( 'id' => (int) $reserva_id ),
			array( '%d' ),
			array( '%d' )
		);
	}

	/**
	 * Cambia el estado de todas las reservas de un pedido de WooCommerce.
	 * Devuelve el número de reservas afectadas.
	 */
	public static function cambiar_estado_por_order( $order_id, $estado ) {
		global $wpdb;
		return (int) $wpdb->query( $wpdb->prepare(
			'UPDATE ' . self::tabla() . ' SET estado = %s WHERE order_id = %d',
			$estado,
			(int) $order_id
		) );
	}

	public static function reservas_por_order( $order_id ) {
		global $wpdb;
		return $wpdb->get_results( $wpdb->prepare(
			'SELECT * FROM ' . self::tabla() . ' WHERE order_id = %d',
			(int) $order_id
		) );
	}

	public static function listar( $limite = 200 ) {
		global $wpdb;
		return $wpdb->get_results( $wpdb->prepare(
			'SELECT * FROM ' . self::tabla() . ' ORDER BY fecha DESC, id DESC LIMIT %d',
			$limite
		) );
	}
}
