<?php
/**
 * Conservative performance helpers.
 *
 * @package Fahar_Theme_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Defer only the theme's own scripts; WordPress and plugin assets are untouched.
 *
 * @param string $tag    Script tag.
 * @param string $handle Script handle.
 * @return string
 */
function fahar_theme_defer_scripts( $tag, $handle ) {
	if ( 0 !== strpos( $handle, 'fahar-theme-' ) || false !== strpos( $tag, ' defer' ) ) {
		return $tag;
	}

	return str_replace( ' src=', ' defer src=', $tag );
}
add_filter( 'script_loader_tag', 'fahar_theme_defer_scripts', 10, 2 );
