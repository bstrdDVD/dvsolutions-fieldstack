<?php
/**
 * Parte pública: shortcode [dvs_tour_calendario], endpoints AJAX y
 * redirección al Botón de Pago del Banco de Chile.
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

	public static function shortcode_calendario() {
		wp_enqueue_style( 'dvs-tr-calendario' );
		wp_enqueue_script( 'dvs-tr-calendario' );

		$tours  = array();
		foreach ( DVS_TR_Tours::tours() as $clave => $tour ) {
			$tours[ $clave ] = array(
				'nombre'      => $tour['nombre'],
				'descripcion' => $tour['descripcion'],
				'precio'      => DVS_TR_Tours::precio( $clave ),
			);
		}

		wp_localize_script( 'dvs-tr-calendario', 'dvsTrConfig', array(
			'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
			'nonce'       => wp_create_nonce( 'dvs_tr_publico' ),
			'tours'       => $tours,
			'maxPersonas' => (int) DVS_TR_Tours::opcion( 'max_personas' ),
			'i18n'        => array(
				'cargando'      => __( 'Cargando disponibilidad…', 'dvs-tour-reservas' ),
				'errorRed'      => __( 'Error de conexión. Inténtalo nuevamente.', 'dvs-tour-reservas' ),
				'sinPago'       => __( 'Reserva registrada. Te contactaremos para coordinar el pago.', 'dvs-tour-reservas' ),
				'redirigiendo'  => __( 'Reserva registrada. Redirigiendo al pago seguro del Banco de Chile…', 'dvs-tour-reservas' ),
				'meses'         => array( 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre' ),
				'dias'          => array( 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom' ),
			),
		) );

		ob_start();
		?>
		<div class="dvs-tr" id="dvs-tr-app">
			<div class="dvs-tr-cabecera">
				<button type="button" class="dvs-tr-nav" data-dir="-1" aria-label="<?php esc_attr_e( 'Mes anterior', 'dvs-tour-reservas' ); ?>">‹</button>
				<h3 class="dvs-tr-mes" aria-live="polite"></h3>
				<button type="button" class="dvs-tr-nav" data-dir="1" aria-label="<?php esc_attr_e( 'Mes siguiente', 'dvs-tour-reservas' ); ?>">›</button>
			</div>

			<div class="dvs-tr-leyenda">
				<span><i class="dvs-tr-punto dvs-tr-punto--libre"></i> <?php esc_html_e( 'Disponible', 'dvs-tour-reservas' ); ?></span>
				<span><i class="dvs-tr-punto dvs-tr-punto--parcial"></i> <?php esc_html_e( 'Guía ocupado (día bloqueado)', 'dvs-tour-reservas' ); ?></span>
				<span><i class="dvs-tr-punto dvs-tr-punto--lleno"></i> <?php esc_html_e( 'No disponible', 'dvs-tour-reservas' ); ?></span>
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
						<?php esc_html_e( 'Nombre completo', 'dvs-tour-reservas' ); ?>
						<input type="text" name="nombre" required maxlength="120" autocomplete="name" />
					</label>
					<label>
						<?php esc_html_e( 'Correo electrónico', 'dvs-tour-reservas' ); ?>
						<input type="email" name="email" required maxlength="120" autocomplete="email" />
					</label>
					<label>
						<?php esc_html_e( 'Teléfono', 'dvs-tour-reservas' ); ?>
						<input type="tel" name="telefono" required maxlength="40" autocomplete="tel" placeholder="+56 9 …" />
					</label>
					<label>
						<?php esc_html_e( 'Número de personas', 'dvs-tour-reservas' ); ?>
						<input type="number" name="personas" min="1" value="1" required />
					</label>
					<p class="dvs-tr-aviso">
						<?php esc_html_e( 'Al confirmar serás redirigido al Botón de Pago del Banco de Chile para completar tu pago de forma segura.', 'dvs-tour-reservas' ); ?>
					</p>
					<button type="submit" class="dvs-tr-btn-pagar">
						<?php esc_html_e( 'Confirmar y pagar', 'dvs-tour-reservas' ); ?>
					</button>
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

		$reserva = DVS_TR_DB::crear_reserva( $tour, $fecha, $nombre, $email, $telefono, $personas );
		if ( is_wp_error( $reserva ) ) {
			wp_send_json_error( array( 'mensaje' => $reserva->get_error_message() ), 409 );
		}

		self::notificar_reserva( $reserva, $nombre, $email, $telefono, $personas );

		wp_send_json_success( array(
			'codigo'  => $reserva['codigo'],
			'pagoUrl' => DVS_TR_Tours::url_pago( $tour ),
		) );
	}

	private static function notificar_reserva( $reserva, $nombre, $email, $telefono, $personas ) {
		$tours = DVS_TR_Tours::tours();
		$tour  = $tours[ $reserva['tour'] ];

		$cuerpo = sprintf(
			"%s\n\n%s: %s\n%s: %s (%s)\n%s: %s\n%s: %s\n%s: %s\n%s: %d\n%s: %s\n",
			__( 'Nueva reserva de tour (pendiente de pago):', 'dvs-tour-reservas' ),
			__( 'Código', 'dvs-tour-reservas' ), $reserva['codigo'],
			__( 'Tour', 'dvs-tour-reservas' ), $tour['nombre'], $tour['descripcion'],
			__( 'Fecha', 'dvs-tour-reservas' ), $reserva['fecha'],
			__( 'Nombre', 'dvs-tour-reservas' ), $nombre,
			__( 'Email', 'dvs-tour-reservas' ), $email,
			__( 'Personas', 'dvs-tour-reservas' ), $personas,
			__( 'Teléfono', 'dvs-tour-reservas' ), $telefono
		);

		$destino = sanitize_email( DVS_TR_Tours::opcion( 'email_notificacion' ) );
		if ( $destino ) {
			wp_mail( $destino, __( 'Nueva reserva de tour', 'dvs-tour-reservas' ), $cuerpo );
		}

		// Confirmación al cliente.
		$cuerpo_cliente = sprintf(
			"%s %s\n\n%s: %s\n%s: %s (%s)\n%s: %s\n\n%s\n",
			__( 'Hola', 'dvs-tour-reservas' ), $nombre,
			__( 'Código de reserva', 'dvs-tour-reservas' ), $reserva['codigo'],
			__( 'Tour', 'dvs-tour-reservas' ), $tour['nombre'], $tour['descripcion'],
			__( 'Fecha', 'dvs-tour-reservas' ), $reserva['fecha'],
			__( 'Tu reserva quedará confirmada una vez completado el pago a través del Botón de Pago del Banco de Chile.', 'dvs-tour-reservas' )
		);
		wp_mail( $email, __( 'Recibimos tu reserva', 'dvs-tour-reservas' ), $cuerpo_cliente );
	}
}
