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
			'permitir_mismo_dia' => empty( $entrada['permitir_mismo_dia'] ) ? 0 : 1,
			'max_personas'       => min( 100, max( 1, (int) ( isset( $entrada['max_personas'] ) ? $entrada['max_personas'] : $defaults['max_personas'] ) ) ),
			'dias_anticipacion'  => min( 365, max( 1, (int) ( isset( $entrada['dias_anticipacion'] ) ? $entrada['dias_anticipacion'] : $defaults['dias_anticipacion'] ) ) ),
			'minutos_retencion'  => max( 0, (int) ( isset( $entrada['minutos_retencion'] ) ? $entrada['minutos_retencion'] : 0 ) ),
			'email_notificacion' => sanitize_email( isset( $entrada['email_notificacion'] ) ? $entrada['email_notificacion'] : $defaults['email_notificacion'] ),
			'traductor_activo'   => empty( $entrada['traductor_activo'] ) ? 0 : 1,
			'frases_personalizadas' => sanitize_textarea_field( isset( $entrada['frases_personalizadas'] ) ? $entrada['frases_personalizadas'] : '' ),
		);
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
						<th><?php esc_html_e( 'Personas', 'dvs-tour-reservas' ); ?></th>
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
							<td><?php echo (int) $r->personas; ?></td>
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

				<h2><?php esc_html_e( 'Reglas de reserva', 'dvs-tour-reservas' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Guía único', 'dvs-tour-reservas' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( $k ); ?>[permitir_mismo_dia]" value="1" <?php checked( $o['permitir_mismo_dia'] ); ?> />
								<?php esc_html_e( 'Permitir reservar ambos tours el mismo día (actívalo solo si cuentas con un segundo guía).', 'dvs-tour-reservas' ); ?>
							</label>
							<p class="description"><?php esc_html_e( 'Desactivado (recomendado): al reservar Termas (09:30–14:30) o Embalse (15:00–17:30), el otro tour queda bloqueado ese día porque el guía está ocupado.', 'dvs-tour-reservas' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="max_personas"><?php esc_html_e( 'Máximo de personas por reserva', 'dvs-tour-reservas' ); ?></label></th>
						<td><input type="number" min="1" max="100" id="max_personas" name="<?php echo esc_attr( $k ); ?>[max_personas]" value="<?php echo esc_attr( $o['max_personas'] ); ?>" /></td>
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
