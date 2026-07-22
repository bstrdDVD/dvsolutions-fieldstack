<?php
/**
 * Parte pública: shortcode [dvs_tour_calendario], endpoints AJAX y
 * redirección al Botón de Pago del Banco de Chile.
 *
 * El calendario incluye un selector de idioma ES / EN / PT; todos los
 * textos visibles se traducen en el navegador sin recargar la página.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DVS_TR_Front {

	public static function init() {
		add_shortcode( 'dvs_tour_calendario', array( __CLASS__, 'shortcode_calendario' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'registrar_assets' ) );

		add_action( 'wp_ajax_dvs_tr_disponibilidad', array( __CLASS__, 'ajax_disponibilidad' ) );
		add_action( 'wp_ajax_nopriv_dvs_tr_disponibilidad', array( __CLASS__, 'ajax_disponibilidad' ) );
		add_action( 'wp_ajax_dvs_tr_reservar', array( __CLASS__, 'ajax_reservar' ) );
		add_action( 'wp_ajax_nopriv_dvs_tr_reservar', array( __CLASS__, 'ajax_reservar' ) );
	}

	public static function registrar_assets() {
		wp_register_style(
			'dvs-tr-calendario',
			DVS_TR_PLUGIN_URL . 'assets/css/calendario.css',
			array(),
			DVS_TR_VERSION
		);
		wp_register_script(
			'dvs-tr-calendario',
			DVS_TR_PLUGIN_URL . 'assets/js/calendario.js',
			array(),
			DVS_TR_VERSION,
			true
		);
	}

	/**
	 * Shortcode. Acepta el atributo idioma para fijar el idioma inicial:
	 * [dvs_tour_calendario idioma="es"] (es, en o pt).
	 */
	public static function shortcode_calendario( $atts = array() ) {
		$atts   = shortcode_atts( array( 'idioma' => DVS_TR_I18n::IDIOMA_DEFECTO ), $atts, 'dvs_tour_calendario' );
		$idioma = DVS_TR_I18n::valido( $atts['idioma'] ) ? $atts['idioma'] : DVS_TR_I18n::IDIOMA_DEFECTO;

		wp_enqueue_style( 'dvs-tr-calendario' );
		wp_enqueue_script( 'dvs-tr-calendario' );

		$precios = array();
		foreach ( array_keys( DVS_TR_Tours::tours() ) as $clave ) {
			$precios[ $clave ] = DVS_TR_Tours::precio( $clave );
		}

		wp_localize_script( 'dvs-tr-calendario', 'dvsTrConfig', array(
			'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
			'nonce'       => wp_create_nonce( 'dvs_tr_publico' ),
			'idioma'      => $idioma,
			'i18n'        => DVS_TR_I18n::cadenas(),
			'precios'     => $precios,
			'maxPersonas' => (int) DVS_TR_Tours::opcion( 'max_personas' ),
		) );

		$t = function ( $clave ) use ( $idioma ) {
			return DVS_TR_I18n::t( $idioma, $clave );
		};

		ob_start();
		?>
		<div class="dvs-tr" id="dvs-tr-app">
			<div class="dvs-tr-idiomas" role="group" aria-label="Idioma / Language / Idioma">
				<?php foreach ( DVS_TR_I18n::idiomas() as $cod ) : ?>
					<button type="button" class="dvs-tr-idioma<?php echo $cod === $idioma ? ' dvs-tr-idioma--activo' : ''; ?>" data-lang="<?php echo esc_attr( $cod ); ?>">
						<?php echo esc_html( strtoupper( $cod ) ); ?>
					</button>
				<?php endforeach; ?>
			</div>

			<div class="dvs-tr-cabecera">
				<button type="button" class="dvs-tr-nav" data-dir="-1" aria-label="<?php echo esc_attr( $t( 'mes_anterior' ) ); ?>">‹</button>
				<h3 class="dvs-tr-mes" aria-live="polite"></h3>
				<button type="button" class="dvs-tr-nav" data-dir="1" aria-label="<?php echo esc_attr( $t( 'mes_siguiente' ) ); ?>">›</button>
			</div>

			<div class="dvs-tr-leyenda">
				<span><i class="dvs-tr-punto dvs-tr-punto--libre"></i> <span data-dvs-i18n="leyenda_disponible"><?php echo esc_html( $t( 'leyenda_disponible' ) ); ?></span></span>
				<span><i class="dvs-tr-punto dvs-tr-punto--parcial"></i> <span data-dvs-i18n="leyenda_guia"><?php echo esc_html( $t( 'leyenda_guia' ) ); ?></span></span>
				<span><i class="dvs-tr-punto dvs-tr-punto--lleno"></i> <span data-dvs-i18n="leyenda_lleno"><?php echo esc_html( $t( 'leyenda_lleno' ) ); ?></span></span>
			</div>

			<div class="dvs-tr-grilla" role="grid"></div>
			<p class="dvs-tr-estado" aria-live="polite"></p>

			<!-- Panel de reserva -->
			<div class="dvs-tr-panel" hidden>
				<h4 class="dvs-tr-panel-fecha"></h4>
				<div class="dvs-tr-opciones"></div>

				<form class="dvs-tr-form" hidden>
					<h5 class="dvs-tr-form-titulo"></h5>
					<label>
						<span data-dvs-i18n="lbl_nombre"><?php echo esc_html( $t( 'lbl_nombre' ) ); ?></span>
						<input type="text" name="nombre" required maxlength="120" autocomplete="name" />
					</label>
					<label>
						<span data-dvs-i18n="lbl_email"><?php echo esc_html( $t( 'lbl_email' ) ); ?></span>
						<input type="email" name="email" required maxlength="120" autocomplete="email" />
					</label>
					<label>
						<span data-dvs-i18n="lbl_telefono"><?php echo esc_html( $t( 'lbl_telefono' ) ); ?></span>
						<input type="tel" name="telefono" required maxlength="40" autocomplete="tel" placeholder="+56 9 …" />
					</label>
					<label>
						<span data-dvs-i18n="lbl_personas"><?php echo esc_html( $t( 'lbl_personas' ) ); ?></span>
						<input type="number" name="personas" min="1" value="1" required />
					</label>
					<p class="dvs-tr-aviso" data-dvs-i18n="aviso_pago"><?php echo esc_html( $t( 'aviso_pago' ) ); ?></p>
					<button type="submit" class="dvs-tr-btn-pagar" data-dvs-i18n="btn_pagar"><?php echo esc_html( $t( 'btn_pagar' ) ); ?></button>
				</form>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * AJAX: disponibilidad de un mes.
	 */
	public static function ajax_disponibilidad() {
		check_ajax_referer( 'dvs_tr_publico', 'nonce' );

		$anio = isset( $_GET['anio'] ) ? (int) $_GET['anio'] : 0;
		$mes  = isset( $_GET['mes'] ) ? (int) $_GET['mes'] : 0;
		if ( $anio < 2020 || $anio > 2100 || $mes < 1 || $mes > 12 ) {
			wp_send_json_error( array( 'mensaje' => __( 'Mes inválido.', 'dvs-tour-reservas' ) ), 400 );
		}

		$dias_mes = (int) gmdate( 't', gmmktime( 0, 0, 0, $mes, 1, $anio ) );
		$desde    = sprintf( '%04d-%02d-01', $anio, $mes );
		$hasta    = sprintf( '%04d-%02d-%02d', $anio, $mes, $dias_mes );

		$ocupados = DVS_TR_DB::tours_ocupados_por_fecha( $desde, $hasta );

		$hoy    = current_time( 'Y-m-d' );
		$limite = gmdate( 'Y-m-d', strtotime( $hoy . ' + ' . (int) DVS_TR_Tours::opcion( 'dias_anticipacion' ) . ' days' ) );

		$dias = array();
		for ( $d = 1; $d <= $dias_mes; $d++ ) {
			$fecha       = sprintf( '%04d-%02d-%02d', $anio, $mes, $d );
			$del_dia     = isset( $ocupados[ $fecha ] ) ? $ocupados[ $fecha ] : array();
			$reservable  = ( $fecha > $hoy && $fecha <= $limite );

			$disponibles = array();
			foreach ( array_keys( DVS_TR_Tours::tours() ) as $tour ) {
				if ( $reservable && DVS_TR_DB::tour_disponible( $tour, $fecha, $del_dia ) ) {
					$disponibles[] = $tour;
				}
			}

			$dias[ $fecha ] = array(
				'disponibles' => $disponibles,
				'ocupados'    => array_values( $del_dia ),
			);
		}

		wp_send_json_success( array( 'dias' => $dias ) );
	}

	/**
	 * AJAX: crear reserva y devolver la URL del Botón de Pago del Banco de Chile.
	 */
	public static function ajax_reservar() {
		check_ajax_referer( 'dvs_tr_publico', 'nonce' );

		$tour     = isset( $_POST['tour'] ) ? sanitize_key( $_POST['tour'] ) : '';
		$fecha    = isset( $_POST['fecha'] ) ? sanitize_text_field( wp_unslash( $_POST['fecha'] ) ) : '';
		$nombre   = isset( $_POST['nombre'] ) ? sanitize_text_field( wp_unslash( $_POST['nombre'] ) ) : '';
		$email    = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$telefono = isset( $_POST['telefono'] ) ? sanitize_text_field( wp_unslash( $_POST['telefono'] ) ) : '';
		$personas = isset( $_POST['personas'] ) ? (int) $_POST['personas'] : 0;
		$idioma   = isset( $_POST['idioma'] ) ? sanitize_key( $_POST['idioma'] ) : DVS_TR_I18n::IDIOMA_DEFECTO;
		if ( ! DVS_TR_I18n::valido( $idioma ) ) {
			$idioma = DVS_TR_I18n::IDIOMA_DEFECTO;
		}

		if ( ! DVS_TR_Tours::existe( $tour ) ) {
			wp_send_json_error( array( 'mensaje' => __( 'Tour inválido.', 'dvs-tour-reservas' ) ), 400 );
		}
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $fecha ) || ! strtotime( $fecha ) ) {
			wp_send_json_error( array( 'mensaje' => __( 'Fecha inválida.', 'dvs-tour-reservas' ) ), 400 );
		}
		$hoy    = current_time( 'Y-m-d' );
		$limite = gmdate( 'Y-m-d', strtotime( $hoy . ' + ' . (int) DVS_TR_Tours::opcion( 'dias_anticipacion' ) . ' days' ) );
		if ( $fecha <= $hoy || $fecha > $limite ) {
			wp_send_json_error( array( 'mensaje' => __( 'La fecha está fuera del rango permitido de reserva.', 'dvs-tour-reservas' ) ), 400 );
		}
		if ( '' === $nombre || ! is_email( $email ) || '' === $telefono ) {
			wp_send_json_error( array( 'mensaje' => __( 'Completa todos los datos de contacto.', 'dvs-tour-reservas' ) ), 400 );
		}
		$max = (int) DVS_TR_Tours::opcion( 'max_personas' );
		if ( $personas < 1 || $personas > $max ) {
			wp_send_json_error( array(
				'mensaje' => sprintf(
					/* translators: %d: máximo de personas */
					__( 'El número de personas debe estar entre 1 y %d.', 'dvs-tour-reservas' ),
					$max
				),
			), 400 );
		}

		// Flujo con WooCommerce: crea el pedido y redirige a su página de pago
		// (donde se cobra con Banchile Pagos). WooCommerce envía sus propios
		// correos de pedido, por lo que aquí no duplicamos la notificación.
		if ( DVS_TR_Tours::wc_activo() ) {
			$resultado = DVS_TR_WooCommerce::crear_pedido( $tour, $fecha, $personas, $nombre, $email, $telefono );
			if ( is_wp_error( $resultado ) ) {
				$codigo = ( 'dvs_tr_no_disponible' === $resultado->get_error_code() ) ? 'err_no_disponible' : '';
				wp_send_json_error( array(
					'mensaje' => $resultado->get_error_message(),
					'codigo'  => $codigo,
				), 409 );
			}
			wp_send_json_success( array(
				'codigo'  => $resultado['codigo'],
				'pagoUrl' => $resultado['pagoUrl'],
			) );
		}

		// Flujo heredado (sin WooCommerce): reserva simple + enlace de pago fijo.
		$reserva = DVS_TR_DB::crear_reserva( $tour, $fecha, $nombre, $email, $telefono, $personas );
		if ( is_wp_error( $reserva ) ) {
			// El JS traduce este error con la clave indicada.
			wp_send_json_error( array(
				'mensaje' => $reserva->get_error_message(),
				'codigo'  => 'err_no_disponible',
			), 409 );
		}

		self::notificar_reserva( $reserva, $nombre, $email, $telefono, $personas, $idioma );

		wp_send_json_success( array(
			'codigo'  => $reserva['codigo'],
			'pagoUrl' => DVS_TR_Tours::url_pago( $tour ),
		) );
	}

	/**
	 * Correo al negocio (siempre en español) y confirmación al cliente
	 * en el idioma que usó en el calendario.
	 */
	private static function notificar_reserva( $reserva, $nombre, $email, $telefono, $personas, $idioma ) {
		$tour_es = DVS_TR_I18n::tour( 'es', $reserva['tour'] );

		$cuerpo = sprintf(
			"%s\n\n%s: %s\n%s: %s (%s)\n%s: %s\n%s: %s\n%s: %s\n%s: %d\n%s: %s\n%s: %s\n",
			__( 'Nueva reserva de tour (pendiente de pago):', 'dvs-tour-reservas' ),
			__( 'Código', 'dvs-tour-reservas' ), $reserva['codigo'],
			__( 'Tour', 'dvs-tour-reservas' ), $tour_es['nombre'], $tour_es['descripcion'],
			__( 'Fecha', 'dvs-tour-reservas' ), $reserva['fecha'],
			__( 'Nombre', 'dvs-tour-reservas' ), $nombre,
			__( 'Email', 'dvs-tour-reservas' ), $email,
			__( 'Personas', 'dvs-tour-reservas' ), $personas,
			__( 'Teléfono', 'dvs-tour-reservas' ), $telefono,
			__( 'Idioma del cliente', 'dvs-tour-reservas' ), strtoupper( $idioma )
		);

		$destino = sanitize_email( DVS_TR_Tours::opcion( 'email_notificacion' ) );
		if ( $destino ) {
			wp_mail( $destino, __( 'Nueva reserva de tour', 'dvs-tour-reservas' ), $cuerpo );
		}

		// Confirmación al cliente en su idioma.
		$tour_cliente = DVS_TR_I18n::tour( $idioma, $reserva['tour'] );
		$cuerpo_cliente = sprintf(
			"%s %s\n\n%s: %s\n%s: %s (%s)\n%s: %s\n\n%s\n",
			DVS_TR_I18n::t( $idioma, 'email_hola' ), $nombre,
			DVS_TR_I18n::t( $idioma, 'email_codigo' ), $reserva['codigo'],
			DVS_TR_I18n::t( $idioma, 'email_tour' ), $tour_cliente['nombre'], $tour_cliente['descripcion'],
			DVS_TR_I18n::t( $idioma, 'email_fecha' ), $reserva['fecha'],
			DVS_TR_I18n::t( $idioma, 'email_pago' )
		);
		wp_mail( $email, DVS_TR_I18n::t( $idioma, 'email_asunto' ), $cuerpo_cliente );
	}
}
