<?php
/**
 * Cuatriyeso Tour Theme Functions
 */

// Agregar soporte para características de WordPress
function cuatriyeso_setup() {
    // Soporte para título del sitio desde admin
    add_theme_support( 'title-tag' );
    
    // Soporte para imagen destacada
    add_theme_support( 'post-thumbnails' );
    
    // Soporte para menus
    register_nav_menus( array(
        'primary' => __( 'Primary Menu', 'cuatriyeso-tour' ),
    ) );
}
add_action( 'after_setup_theme', 'cuatriyeso_setup' );

// Enqueue estilos y scripts del tema
function cuatriyeso_scripts() {
    wp_enqueue_style( 'cuatriyeso-style', get_stylesheet_uri() );
}
add_action( 'wp_enqueue_scripts', 'cuatriyeso_scripts' );
