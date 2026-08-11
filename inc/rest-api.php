<?php
/**
 * Narrow public REST endpoints for Fahar frontend discovery.
 *
 * @package Fahar_Theme_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the bounded portfolio suggestion endpoint.
 *
 * @return void
 */
function fahar_theme_register_rest_routes() {
	register_rest_route(
		'fahar/v1',
		'/portfolio-suggestions',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'fahar_theme_rest_portfolio_suggestions',
			'permission_callback' => '__return_true',
			'args'                => array(
				'search' => array(
					'required'          => true,
					'sanitize_callback' => 'sanitize_text_field',
					'validate_callback' => static function ( $value ) {
						return is_string( $value ) && 2 <= fahar_theme_string_length( trim( $value ) );
					},
				),
				'limit'  => array(
					'default'           => 6,
					'sanitize_callback' => 'absint',
					'validate_callback' => static function ( $value ) {
						$value = absint( $value );

						return 1 <= $value && 8 >= $value;
					},
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'fahar_theme_register_rest_routes' );

/**
 * Return a multibyte-safe string length when the extension is available.
 *
 * @internal
 * @param string $value Text value.
 * @return int
 */
function fahar_theme_string_length( $value ) {
	return function_exists( 'mb_strlen' ) ? mb_strlen( $value, 'UTF-8' ) : strlen( $value );
}

/**
 * Return published portfolio title suggestions.
 *
 * @param WP_REST_Request $request REST request.
 * @return WP_REST_Response
 */
function fahar_theme_rest_portfolio_suggestions( WP_REST_Request $request ) {
	$search    = trim( sanitize_text_field( (string) $request->get_param( 'search' ) ) );
	$limit     = min( 8, max( 1, absint( $request->get_param( 'limit' ) ) ) );
	$post_type = fahar_theme_get_portfolio_post_type();
	$results   = array();

	if ( 2 > fahar_theme_string_length( $search ) || ! $post_type || ! post_type_exists( $post_type ) ) {
		return rest_ensure_response( $results );
	}

	$query = fahar_theme_query_portfolios(
		array(
			'post_type'              => $post_type,
			'post_status'            => 'publish',
			'posts_per_page'         => $limit,
			's'                      => $search,
			'orderby'                => 'relevance',
			'order'                  => 'DESC',
			'fields'                 => 'ids',
			'has_password'           => false,
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	$type_labels = array(
		'website'     => __( 'وب‌سایت', 'fahar-theme-child' ),
		'image'       => __( 'تصویر', 'fahar-theme-child' ),
		'video'       => __( 'ویدیو', 'fahar-theme-child' ),
		'single-page' => __( 'پروژه', 'fahar-theme-child' ),
	);

	foreach ( $query->posts as $post_id ) {
		$post        = get_post( $post_id );
		$destination = fahar_theme_get_portfolio_destination( $post );

		if ( ! $post instanceof WP_Post || 'publish' !== $post->post_status || empty( $destination['url'] ) ) {
			continue;
		}

		$cover_id  = fahar_theme_get_portfolio_cover_id( $post );
		$thumbnail = $cover_id ? wp_get_attachment_image_src( $cover_id, 'thumbnail' ) : false;
		$type      = fahar_theme_get_portfolio_type( $post );
		$item      = array(
			'id'         => (int) $post->ID,
			'title'      => get_the_title( $post ),
			'url'        => $destination['url'],
			'type'       => $type,
			'type_label' => isset( $type_labels[ $type ] ) ? $type_labels[ $type ] : '',
		);

		if ( is_array( $thumbnail ) ) {
			$item['thumbnail'] = array(
				'url'    => $thumbnail[0],
				'width'  => (int) $thumbnail[1],
				'height' => (int) $thumbnail[2],
				'alt'    => trim( (string) get_post_meta( $cover_id, '_wp_attachment_image_alt', true ) ),
			);
		}

		$results[] = $item;
	}

	$response = rest_ensure_response( $results );
	$response->header( 'Cache-Control', 'public, max-age=60' );

	return $response;
}
