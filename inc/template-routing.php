<?php
/**
 * Theme template routing.
 *
 * @package Fahar_Theme_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Determine whether the current request is a supported portfolio single.
 *
 * @return bool
 */
function fahar_theme_is_portfolio_single() {
	if ( ! fahar_theme_is_portfolio_item() ) {
		return false;
	}

	$post = get_queried_object();

	return $post instanceof WP_Post && fahar_theme_portfolio_has_single_permalink( $post );
}

/**
 * Load the Fahar shell for portfolio items with verified single behavior.
 *
 * @param string $template Resolved WordPress template path.
 * @return string
 */
function fahar_theme_route_portfolio_single_template( $template ) {
	if ( ! fahar_theme_is_portfolio_single() ) {
		return $template;
	}

	$portfolio_template = locate_template( 'templates/single-portfolio.php', false, false );

	return $portfolio_template ? $portfolio_template : $template;
}
add_filter( 'template_include', 'fahar_theme_route_portfolio_single_template', 99 );
