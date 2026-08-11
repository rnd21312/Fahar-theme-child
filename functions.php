<?php
/**
 * Fahar Theme Child bootstrap.
 *
 * @package Fahar_Theme_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fahar_theme = wp_get_theme();

define( 'FAHAR_THEME_VERSION', $fahar_theme->get( 'Version' ) ?: '1.0.0' );
define( 'FAHAR_THEME_DIR', get_stylesheet_directory() );
define( 'FAHAR_THEME_URI', get_stylesheet_directory_uri() );

$fahar_theme_modules = array(
	'helpers',
	'setup',
	'compatibility',
	'elementor',
	'portfolio',
	'rest-api',
	'template-routing',
	'navigation',
	'assets',
	'performance',
);

foreach ( $fahar_theme_modules as $fahar_theme_module ) {
	require_once FAHAR_THEME_DIR . '/inc/' . $fahar_theme_module . '.php';
}

unset( $fahar_theme, $fahar_theme_module, $fahar_theme_modules );
