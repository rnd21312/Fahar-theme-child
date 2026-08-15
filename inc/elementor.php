<?php
/**
 * Defensive Elementor integration boundary.
 *
 * @package Fahar_Theme_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Determine whether Elementor has loaded.
 *
 * @return bool
 */
function fahar_theme_is_elementor_active() {
	return did_action( 'elementor/loaded' ) || defined( 'ELEMENTOR_VERSION' );
}

/**
 * Determine whether Elementor Pro is available without requiring it.
 *
 * @return bool
 */
function fahar_theme_is_elementor_pro_active() {
	return defined( 'ELEMENTOR_PRO_VERSION' );
}

/**
 * Determine whether Elementor Canvas owns the complete page shell.
 *
 * This remains safe when Elementor is inactive because WordPress stores page
 * template slugs independently of the plugin runtime.
 *
 * @return bool
 */
function fahar_theme_is_elementor_canvas_template() {
	return 'elementor_canvas' === get_page_template_slug();
}

/**
 * Render an Elementor Theme Builder location when its public API is available.
 *
 * The API returns true only when the requested location owns the output. A
 * false result allows the child theme to render its semantic fallback.
 *
 * @param string $location Theme location slug.
 * @return bool Whether Elementor rendered the location.
 */
function fahar_theme_render_elementor_location( $location ) {
	$location = sanitize_key( $location );

	if ( ! $location || ! function_exists( 'elementor_theme_do_location' ) ) {
		return false;
	}

	return (bool) elementor_theme_do_location( $location );
}

/**
 * Expose a stable initialization action for a future companion plugin.
 *
 * @return void
 */
function fahar_theme_elementor_ready() {
	/**
	 * Fires after Elementor initializes and the Fahar theme is ready to extend.
	 *
	 * @since 1.0.0
	 */
	do_action( 'fahar_theme_elementor_ready' );
}
add_action( 'elementor/init', 'fahar_theme_elementor_ready' );
