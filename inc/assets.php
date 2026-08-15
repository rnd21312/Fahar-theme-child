<?php
/**
 * Theme asset registration and conditional loading.
 *
 * @package Fahar_Theme_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register and enqueue frontend assets in deterministic order.
 *
 * @return void
 */
function fahar_theme_enqueue_assets() {
	wp_enqueue_style(
		'fahar-theme-style',
		get_stylesheet_uri(),
		array( 'hello-elementor-theme-style' ),
		fahar_theme_asset_version( 'style.css' )
	);

	$global_styles = array( 'tokens', 'reset', 'globals', 'typography', 'layout', 'components', 'elementor', 'utilities' );
	$dependency    = 'fahar-theme-style';

	foreach ( $global_styles as $stylesheet ) {
		$handle = 'fahar-theme-' . $stylesheet;
		$path   = 'assets/css/' . $stylesheet . '.css';
		wp_enqueue_style( $handle, fahar_theme_asset_uri( $path ), array( $dependency ), fahar_theme_asset_version( $path ) );
		$dependency = $handle;
	}

	if ( apply_filters( 'fahar_theme_load_header_assets', false ) ) {
		fahar_theme_enqueue_style_asset( 'header' );
	}

	if ( apply_filters( 'fahar_theme_load_footer_assets', false ) ) {
		fahar_theme_enqueue_style_asset( 'footer' );
	}

	if ( fahar_theme_should_show_mobile_navigation() ) {
		fahar_theme_enqueue_style_asset( 'mobile-nav' );
	}

	if ( fahar_theme_is_portfolio_explore() ) {
		fahar_theme_enqueue_style_asset( 'portfolio-card' );
		fahar_theme_enqueue_component_assets( 'portfolio-explore', 'portfolio-explore' );
	}

}
add_action( 'wp_enqueue_scripts', 'fahar_theme_enqueue_assets', 20 );

/**
 * Enqueue a paired component stylesheet and script.
 *
 * @param string $style_name  CSS basename.
 * @param string $script_name JS basename.
 * @return void
 */
function fahar_theme_enqueue_component_assets( $style_name, $script_name ) {
	fahar_theme_enqueue_style_asset( $style_name );
	fahar_theme_enqueue_script_asset( $script_name );
}

/**
 * Enqueue a conditional theme stylesheet.
 *
 * @param string $style_name CSS basename.
 * @return void
 */
function fahar_theme_enqueue_style_asset( $style_name ) {
	$style_name = sanitize_file_name( $style_name );
	$style_path = 'assets/css/' . $style_name . '.css';
	wp_enqueue_style(
		'fahar-theme-' . sanitize_key( $style_name ),
		fahar_theme_asset_uri( $style_path ),
		array( 'fahar-theme-utilities' ),
		fahar_theme_asset_version( $style_path )
	);
}

/**
 * Enqueue a dependency-free theme script in the footer.
 *
 * @param string $script_name JS basename.
 * @return void
 */
function fahar_theme_enqueue_script_asset( $script_name ) {
	$script_name = sanitize_file_name( $script_name );
	$path        = 'assets/js/' . $script_name . '.js';
	wp_enqueue_script(
		'fahar-theme-' . sanitize_key( $script_name ),
		fahar_theme_asset_uri( $path ),
		array(),
		fahar_theme_asset_version( $path ),
		true
	);
}
