<?php
/**
 * Administración: listado de reservas y ajustes (URLs del Botón de Pago
 * del Banco de Chile, precios y reglas de reserva).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DVS_TR_Admin {

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'registrar_ajustes' ) );
		add_action( 'admin_post_dvs_tr_estado', array( __CLASS__, 'accion_cambiar_estado' ) );
	}

	public static function menu() {
		add_menu_page(
			__( 'Reservas de Tours', 'dvs-tour-reservas' ),
			__( 'Tours', 'dvs-tour-reservas' ),
			'manage_options',
			'dvs-tr-reservas',
			array( __CLASS__, 'pagina_reservas' ),
			'dashicons-calendar-alt',
			26
		);
		add_submenu_page(
			'dvs-tr-reservas',
			__( 'Ajustes de Tours', 'dvs-tour-reservas' ),
			__( 'Ajustes', 'dvs-tour-reservas' ),
			'manage_options',
			'dvs-tr-ajustes',
			array( __CLASS__, 'pagina_ajustes' )
		);
	}

	public static function registrar_ajustes() {
		register_setting( 'dvs_tr_ajustes', DVS_TR_Tours::OPTION_KEY, array(
			'type'              => 'array',
			'sanitize_callback' => array( __CLASS__, 'sanitizar_ajustes' ),
		) );
	}

	public static function sanitizar_ajustes( $entrada ) {
		$defaults = DVS_TR_Tours::defaults();
		$entrada  = is_array( $entrada ) ? $entrada : array();

		return array(
			'pago_url_termas'    => esc_url_raw( isset( $entrada['pago_url_termas'] ) ? $entrada['pago_url_termas'] : '' ),
			'pago_url_embalse'   => esc_url_raw( isset( $entrada['pago_url_embalse'] ) ? $entrada['pago_url_embalse'] : '' ),
			'precio_termas'      => max( 0, (int) ( isset( $entrada['precio_termas'] ) ? $entrada['precio_termas'] : 0 ) ),
			'precio_embalse'     => max( 0, (int) ( isset( $entrada['precio_embalse'] ) ? $entrada['precio_embalse'] : 0 ) ),
			'capacidad_motos'    => min( 50, max( 1, (int) ( isset( $entrada['capacidad_motos'] ) ? $entrada['capacidad_motos'] : $defaults['capacidad_motos'] ) ) ),
			'max_motos'          => min( 50, max( 1, (int) ( isset( $entrada['max_motos'] ) ? $entrada['max_motos'] : $defaults['max_motos'] ) ) ),
			'dias_termas'        => self::sanitizar_dias( isset( $entrada['dias_termas'] ) ? $entrada['dias_termas'] : array() ),
			'dias_embalse'       => self::sanitizar_dias( isset( $entrada['dias_embalse'] ) ? $entrada['dias_embalse'] : array() ),
			'fechas_festivas'    => self::sanitizar_fechas( isset( $entrada['fechas_festivas'] ) ? $entrada['fechas_festivas'] : '' ),
			'fechas_cerradas'    => self::sanitizar_fechas( isset( $entrada['fechas_cerradas'] ) ? $entrada['fechas_cerradas'] : '' ),
			'dias_anticipacion'  => min( 365, max( 1, (int) ( isset( $entrada['dias_anticipacion'] ) ? $entrada['dias_anticipacion'] : $defaults['dias_anticipacion'] ) ) ),
			'minutos_retencion'  => max( 0, (int) ( isset( $entrada['minutos_retencion'] ) ? $entrada['minutos_retencion'] : 0 ) ),
			'email_notificacion' => sanitize_email( isset( $entrada['email_notificacion'] ) ? $entrada['email_notificacion'] : $defaults['email_notificacion'] ),
			'usar_woocommerce'   => empty( $entrada['usar_woocommerce'] ) ? 0 : 1,
			'producto_termas'    => max( 0, (int) ( isset( $entrada['producto_termas'] ) ? $entrada['producto_termas'] : 0 ) ),
			'producto_embalse'   => max( 0, (int) ( isset( $entrada['producto_embalse'] ) ? $entrada['producto_embalse'] : 0 ) ),
			'traductor_activo'   => empty( $entrada['traductor_activo'] ) ? 0 : 1,
			'frases_personalizadas' => sanitize_textarea_field( isset( $entrada['frases_personalizadas'] ) ? $entrada['frases_personalizadas'] : '' ),
		);
	}

	/**
	 * Sanitiza una lista de días de la semana (0=Dom … 6=Sáb).
	 */
	private static function sanitizar_dias( $dias ) {
		if ( ! is_array( $dias ) ) {
			return array();
		}
		$out = array();
		foreach ( $dias as $d ) {
			$d = (int) $d;
			if ( $d >= 0 && $d <= 6 && ! in_array( $d, $out, true ) ) {
				$out[] = $d;
			}
		}
		sort( $out );
		return $out;
	}

	/**
	 * Sanitiza un textarea de fechas: deja solo YYYY-MM-DD válidas, una por línea.
	 */
	private static function sanitizar_fechas( $texto ) {
		$out = array();
		foreach ( preg_split( '/[\r\n,]+/', (string) $texto ) as $linea ) {
			$linea = trim( $linea );
			if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $linea ) && strtotime( $linea ) ) {
				$out[] = $linea;
			}
		}
		return implode( "\n", array_unique( $out ) );
	}

	public static function accion_cambiar_estado() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Sin permisos.', 'dvs-tour-reservas' ) );
		}
		$id     = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;
		$estado = isset( $_GET['estado'] ) ? sanitize_key( $_GET['estado'] ) : '';
		check_admin_referer( 'dvs_tr_estado_' . $id );

		$validos = array( DVS_TR_DB::ESTADO_PENDIENTE, DVS_TR_DB::ESTADO_PAGADA, DVS_TR_DB::ESTADO_CANCELADA );
		if ( $id && in_array( $estado, $validos, true ) ) {
			DVS_TR_DB::cambiar_estado( $id, $estado );
		}
		wp_safe_redirect( admin_url( 'admin.php?page=dvs-tr-reservas' ) );
		exit;
	}

	private static function enlace_estado( $id, $estado, $texto ) {
		$url = wp_nonce_url(
			admin_url( 'admin-post.php?action=dvs_tr_estado&id=' . (int) $id . '&estado=' . $estado ),
			'dvs_tr_estado_' . (int) $id
		);
		return '<a href="' . esc_url( $url ) . '">' . esc_html( $texto ) . '</a>';
	}

	public static function pagina_reservas() {
		$reservas = DVS_TR_DB::listar();
		$tours    = DVS_TR_Tours::tours();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Reservas de Tours', 'dvs-tour-reservas' ); ?></h1>
			<p>
				<?php esc_html_e( 'Una reserva pendiente o pagada bloquea el día para el otro tour (guía único). Marca como "pagada" cuando confirmes el pago en el Banco de Chile, o cancela para liberar el cupo.', 'dvs-tour-reservas' ); ?>
			</p>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Código', 'dvs-tour-reservas' ); ?></th>
						<th><?php esc_html_e( 'Fecha', 'dvs-tour-reservas' ); ?></th>
						<th><?php esc_html_e( 'Tour', 'dvs-tour-reservas' ); ?></th>
						<th><?php esc_html_e( 'Cliente', 'dvs-tour-reservas' ); ?></th>
						<th><?php esc_html_e( 'Contacto', 'dvs-tour-reservas' ); ?></th>
						<th><?php esc_html_e( 'Motos', 'dvs-tour-reservas' ); ?></th>
						<th><?php esc_html_e( 'Estado', 'dvs-tour-reservas' ); ?></th>
						<th><?php esc_html_e( 'Acciones', 'dvs-tour-reservas' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php if ( empty( $reservas ) ) : ?>
					<tr><td colspan="8"><?php esc_html_e( 'Aún no hay reservas.', 'dvs-tour-reservas' ); ?></td></tr>
				<?php else : ?>
					<?php foreach ( $reservas as $r ) : ?>
						<tr>
							<td><code><?php echo esc_html( $r->codigo ); ?></code></td>
							<td><?php echo esc_html( $r->fecha ); ?></td>
							<td><?php echo esc_html( isset( $tours[ $r->tour ] ) ? $tours[ $r->tour ]['nombre'] : $r->tour ); ?></td>
							<td><?php echo esc_html( $r->nombre ); ?></td>
							<td><?php echo esc_html( $r->email ); ?><br/><?php echo esc_html( $r->telefono ); ?></td>
							<td><?php echo isset( $r->motos ) ? (int) $r->motos : (int) $r->personas; ?></td>
							<td><strong><?php echo esc_html( $r->estado ); ?></strong></td>
							<td>
								<?php
								$acciones = array();
								if ( DVS_TR_DB::ESTADO_PAGADA !== $r->estado ) {
									$acciones[] = self::enlace_estado( $r->id, DVS_TR_DB::ESTADO_PAGADA, __( 'Marcar pagada', 'dvs-tour-reservas' ) );
								}
								if ( DVS_TR_DB::ESTADO_CANCELADA !== $r->estado ) {
									$acciones[] = self::enlace_estado( $r->id, DVS_TR_DB::ESTADO_CANCELADA, __( 'Cancelar', 'dvs-tour-reservas' ) );
								}
								if ( DVS_TR_DB::ESTADO_PENDIENTE !== $r->estado ) {
									$acciones[] = self::enlace_estado( $r->id, DVS_TR_DB::ESTADO_PENDIENTE, __( 'Volver a pendiente', 'dvs-tour-reservas' ) );
								}
								echo wp_kses_post( implode( ' · ', $acciones ) );
								?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	public static function pagina_ajustes() {
		$o = DVS_TR_Tours::opciones();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Ajustes de Tours', 'dvs-tour-reservas' ); ?></h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'dvs_tr_ajustes' ); ?>
				<?php $k = DVS_TR_Tours::OPTION_KEY; ?>

				<h2><?php esc_html_e( 'Botón de Pago del Banco de Chile', 'dvs-tour-reservas' ); ?></h2>
				<p class="description">
					<?php esc_html_e( 'Pega aquí los enlaces de cobro generados en tu portal del Banco de Chile (Botón de Pago). El cliente será redirigido a este enlace al confirmar su reserva.', 'dvs-tour-reservas' ); ?>
				</p>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="pago_url_termas"><?php esc_html_e( 'URL de pago — Tour Termas', 'dvs-tour-reservas' ); ?></label></th>
						<td><input type="url" class="regular-text" id="pago_url_termas" name="<?php echo esc_attr( $k ); ?>[pago_url_termas]" value="<?php echo esc_attr( $o['pago_url_termas'] ); ?>" placeholder="https://…" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="pago_url_embalse"><?php esc_html_e( 'URL de pago — Tour Embalse', 'dvs-tour-reservas' ); ?></label></th>
						<td><input type="url" class="regular-text" id="pago_url_embalse" name="<?php echo esc_attr( $k ); ?>[pago_url_embalse]" value="<?php echo esc_attr( $o['pago_url_embalse'] ); ?>" placeholder="https://…" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="precio_termas"><?php esc_html_e( 'Precio Tour Termas (CLP)', 'dvs-tour-reservas' ); ?></label></th>
						<td><input type="number" min="0" id="precio_termas" name="<?php echo esc_attr( $k ); ?>[precio_termas]" value="<?php echo esc_attr( $o['precio_termas'] ); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="precio_embalse"><?php esc_html_e( 'Precio Tour Embalse (CLP)', 'dvs-tour-reservas' ); ?></label></th>
						<td><input type="number" min="0" id="precio_embalse" name="<?php echo esc_attr( $k ); ?>[precio_embalse]" value="<?php echo esc_attr( $o['precio_embalse'] ); ?>" /></td>
					</tr>
				</table>

				<h2><?php esc_html_e( 'Cobro con WooCommerce (Banchile Pagos)', 'dvs-tour-reservas' ); ?></h2>
				<?php
				$wc_ok        = class_exists( 'WooCommerce' );
				$productos_wc = array();
				if ( $wc_ok && function_exists( 'wc_get_products' ) ) {
					foreach ( wc_get_products( array( 'limit' => 100, 'status' => 'publish', 'orderby' => 'title', 'order' => 'ASC' ) ) as $p ) {
						$productos_wc[ $p->get_id() ] = $p->get_name();
					}
				}
				?>
				<?php if ( ! $wc_ok ) : ?>
					<p class="description" style="color:#b32d2e;">
						<?php esc_html_e( 'WooCommerce no está activo. Actívalo para cobrar las reservas con Banchile Pagos; mientras tanto se usa el enlace de pago fijo de arriba.', 'dvs-tour-reservas' ); ?>
					</p>
				<?php endif; ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Usar WooCommerce', 'dvs-tour-reservas' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( $k ); ?>[usar_woocommerce]" value="1" <?php checked( $o['usar_woocommerce'] ); ?> />
								<?php esc_html_e( 'Al reservar, crear un pedido de WooCommerce y cobrar con la pasarela (Banchile Pagos). Recomendado.', 'dvs-tour-reservas' ); ?>
							</label>
							<p class="description"><?php esc_html_e( 'El pedido pasa a "pagado" automáticamente cuando Banchile confirma el pago (webhook), y la reserva bloquea el día para el otro tour. Si un pago se cancela o falla, el día se libera solo.', 'dvs-tour-reservas' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="producto_termas"><?php esc_html_e( 'Producto — Tour Termas', 'dvs-tour-reservas' ); ?></label></th>
						<td>
							<?php if ( $productos_wc ) : ?>
								<select id="producto_termas" name="<?php echo esc_attr( $k ); ?>[producto_termas]">
									<option value="0"><?php esc_html_e( '— Selecciona un producto —', 'dvs-tour-reservas' ); ?></option>
									<?php foreach ( $productos_wc as $pid => $pnombre ) : ?>
										<option value="<?php echo esc_attr( $pid ); ?>" <?php selected( (int) $o['producto_termas'], $pid ); ?>><?php echo esc_html( $pnombre . ' (#' . $pid . ')' ); ?></option>
									<?php endforeach; ?>
								</select>
							<?php else : ?>
								<input type="number" min="0" id="producto_termas" name="<?php echo esc_attr( $k ); ?>[producto_termas]" value="<?php echo esc_attr( $o['producto_termas'] ); ?>" />
								<p class="description"><?php esc_html_e( 'ID del producto de WooCommerce del Tour Termas.', 'dvs-tour-reservas' ); ?></p>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="producto_embalse"><?php esc_html_e( 'Producto — Tour Embalse', 'dvs-tour-reservas' ); ?></label></th>
						<td>
							<?php if ( $productos_wc ) : ?>
								<select id="producto_embalse" name="<?php echo esc_attr( $k ); ?>[producto_embalse]">
									<option value="0"><?php esc_html_e( '— Selecciona un producto —', 'dvs-tour-reservas' ); ?></option>
									<?php foreach ( $productos_wc as $pid => $pnombre ) : ?>
										<option value="<?php echo esc_attr( $pid ); ?>" <?php selected( (int) $o['producto_embalse'], $pid ); ?>><?php echo esc_html( $pnombre . ' (#' . $pid . ')' ); ?></option>
									<?php endforeach; ?>
								</select>
							<?php else : ?>
								<input type="number" min="0" id="producto_embalse" name="<?php echo esc_attr( $k ); ?>[producto_embalse]" value="<?php echo esc_attr( $o['producto_embalse'] ); ?>" />
								<p class="description"><?php esc_html_e( 'ID del producto de WooCommerce del Tour Embalse.', 'dvs-tour-reservas' ); ?></p>
							<?php endif; ?>
						</td>
					</tr>
				</table>

				<h2><?php esc_html_e( 'Días de operación y cupo', 'dvs-tour-reservas' ); ?></h2>
				<?php
				$nombres_dias = array(
					1 => __( 'Lun', 'dvs-tour-reservas' ),
					2 => __( 'Mar', 'dvs-tour-reservas' ),
					3 => __( 'Mié', 'dvs-tour-reservas' ),
					4 => __( 'Jue', 'dvs-tour-reservas' ),
					5 => __( 'Vie', 'dvs-tour-reservas' ),
					6 => __( 'Sáb', 'dvs-tour-reservas' ),
					0 => __( 'Dom', 'dvs-tour-reservas' ),
				);
				$dias_termas  = is_array( $o['dias_termas'] ) ? array_map( 'intval', $o['dias_termas'] ) : array();
				$dias_embalse = is_array( $o['dias_embalse'] ) ? array_map( 'intval', $o['dias_embalse'] ) : array();
				?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Días que opera Tour Termas', 'dvs-tour-reservas' ); ?></th>
						<td>
							<?php foreach ( $nombres_dias as $num => $etiqueta ) : ?>
								<label style="margin-right:12px;display:inline-block;">
									<input type="checkbox" name="<?php echo esc_attr( $k ); ?>[dias_termas][]" value="<?php echo esc_attr( $num ); ?>" <?php checked( in_array( $num, $dias_termas, true ) ); ?> />
									<?php echo esc_html( $etiqueta ); ?>
								</label>
							<?php endforeach; ?>
							<p class="description"><?php esc_html_e( 'Por defecto: sábado y domingo.', 'dvs-tour-reservas' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Días que opera Tour Embalse', 'dvs-tour-reservas' ); ?></th>
						<td>
							<?php foreach ( $nombres_dias as $num => $etiqueta ) : ?>
								<label style="margin-right:12px;display:inline-block;">
									<input type="checkbox" name="<?php echo esc_attr( $k ); ?>[dias_embalse][]" value="<?php echo esc_attr( $num ); ?>" <?php checked( in_array( $num, $dias_embalse, true ) ); ?> />
									<?php echo esc_html( $etiqueta ); ?>
								</label>
							<?php endforeach; ?>
							<p class="description"><?php esc_html_e( 'Por defecto: solo sábado.', 'dvs-tour-reservas' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="capacidad_motos"><?php esc_html_e( 'Cupo de motos por tour por día', 'dvs-tour-reservas' ); ?></label></th>
						<td>
							<input type="number" min="1" max="50" id="capacidad_motos" name="<?php echo esc_attr( $k ); ?>[capacidad_motos]" value="<?php echo esc_attr( $o['capacidad_motos'] ); ?>" />
							<p class="description"><?php esc_html_e( 'Se aceptan reservas hasta agotar estas motos (por defecto 3). Varias reservas comparten el cupo del día.', 'dvs-tour-reservas' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="max_motos"><?php esc_html_e( 'Máximo de motos por reserva', 'dvs-tour-reservas' ); ?></label></th>
						<td><input type="number" min="1" max="50" id="max_motos" name="<?php echo esc_attr( $k ); ?>[max_motos]" value="<?php echo esc_attr( $o['max_motos'] ); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="fechas_festivas"><?php esc_html_e( 'Festivos habilitados (solo Termas)', 'dvs-tour-reservas' ); ?></label></th>
						<td>
							<textarea id="fechas_festivas" name="<?php echo esc_attr( $k ); ?>[fechas_festivas]" rows="3" class="regular-text code" placeholder="2026-09-18"><?php echo esc_textarea( $o['fechas_festivas'] ); ?></textarea>
							<p class="description"><?php esc_html_e( 'Una fecha por línea (formato AAAA-MM-DD). En estos días se habilita solo el Tour Termas, aunque no sea fin de semana.', 'dvs-tour-reservas' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="fechas_cerradas"><?php esc_html_e( 'Fechas cerradas (sin tours)', 'dvs-tour-reservas' ); ?></label></th>
						<td>
							<textarea id="fechas_cerradas" name="<?php echo esc_attr( $k ); ?>[fechas_cerradas]" rows="3" class="regular-text code" placeholder="2026-12-25"><?php echo esc_textarea( $o['fechas_cerradas'] ); ?></textarea>
							<p class="description"><?php esc_html_e( 'Una fecha por línea (AAAA-MM-DD). Bloquea por completo esos días (ej. un sábado que no operarás).', 'dvs-tour-reservas' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="dias_anticipacion"><?php esc_html_e( 'Días de anticipación máxima', 'dvs-tour-reservas' ); ?></label></th>
						<td><input type="number" min="1" max="365" id="dias_anticipacion" name="<?php echo esc_attr( $k ); ?>[dias_anticipacion]" value="<?php echo esc_attr( $o['dias_anticipacion'] ); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="minutos_retencion"><?php esc_html_e( 'Retención del cupo sin pago (minutos)', 'dvs-tour-reservas' ); ?></label></th>
						<td>
							<input type="number" min="0" id="minutos_retencion" name="<?php echo esc_attr( $k ); ?>[minutos_retencion]" value="<?php echo esc_attr( $o['minutos_retencion'] ); ?>" />
							<p class="description"><?php esc_html_e( '0 = la reserva pendiente retiene el cupo hasta que la gestiones manualmente. Como el Botón de Pago no notifica automáticamente a la web, debes marcar la reserva como "pagada" cuando confirmes el abono en tu banco.', 'dvs-tour-reservas' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="email_notificacion"><?php esc_html_e( 'Correo de notificación', 'dvs-tour-reservas' ); ?></label></th>
						<td><input type="email" class="regular-text" id="email_notificacion" name="<?php echo esc_attr( $k ); ?>[email_notificacion]" value="<?php echo esc_attr( $o['email_notificacion'] ); ?>" /></td>
					</tr>
				</table>

				<h2><?php esc_html_e( 'Traductor del sitio (ES / EN / PT)', 'dvs-tour-reservas' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Botón flotante de idioma', 'dvs-tour-reservas' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( $k ); ?>[traductor_activo]" value="1" <?php checked( $o['traductor_activo'] ); ?> />
								<?php esc_html_e( 'Mostrar el botón 🌐 en la esquina superior derecha de todo el sitio para traducir el contenido a inglés y portugués.', 'dvs-tour-reservas' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="frases_personalizadas"><?php esc_html_e( 'Frases adicionales', 'dvs-tour-reservas' ); ?></label></th>
						<td>
							<textarea id="frases_personalizadas" name="<?php echo esc_attr( $k ); ?>[frases_personalizadas]" rows="8" class="large-text code" placeholder="Bienvenidos a la cordillera || Welcome to the mountains || Bem-vindos à cordilheira"><?php echo esc_textarea( $o['frases_personalizadas'] ); ?></textarea>
							<p class="description">
								<?php esc_html_e( 'Si agregas o cambias textos en tu web y quedan sin traducir, añádelos aquí: una frase por línea con el formato "texto español || inglés || portugués". Estas frases tienen prioridad sobre las traducciones incluidas.', 'dvs-tour-reservas' ); ?>
							</p>
						</td>
					</tr>
				</table>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
