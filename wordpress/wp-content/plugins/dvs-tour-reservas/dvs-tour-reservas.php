<?php
/**
 * Plugin Name: DVS Tour Reservas
 * Description: Calendario de reservas para Tour Termas (09:30–14:30) y Tour Embalse (15:00–17:30) con guía único compartido: al reservar un tour, el otro queda bloqueado ese día. Redirige al Botón de Pago del Banco de Chile.
 * Version: 1.0.0
 * Author: DV Solutions
 * Text Domain: dvs-tour-reservas
 * License: GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DVS_TR_VERSION', '1.0.0' );
define( 'DVS_TR_PLUGIN_FILE', __FILE__ );
define( 'DVS_TR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'DVS_TR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once DVS_TR_PLUGIN_DIR . 'includes/class-dvs-tr-i18n.php';
require_once DVS_TR_PLUGIN_DIR . 'includes/class-dvs-tr-tours.php';
require_once DVS_TR_PLUGIN_DIR . 'includes/class-dvs-tr-db.php';
require_once DVS_TR_PLUGIN_DIR . 'includes/class-dvs-tr-front.php';
require_once DVS_TR_PLUGIN_DIR . 'includes/class-dvs-tr-traductor.php';
require_once DVS_TR_PLUGIN_DIR . 'includes/class-dvs-tr-admin.php';

register_activation_hook( __FILE__, array( 'DVS_TR_DB', 'install' ) );

add_action( 'plugins_loaded', function () {
	DVS_TR_DB::maybe_upgrade();
	DVS_TR_Front::init();
	DVS_TR_Traductor::init();
	if ( is_admin() ) {
		DVS_TR_Admin::init();
	}
} );
