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
	 * Los nombres y descripciones se traducen según el idioma pedido.
	 */
	public static function tours( $idioma = 'es' ) {
		$horarios = array(
			'termas'  => array( 'inicio' => '09:30', 'fin' => '14:30' ),
			'embalse' => array( 'inicio' => '15:00', 'fin' => '17:30' ),
		);
		$tours = array();
		foreach ( $horarios as $clave => $horario ) {
			$tours[ $clave ] = array_merge( $horario, DVS_TR_I18n::tour( $idioma, $clave ) );
		}
		return $tours;
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
			// Integración con WooCommerce: al reservar se crea un pedido y el
			// pago se cobra con la pasarela de WooCommerce (Banchile Pagos).
			'usar_woocommerce'     => 1,
			// ID del producto de WooCommerce asociado a cada tour.
			'producto_termas'      => 0,
			'producto_embalse'     => 0,
			// Correo del negocio que recibe aviso de nuevas reservas.
			'email_notificacion'   => get_option( 'admin_email' ),
			// Traductor global del sitio (botón flotante 🌐 ES/EN/PT).
			'traductor_activo'     => 1,
			// Frases extra del traductor, una por línea:
			// "texto español || english || português".
			'frases_personalizadas' => '',
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

	/**
	 * ID del producto de WooCommerce asociado a un tour.
	 */
	public static function producto_id( $tour ) {
		return (int) self::opcion( 'termas' === $tour ? 'producto_termas' : 'producto_embalse' );
	}

	/**
	 * ¿Está operativa la integración con WooCommerce?
	 * Requiere que la opción esté activa, que WooCommerce esté cargado y que
	 * ambos tours tengan un producto asociado.
	 */
	public static function wc_activo() {
		if ( ! self::opcion( 'usar_woocommerce' ) ) {
			return false;
		}
		if ( ! class_exists( 'WooCommerce' ) ) {
			return false;
		}
		return self::producto_id( 'termas' ) > 0 && self::producto_id( 'embalse' ) > 0;
	}
}
