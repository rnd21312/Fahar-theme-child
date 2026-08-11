<?php
/**
 * Render Portfolio Card instances for an Explore query.
 *
 * @package Fahar_Theme_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fahar_feed_query = isset( $args['query'] ) && $args['query'] instanceof WP_Query ? $args['query'] : null;

if ( ! $fahar_feed_query ) {
	return;
}

while ( $fahar_feed_query->have_posts() ) {
	$fahar_feed_query->the_post();
	get_template_part( 'template-parts/portfolio/card', null, array( 'post' => get_post() ) );
}

unset( $fahar_feed_query );
