<?php
/**
 * Cross-plugin and parent-theme compatibility helpers.
 *
 * @package Fahar_Theme_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Determine whether the expected Hello Elementor parent is active.
 *
 * @return bool
 */
function fahar_theme_has_hello_parent() {
	$parent = wp_get_theme()->parent();

	return $parent && 'hello-elementor' === $parent->get_stylesheet();
}

/**
 * Filter classes exposed to integrations without changing parent markup.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function fahar_theme_body_classes( $classes ) {
	$classes[] = is_rtl() ? 'fahar-is-rtl' : 'fahar-is-ltr';

	if ( fahar_theme_is_elementor_active() ) {
		$classes[] = 'fahar-has-elementor';
	}

	return $classes;
}
add_filter( 'body_class', 'fahar_theme_body_classes' );
