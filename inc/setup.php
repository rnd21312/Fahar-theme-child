<?php
/**
 * Child-theme setup.
 *
 * @package Fahar_Theme_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Configure features that complement the Hello Elementor parent theme.
 *
 * The parent owns the baseline classic-theme supports. These additions make
 * embedded media and wide block content usable without duplicating its setup.
 *
 * @return void
 */
function fahar_theme_setup() {
	load_child_theme_textdomain( 'fahar-theme-child', FAHAR_THEME_DIR . '/languages' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );

	register_nav_menus(
		array(
			'fahar-primary'       => esc_html__( 'Fahar Primary Navigation', 'fahar-theme-child' ),
			'fahar-mobile-bottom' => esc_html__( 'Fahar Mobile Bottom Navigation', 'fahar-theme-child' ),
		)
	);
}
add_action( 'after_setup_theme', 'fahar_theme_setup', 20 );
