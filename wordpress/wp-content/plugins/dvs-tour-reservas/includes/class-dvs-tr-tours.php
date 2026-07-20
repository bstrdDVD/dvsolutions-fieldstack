<?php
/**
 * Definición de los tours y acceso a la configuración del plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DVS_TR_Tours {

	const OPTION_KEY = 'dvs_tr_opciones';

	/**
	 * Tours disponibles. El guía es uno solo, por lo que una reserva
	 * en cualquiera de los dos bloquea el día completo para el otro.
	 */
	public static function tours() {
		return array(
			'termas' => array(
				'nombre'      => __( 'Tour Termas', 'dvs-tour-reservas' ),
				'inicio'      => '09:30',
				'fin'         => '14:30',
				'descripcion' => __( 'Salida 09:30 · Regreso 14:30', 'dvs-tour-reservas' ),
			),
			'embalse' => array(
				'nombre'      => __( 'Tour Embalse', 'dvs-tour-reservas' ),
				'inicio'      => '15:00',
				'fin'         => '17:30',
				'descripcion' => __( 'Salida 15:00 · Regreso 17:30', 'dvs-tour-reservas' ),
			),
		);
	}

	public static function existe( $tour ) {
		return array_key_exists( $tour, self::tours() );
	}

	public static function defaults() {
		return array(
			// URL del Botón de Pago del Banco de Chile por tour.
			'pago_url_termas'      => '',
			'pago_url_embalse'     => '',
			// Precios de referencia (CLP) mostrados al cliente.
			'precio_termas'        => 0,
			'precio_embalse'       => 0,
			// Si está activo, se permite reservar ambos tours el mismo día
			// (por ejemplo si se contrata un segundo guía).
			'permitir_mismo_dia'   => 0,
			// Máximo de personas por reserva.
			'max_personas'         => 10,
			// Días hacia adelante que se pueden reservar.
			'dias_anticipacion'    => 90,
			// Minutos que una reserva pendiente de pago retiene el cupo.
			// 0 = retiene indefinidamente hasta que el administrador la gestione.
			'minutos_retencion'    => 0,
			// Correo del negocio que recibe aviso de nuevas reservas.
			'email_notificacion'   => get_option( 'admin_email' ),
		);
	}

	public static function opciones() {
		$guardadas = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $guardadas ) ) {
			$guardadas = array();
		}
		return array_merge( self::defaults(), $guardadas );
	}

	public static function opcion( $clave ) {
		$opciones = self::opciones();
		return isset( $opciones[ $clave ] ) ? $opciones[ $clave ] : null;
	}

	public static function url_pago( $tour ) {
		return (string) self::opcion( 'termas' === $tour ? 'pago_url_termas' : 'pago_url_embalse' );
	}

	public static function precio( $tour ) {
		return (int) self::opcion( 'termas' === $tour ? 'precio_termas' : 'precio_embalse' );
	}
}
