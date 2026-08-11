<?php
/**
 * Single Portfolio native comments integration.
 *
 * @package Fahar_Theme_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fahar_comments_post = isset( $args['post'] ) ? get_post( $args['post'] ) : get_post();

if ( ! $fahar_comments_post instanceof WP_Post || ! fahar_theme_is_portfolio_post( $fahar_comments_post ) ) {
	return;
}

$fahar_comments_available = post_type_supports( $fahar_comments_post->post_type, 'comments' );
$fahar_comments_visible   = comments_open( $fahar_comments_post->ID ) || get_comments_number( $fahar_comments_post->ID );

if ( $fahar_comments_available && $fahar_comments_visible ) {
	comments_template();
}

unset(
	$fahar_comments_post,
	$fahar_comments_available,
	$fahar_comments_visible
);
