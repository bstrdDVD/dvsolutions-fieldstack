<?php
/**
 * Tabla de reservas y lógica de disponibilidad.
 *
 * Regla central: el guía es uno solo. Una reserva activa (pendiente o pagada)
 * de cualquier tour bloquea el día completo para ambos tours, salvo que la
 * opción "permitir_mismo_dia" esté activa, en cuyo caso solo bloquea su tour.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DVS_TR_DB {

	const DB_VERSION_KEY = 'dvs_tr_db_version';
	const DB_VERSION     = '2';

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
			personas SMALLINT UNSIGNED NOT NULL DEFAULT 1,
			estado VARCHAR(20) NOT NULL DEFAULT 'pendiente',
			codigo VARCHAR(20) NOT NULL,
			order_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
			creado DATETIME NOT NULL,
			PRIMARY KEY (id),
			KEY fecha_estado (fecha, estado),
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
	 * Devuelve las reservas activas (pendientes o pagadas) de un rango de fechas,
	 * como mapa fecha => lista de tours reservados.
	 */
	public static function tours_ocupados_por_fecha( $desde, $hasta ) {
		global $wpdb;
		self::expirar_pendientes();

		$filas = $wpdb->get_results( $wpdb->prepare(
			'SELECT fecha, tour FROM ' . self::tabla() . ' WHERE fecha BETWEEN %s AND %s AND estado IN (%s, %s)',
			$desde,
			$hasta,
			self::ESTADO_PENDIENTE,
			self::ESTADO_PAGADA
		) );

		$mapa = array();
		foreach ( $filas as $fila ) {
			$mapa[ $fila->fecha ][] = $fila->tour;
		}
		return $mapa;
	}

	/**
	 * Indica si un tour puede reservarse en una fecha dada.
	 */
	public static function tour_disponible( $tour, $fecha, $ocupados_del_dia ) {
		if ( in_array( $tour, $ocupados_del_dia, true ) ) {
			return false; // Ese tour ya está reservado ese día.
		}
		if ( ! DVS_TR_Tours::opcion( 'permitir_mismo_dia' ) && ! empty( $ocupados_del_dia ) ) {
			return false; // El guía ya está comprometido en el otro tour ese día.
		}
		return true;
	}

	/**
	 * Crea una reserva de forma segura contra reservas simultáneas.
	 *
	 * @return array|WP_Error La reserva creada o un error si el cupo ya no está disponible.
	 */
	public static function crear_reserva( $tour, $fecha, $nombre, $email, $telefono, $personas, $order_id = 0 ) {
		global $wpdb;
		self::expirar_pendientes();

		$tabla = self::tabla();
		$wpdb->query( 'START TRANSACTION' );

		// Bloqueo de las filas del día para evitar doble reserva simultánea.
		$activas = $wpdb->get_col( $wpdb->prepare(
			"SELECT tour FROM {$tabla} WHERE fecha = %s AND estado IN (%s, %s) FOR UPDATE",
			$fecha,
			self::ESTADO_PENDIENTE,
			self::ESTADO_PAGADA
		) );

		if ( ! self::tour_disponible( $tour, $fecha, $activas ) ) {
			$wpdb->query( 'ROLLBACK' );
			return new WP_Error(
				'dvs_tr_no_disponible',
				__( 'Lo sentimos, esta fecha acaba de ser reservada. El guía ya está comprometido ese día.', 'dvs-tour-reservas' )
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
				'personas' => $personas,
				'estado'   => self::ESTADO_PENDIENTE,
				'codigo'   => $codigo,
				'order_id' => (int) $order_id,
				'creado'   => current_time( 'mysql', true ),
			),
			array( '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%d', '%s' )
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
