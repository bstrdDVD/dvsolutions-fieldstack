<?php
/**
 * Traductor global del sitio (ES / EN / PT).
 *
 * Muestra un botón flotante 🌐 en la esquina superior derecha de todas las
 * páginas. Al elegir idioma, un script recorre el texto visible de la página
 * y lo reemplaza usando el diccionario de este archivo (contenido real de
 * cuatriyesotour.com) más las frases personalizadas agregadas en los ajustes.
 *
 * No depende de servicios externos ni de plugins de traducción: todo el
 * diccionario viaja con el plugin y funciona al instante, sin recargar.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DVS_TR_Traductor {

	public static function init() {
		if ( ! DVS_TR_Tours::opcion( 'traductor_activo' ) ) {
			return;
		}
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'encolar' ) );
	}

	public static function encolar() {
		wp_enqueue_style(
			'dvs-tr-traductor',
			DVS_TR_PLUGIN_URL . 'assets/css/traductor.css',
			array(),
			DVS_TR_VERSION
		);
		wp_enqueue_script(
			'dvs-tr-traductor',
			DVS_TR_PLUGIN_URL . 'assets/js/traductor.js',
			array(),
			DVS_TR_VERSION,
			true
		);
		wp_localize_script( 'dvs-tr-traductor', 'dvsTrTraductor', array(
			'diccionario' => self::diccionario(),
		) );
	}

	/**
	 * Diccionario completo: texto en español => traducciones.
	 * Las frases personalizadas de los ajustes se agregan al final y
	 * tienen prioridad sobre las incluidas.
	 */
	public static function diccionario() {
		$dicc = self::diccionario_base();

		foreach ( self::frases_personalizadas() as $es => $trad ) {
			$dicc[ $es ] = $trad;
		}
		return $dicc;
	}

	/**
	 * Frases agregadas por el administrador en Tours → Ajustes.
	 * Formato: una por línea, "texto español || english || português".
	 */
	private static function frases_personalizadas() {
		$crudo  = (string) DVS_TR_Tours::opcion( 'frases_personalizadas' );
		$frases = array();

		foreach ( preg_split( '/\r\n|\r|\n/', $crudo ) as $linea ) {
			$partes = array_map( 'trim', explode( '||', $linea ) );
			if ( count( $partes ) >= 3 && '' !== $partes[0] ) {
				$frases[ $partes[0] ] = array( 'en' => $partes[1], 'pt' => $partes[2] );
			}
		}
		return $frases;
	}

	private static function diccionario_base() {
		return array(
			// ——— Menú y generales ———
			'Turismo Guiado'      => array( 'en' => 'Guided Tourism', 'pt' => 'Turismo Guiado' ),
			'Tours'               => array( 'en' => 'Tours', 'pt' => 'Passeios' ),
			'Info'                => array( 'en' => 'Info', 'pt' => 'Informações' ),
			'Contacto'            => array( 'en' => 'Contact', 'pt' => 'Contato' ),
			'Ubicación'           => array( 'en' => 'Location', 'pt' => 'Localização' ),
			'Explorar'            => array( 'en' => 'Explore', 'pt' => 'Explorar' ),
			'Síguenos'            => array( 'en' => 'Follow us', 'pt' => 'Siga-nos' ),
			'Escríbenos directo'  => array( 'en' => 'Message us directly', 'pt' => 'Fale conosco direto' ),

			// ——— Portada ———
			'Cordillera · Aventura · Adrenalina' => array(
				'en' => 'Mountains · Adventure · Adrenaline',
				'pt' => 'Cordilheira · Aventura · Adrenalina',
			),
			'¡Atrevete a vivir la experiencia!'  => array(
				'en' => 'Dare to live the experience!',
				'pt' => 'Atreva-se a viver a experiência!',
			),
			'🏁 Ver Tours'         => array( 'en' => '🏁 See Tours', 'pt' => '🏁 Ver Passeios' ),
			'Ver Tours'            => array( 'en' => 'See Tours', 'pt' => 'Ver Passeios' ),
			'Nuestras Aventuras'   => array( 'en' => 'Our Adventures', 'pt' => 'Nossas Aventuras' ),
			'Tours Disponibles'    => array( 'en' => 'Available Tours', 'pt' => 'Passeios Disponíveis' ),
			'🔍 Ver en grande'     => array( 'en' => '🔍 View larger', 'pt' => '🔍 Ver ampliado' ),

			// ——— Tour Termas ———
			'♨️ Termas'                    => array( 'en' => '♨️ Hot Springs', 'pt' => '♨️ Termas' ),
			'Tour Termas Valle de Colina'  => array(
				'en' => 'Valle de Colina Hot Springs Tour',
				'pt' => 'Passeio às Termas Valle de Colina',
			),
			'Recorre los senderos de la alta cordillera en cuatrimoto hasta llegar a las Termas Valle de Colina, piscinas naturales de aguas termales rodeadas de imponentes cumbres andinas. Una experiencia única que combina adrenalina y el relajo en plena naturaleza.' => array(
				'en' => 'Ride the high-mountain trails by ATV up to the Valle de Colina Hot Springs, natural thermal pools surrounded by towering Andean peaks. A unique experience combining adrenaline with relaxation in the heart of nature.',
				'pt' => 'Percorra as trilhas da alta cordilheira de quadriciclo até chegar às Termas Valle de Colina, piscinas naturais de águas termais cercadas por imponentes picos andinos. Uma experiência única que combina adrenalina e relaxamento em plena natureza.',
			),
			'Precio por moto $170.000 CLP' => array(
				'en' => 'Price per ATV $170,000 CLP',
				'pt' => 'Preço por quadriciclo $170.000 CLP',
			),
			'Día completo (5 hrs)'         => array( 'en' => 'Full day (5 hrs)', 'pt' => 'Dia inteiro (5 h)' ),

			// ——— Tour Embalse ———
			'💧 Embalse'             => array( 'en' => '💧 Reservoir', 'pt' => '💧 Represa' ),
			'Tour Embalse El Yeso'   => array(
				'en' => 'El Yeso Reservoir Tour',
				'pt' => 'Passeio ao Embalse El Yeso',
			),
			'Aventúrate hasta el imponente Embalse El Yeso, de aguas turquesas rodeado por cerros de más de 4.000msnm.' => array(
				'en' => 'Venture out to the stunning El Yeso Reservoir, with turquoise waters surrounded by peaks over 4,000 m above sea level.',
				'pt' => 'Aventure-se até o imponente Embalse El Yeso, de águas turquesa cercado por montanhas com mais de 4.000 m de altitude.',
			),
			'Precio por moto $80.000 CLP' => array(
				'en' => 'Price per ATV $80,000 CLP',
				'pt' => 'Preço por quadriciclo $80.000 CLP',
			),
			'Medio día (3-4 hrs)'         => array( 'en' => 'Half day (3-4 hrs)', 'pt' => 'Meio dia (3-4 h)' ),

			// ——— Fichas de los tours ———
			'Duración'            => array( 'en' => 'Duration', 'pt' => 'Duração' ),
			'Grupo máximo'        => array( 'en' => 'Max. group', 'pt' => 'Grupo máximo' ),
			'6 personas (3 motos)' => array( 'en' => '6 people (3 ATVs)', 'pt' => '6 pessoas (3 quadriciclos)' ),
			'Punto de salida'     => array( 'en' => 'Departure point', 'pt' => 'Ponto de partida' ),
			'Equipamiento'        => array( 'en' => 'Equipment', 'pt' => 'Equipamento' ),
			'Incluido completo'   => array( 'en' => 'Fully included', 'pt' => 'Totalmente incluído' ),
			'Disponibilidad'      => array( 'en' => 'Availability', 'pt' => 'Disponibilidade' ),
			'Sáb, Dom y Festivos RM' => array(
				'en' => 'Sat, Sun & holidays (Metropolitan Region)',
				'pt' => 'Sáb, Dom e feriados (Região Metropolitana)',
			),
			'Motos disponibles'   => array( 'en' => 'ATVs available', 'pt' => 'Quadriciclos disponíveis' ),
			'3 cuatriciclos'      => array( 'en' => '3 ATVs', 'pt' => '3 quadriciclos' ),
			'3 cuatrmotos'        => array( 'en' => '3 ATVs', 'pt' => '3 quadriciclos' ),
			'📅 Reservar Ahora'   => array( 'en' => '📅 Book Now', 'pt' => '📅 Reservar Agora' ),
			'Reservar Ahora'      => array( 'en' => 'Book Now', 'pt' => 'Reservar Agora' ),

			// ——— Beneficios ———
			'¿Por qué elegirnos?' => array( 'en' => 'Why choose us?', 'pt' => 'Por que nos escolher?' ),
			'Beneficios e Información Importante' => array(
				'en' => 'Benefits & Important Information',
				'pt' => 'Benefícios e Informações Importantes',
			),
			'☕ Cafetería Gratuita' => array( 'en' => '☕ Free Café', 'pt' => '☕ Cafeteria Gratuita' ),
			'Todos los tours incluyen acceso gratuito al Emporio del Yeso. Disfruta de café, té y snacks antes y después de tu aventura.' => array(
				'en' => 'All tours include free access to Emporio del Yeso. Enjoy coffee, tea and snacks before and after your adventure.',
				'pt' => 'Todos os passeios incluem acesso gratuito ao Emporio del Yeso. Aproveite café, chá e lanches antes e depois da sua aventura.',
			),
			'🛡 Equipamiento Completo' => array( 'en' => '🛡 Full Equipment', 'pt' => '🛡 Equipamento Completo' ),
			'Casco, guantes y equipo de protección homologado incluido en todos los tours. Tu seguridad es nuestra prioridad número uno.' => array(
				'en' => 'Certified helmet, gloves and protective gear included in every tour. Your safety is our number-one priority.',
				'pt' => 'Capacete, luvas e equipamento de proteção homologado incluídos em todos os passeios. Sua segurança é a nossa prioridade número um.',
			),
			'🏔 Guías Certificados' => array( 'en' => '🏔 Certified Guides', 'pt' => '🏔 Guias Certificados' ),
			'Nuestros guías conocen cada rincón de la cordillera con años de experiencia en turismo de aventura andino. Seguridad garantizada.' => array(
				'en' => 'Our guides know every corner of the mountains, with years of experience in Andean adventure tourism. Safety guaranteed.',
				'pt' => 'Nossos guias conhecem cada canto da cordilheira, com anos de experiência em turismo de aventura andino. Segurança garantida.',
			),
			'📸 Fotografía del Tour' => array( 'en' => '📸 Tour Photography', 'pt' => '📸 Fotografia do Passeio' ),
			'Paradas en los mejores miradores para que vuelvas con recuerdos únicos de una experiencia completamente inolvidable.' => array(
				'en' => 'Stops at the best viewpoints so you go home with unique memories of a truly unforgettable experience.',
				'pt' => 'Paradas nos melhores mirantes para que você volte com lembranças únicas de uma experiência totalmente inesquecível.',
			),

			// ——— Restricciones ———
			'⚠️ Restricciones Importantes — Leer antes de reservar' => array(
				'en' => '⚠️ Important Restrictions — Read before booking',
				'pt' => '⚠️ Restrições Importantes — Leia antes de reservar',
			),
			'Prohibido el ingreso de menores de 12 años a bordo de los cuatriciclos (Ley Jacinta). Sin excepciones.' => array(
				'en' => 'Children under 12 are not allowed on the ATVs (Jacinta Law). No exceptions.',
				'pt' => 'É proibida a entrada de menores de 12 anos a bordo dos quadriciclos (Lei Jacinta). Sem exceções.',
			),
			'Licencia de conducir Clase B vigente es requisito obligatorio para operar el vehículo. Debe presentarse al inicio del tour. Sin licencia, no podrás participar de la aventura.' => array(
				'en' => 'A valid Class B driver\'s license is mandatory to operate the vehicle and must be presented at the start of the tour. Without a license you will not be able to join the adventure.',
				'pt' => 'Carteira de motorista Classe B válida é requisito obrigatório para operar o veículo e deve ser apresentada no início do passeio. Sem a carteira, você não poderá participar da aventura.',
			),
			'El incumplimiento de cualquiera de estas condiciones impide la participación en el tour sin derecho a reembolso.' => array(
				'en' => 'Failure to meet any of these conditions prevents participation in the tour, with no right to a refund.',
				'pt' => 'O não cumprimento de qualquer uma destas condições impede a participação no passeio, sem direito a reembolso.',
			),

			// ——— Contacto y pie ———
			'Sábado, Domingo y Festivos RM · 8:00 – 18:00 hrs' => array(
				'en' => 'Saturday, Sunday & holidays (Metropolitan Region) · 8:00 – 18:00',
				'pt' => 'Sábado, Domingo e feriados (Região Metropolitana) · 8h – 18h',
			),
			'Cafetería "Emporio del Yeso"' => array(
				'en' => '"Emporio del Yeso" Café',
				'pt' => 'Cafeteria "Emporio del Yeso"',
			),
			'Camino al Volcán, Región Metropolitana' => array(
				'en' => 'Camino al Volcán, Metropolitan Region',
				'pt' => 'Camino al Volcán, Região Metropolitana',
			),
			'© 2026 CuatriYesoTour. Todos los derechos reservados.' => array(
				'en' => '© 2026 CuatriYesoTour. All rights reserved.',
				'pt' => '© 2026 CuatriYesoTour. Todos os direitos reservados.',
			),
		);
	}
}
