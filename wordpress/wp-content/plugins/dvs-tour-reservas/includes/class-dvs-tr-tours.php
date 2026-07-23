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
			// Cupo de motos por tour por día (se reserva hasta agotarlo).
			'capacidad_motos'      => 3,
			// Máximo de motos por reserva.
			'max_motos'            => 3,
			// Días de la semana que opera cada tour (0=Dom … 6=Sáb).
			// Por defecto: Termas sábado y domingo; Embalse solo sábado.
			'dias_termas'          => array( 6, 0 ),
			'dias_embalse'         => array( 6 ),
			// Fechas de festivos habilitados (solo Termas), una por línea YYYY-MM-DD.
			'fechas_festivas'      => '',
			// Fechas cerradas / excepciones, una por línea YYYY-MM-DD.
			'fechas_cerradas'      => '',
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

	/**
	 * Cupo de motos por día para un tour.
	 */
	public static function capacidad( $tour ) {
		return max( 1, (int) self::opcion( 'capacidad_motos' ) );
	}

	/**
	 * Máximo de motos que puede tomar una sola reserva.
	 */
	public static function max_motos() {
		return max( 1, (int) self::opcion( 'max_motos' ) );
	}

	/**
	 * Días de la semana (0=Dom … 6=Sáb) en que opera un tour.
	 */
	public static function dias_operacion( $tour ) {
		$clave = 'termas' === $tour ? 'dias_termas' : 'dias_embalse';
		$dias  = self::opcion( $clave );
		return is_array( $dias ) ? array_map( 'intval', $dias ) : array();
	}

	/**
	 * Extrae fechas válidas (YYYY-MM-DD) de un texto con una por línea.
	 */
	private static function parse_fechas( $texto ) {
		$out = array();
		foreach ( preg_split( '/[\r\n,]+/', (string) $texto ) as $linea ) {
			$linea = trim( $linea );
			if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $linea ) ) {
				$out[] = $linea;
			}
		}
		return $out;
	}

	public static function es_festivo( $fecha ) {
		return in_array( $fecha, self::parse_fechas( self::opcion( 'fechas_festivas' ) ), true );
	}

	public static function es_cerrado( $fecha ) {
		return in_array( $fecha, self::parse_fechas( self::opcion( 'fechas_cerradas' ) ), true );
	}

	/**
	 * Tours que se ofrecen en una fecha concreta, según el día de la semana,
	 * los festivos habilitados (solo Termas) y las fechas cerradas.
	 *
	 * @return array Lista de claves de tour (ej. array('termas','embalse')).
	 */
	public static function tours_ofrecidos( $fecha ) {
		if ( self::es_cerrado( $fecha ) ) {
			return array();
		}
		$wd        = (int) gmdate( 'w', strtotime( $fecha . ' 12:00:00' ) ); // 0=Dom … 6=Sáb
		$ofrecidos = array();
		foreach ( array_keys( self::tours() ) as $tour ) {
			if ( in_array( $wd, self::dias_operacion( $tour ), true ) ) {
				$ofrecidos[] = $tour;
			}
		}
		// Festivos habilitados: solo Termas.
		if ( self::es_festivo( $fecha ) && ! in_array( 'termas', $ofrecidos, true ) ) {
			$ofrecidos[] = 'termas';
		}
		return $ofrecidos;
	}
}
