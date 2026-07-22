<?php
/**
 * Integración con WooCommerce.
 *
 * Cuando un cliente confirma una reserva en el calendario:
 *   1. Se crea la reserva (pendiente), que bloquea el día para el otro tour
 *      (regla de guía único), con bloqueo de fila contra reservas simultáneas.
 *   2. Se crea un pedido de WooCommerce con el producto del tour, la fecha y
 *      los datos de contacto ya cargados.
 *   3. Se redirige al cliente a la página de pago del pedido, donde paga con
 *      la pasarela de WooCommerce (Banchile Pagos).
 *
 * Cuando WooCommerce cambia el estado del pedido:
 *   - Pagado (processing/completed)  → la reserva pasa a "pagada".
 *   - Cancelado/fallido/reembolsado  → la reserva pasa a "cancelada" y libera
 *     el día para ambos tours.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DVS_TR_WooCommerce {

	public static function init() {
		// Sincroniza el estado de la reserva con el del pedido.
		add_action( 'woocommerce_order_status_processing', array( __CLASS__, 'marcar_pagada' ) );
		add_action( 'woocommerce_order_status_completed', array( __CLASS__, 'marcar_pagada' ) );
		add_action( 'woocommerce_order_status_on-hold', array( __CLASS__, 'marcar_pagada' ) );

		add_action( 'woocommerce_order_status_cancelled', array( __CLASS__, 'liberar' ) );
		add_action( 'woocommerce_order_status_failed', array( __CLASS__, 'liberar' ) );
		add_action( 'woocommerce_order_status_refunded', array( __CLASS__, 'liberar' ) );

		// Muestra la fecha y el tour en el detalle del pedido (admin).
		add_action( 'woocommerce_admin_order_data_after_billing_address', array( __CLASS__, 'mostrar_datos_reserva' ) );
	}

	/**
	 * Crea la reserva + el pedido de WooCommerce y devuelve la URL de pago.
	 *
	 * @return array|WP_Error array( 'pagoUrl', 'codigo', 'order_id' ) o error.
	 */
	public static function crear_pedido( $tour, $fecha, $personas, $nombre, $email, $telefono ) {
		if ( ! DVS_TR_Tours::wc_activo() ) {
			return new WP_Error( 'dvs_tr_wc_inactivo', __( 'La reserva en línea no está disponible en este momento.', 'dvs-tour-reservas' ) );
		}

		$producto_id = DVS_TR_Tours::producto_id( $tour );
		$producto    = wc_get_product( $producto_id );
		if ( ! $producto ) {
			return new WP_Error( 'dvs_tr_wc_sin_producto', __( 'El tour no tiene un producto configurado.', 'dvs-tour-reservas' ) );
		}

		// 1. Reserva pendiente (bloquea el cupo con lock de fila).
		$reserva = DVS_TR_DB::crear_reserva( $tour, $fecha, $nombre, $email, $telefono, $personas );
		if ( is_wp_error( $reserva ) ) {
			return $reserva;
		}

		// 2. Pedido de WooCommerce.
		try {
			$order = wc_create_order();
			if ( is_wp_error( $order ) || ! $order ) {
				throw new Exception( 'no_order' );
			}

			$nombres    = self::separar_nombre( $nombre );
			$tour_es    = DVS_TR_I18n::tour( 'es', $tour );
			$item_id    = $order->add_product( $producto, 1 );

			if ( $item_id ) {
				$item = $order->get_item( $item_id );
				if ( $item ) {
					$item->add_meta_data( __( 'Tour', 'dvs-tour-reservas' ), $tour_es['nombre'], true );
					$item->add_meta_data( __( 'Fecha del tour', 'dvs-tour-reservas' ), $fecha, true );
					$item->add_meta_data( __( 'Horario', 'dvs-tour-reservas' ), $tour_es['descripcion'], true );
					$item->add_meta_data( __( 'Personas', 'dvs-tour-reservas' ), $personas, true );
					$item->add_meta_data( __( 'Código de reserva', 'dvs-tour-reservas' ), $reserva['codigo'], true );
					$item->save();
				}
			}

			$order->set_address( array(
				'first_name' => $nombres['first'],
				'last_name'  => $nombres['last'],
				'email'      => $email,
				'phone'      => $telefono,
			), 'billing' );

			// Metadatos del pedido para poder cruzarlo con la reserva.
			$order->update_meta_data( '_dvs_tr_tour', $tour );
			$order->update_meta_data( '_dvs_tr_fecha', $fecha );
			$order->update_meta_data( '_dvs_tr_codigo', $reserva['codigo'] );

			$order->calculate_totals();
			$order->update_status( 'pending', __( 'Reserva creada, en espera de pago.', 'dvs-tour-reservas' ) );
			$order->save();
		} catch ( Exception $e ) {
			// Si el pedido falla, liberamos la reserva para no bloquear el día.
			DVS_TR_DB::cambiar_estado( $reserva['id'], DVS_TR_DB::ESTADO_CANCELADA );
			return new WP_Error( 'dvs_tr_wc_error', __( 'No se pudo generar el pedido. Inténtalo nuevamente.', 'dvs-tour-reservas' ) );
		}

		// 3. Vincular reserva ↔ pedido.
		DVS_TR_DB::vincular_order( $reserva['id'], $order->get_id() );

		return array(
			'pagoUrl' => $order->get_checkout_payment_url(),
			'codigo'  => $reserva['codigo'],
			'order_id' => $order->get_id(),
		);
	}

	public static function marcar_pagada( $order_id ) {
		DVS_TR_DB::cambiar_estado_por_order( $order_id, DVS_TR_DB::ESTADO_PAGADA );
	}

	public static function liberar( $order_id ) {
		DVS_TR_DB::cambiar_estado_por_order( $order_id, DVS_TR_DB::ESTADO_CANCELADA );
	}

	public static function mostrar_datos_reserva( $order ) {
		$tour  = $order->get_meta( '_dvs_tr_tour' );
		$fecha = $order->get_meta( '_dvs_tr_fecha' );
		if ( ! $tour && ! $fecha ) {
			return;
		}
		$tour_es = DVS_TR_I18n::tour( 'es', $tour );
		echo '<p><strong>' . esc_html__( 'Reserva de tour', 'dvs-tour-reservas' ) . ':</strong> '
			. esc_html( $tour_es['nombre'] ) . ' — ' . esc_html( $fecha ) . '</p>';
	}

	private static function separar_nombre( $completo ) {
		$completo = trim( preg_replace( '/\s+/', ' ', (string) $completo ) );
		if ( '' === $completo ) {
			return array( 'first' => '', 'last' => '' );
		}
		$partes = explode( ' ', $completo );
		$first  = array_shift( $partes );
		return array(
			'first' => $first,
			'last'  => implode( ' ', $partes ),
		);
	}
}
