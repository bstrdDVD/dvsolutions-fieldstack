<?php
/**
 * Traducciones del calendario público y correos al cliente.
 * Idiomas: español (es), inglés (en) y portugués (pt).
 *
 * El mapa completo se entrega al JavaScript del calendario, que muestra
 * un selector ES / EN / PT y aplica los textos sin recargar la página.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DVS_TR_I18n {

	const IDIOMA_DEFECTO = 'es';

	public static function idiomas() {
		return array( 'es', 'en', 'pt' );
	}

	public static function valido( $idioma ) {
		return in_array( $idioma, self::idiomas(), true );
	}

	public static function cadenas() {
		return array(
			'es' => array(
				'tours' => array(
					'termas'  => array(
						'nombre'      => 'Tour Termas Valle de Colina',
						'descripcion' => 'Salida 09:30 · Regreso 14:30',
					),
					'embalse' => array(
						'nombre'      => 'Tour Embalse El Yeso',
						'descripcion' => 'Salida 15:00 · Regreso 17:30',
					),
				),
				'elige_fecha'        => 'Elige tu fecha',
				'mes_anterior'       => 'Mes anterior',
				'mes_siguiente'      => 'Mes siguiente',
				'leyenda_disponible' => 'Disponible',
				'leyenda_guia'       => 'Guía ocupado (día bloqueado)',
				'leyenda_lleno'      => 'No disponible',
				'cargando'           => 'Cargando disponibilidad…',
				'error_red'          => 'Error de conexión. Inténtalo nuevamente.',
				'sin_pago'           => 'Reserva registrada. Te contactaremos para coordinar el pago.',
				'redirigiendo'       => 'Reserva registrada. Redirigiendo al pago seguro del Banco de Chile…',
				'reservado'          => 'Reservado',
				'guia_otro_tour'     => 'No disponible: el guía está en el otro tour este día',
				'guia_ocupado'       => 'Guía ocupado este día',
				'err_no_disponible'  => 'Lo sentimos, esta fecha acaba de ser reservada. El guía ya está comprometido ese día.',
				'lbl_nombre'         => 'Nombre completo',
				'lbl_email'          => 'Correo electrónico',
				'lbl_telefono'       => 'Teléfono',
				'lbl_personas'       => 'Número de personas',
				'aviso_pago'         => 'Al confirmar serás redirigido al Botón de Pago del Banco de Chile para completar tu pago de forma segura.',
				'btn_pagar'          => 'Confirmar y pagar',
				'meses'              => array( 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre' ),
				'dias'               => array( 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom' ),
				'email_asunto'       => 'Recibimos tu reserva',
				'email_hola'         => 'Hola',
				'email_codigo'       => 'Código de reserva',
				'email_tour'         => 'Tour',
				'email_fecha'        => 'Fecha',
				'email_pago'         => 'Tu reserva quedará confirmada una vez completado el pago a través del Botón de Pago del Banco de Chile.',
			),
			'en' => array(
				'tours' => array(
					'termas'  => array(
						'nombre'      => 'Valle de Colina Hot Springs Tour',
						'descripcion' => 'Departure 09:30 · Return 14:30',
					),
					'embalse' => array(
						'nombre'      => 'El Yeso Reservoir Tour',
						'descripcion' => 'Departure 15:00 · Return 17:30',
					),
				),
				'elige_fecha'        => 'Pick your date',
				'mes_anterior'       => 'Previous month',
				'mes_siguiente'      => 'Next month',
				'leyenda_disponible' => 'Available',
				'leyenda_guia'       => 'Guide busy (day blocked)',
				'leyenda_lleno'      => 'Unavailable',
				'cargando'           => 'Loading availability…',
				'error_red'          => 'Connection error. Please try again.',
				'sin_pago'           => 'Booking received. We will contact you to arrange payment.',
				'redirigiendo'       => 'Booking received. Redirecting to Banco de Chile secure payment…',
				'reservado'          => 'Booked',
				'guia_otro_tour'     => 'Unavailable: the guide is on the other tour this day',
				'guia_ocupado'       => 'Guide busy this day',
				'err_no_disponible'  => 'Sorry, this date has just been booked. The guide is already committed that day.',
				'lbl_nombre'         => 'Full name',
				'lbl_email'          => 'Email address',
				'lbl_telefono'       => 'Phone',
				'lbl_personas'       => 'Number of people',
				'aviso_pago'         => 'After confirming you will be redirected to the Banco de Chile payment button to complete your payment securely.',
				'btn_pagar'          => 'Confirm and pay',
				'meses'              => array( 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' ),
				'dias'               => array( 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun' ),
				'email_asunto'       => 'We received your booking',
				'email_hola'         => 'Hello',
				'email_codigo'       => 'Booking code',
				'email_tour'         => 'Tour',
				'email_fecha'        => 'Date',
				'email_pago'         => 'Your booking will be confirmed once the payment is completed through the Banco de Chile payment button.',
			),
			'pt' => array(
				'tours' => array(
					'termas'  => array(
						'nombre'      => 'Passeio às Termas Valle de Colina',
						'descripcion' => 'Saída 09:30 · Retorno 14:30',
					),
					'embalse' => array(
						'nombre'      => 'Passeio ao Embalse El Yeso',
						'descripcion' => 'Saída 15:00 · Retorno 17:30',
					),
				),
				'elige_fecha'        => 'Escolha sua data',
				'mes_anterior'       => 'Mês anterior',
				'mes_siguiente'      => 'Próximo mês',
				'leyenda_disponible' => 'Disponível',
				'leyenda_guia'       => 'Guia ocupado (dia bloqueado)',
				'leyenda_lleno'      => 'Indisponível',
				'cargando'           => 'Carregando disponibilidade…',
				'error_red'          => 'Erro de conexão. Tente novamente.',
				'sin_pago'           => 'Reserva registrada. Entraremos em contato para coordenar o pagamento.',
				'redirigiendo'       => 'Reserva registrada. Redirecionando ao pagamento seguro do Banco de Chile…',
				'reservado'          => 'Reservado',
				'guia_otro_tour'     => 'Indisponível: o guia está no outro passeio neste dia',
				'guia_ocupado'       => 'Guia ocupado neste dia',
				'err_no_disponible'  => 'Desculpe, esta data acabou de ser reservada. O guia já está comprometido nesse dia.',
				'lbl_nombre'         => 'Nome completo',
				'lbl_email'          => 'E-mail',
				'lbl_telefono'       => 'Telefone',
				'lbl_personas'       => 'Número de pessoas',
				'aviso_pago'         => 'Ao confirmar, você será redirecionado ao botão de pagamento do Banco de Chile para concluir seu pagamento com segurança.',
				'btn_pagar'          => 'Confirmar e pagar',
				'meses'              => array( 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro' ),
				'dias'               => array( 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom' ),
				'email_asunto'       => 'Recebemos sua reserva',
				'email_hola'         => 'Olá',
				'email_codigo'       => 'Código da reserva',
				'email_tour'         => 'Passeio',
				'email_fecha'        => 'Data',
				'email_pago'         => 'Sua reserva será confirmada assim que o pagamento for concluído através do botão de pagamento do Banco de Chile.',
			),
		);
	}

	/**
	 * Devuelve una cadena traducida, con respaldo en español.
	 */
	public static function t( $idioma, $clave ) {
		$cadenas = self::cadenas();
		if ( ! self::valido( $idioma ) ) {
			$idioma = self::IDIOMA_DEFECTO;
		}
		if ( isset( $cadenas[ $idioma ][ $clave ] ) ) {
			return $cadenas[ $idioma ][ $clave ];
		}
		return isset( $cadenas[ self::IDIOMA_DEFECTO ][ $clave ] ) ? $cadenas[ self::IDIOMA_DEFECTO ][ $clave ] : $clave;
	}

	/**
	 * Nombre y descripción de un tour en el idioma pedido.
	 */
	public static function tour( $idioma, $tour ) {
		$tours = self::t( $idioma, 'tours' );
		return isset( $tours[ $tour ] ) ? $tours[ $tour ] : array( 'nombre' => $tour, 'descripcion' => '' );
	}
}
