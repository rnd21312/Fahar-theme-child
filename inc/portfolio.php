<?php
/**
 * Portfolio integration boundary.
 *
 * @package Fahar_Theme_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add native WordPress comment support to the verified portfolio post type.
 *
 * Comment status remains controlled by WordPress per-post and site settings.
 *
 * @param array $args Portfolio post type registration arguments.
 * @return array
 */
function fahar_theme_enable_portfolio_comments( $args ) {
	$args     = is_array( $args ) ? $args : array();
	$supports = isset( $args['supports'] ) ? (array) $args['supports'] : array();

	if ( ! in_array( 'comments', $supports, true ) ) {
		$supports[] = 'comments';
	}

	$args['supports'] = array_values( array_unique( $supports ) );

	return $args;
}
add_filter( 'astra_portfolio_post_type_args', 'fahar_theme_enable_portfolio_comments' );

/**
 * Return the verified portfolio post type.
 *
 * @return string
 */
function fahar_theme_get_portfolio_post_type() {
	return sanitize_key( (string) apply_filters( 'fahar_theme_portfolio_post_type', 'astra-portfolio' ) );
}

/**
 * Return the verified plugin-native to Fahar portfolio type map.
 *
 * @internal
 * @return string[] Native type keys mapped to normalized Fahar type keys.
 */
function fahar_theme_get_portfolio_type_map() {
	$type_map = (array) apply_filters(
		'fahar_theme_portfolio_type_map',
		array(
			'iframe' => 'website',
			'image'  => 'image',
			'video'  => 'video',
			'page'   => 'single-page',
		)
	);
	$normalized = array();

	foreach ( $type_map as $native_type => $fahar_type ) {
		$native_type = sanitize_key( (string) $native_type );
		$fahar_type  = sanitize_key( (string) $fahar_type );

		if ( $native_type && $fahar_type ) {
			$normalized[ $native_type ] = $fahar_type;
		}
	}

	return $normalized;
}

/**
 * Return the supported normalized portfolio types.
 *
 * @return string[]
 */
function fahar_theme_get_portfolio_types() {
	return array_values( array_unique( array_values( fahar_theme_get_portfolio_type_map() ) ) );
}

/**
 * Return the provider-native type used when portfolio type metadata is absent.
 *
 * @internal
 * @return string
 */
function fahar_theme_get_default_portfolio_native_type() {
	return sanitize_key( (string) apply_filters( 'fahar_theme_default_portfolio_native_type', 'iframe' ) );
}

/**
 * Return the verified plugin meta key map used by the adapter.
 *
 * @internal
 * @return string[]
 */
function fahar_theme_get_portfolio_meta_keys() {
	$meta_keys = (array) apply_filters(
		'fahar_theme_portfolio_meta_keys',
		array(
			'type'                => 'astra-portfolio-type',
			'cover_id'            => 'astra-portfolio-image-id',
			'lightbox_image_id'   => 'astra-lightbox-image-id',
			'external_url'        => 'astra-site-url',
			'open_in_new_tab'     => 'astra-site-open-in-new-tab',
			'open_portfolio_in'   => 'astra-site-open-portfolio-in',
			'call_to_action'      => 'astra-site-call-to-action',
			'video_source'        => 'astra-portfolio-video-url',
		)
	);
	$normalized = array();

	foreach ( $meta_keys as $purpose => $meta_key ) {
		$purpose  = sanitize_key( (string) $purpose );
		$meta_key = sanitize_key( (string) $meta_key );

		if ( $purpose && $meta_key ) {
			$normalized[ $purpose ] = $meta_key;
		}
	}

	return $normalized;
}

/**
 * Normalize a post argument to a portfolio WP_Post object.
 *
 * @internal
 * @param int|WP_Post|null $post Post ID, post object, or current post.
 * @return WP_Post|null
 */
function fahar_theme_normalize_portfolio_post( $post = null ) {
	$post_type = fahar_theme_get_portfolio_post_type();

	if ( ! $post_type || ! post_type_exists( $post_type ) ) {
		return null;
	}

	$post = get_post( $post );

	if ( ! $post instanceof WP_Post || $post_type !== $post->post_type ) {
		return null;
	}

	return $post;
}

/**
 * Read one mapped portfolio meta value.
 *
 * @internal
 * @param WP_Post $post    Portfolio post.
 * @param string  $purpose Adapter meta purpose.
 * @return mixed|null
 */
function fahar_theme_get_portfolio_meta_value( $post, $purpose ) {
	$meta_keys = fahar_theme_get_portfolio_meta_keys();
	$purpose   = sanitize_key( (string) $purpose );

	if ( ! isset( $meta_keys[ $purpose ] ) ) {
		return null;
	}

	return get_post_meta( $post->ID, $meta_keys[ $purpose ], true );
}

/**
 * Determine whether a post is a portfolio item from the verified provider.
 *
 * @param int|WP_Post|null $post Post ID, post object, or current post.
 * @return bool
 */
function fahar_theme_is_portfolio_post( $post = null ) {
	return fahar_theme_normalize_portfolio_post( $post ) instanceof WP_Post;
}

/**
 * Return the normalized portfolio type.
 *
 * Normalized values are website, image, video, and single-page.
 *
 * @param int|WP_Post|null $post Post ID, post object, or current post.
 * @return string
 */
function fahar_theme_get_portfolio_type( $post = null ) {
	$post = fahar_theme_normalize_portfolio_post( $post );

	if ( ! $post ) {
		return '';
	}

	$raw_type = fahar_theme_get_portfolio_meta_value( $post, 'type' );

	if ( '' === $raw_type || null === $raw_type ) {
		$raw_type = fahar_theme_get_default_portfolio_native_type();
	}

	if ( ! is_scalar( $raw_type ) ) {
		return '';
	}

	$type_map = fahar_theme_get_portfolio_type_map();
	$raw_type = sanitize_key( (string) $raw_type );

	return isset( $type_map[ $raw_type ] ) ? $type_map[ $raw_type ] : '';
}

/**
 * Build a provider-specific meta query from normalized portfolio types.
 *
 * Multiple types use OR semantics. Selecting the normalized type associated
 * with the provider's missing-meta default also includes posts without the type
 * metadata key.
 *
 * @param string[] $types Normalized Fahar portfolio types.
 * @return array<string, mixed> WP_Query fragment, or an empty array.
 */
function fahar_theme_get_portfolio_type_query_args( $types ) {
	$type_map        = fahar_theme_get_portfolio_type_map();
	$supported_types = fahar_theme_get_portfolio_types();
	$requested_types = array();

	foreach ( (array) $types as $type ) {
		if ( is_scalar( $type ) ) {
			$type = sanitize_key( (string) $type );

			if ( in_array( $type, $supported_types, true ) ) {
				$requested_types[] = $type;
			}
		}
	}

	$requested_types = array_values( array_unique( $requested_types ) );
	$meta_keys       = fahar_theme_get_portfolio_meta_keys();
	$type_meta_key   = isset( $meta_keys['type'] ) ? $meta_keys['type'] : '';

	if ( ! $requested_types || ! $type_meta_key ) {
		return array();
	}

	$default_native_type = fahar_theme_get_default_portfolio_native_type();
	$meta_query          = array( 'relation' => 'OR' );

	foreach ( $type_map as $native_type => $normalized_type ) {
		if ( ! in_array( $normalized_type, $requested_types, true ) ) {
			continue;
		}

		$meta_query[] = array(
			'key'     => $type_meta_key,
			'value'   => $native_type,
			'compare' => '=',
		);

		if ( $native_type === $default_native_type ) {
			$meta_query[] = array(
				'key'     => $type_meta_key,
				'compare' => 'NOT EXISTS',
			);
		}
	}

	return count( $meta_query ) > 1 ? array( 'meta_query' => $meta_query ) : array();
}

/**
 * Disable the provider's normal-query exclusion during a scoped Fahar listing.
 *
 * @internal
 * @return bool
 */
function fahar_theme_include_all_portfolio_types_in_query( $exclude = true ) {
	unset( $exclude );

	return false;
}

/**
 * Run one portfolio WP_Query while accounting for provider query exclusions.
 *
 * Provider-specific hooks remain scoped to construction of this query and are
 * removed immediately afterward.
 *
 * @param array $query_args WP_Query arguments.
 * @return WP_Query
 */
function fahar_theme_query_portfolios( $query_args ) {
	add_filter( 'astra_portfolio_exclude_portfolio_items', 'fahar_theme_include_all_portfolio_types_in_query', PHP_INT_MAX );

	try {
		$query = new WP_Query( (array) $query_args );
	} finally {
		remove_filter( 'astra_portfolio_exclude_portfolio_items', 'fahar_theme_include_all_portfolio_types_in_query', PHP_INT_MAX );
	}

	return $query;
}

/**
 * Validate a locally resolvable attachment ID.
 *
 * @internal
 * @param mixed $attachment_id Possible attachment ID.
 * @return int
 */
function fahar_theme_normalize_attachment_id( $attachment_id ) {
	$attachment_id = absint( $attachment_id );
	$attachment    = $attachment_id ? get_post( $attachment_id ) : null;

	return $attachment instanceof WP_Post && 'attachment' === $attachment->post_type ? $attachment_id : 0;
}

/**
 * Validate a locally resolvable image attachment ID.
 *
 * @internal
 * @param mixed $attachment_id Possible image attachment ID.
 * @return int
 */
function fahar_theme_normalize_image_attachment_id( $attachment_id ) {
	$attachment_id = fahar_theme_normalize_attachment_id( $attachment_id );

	return $attachment_id && wp_attachment_is_image( $attachment_id ) ? $attachment_id : 0;
}

/**
 * Return the verified local cover attachment ID.
 *
 * @param int|WP_Post|null $post Post ID, post object, or current post.
 * @return int
 */
function fahar_theme_get_portfolio_cover_id( $post = null ) {
	$post = fahar_theme_normalize_portfolio_post( $post );

	if ( ! $post ) {
		return 0;
	}

	$cover_id = fahar_theme_normalize_image_attachment_id( fahar_theme_get_portfolio_meta_value( $post, 'cover_id' ) );

	if ( ! $cover_id ) {
		$cover_id = fahar_theme_normalize_image_attachment_id( get_post_thumbnail_id( $post ) );
	}

	$cover_id = apply_filters( 'fahar_theme_portfolio_cover_id', $cover_id, $post );

	return fahar_theme_normalize_image_attachment_id( $cover_id );
}

/**
 * Return the verified local cover URL for an image size.
 *
 * @param int|WP_Post|null $post Post ID, post object, or current post.
 * @param string|int[]     $size Registered image size or width/height pair.
 * @return string
 */
function fahar_theme_get_portfolio_cover_url( $post = null, $size = 'large' ) {
	$post = fahar_theme_normalize_portfolio_post( $post );

	if ( ! $post ) {
		return '';
	}

	$cover_id = fahar_theme_get_portfolio_cover_id( $post );

	if ( ! $cover_id ) {
		return '';
	}

	if ( is_array( $size ) ) {
		$size = array_values( array_map( 'absint', array_slice( $size, 0, 2 ) ) );
		$size = 2 === count( $size ) && $size[0] && $size[1] ? $size : 'large';
	} else {
		$size = sanitize_key( (string) $size );
		$size = $size ? $size : 'large';
	}

	$url = wp_get_attachment_image_url( $cover_id, $size );
	$url = apply_filters( 'fahar_theme_portfolio_cover_url', $url ? $url : '', $cover_id, $size, $post );

	return esc_url_raw( (string) $url, array( 'http', 'https' ) );
}

/**
 * Return the verified local Image portfolio attachment ID.
 *
 * @param int|WP_Post|null $post Post ID, post object, or current post.
 * @return int
 */
function fahar_theme_get_portfolio_lightbox_image_id( $post = null ) {
	$post = fahar_theme_normalize_portfolio_post( $post );

	if ( ! $post ) {
		return 0;
	}

	$image_id = fahar_theme_normalize_image_attachment_id( fahar_theme_get_portfolio_meta_value( $post, 'lightbox_image_id' ) );
	$image_id = apply_filters( 'fahar_theme_portfolio_lightbox_image_id', $image_id, $post );

	return fahar_theme_normalize_image_attachment_id( $image_id );
}

/**
 * Return the verified Image portfolio URL for an image size.
 *
 * @param int|WP_Post|null $post Post ID, post object, or current post.
 * @param string|int[]     $size Registered image size or width/height pair.
 * @return string
 */
function fahar_theme_get_portfolio_lightbox_image_url( $post = null, $size = 'full' ) {
	$post = fahar_theme_normalize_portfolio_post( $post );

	if ( ! $post ) {
		return '';
	}

	$image_id = fahar_theme_get_portfolio_lightbox_image_id( $post );

	if ( ! $image_id ) {
		return '';
	}

	if ( is_array( $size ) ) {
		$size = array_values( array_map( 'absint', array_slice( $size, 0, 2 ) ) );
		$size = 2 === count( $size ) && $size[0] && $size[1] ? $size : 'full';
	} else {
		$size = sanitize_key( (string) $size );
		$size = $size ? $size : 'full';
	}

	$url = wp_get_attachment_image_url( $image_id, $size );
	$url = apply_filters( 'fahar_theme_portfolio_lightbox_image_url', $url ? $url : '', $image_id, $size, $post );

	return esc_url_raw( (string) $url, array( 'http', 'https' ) );
}

/**
 * Normalize a usable absolute HTTP(S) URL.
 *
 * @internal
 * @param mixed $value Possible URL.
 * @return string
 */
function fahar_theme_normalize_http_url( $value ) {
	if ( ! is_string( $value ) ) {
		return '';
	}

	$value = trim( $value );
	$value = 0 === strpos( $value, '//' ) ? 'https:' . $value : $value;
	$url   = esc_url_raw( $value, array( 'http', 'https' ) );
	$parts = $url ? wp_parse_url( $url ) : false;

	if ( ! is_array( $parts ) || empty( $parts['scheme'] ) || empty( $parts['host'] ) ) {
		return '';
	}

	return in_array( strtolower( $parts['scheme'] ), array( 'http', 'https' ), true ) ? $url : '';
}

/**
 * Return the first safe media URL from common native/lazy attributes.
 *
 * @internal
 * @param array<string, mixed> $attributes Attribute values keyed by name.
 * @param bool                 $allow_srcset Whether to inspect srcset candidates.
 * @return string
 */
function fahar_theme_get_portfolio_media_candidate_url( $attributes, $allow_srcset = false ) {
	$attributes = is_array( $attributes ) ? $attributes : array();

	foreach ( array( 'src', 'data-src', 'data-lazy-src', 'data-original', 'data-orig-file' ) as $attribute ) {
		$url = isset( $attributes[ $attribute ] ) ? fahar_theme_normalize_http_url( $attributes[ $attribute ] ) : '';

		if ( $url ) {
			return $url;
		}
	}

	if ( ! $allow_srcset ) {
		return '';
	}

	foreach ( array( 'srcset', 'data-srcset' ) as $attribute ) {
		if ( empty( $attributes[ $attribute ] ) || ! is_string( $attributes[ $attribute ] ) ) {
			continue;
		}

		$valid_url = '';

		foreach ( explode( ',', $attributes[ $attribute ] ) as $candidate ) {
			$candidate_parts = preg_split( '/\s+/', trim( $candidate ) );
			$candidate_url   = isset( $candidate_parts[0] ) ? fahar_theme_normalize_http_url( $candidate_parts[0] ) : '';

			if ( $candidate_url ) {
				$valid_url = $candidate_url;
			}
		}

		if ( $valid_url ) {
			return $valid_url;
		}
	}

	return '';
}

/**
 * Determine whether an image candidate is an inert loading surface.
 *
 * @internal
 * @param string               $url           Normalized image URL.
 * @param int                  $attachment_id Verified WordPress attachment ID.
 * @param array<string, mixed> $attributes    Source image attributes.
 * @return bool
 */
function fahar_theme_is_portfolio_placeholder_image( $url, $attachment_id = 0, $attributes = array() ) {
	if ( fahar_theme_normalize_image_attachment_id( $attachment_id ) ) {
		return false;
	}

	$url        = fahar_theme_normalize_http_url( $url );
	$attributes = is_array( $attributes ) ? $attributes : array();
	$width      = isset( $attributes['width'] ) ? absint( $attributes['width'] ) : 0;
	$height     = isset( $attributes['height'] ) ? absint( $attributes['height'] ) : 0;
	$path       = $url ? (string) wp_parse_url( $url, PHP_URL_PATH ) : '';
	$file_name  = strtolower( basename( $path ) );

	return ! $url
		|| ( $width && $height && $width <= 1 && $height <= 1 )
		|| 1 === preg_match( '/(?:^|[-_.])(blank|loading|placeholder|spacer|transparent)(?:[-_.]|$)/i', $file_name );
}

/**
 * Return a real image URL while skipping inert lazy-loading surfaces.
 *
 * @internal
 * @param array<string, mixed> $attributes    Image source attributes.
 * @param int                  $attachment_id Verified WordPress attachment ID.
 * @return string
 */
function fahar_theme_get_portfolio_image_candidate_url( $attributes, $attachment_id = 0 ) {
	$attributes = is_array( $attributes ) ? $attributes : array();

	foreach ( array( 'src', 'data-src', 'data-lazy-src', 'data-original', 'data-orig-file' ) as $attribute ) {
		$url = isset( $attributes[ $attribute ] ) ? fahar_theme_normalize_http_url( $attributes[ $attribute ] ) : '';

		if ( ! $url ) {
			continue;
		}

		$placeholder_attributes = 'src' === $attribute ? $attributes : array();

		if ( ! fahar_theme_is_portfolio_placeholder_image( $url, $attachment_id, $placeholder_attributes ) ) {
			return $url;
		}
	}

	$url = fahar_theme_get_portfolio_media_candidate_url(
		array(
			'srcset'      => isset( $attributes['srcset'] ) ? $attributes['srcset'] : '',
			'data-srcset' => isset( $attributes['data-srcset'] ) ? $attributes['data-srcset'] : '',
		),
		true
	);

	return $url && ! fahar_theme_is_portfolio_placeholder_image( $url, $attachment_id ) ? $url : '';
}

/**
 * Read supported media source attributes from a DOM element.
 *
 * @internal
 * @param DOMElement $node DOM media node.
 * @return array<string, string>
 */
function fahar_theme_get_portfolio_dom_media_attributes( $node ) {
	$attributes = array();

	if ( ! $node instanceof DOMElement ) {
		return $attributes;
	}

	foreach ( array( 'src', 'data-src', 'data-lazy-src', 'data-original', 'data-orig-file', 'srcset', 'data-srcset', 'poster', 'data-poster', 'alt', 'width', 'height' ) as $attribute ) {
		$attributes[ $attribute ] = $node->getAttribute( $attribute );
	}

	return $attributes;
}

/**
 * Return the verified external URL for a Website portfolio item.
 *
 * @param int|WP_Post|null $post Post ID, post object, or current post.
 * @return string
 */
function fahar_theme_get_portfolio_external_url( $post = null ) {
	$post = fahar_theme_normalize_portfolio_post( $post );

	if ( ! $post || 'website' !== fahar_theme_get_portfolio_type( $post ) ) {
		return '';
	}

	$url = fahar_theme_normalize_http_url( fahar_theme_get_portfolio_meta_value( $post, 'external_url' ) );
	$url = apply_filters( 'fahar_theme_portfolio_external_url', $url, $post );

	return fahar_theme_normalize_http_url( $url );
}

/**
 * Determine whether a hostname is a domain or one of its subdomains.
 *
 * @internal
 * @param string $host   Hostname.
 * @param string $domain Domain to match.
 * @return bool
 */
function fahar_theme_portfolio_host_matches( $host, $domain ) {
	$host   = strtolower( trim( $host, '.' ) );
	$domain = strtolower( trim( $domain, '.' ) );

	$is_subdomain = strlen( $host ) > strlen( $domain )
		&& '.' . $domain === substr( $host, -1 - strlen( $domain ) );

	return $host === $domain || $is_subdomain;
}

/**
 * Detect a normalized video provider from a URL without remote requests.
 *
 * @internal
 * @param string $url HTTP(S) or protocol-relative URL.
 * @return string
 */
function fahar_theme_detect_portfolio_video_provider( $url ) {
	$url = trim( html_entity_decode( (string) $url, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );

	if ( 0 === strpos( $url, '//' ) ) {
		$url = 'https:' . $url;
	}

	$parts = wp_parse_url( $url );
	$host  = is_array( $parts ) && ! empty( $parts['host'] ) ? strtolower( $parts['host'] ) : '';

	$is_youtube = fahar_theme_portfolio_host_matches( $host, 'youtube.com' )
		|| fahar_theme_portfolio_host_matches( $host, 'youtu.be' )
		|| fahar_theme_portfolio_host_matches( $host, 'youtube-nocookie.com' );

	if ( $is_youtube ) {
		return 'youtube';
	}

	if ( fahar_theme_portfolio_host_matches( $host, 'vimeo.com' ) ) {
		return 'vimeo';
	}

	if ( fahar_theme_portfolio_host_matches( $host, 'aparat.com' ) ) {
		return 'aparat';
	}

	$path      = is_array( $parts ) && isset( $parts['path'] ) ? $parts['path'] : '';
	$extension = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );

	return $extension && in_array( $extension, wp_get_video_extensions(), true ) ? 'self-hosted' : 'unknown';
}

/**
 * Extract an iframe source solely for provider classification.
 *
 * @internal
 * @param string $embed Raw, untrusted embed HTML.
 * @return string
 */
function fahar_theme_get_portfolio_iframe_src( $embed ) {
	if ( ! is_string( $embed ) || ! preg_match( '/<iframe\b(?<attributes>[^>]*)>/i', $embed, $iframe_match ) ) {
		return '';
	}

	$attributes = isset( $iframe_match['attributes'] ) ? $iframe_match['attributes'] : '';

	foreach ( array( 'src', 'data-src', 'data-lazy-src' ) as $attribute ) {
		$pattern = '/(?:^|\s)' . preg_quote( $attribute, '/' ) . '\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s>]+))/i';

		if ( ! preg_match( $pattern, $attributes, $matches ) ) {
			continue;
		}

		foreach ( array( 1, 2, 3 ) as $index ) {
			if ( ! empty( $matches[ $index ] ) ) {
				$src = html_entity_decode( $matches[ $index ], ENT_QUOTES | ENT_HTML5, 'UTF-8' );
				$src = 0 === strpos( $src, '//' ) ? 'https:' . $src : $src;

				if ( fahar_theme_normalize_http_url( $src ) ) {
					return $src;
				}
			}
		}
	}

	return '';
}

/**
 * Normalize a stored video source without rendering or executing it.
 *
 * The returned array always contains type, provider, and value keys. Raw embed
 * and unknown legacy values remain untrusted data for a future media renderer.
 *
 * @param int|WP_Post|null $post Post ID, post object, or current post.
 * @return array{type:string,provider:string,value:mixed}
 */
function fahar_theme_get_portfolio_video_source( $post = null ) {
	$empty = array(
		'type'     => 'none',
		'provider' => 'none',
		'value'    => '',
	);
	$post  = fahar_theme_normalize_portfolio_post( $post );

	if ( ! $post ) {
		return $empty;
	}

	$raw_value = fahar_theme_get_portfolio_meta_value( $post, 'video_source' );
	$source    = $empty;

	if ( is_string( $raw_value ) && '' !== trim( $raw_value ) ) {
		$candidate = trim( $raw_value );
		$url       = fahar_theme_normalize_http_url( $candidate );

		if ( $url ) {
			$source = array(
				'type'     => 'url',
				'provider' => fahar_theme_detect_portfolio_video_provider( $url ),
				'value'    => $url,
			);
		} elseif ( preg_match( '/<\s*(?:iframe|embed|object|video|script)\b/i', $candidate ) ) {
			$iframe_src = fahar_theme_get_portfolio_iframe_src( $raw_value );
			$source     = array(
				'type'     => 'embed',
				'provider' => $iframe_src ? fahar_theme_detect_portfolio_video_provider( $iframe_src ) : 'unknown',
				'value'    => $raw_value,
			);
		} else {
			$source = array(
				'type'     => 'unknown',
				'provider' => 'unknown',
				'value'    => $raw_value,
			);
		}
	} elseif ( ! empty( $raw_value ) ) {
		$source = array(
			'type'     => 'unknown',
			'provider' => 'unknown',
			'value'    => $raw_value,
		);
	}

	$source            = (array) apply_filters( 'fahar_theme_portfolio_video_source', $source, $post, $raw_value );
	$allowed_types     = array( 'url', 'embed', 'unknown', 'none' );
	$allowed_providers = array( 'youtube', 'vimeo', 'aparat', 'self-hosted', 'unknown', 'none' );
	$type              = isset( $source['type'] ) ? sanitize_key( (string) $source['type'] ) : 'none';
	$provider          = isset( $source['provider'] ) ? sanitize_key( (string) $source['provider'] ) : 'none';

	if ( ! in_array( $type, $allowed_types, true ) || ! in_array( $provider, $allowed_providers, true ) ) {
		return $empty;
	}

	if ( 'none' === $type ) {
		return $empty;
	}

	$value = isset( $source['value'] ) ? $source['value'] : '';

	if ( 'url' === $type ) {
		$value = fahar_theme_normalize_http_url( $value );

		if ( ! $value ) {
			return $empty;
		}

		$provider = fahar_theme_detect_portfolio_video_provider( $value );
	} elseif ( 'embed' === $type && ! is_string( $value ) ) {
		return $empty;
	}

	return array(
		'type'     => $type,
		'provider' => $provider,
		'value'    => $value,
	);
}

/**
 * Convert a verified video URL into a normalized renderer item.
 *
 * Provider URLs are rebuilt from locally parsed IDs. Arbitrary iframe markup
 * and unrecognized remote URLs never reach the renderer.
 *
 * @internal
 * @param string $url   Verified source URL.
 * @param string $title Accessible portfolio title.
 * @return array<string, mixed>
 */
function fahar_theme_prepare_portfolio_video_media_item( $url, $title ) {
	$url   = is_string( $url ) && 0 === strpos( trim( $url ), '//' ) ? 'https:' . trim( $url ) : $url;
	$url   = fahar_theme_normalize_http_url( $url );
	$parts = $url ? wp_parse_url( $url ) : false;

	if ( ! is_array( $parts ) || empty( $parts['host'] ) || empty( $parts['path'] ) ) {
		return array();
	}

	$host = strtolower( $parts['host'] );
	$path = rawurldecode( $parts['path'] );

	if ( 'youtube' === fahar_theme_detect_portfolio_video_provider( $url ) ) {
		$video_id = '';

		if ( fahar_theme_portfolio_host_matches( $host, 'youtu.be' ) ) {
			$path_segments = explode( '/', ltrim( $path, '/' ) );
			$video_id     = isset( $path_segments[0] ) ? $path_segments[0] : '';
		} elseif ( preg_match( '#/(?:embed|shorts)/([A-Za-z0-9_-]{11})(?:/|$)#', $path, $matches ) ) {
			$video_id = $matches[1];
		} elseif ( isset( $parts['query'] ) && preg_match( '/(?:^|&)v=([^&]+)/', $parts['query'], $matches ) ) {
			$video_id = rawurldecode( $matches[1] );
		}

		if ( 1 === preg_match( '/^[A-Za-z0-9_-]{11}$/', (string) $video_id ) ) {
			$safe_query = array();
			$query      = array();
			wp_parse_str( isset( $parts['query'] ) ? $parts['query'] : '', $query );

			foreach ( array( 'start', 'end', 'index' ) as $key ) {
				if ( isset( $query[ $key ] ) && is_scalar( $query[ $key ] ) && ctype_digit( (string) $query[ $key ] ) ) {
					$safe_query[ $key ] = (string) absint( $query[ $key ] );
				}
			}

			if ( isset( $query['list'] ) && is_scalar( $query['list'] ) && 1 === preg_match( '/^[A-Za-z0-9_-]{1,128}$/', (string) $query['list'] ) ) {
				$safe_query['list'] = (string) $query['list'];
			}

			if ( isset( $query['playlist'] ) && is_scalar( $query['playlist'] ) && 1 === preg_match( '/^[A-Za-z0-9_-]{11}(?:,[A-Za-z0-9_-]{11})*$/', (string) $query['playlist'] ) ) {
				$safe_query['playlist'] = (string) $query['playlist'];
			}

			$source = 'https://www.youtube-nocookie.com/embed/' . $video_id;

			if ( $safe_query ) {
				$source = add_query_arg( $safe_query, $source );
			}

			return array(
				'type'     => 'iframe',
				'source'   => $source,
				'provider' => 'youtube',
				'title'    => sprintf(
					/* translators: %s: portfolio title. */
					__( 'ویدیوی یوتیوب برای %s', 'fahar-theme-child' ),
					$title
				),
			);
		}

		return array();
	}

	if ( 'vimeo' === fahar_theme_detect_portfolio_video_provider( $url ) ) {
		$path_segments = array_values( array_filter( explode( '/', trim( $path, '/' ) ) ) );
		$video_id     = '';

		foreach ( array_reverse( $path_segments ) as $path_segment ) {
			if ( 1 === preg_match( '/^\d{1,20}$/', $path_segment ) ) {
				$video_id = $path_segment;
				break;
			}
		}

		if ( $video_id ) {
			$query = array();
			wp_parse_str( isset( $parts['query'] ) ? $parts['query'] : '', $query );
			$source = 'https://player.vimeo.com/video/' . $video_id;

			if ( isset( $query['h'] ) && is_scalar( $query['h'] ) && 1 === preg_match( '/^[A-Za-z0-9]{6,64}$/', (string) $query['h'] ) ) {
				$source = add_query_arg( 'h', (string) $query['h'], $source );
			}

			return array(
				'type'     => 'iframe',
				'source'   => $source,
				'provider' => 'vimeo',
				'title'    => sprintf(
					/* translators: %s: portfolio title. */
					__( 'ویدیوی ویمئو برای %s', 'fahar-theme-child' ),
					$title
				),
			);
		}

		return array();
	}

	if ( 'aparat' === fahar_theme_detect_portfolio_video_provider( $url ) ) {
		$video_id = '';

		if ( preg_match( '#/(?:v|videohash)/([A-Za-z0-9_-]{1,64})(?:/|$)#', $path, $matches ) ) {
			$video_id = $matches[1];
		}

		if ( $video_id ) {
			return array(
				'type'     => 'iframe',
				'source'   => 'https://www.aparat.com/video/video/embed/videohash/' . rawurlencode( $video_id ) . '/vt/frame',
				'provider' => 'aparat',
				'title'    => sprintf(
					/* translators: %s: portfolio title. */
					__( 'ویدیوی آپارات برای %s', 'fahar-theme-child' ),
					$title
				),
			);
		}

		return array();
	}

	if ( 'self-hosted' !== fahar_theme_detect_portfolio_video_provider( $url ) ) {
		return array();
	}

	$file_type = wp_check_filetype( basename( $path ) );
	$mime_type = isset( $file_type['type'] ) && 0 === strpos( $file_type['type'], 'video/' ) ? $file_type['type'] : '';

	return array(
		'type'     => 'video',
		'source'   => $url,
		'provider' => 'self-hosted',
		'mime'     => $mime_type,
		'title'    => sprintf(
			/* translators: %s: portfolio title. */
			__( 'ویدیو برای %s', 'fahar-theme-child' ),
			$title
		),
	);
}

/**
 * Normalize one content image without trusting stored HTML attributes.
 *
 * @internal
 * @param mixed  $attachment_id Possible WordPress attachment ID.
 * @param mixed  $url           Possible image URL.
 * @param string $title         Accessible fallback title.
 * @param array  $attributes    Optional source attributes.
 * @return array<string, mixed>
 */
function fahar_theme_prepare_portfolio_content_image_item( $attachment_id, $url, $title, $attributes = array() ) {
	$url           = is_string( $url ) && 0 === strpos( trim( $url ), '//' ) ? 'https:' . trim( $url ) : $url;
	$attachment_id = fahar_theme_normalize_image_attachment_id( $attachment_id );
	$url           = fahar_theme_normalize_http_url( $url );

	if ( ! $attachment_id && $url ) {
		$attachment_id = fahar_theme_normalize_image_attachment_id( attachment_url_to_postid( $url ) );
	}

	if ( ( ! $attachment_id && ! $url ) || fahar_theme_is_portfolio_placeholder_image( $url, $attachment_id, $attributes ) ) {
		return array();
	}

	$item_title = isset( $attributes['alt'] ) && is_scalar( $attributes['alt'] ) ? trim( (string) $attributes['alt'] ) : '';
	$item       = array(
		'type'          => 'image',
		'provider'      => $attachment_id ? 'wordpress' : 'external',
		'attachment_id' => $attachment_id,
		'url'           => $url,
		'origin'        => 'content',
		'title'         => $item_title ? $item_title : $title,
	);

	foreach ( array( 'width', 'height' ) as $dimension ) {
		if ( isset( $attributes[ $dimension ] ) && absint( $attributes[ $dimension ] ) ) {
			$item[ $dimension ] = absint( $attributes[ $dimension ] );
		}
	}

	return $item;
}

/**
 * Extract supported media from one WordPress media shortcode.
 *
 * @internal
 * @param string  $shortcode Full shortcode source.
 * @param WP_Post $post      Portfolio post.
 * @param string  $title     Accessible portfolio title.
 * @return array<int, array<string, mixed>>
 */
function fahar_theme_get_portfolio_shortcode_media_items( $shortcode, $post, $title ) {
	$items = array();

	if ( ! preg_match( '/' . get_shortcode_regex( array( 'gallery', 'video', 'embed', 'wpvideo' ) ) . '/s', $shortcode, $matches ) ) {
		return $items;
	}

	if ( isset( $matches[1], $matches[6] ) && '[' === $matches[1] && ']' === $matches[6] ) {
		return $items;
	}

	$tag        = isset( $matches[2] ) ? sanitize_key( $matches[2] ) : '';
	$attributes = isset( $matches[3] ) ? shortcode_parse_atts( $matches[3] ) : array();
	$attributes = is_array( $attributes ) ? $attributes : array();

	if ( 'gallery' === $tag ) {
		$image_ids = array();

		if ( ! empty( $attributes['ids'] ) ) {
			$image_ids = wp_parse_id_list( $attributes['ids'] );
		} elseif ( ! empty( $attributes['include'] ) ) {
			$image_ids = wp_parse_id_list( $attributes['include'] );
		} else {
			$children = get_posts(
				array(
					'post_parent'    => $post->ID,
					'post_status'    => 'inherit',
					'post_type'      => 'attachment',
					'post_mime_type' => 'image',
					'orderby'        => 'menu_order ID',
					'order'          => 'ASC',
					'fields'         => 'ids',
				)
			);
			$image_ids = array_map( 'absint', (array) $children );
		}

		if ( ! empty( $attributes['exclude'] ) ) {
			$image_ids = array_diff( $image_ids, wp_parse_id_list( $attributes['exclude'] ) );
		}

		foreach ( $image_ids as $image_id ) {
			$item = fahar_theme_prepare_portfolio_content_image_item( $image_id, '', $title );

			if ( $item ) {
				$items[] = $item;
			}
		}
	} elseif ( 'video' === $tag ) {
		if ( isset( $matches[5] ) && '' !== trim( $matches[5] ) ) {
			$attributes['src'] = trim( wp_strip_all_tags( $matches[5] ) );
		}

		foreach ( array( 'src', 'mp4', 'm4v', 'webm', 'ogv', 'flv', 'wmv' ) as $attribute ) {
			if ( empty( $attributes[ $attribute ] ) ) {
				continue;
			}

			$item = fahar_theme_prepare_portfolio_video_media_item( $attributes[ $attribute ], $title );

			if ( $item ) {
				$item['origin'] = 'content';
				$items[]        = $item;
				break;
			}
		}
	} elseif ( in_array( $tag, array( 'embed', 'wpvideo' ), true ) ) {
		$url  = isset( $matches[5] ) ? trim( wp_strip_all_tags( $matches[5] ) ) : '';
		$item = fahar_theme_prepare_portfolio_video_media_item( $url, $title );

		if ( $item ) {
			$item['origin'] = 'content';
			$items[]        = $item;
		}
	}

	return $items;
}

/**
 * Extract supported Classic Editor media from an HTML/content fragment.
 *
 * Shortcodes are replaced with inert placeholders before DOM traversal so
 * their position relative to HTML media remains stable.
 *
 * @internal
 * @param string  $content Content fragment.
 * @param WP_Post $post    Portfolio post.
 * @param string  $title   Accessible portfolio title.
 * @return array<int, array<string, mixed>>
 */
function fahar_theme_get_portfolio_html_media_items( $content, $post, $title ) {
	if ( '' === trim( $content ) ) {
		return array();
	}

	$shortcodes = array();
	$pattern    = '/' . get_shortcode_regex( array( 'gallery', 'video', 'embed', 'wpvideo' ) ) . '/s';
	$content    = preg_replace_callback(
		$pattern,
		static function ( $matches ) use ( &$shortcodes ) {
			if ( isset( $matches[1], $matches[6] ) && '[' === $matches[1] && ']' === $matches[6] ) {
				return $matches[0];
			}

			$index                 = count( $shortcodes );
			$shortcodes[ $index ] = $matches[0];

			return '<span data-fahar-media-token="' . absint( $index ) . '"></span>';
		},
		$content
	);

	if ( ! is_string( $content ) ) {
		return array();
	}

	if ( ! class_exists( 'DOMDocument' ) && class_exists( 'WP_HTML_Tag_Processor' ) ) {
		$items     = array();
		$processor = new WP_HTML_Tag_Processor( $content );

		while ( $processor->next_tag() ) {
			$tag = strtolower( (string) $processor->get_tag() );

			if ( 'span' === $tag && null !== $processor->get_attribute( 'data-fahar-media-token' ) ) {
				$index = absint( $processor->get_attribute( 'data-fahar-media-token' ) );

				if ( isset( $shortcodes[ $index ] ) ) {
					$items = array_merge( $items, fahar_theme_get_portfolio_shortcode_media_items( $shortcodes[ $index ], $post, $title ) );
				}
			} elseif ( 'img' === $tag ) {
				$class_name    = (string) $processor->get_attribute( 'class' );
				$attachment_id = absint( $processor->get_attribute( 'data-id' ) );
				$attributes    = array();

				foreach ( array( 'src', 'data-src', 'data-lazy-src', 'data-original', 'data-orig-file', 'srcset', 'data-srcset', 'alt', 'width', 'height' ) as $attribute ) {
					$attributes[ $attribute ] = $processor->get_attribute( $attribute );
				}

				if ( ! $attachment_id && preg_match( '/(?:^|\s)wp-image-(\d+)(?:\s|$)/', $class_name, $matches ) ) {
					$attachment_id = absint( $matches[1] );
				}

				$item = fahar_theme_prepare_portfolio_content_image_item(
					$attachment_id,
					fahar_theme_get_portfolio_image_candidate_url( $attributes, $attachment_id ),
					$title,
					$attributes
				);

				if ( $item ) {
					$items[] = $item;
				}
			} elseif ( in_array( $tag, array( 'video', 'source', 'iframe', 'embed', 'object' ), true ) ) {
				$source_attributes = array();

				foreach ( array( 'src', 'data-src', 'data-lazy-src' ) as $attribute ) {
					$source_attributes[ $attribute ] = $processor->get_attribute( $attribute );
				}

				if ( 'object' === $tag ) {
					$source_attributes['src'] = $processor->get_attribute( 'data' );
				}

				$url  = fahar_theme_get_portfolio_media_candidate_url( $source_attributes );
				$item = fahar_theme_prepare_portfolio_video_media_item( $url, $title );

				if ( $item ) {
					$item['origin'] = 'content';
					$items[]        = $item;
				}
			}
		}

		return $items;
	}

	if ( ! class_exists( 'DOMDocument' ) ) {
		$items = array();

		foreach ( $shortcodes as $shortcode ) {
			$items = array_merge( $items, fahar_theme_get_portfolio_shortcode_media_items( $shortcode, $post, $title ) );
		}

		return $items;
	}

	$dom            = new DOMDocument();
	$previous_state = libxml_use_internal_errors( true );
	$loaded         = $dom->loadHTML(
		'<?xml encoding="utf-8" ?><div id="fahar-content-media-root">' . $content . '</div>',
		LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET
	);
	libxml_clear_errors();
	libxml_use_internal_errors( $previous_state );

	if ( ! $loaded ) {
		return array();
	}

	$xpath = new DOMXPath( $dom );
	$root  = $xpath->query( '//*[@id="fahar-content-media-root"]' )->item( 0 );
	$items = array();

	if ( ! $root instanceof DOMElement ) {
		return $items;
	}

	$walk = static function ( $node ) use ( &$walk, &$items, $shortcodes, $post, $title ) {
		if ( $node instanceof DOMText ) {
			$ancestor = $node->parentNode;

			while ( $ancestor instanceof DOMElement ) {
				if ( in_array( strtolower( $ancestor->tagName ), array( 'a', 'code', 'pre', 'script', 'style' ), true ) ) {
					return;
				}

				$ancestor = $ancestor->parentNode;
			}

			if ( ! is_string( $node->nodeValue ) ) {
				return;
			}

			foreach ( (array) preg_split( '/\R/u', $node->nodeValue ) as $line ) {
				$url = fahar_theme_normalize_http_url( trim( $line ) );

				if ( ! $url ) {
					continue;
				}

				$item = fahar_theme_prepare_portfolio_video_media_item( $url, $title );

				if ( $item ) {
					$item['origin'] = 'content';
					$items[]        = $item;
				}
			}

			return;
		}

		if ( ! $node instanceof DOMElement ) {
			return;
		}

		$tag = strtolower( $node->tagName );

		if ( $node->hasAttribute( 'data-fahar-media-token' ) ) {
			$index = absint( $node->getAttribute( 'data-fahar-media-token' ) );

			if ( isset( $shortcodes[ $index ] ) ) {
				$items = array_merge( $items, fahar_theme_get_portfolio_shortcode_media_items( $shortcodes[ $index ], $post, $title ) );
			}

			return;
		}

		if ( 'img' === $tag ) {
			$attachment_id = absint( $node->getAttribute( 'data-id' ) );
			$class_name    = $node->getAttribute( 'class' );
			$attributes    = fahar_theme_get_portfolio_dom_media_attributes( $node );

			if ( ! $attachment_id && preg_match( '/(?:^|\s)wp-image-(\d+)(?:\s|$)/', $class_name, $matches ) ) {
				$attachment_id = absint( $matches[1] );
			}

			$item = fahar_theme_prepare_portfolio_content_image_item(
				$attachment_id,
				fahar_theme_get_portfolio_image_candidate_url( $attributes, $attachment_id ),
				$title,
				$attributes
			);

			if ( $item ) {
				$items[] = $item;
			}

			return;
		}

		if ( 'video' === $tag ) {
			$urls = array( fahar_theme_get_portfolio_media_candidate_url( fahar_theme_get_portfolio_dom_media_attributes( $node ) ) );

			foreach ( $node->getElementsByTagName( 'source' ) as $source ) {
				$urls[] = fahar_theme_get_portfolio_media_candidate_url( fahar_theme_get_portfolio_dom_media_attributes( $source ) );
			}

			foreach ( $urls as $url ) {
				$item = fahar_theme_prepare_portfolio_video_media_item( $url, $title );

				if ( $item ) {
					$item['origin'] = 'content';
					$items[]        = $item;
					break;
				}
			}

			return;
		}

		if ( in_array( $tag, array( 'iframe', 'embed', 'object' ), true ) ) {
			$attributes = fahar_theme_get_portfolio_dom_media_attributes( $node );

			if ( 'object' === $tag ) {
				$attributes['src'] = $node->getAttribute( 'data' );
			}

			$url  = fahar_theme_get_portfolio_media_candidate_url( $attributes );
			$item = fahar_theme_prepare_portfolio_video_media_item( $url, $title );

			if ( $item ) {
				$item['origin'] = 'content';
				$items[]        = $item;
			}

			return;
		}

		foreach ( iterator_to_array( $node->childNodes ) as $child ) {
			$walk( $child );
		}
	};

	foreach ( iterator_to_array( $root->childNodes ) as $child ) {
		$walk( $child );
	}

	return $items;
}

/**
 * Split one final rendered portfolio document into media and description.
 *
 * DOMDocument is used when available so extraction and media removal share one
 * document-order pass. The fallback preserves safe legacy behavior on hosts
 * without the DOM extension.
 *
 * @internal
 * @param string  $html  Rendered portfolio content.
 * @param WP_Post $post  Portfolio post.
 * @param string  $title Accessible fallback title.
 * @return array{media:array<int, array<string, mixed>>,description:string}
 */
function fahar_theme_split_portfolio_rendered_content( $html, $post, $title ) {
	$empty = array(
		'media'       => array(),
		'description' => '',
	);
	$html  = (string) $html;

	if ( '' === trim( $html ) ) {
		return $empty;
	}

	if ( ! class_exists( 'DOMDocument' ) ) {
		$empty['media']       = fahar_theme_get_portfolio_html_media_items( $html, $post, $title );
		$empty['description'] = fahar_theme_strip_portfolio_description_media( $html );

		return $empty;
	}

	$dom            = new DOMDocument();
	$previous_state = libxml_use_internal_errors( true );
	$loaded         = $dom->loadHTML(
		'<?xml encoding="utf-8" ?><div id="fahar-content-split-root">' . $html . '</div>',
		LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET
	);
	libxml_clear_errors();
	libxml_use_internal_errors( $previous_state );

	if ( ! $loaded ) {
		$empty['description'] = trim( wp_kses_post( $html ) );
		return $empty;
	}

	$xpath = new DOMXPath( $dom );
	$root  = $xpath->query( '//*[@id="fahar-content-split-root"]' )->item( 0 );

	if ( ! $root instanceof DOMElement ) {
		$empty['description'] = trim( wp_kses_post( $html ) );
		return $empty;
	}

	$items         = array();
	$get_attachment = static function ( $node, $url = '' ) {
		$attachment_id = 0;

		if ( $node instanceof DOMElement ) {
			foreach ( array( 'data-id', 'data-attachment-id' ) as $attribute ) {
				$attachment_id = absint( $node->getAttribute( $attribute ) );

				if ( $attachment_id ) {
					break;
				}
			}

			if ( ! $attachment_id && preg_match( '/(?:^|\s)wp-(?:image|video)-(\d+)(?:\s|$)/', $node->getAttribute( 'class' ), $matches ) ) {
				$attachment_id = absint( $matches[1] );
			}
		}

		if ( ! $attachment_id && is_string( $url ) && $url ) {
			$attachment_id = attachment_url_to_postid( $url );
		}

		return fahar_theme_normalize_attachment_id( $attachment_id );
	};
	$remove_node    = static function ( $node ) use ( $root ) {
		if ( ! $node instanceof DOMNode || ! $node->parentNode ) {
			return;
		}

		$parent = $node->parentNode;
		$parent->removeChild( $node );

		while ( $parent instanceof DOMElement && $parent !== $root ) {
			if ( '' !== trim( $parent->textContent ) || $parent->hasChildNodes() ) {
				break;
			}

			$next_parent = $parent->parentNode;

			if ( ! $next_parent ) {
				break;
			}

			$next_parent->removeChild( $parent );
			$parent = $next_parent;
		}
	};
	$node_order = new SplObjectStorage();

	foreach ( iterator_to_array( $xpath->query( './/picture|.//img|.//video|.//iframe|.//embed|.//object', $root ) ) as $index => $ordered_node ) {
		if ( $ordered_node instanceof DOMElement ) {
			$node_order[ $ordered_node ] = absint( $index );
		}
	}

	$video_nodes = iterator_to_array( $xpath->query( './/video|.//iframe|.//embed|.//object', $root ) );

	foreach ( $video_nodes as $node ) {
		if ( ! $node instanceof DOMElement || ! $node->parentNode ) {
			continue;
		}

		$tag        = strtolower( $node->tagName );
		$attributes = fahar_theme_get_portfolio_dom_media_attributes( $node );
		$urls       = array();

		if ( 'video' === $tag ) {
			$urls[] = fahar_theme_get_portfolio_media_candidate_url( $attributes );

			foreach ( $node->getElementsByTagName( 'source' ) as $source ) {
				$urls[] = fahar_theme_get_portfolio_media_candidate_url( fahar_theme_get_portfolio_dom_media_attributes( $source ) );
			}
		} else {
			if ( 'object' === $tag ) {
				$attributes['src'] = $node->getAttribute( 'data' );
			}

			$urls[] = fahar_theme_get_portfolio_media_candidate_url( $attributes );
		}

		$item = array();
		$url  = '';

		foreach ( $urls as $candidate_url ) {
			$item = fahar_theme_prepare_portfolio_video_media_item( $candidate_url, $title );

			if ( $item ) {
				$url = $candidate_url;
				break;
			}
		}

		if ( $item ) {
			$item['_content_order'] = $node_order->contains( $node ) ? absint( $node_order[ $node ] ) : count( $items );
			$attachment_id = $get_attachment( $node, $url );

			if ( $attachment_id ) {
				$item['attachment_id'] = $attachment_id;
			}

			$poster_node    = null;
			$poster_wrapper = null;
			$poster_id      = 0;
			$poster_url     = fahar_theme_normalize_http_url(
				! empty( $attributes['poster'] ) ? $attributes['poster'] : ( isset( $attributes['data-poster'] ) ? $attributes['data-poster'] : '' )
			);

			if ( ! $poster_url && 'video' !== $tag ) {
				$ancestor = $node->parentNode;
				$depth    = 0;

				while ( $ancestor instanceof DOMElement && $ancestor !== $root && $depth < 4 ) {
					$class_name  = strtolower( $ancestor->getAttribute( 'class' ) );
					$tag_name    = strtolower( $ancestor->tagName );
					$video_count = $xpath->query( './/video|.//iframe|.//embed|.//object', $ancestor )->length;
					$images      = $xpath->query( './/img', $ancestor );
					$has_prose   = $xpath->query( './/figcaption|.//p|.//h1|.//h2|.//h3|.//h4|.//h5|.//h6|.//ul|.//ol|.//blockquote', $ancestor )->length > 0;
					$is_media_ui = 'figure' === $tag_name
						|| 1 === preg_match( '/(?:^|[\s_-])(?:video|embed|player|media|lightbox)(?:[\s_-]|$)/', $class_name )
						|| ( ! $has_prose && '' === trim( $ancestor->textContent ) );

					if ( $is_media_ui && 1 === $video_count && $images->length > 0 ) {
						$poster_node    = $images->item( 0 );
						$poster_wrapper = $ancestor;
						break;
					}

					$ancestor = $ancestor->parentNode;
					++$depth;
				}

				if ( $poster_node instanceof DOMElement ) {
					if ( $node_order->contains( $poster_node ) ) {
						$item['_content_order'] = min( $item['_content_order'], absint( $node_order[ $poster_node ] ) );
					}

					$poster_attributes = fahar_theme_get_portfolio_dom_media_attributes( $poster_node );
					$poster_id         = $get_attachment( $poster_node );
					$poster_url        = fahar_theme_get_portfolio_image_candidate_url( $poster_attributes, $poster_id );
				}
			}

			if ( $poster_url ) {
				$poster_id = $poster_id ? fahar_theme_normalize_image_attachment_id( $poster_id ) : fahar_theme_normalize_image_attachment_id( attachment_url_to_postid( $poster_url ) );

				if ( $poster_id ) {
					$item['poster_attachment_id'] = $poster_id;
				}

				$item['poster_url'] = $poster_url;
			}

			$item['origin'] = 'content';
			$items[]        = $item;

			$remove_wrapper = $poster_wrapper instanceof DOMElement
				&& 0 === $xpath->query( './/figcaption|.//p|.//h1|.//h2|.//h3|.//h4|.//h5|.//h6|.//ul|.//ol|.//blockquote', $poster_wrapper )->length;

			if ( $remove_wrapper ) {
				$remove_node( $poster_wrapper );
			} else {
				if ( $poster_node instanceof DOMElement && $poster_node->parentNode ) {
					$remove_node( $poster_node );
				}

				$remove_node( $node );
			}
		} elseif ( in_array( $tag, array( 'iframe', 'embed', 'object' ), true ) ) {
			$remove_node( $node );
		}
	}

	$media_nodes = iterator_to_array( $xpath->query( './/picture|.//img', $root ) );

	foreach ( $media_nodes as $node ) {
		if ( ! $node instanceof DOMElement || ! $node->parentNode ) {
			continue;
		}

		$tag        = strtolower( $node->tagName );
		$parent_tag = $node->parentNode instanceof DOMElement ? strtolower( $node->parentNode->tagName ) : '';
		$image      = 'picture' === $tag ? $xpath->query( './/img', $node )->item( 0 ) : $node;

		if ( ! $image instanceof DOMElement || ( 'img' === $tag && 'picture' === $parent_tag ) ) {
			continue;
		}

		$attributes    = fahar_theme_get_portfolio_dom_media_attributes( $image );
		$attachment_id = $get_attachment( $image );
		$url           = fahar_theme_get_portfolio_image_candidate_url( $attributes, $attachment_id );
		$item          = fahar_theme_prepare_portfolio_content_image_item( $attachment_id, $url, $title, $attributes );

		if ( $item ) {
			$item['_content_order'] = $node_order->contains( $node ) ? absint( $node_order[ $node ] ) : count( $items );
			$items[] = $item;
			$remove_node( $node );
		}
	}

	foreach ( iterator_to_array( $xpath->query( './/script|.//style|.//param', $root ) ) as $node ) {
		$remove_node( $node );
	}

	foreach ( iterator_to_array( $xpath->query( './/*[@style]', $root ) ) as $node ) {
		if ( ! $node instanceof DOMElement ) {
			continue;
		}

		$declarations = array_filter(
			array_map( 'trim', explode( ';', $node->getAttribute( 'style' ) ) ),
			static function ( $declaration ) {
				return '' !== $declaration && 1 !== preg_match( '/^(?:background(?:-image)?|content)\s*:/i', $declaration );
			}
		);

		if ( $declarations ) {
			$node->setAttribute( 'style', implode( '; ', $declarations ) );
		} else {
			$node->removeAttribute( 'style' );
		}
	}

	usort(
		$items,
		static function ( $first, $second ) {
			return ( isset( $first['_content_order'] ) ? absint( $first['_content_order'] ) : 0 )
				<=> ( isset( $second['_content_order'] ) ? absint( $second['_content_order'] ) : 0 );
		}
	);

	foreach ( $items as &$ordered_item ) {
		unset( $ordered_item['_content_order'] );
	}
	unset( $ordered_item );

	$description = '';

	foreach ( iterator_to_array( $root->childNodes ) as $child ) {
		$description .= $dom->saveHTML( $child );
	}

	$allowed_html = wp_kses_allowed_html( 'post' );

	foreach ( array( 'iframe', 'embed', 'object', 'param' ) as $media_tag ) {
		unset( $allowed_html[ $media_tag ] );
	}

	return array(
		'media'       => $items,
		'description' => trim( wp_kses( $description, $allowed_html ) ),
	);
}

/**
 * Remove residual Classic Editor media while preserving non-media rich HTML.
 *
 * Unknown embeds are removed rather than rendered, and the final allowlist
 * provides a DOM-extension-independent defense against media duplication.
 *
 * @internal
 * @param string $html Rendered description HTML.
 * @return string
 */
function fahar_theme_strip_portfolio_description_media( $html ) {
	$html = (string) $html;

	if ( '' !== trim( $html ) && class_exists( 'DOMDocument' ) ) {
		$dom            = new DOMDocument();
		$previous_state = libxml_use_internal_errors( true );
		$loaded         = $dom->loadHTML(
			'<?xml encoding="utf-8" ?><div id="fahar-description-root">' . $html . '</div>',
			LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET
		);
		libxml_clear_errors();
		libxml_use_internal_errors( $previous_state );

		if ( $loaded ) {
			$xpath = new DOMXPath( $dom );
			$root  = $xpath->query( '//*[@id="fahar-description-root"]' )->item( 0 );

			if ( $root instanceof DOMElement ) {
				foreach ( iterator_to_array( $xpath->query( './/*[@style]', $root ) ) as $styled_node ) {
					$declarations = array_filter(
						array_map( 'trim', explode( ';', $styled_node->getAttribute( 'style' ) ) ),
						static function ( $declaration ) {
							return '' !== $declaration && 1 !== preg_match( '/^(?:background(?:-image)?|content)\s*:/i', $declaration );
						}
					);

					if ( $declarations ) {
						$styled_node->setAttribute( 'style', implode( '; ', $declarations ) );
					} else {
						$styled_node->removeAttribute( 'style' );
					}
				}

				$media_nodes = $xpath->query( './/picture|.//img|.//video|.//audio|.//iframe|.//embed|.//object', $root );
				$remove      = array();

				foreach ( iterator_to_array( $media_nodes ) as $media_node ) {
					$target   = $media_node;
					$ancestor = $media_node->parentNode;

					while ( $ancestor instanceof DOMElement && $ancestor !== $root ) {
						$tag_name   = strtolower( $ancestor->tagName );
						$class_name = ' ' . preg_replace( '/\s+/', ' ', $ancestor->getAttribute( 'class' ) ) . ' ';
						$is_wrapper = in_array( $tag_name, array( 'figure', 'picture' ), true )
							|| false !== strpos( $class_name, ' wp-block-image ' )
							|| false !== strpos( $class_name, ' wp-block-gallery ' )
							|| false !== strpos( $class_name, ' wp-block-video ' )
							|| false !== strpos( $class_name, ' wp-block-embed ' )
							|| false !== strpos( $class_name, ' wp-block-media-text__media ' )
							|| false !== strpos( $class_name, ' wp-video ' )
							|| false !== strpos( $class_name, ' wp-caption ' )
							|| false !== strpos( $class_name, ' gallery ' );

						if ( $is_wrapper ) {
							$target = $ancestor;
						}

						$ancestor = $ancestor->parentNode;
					}

					$remove[] = $target;
				}

				foreach ( array_reverse( $remove ) as $node ) {
					if ( $node->parentNode ) {
						$node->parentNode->removeChild( $node );
					}
				}

				foreach ( iterator_to_array( $xpath->query( './/text()[normalize-space()]', $root ) ) as $text_node ) {
					$parent = $text_node->parentNode;

					if ( $parent instanceof DOMElement && in_array( strtolower( $parent->tagName ), array( 'a', 'code', 'pre' ), true ) ) {
						continue;
					}

					$url      = fahar_theme_normalize_http_url( trim( $text_node->nodeValue ) );
					$provider = $url ? fahar_theme_detect_portfolio_video_provider( $url ) : 'unknown';

					if ( in_array( $provider, array( 'youtube', 'vimeo', 'aparat', 'self-hosted' ), true ) && $text_node->parentNode ) {
						$text_node->parentNode->removeChild( $text_node );
					}
				}

				foreach ( iterator_to_array( $xpath->query( './/p|.//figure|.//a', $root ) ) as $candidate ) {
					if ( '' === trim( $candidate->textContent ) && 0 === $candidate->childNodes->length && $candidate->parentNode ) {
						$candidate->parentNode->removeChild( $candidate );
					}
				}

				$html = '';

				foreach ( iterator_to_array( $root->childNodes ) as $child ) {
					$html .= $dom->saveHTML( $child );
				}
			}
		}
	}

	if ( class_exists( 'WP_HTML_Tag_Processor' ) ) {
		$processor = new WP_HTML_Tag_Processor( $html );

		while ( $processor->next_tag() ) {
			$style = $processor->get_attribute( 'style' );

			if ( ! is_string( $style ) || '' === $style ) {
				continue;
			}

			$declarations = array_filter(
				array_map( 'trim', explode( ';', $style ) ),
				static function ( $declaration ) {
					return '' !== $declaration && 1 !== preg_match( '/^(?:background(?:-image)?|content)\s*:/i', $declaration );
				}
			);

			if ( $declarations ) {
				$processor->set_attribute( 'style', implode( '; ', $declarations ) );
			} else {
				$processor->remove_attribute( 'style' );
			}
		}

		$html = $processor->get_updated_html();
	}

	$allowed_html = wp_kses_allowed_html( 'post' );

	foreach ( array( 'iframe', 'embed', 'object', 'param' ) as $media_tag ) {
		unset( $allowed_html[ $media_tag ] );
	}

	return trim( wp_kses( $html, $allowed_html ) );
}

/**
 * Build and memoize the content media/description split for one request.
 *
 * @internal
 * @param int|WP_Post|null $post Portfolio post.
 * @return array{media:array<int, array<string, mixed>>,description:string}
 */
function fahar_theme_get_portfolio_content_transform( $post = null ) {
	static $cache = array();

	$post = fahar_theme_normalize_portfolio_post( $post );

	if ( ! $post || 'single-page' !== fahar_theme_get_portfolio_type( $post ) ) {
		return array(
			'media'       => array(),
			'description' => '',
		);
	}

	if ( isset( $cache[ $post->ID ] ) ) {
		return $cache[ $post->ID ];
	}

	$title = trim( get_the_title( $post ) );
	$title = $title ? $title : __( 'نمونه‌کار', 'fahar-theme-child' );
	$rendered = apply_filters( 'the_content', (string) $post->post_content );

	$cache[ $post->ID ] = fahar_theme_split_portfolio_rendered_content( $rendered, $post, $title );

	return $cache[ $post->ID ];
}

/**
 * Return every verified portfolio media item in authoritative display order.
 *
 * A valid WordPress Featured Image always leads. Content-authored media follows
 * in authoring order, then verified Astra cover/lightbox/video media, and then
 * remaining attached media. Without a Featured Image, the first valid content
 * item leads. Attachment IDs and canonical URLs are deduplicated across sources.
 *
 * @param int|WP_Post|null $post Post ID, post object, or current post.
 * @return array<int, array<string, mixed>>
 */
function fahar_theme_get_portfolio_media_items( $post = null ) {
	$post = fahar_theme_normalize_portfolio_post( $post );

	if ( ! $post ) {
		return array();
	}

	$title = trim( get_the_title( $post ) );

	if ( '' === $title ) {
		$title = __( 'نمونه‌کار', 'fahar-theme-child' );
	}

	$items               = array();
	$seen_attachment_ids = array();
	$seen_canonical_urls = array();
	$featured_image_id   = fahar_theme_normalize_image_attachment_id( get_post_thumbnail_id( $post ) );
	$astra_cover_id      = fahar_theme_normalize_image_attachment_id( fahar_theme_get_portfolio_meta_value( $post, 'cover_id' ) );
	$lightbox_image_id   = fahar_theme_get_portfolio_lightbox_image_id( $post );
	$append_image        = static function ( $candidate, $origin = '', $size = 'large' ) use ( &$items, &$seen_attachment_ids, &$seen_canonical_urls, $title ) {
		if ( ! is_array( $candidate ) ) {
			$candidate = array( 'attachment_id' => $candidate );
		}

		$attachment_id = isset( $candidate['attachment_id'] ) ? fahar_theme_normalize_image_attachment_id( $candidate['attachment_id'] ) : 0;
		$url           = isset( $candidate['url'] ) ? fahar_theme_normalize_http_url( $candidate['url'] ) : '';

		if ( ! $attachment_id && $url ) {
			$attachment_id = fahar_theme_normalize_image_attachment_id( attachment_url_to_postid( $url ) );
		}

		$original_url = $attachment_id ? fahar_theme_normalize_http_url( wp_get_attachment_url( $attachment_id ) ) : $url;

		if (
			( ! $attachment_id && ! $url )
			|| ( $attachment_id && isset( $seen_attachment_ids[ $attachment_id ] ) )
			|| ( $original_url && isset( $seen_canonical_urls[ $original_url ] ) )
			|| ( $url && isset( $seen_canonical_urls[ $url ] ) )
		) {
			return;
		}

		if ( $attachment_id ) {
			$seen_attachment_ids[ $attachment_id ] = true;
		}

		foreach ( array_filter( array( $original_url, $url ) ) as $seen_url ) {
			$seen_canonical_urls[ $seen_url ] = true;
		}

		$item = array(
			'type'          => 'image',
			'origin'        => sanitize_key( $origin ? $origin : ( isset( $candidate['origin'] ) ? (string) $candidate['origin'] : '' ) ),
			'provider'      => $attachment_id ? 'wordpress' : 'external',
			'attachment_id' => $attachment_id,
			'url'           => $url ? $url : $original_url,
			'primary'       => ! $items,
			'size'          => sanitize_key( (string) $size ),
			'title'         => isset( $candidate['title'] ) && is_scalar( $candidate['title'] ) ? trim( (string) $candidate['title'] ) : $title,
		);

		foreach ( array( 'width', 'height' ) as $dimension ) {
			if ( ! empty( $candidate[ $dimension ] ) ) {
				$item[ $dimension ] = absint( $candidate[ $dimension ] );
			}
		}

		$items[] = $item;
	};
	$append_video        = static function ( $item, $attachment_id = 0 ) use ( &$items, &$seen_attachment_ids, &$seen_canonical_urls ) {
		if ( ! is_array( $item ) || empty( $item['type'] ) || empty( $item['source'] ) ) {
			return;
		}

		$attachment_id = fahar_theme_normalize_attachment_id( $attachment_id );
		$source        = fahar_theme_normalize_http_url( $item['source'] );

		if (
			! $source
			|| ( $attachment_id && isset( $seen_attachment_ids[ $attachment_id ] ) )
			|| isset( $seen_canonical_urls[ $source ] )
		) {
			return;
		}

		if ( $attachment_id ) {
			$seen_attachment_ids[ $attachment_id ] = true;
			$item['attachment_id']                  = $attachment_id;
		}

		$seen_canonical_urls[ $source ] = true;
		$item['source']                  = $source;
		$poster_attachment_id           = isset( $item['poster_attachment_id'] ) ? fahar_theme_normalize_image_attachment_id( $item['poster_attachment_id'] ) : 0;
		$poster_url                     = isset( $item['poster_url'] ) ? fahar_theme_normalize_http_url( $item['poster_url'] ) : '';

		if ( ! $poster_attachment_id && $poster_url ) {
			$poster_attachment_id = fahar_theme_normalize_image_attachment_id( attachment_url_to_postid( $poster_url ) );
		}

		if ( $poster_attachment_id ) {
			$poster_original_url                            = fahar_theme_normalize_http_url( wp_get_attachment_url( $poster_attachment_id ) );
			$seen_attachment_ids[ $poster_attachment_id ]   = true;
			$item['poster_attachment_id']                   = $poster_attachment_id;
			$item['poster_url']                             = $poster_url ? $poster_url : $poster_original_url;

			if ( $poster_original_url ) {
				$seen_canonical_urls[ $poster_original_url ] = true;
			}
		} elseif ( $poster_url && ! fahar_theme_is_portfolio_placeholder_image( $poster_url ) ) {
			$item['poster_url']                  = $poster_url;
			$seen_canonical_urls[ $poster_url ] = true;
		} else {
			unset( $item['poster_attachment_id'], $item['poster_url'] );
		}

		$item['primary']                 = ! $items;
		$items[]                         = $item;
	};

	$append_image( $featured_image_id, 'featured-image', 'full' );

	$content_transform = fahar_theme_get_portfolio_content_transform( $post );

	foreach ( $content_transform['media'] as $content_item ) {
		if ( isset( $content_item['type'] ) && 'image' === $content_item['type'] ) {
			$append_image( $content_item, 'content', 'large' );
		} else {
			$append_video( $content_item, isset( $content_item['attachment_id'] ) ? $content_item['attachment_id'] : 0 );
		}
	}

	$append_image( $astra_cover_id, 'astra-cover', $featured_image_id ? 'large' : 'full' );
	$append_image( $lightbox_image_id, 'astra-lightbox', 'full' );

	$video_source = fahar_theme_get_portfolio_video_source( $post );
	$video_url    = '';

	if ( 'url' === $video_source['type'] && is_string( $video_source['value'] ) ) {
		$video_url = $video_source['value'];
	} elseif ( 'embed' === $video_source['type'] && is_string( $video_source['value'] ) ) {
		$video_url = fahar_theme_get_portfolio_iframe_src( $video_source['value'] );
	}

	if ( $video_url ) {
		$video_item = fahar_theme_prepare_portfolio_video_media_item( $video_url, $title );

		if ( $video_item ) {
			$video_item['origin'] = 'astra-video';
			$append_video( $video_item );
		}
	}

	foreach ( get_attached_media( '', $post ) as $attachment ) {
		if ( ! $attachment instanceof WP_Post ) {
			continue;
		}

		if ( wp_attachment_is_image( $attachment ) ) {
			$append_image( $attachment->ID, 'wordpress-attachment', 'large' );
			continue;
		}

		$mime_type = (string) get_post_mime_type( $attachment );

		if ( 0 !== strpos( $mime_type, 'video/' ) ) {
			continue;
		}

		$video_url  = wp_get_attachment_url( $attachment->ID );
		$video_item = fahar_theme_prepare_portfolio_video_media_item( $video_url, $title );

		if ( $video_item ) {
			$video_item['mime']   = sanitize_mime_type( $mime_type );
			$video_item['origin'] = 'wordpress-attachment';
			$append_video( $video_item, $attachment->ID );
		}
	}

	return array_values( $items );
}

/**
 * Return the intentional destination for a portfolio card.
 *
 * Image and Video items use their verified media source because the provider
 * excludes those items from normal WordPress single routing.
 *
 * @param int|WP_Post|null $post Post ID, post object, or current post.
 * @return array{url:string,kind:string,is_internal:bool,opens_new_tab:bool}
 */
function fahar_theme_get_portfolio_destination( $post = null ) {
	$empty = array(
		'url'           => '',
		'kind'          => 'none',
		'is_internal'   => false,
		'opens_new_tab' => false,
	);
	$post  = fahar_theme_normalize_portfolio_post( $post );

	if ( ! $post ) {
		return $empty;
	}

	$type        = fahar_theme_get_portfolio_type( $post );
	$destination = $empty;

	if ( 'single-page' === $type ) {
		$permalink = get_permalink( $post );

		if ( $permalink ) {
			$destination['url']         = fahar_theme_normalize_http_url( $permalink );
			$destination['kind']        = 'detail';
			$destination['is_internal'] = true;
		}
	} elseif ( 'website' === $type ) {
		$destination['url']           = fahar_theme_get_portfolio_external_url( $post );
		$destination['kind']          = 'website';
		$destination['opens_new_tab'] = 1 === absint( fahar_theme_get_portfolio_meta_value( $post, 'open_in_new_tab' ) );
	} elseif ( 'image' === $type ) {
		$destination['url']  = fahar_theme_get_portfolio_lightbox_image_url( $post, 'full' );
		$destination['url']  = $destination['url'] ? $destination['url'] : fahar_theme_get_portfolio_cover_url( $post, 'full' );
		$destination['kind'] = 'image';
	} elseif ( 'video' === $type ) {
		$source = fahar_theme_get_portfolio_video_source( $post );

		if ( 'url' === $source['type'] && is_string( $source['value'] ) ) {
			$destination['url'] = fahar_theme_normalize_http_url( $source['value'] );
		} elseif (
			'embed' === $source['type']
			&& in_array( $source['provider'], array( 'youtube', 'vimeo', 'aparat' ), true )
			&& is_string( $source['value'] )
		) {
			$destination['url'] = fahar_theme_normalize_http_url( fahar_theme_get_portfolio_iframe_src( $source['value'] ) );
		}

		$destination['kind'] = 'video';
	}

	if ( ! $destination['url'] ) {
		return $empty;
	}

	return $destination;
}

/**
 * Return the unformatted WordPress content for a Single Page portfolio item.
 *
 * @param int|WP_Post|null $post Post ID, post object, or current post.
 * @return string
 */
function fahar_theme_get_portfolio_description( $post = null ) {
	$post = fahar_theme_normalize_portfolio_post( $post );

	return $post && 'single-page' === fahar_theme_get_portfolio_type( $post ) ? (string) $post->post_content : '';
}

/**
 * Return rendered non-media content for the Single Portfolio description.
 *
 * The stored post_content remains untouched; this returns only the request-
 * scoped frontend transformation shared with the media collection.
 *
 * @param int|WP_Post|null $post Post ID, post object, or current post.
 * @return string
 */
function fahar_theme_get_portfolio_description_content( $post = null ) {
	$transform = fahar_theme_get_portfolio_content_transform( $post );

	return $transform['description'];
}

/**
 * Return the verified portfolio taxonomy slugs.
 *
 * @return string[]
 */
function fahar_theme_get_portfolio_taxonomies() {
	$taxonomies = (array) apply_filters(
		'fahar_theme_portfolio_taxonomies',
		array(
			'astra-portfolio-categories',
			'astra-portfolio-other-categories',
			'astra-portfolio-tags',
		)
	);

	return array_values( array_unique( array_filter( array_map( 'sanitize_key', $taxonomies ) ) ) );
}

/**
 * Return the visitor-facing taxonomies used by Explore filter controls.
 *
 * The provider's secondary classification taxonomy remains available through
 * fahar_theme_get_portfolio_taxonomies() for internal integrations.
 *
 * @return string[]
 */
function fahar_theme_get_explore_filter_taxonomies() {
	$configured = fahar_theme_get_portfolio_taxonomies();
	$taxonomies = (array) apply_filters(
		'fahar_theme_explore_filter_taxonomies',
		array(
			'astra-portfolio-categories',
			'astra-portfolio-tags',
		)
	);
	$taxonomies = array_values( array_unique( array_filter( array_map( 'sanitize_key', $taxonomies ) ) ) );

	return array_values( array_intersect( $taxonomies, $configured ) );
}

/**
 * Return one verified visitor-facing Explore taxonomy by semantic role.
 *
 * @param string $role Either category or tag.
 * @return string
 */
function fahar_theme_get_explore_filter_taxonomy( $role ) {
	$mapping = array(
		'category' => 'astra-portfolio-categories',
		'tag'      => 'astra-portfolio-tags',
	);
	$role    = sanitize_key( (string) $role );

	if ( ! isset( $mapping[ $role ] ) ) {
		return '';
	}

	$taxonomy = $mapping[ $role ];

	return in_array( $taxonomy, fahar_theme_get_explore_filter_taxonomies(), true ) ? $taxonomy : '';
}

/**
 * Return public portfolio tags used by published items in one category.
 *
 * The bounded object query avoids exposing the global tag universe and keeps
 * the category-to-tag relationship inside the portfolio adapter boundary.
 *
 * @param WP_Term|int $category Portfolio category term or term ID.
 * @return WP_Term[]
 */
function fahar_theme_get_contextual_portfolio_tags( $category ) {
	$category_taxonomy = fahar_theme_get_explore_filter_taxonomy( 'category' );
	$tag_taxonomy      = fahar_theme_get_explore_filter_taxonomy( 'tag' );
	$category          = $category instanceof WP_Term
		? $category
		: ( $category_taxonomy ? get_term( absint( $category ), $category_taxonomy ) : null );
	$post_type         = fahar_theme_get_portfolio_post_type();

	if (
		! $category instanceof WP_Term
		|| $category_taxonomy !== $category->taxonomy
		|| ! taxonomy_exists( $category_taxonomy )
		|| ! taxonomy_exists( $tag_taxonomy )
		|| ! $post_type
		|| ! post_type_exists( $post_type )
		|| ! is_object_in_taxonomy( $post_type, $category_taxonomy )
		|| ! is_object_in_taxonomy( $post_type, $tag_taxonomy )
	) {
		return array();
	}

	$post_limit = absint( apply_filters( 'fahar_theme_contextual_tag_post_limit', 1000 ) );
	$post_limit = min( 2000, max( 100, $post_limit ) );
	$query      = fahar_theme_query_portfolios(
		array(
			'post_type'              => $post_type,
			'post_status'            => 'publish',
			'posts_per_page'         => $post_limit,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'tax_query'              => array(
				array(
					'taxonomy' => $category_taxonomy,
					'field'    => 'term_id',
					'terms'    => array( $category->term_id ),
				),
			),
		)
	);

	if ( ! $query->posts ) {
		return array();
	}

	$terms = get_terms(
		array(
			'taxonomy'   => $tag_taxonomy,
			'object_ids' => array_map( 'absint', $query->posts ),
			'hide_empty' => true,
			'orderby'    => 'name',
			'order'      => 'ASC',
			'number'     => 100,
		)
	);

	return is_wp_error( $terms ) ? array() : array_values( $terms );
}

/**
 * Return verified portfolio terms for one or all configured taxonomies.
 *
 * @param int|WP_Post|null $post     Post ID, post object, or current post.
 * @param string|null      $taxonomy Taxonomy slug, or null for all.
 * @return WP_Term[]
 */
function fahar_theme_get_portfolio_terms( $post = null, $taxonomy = null ) {
	$post       = fahar_theme_normalize_portfolio_post( $post );
	$configured = fahar_theme_get_portfolio_taxonomies();

	if ( ! $post ) {
		return array();
	}

	if ( null !== $taxonomy ) {
		$taxonomy = sanitize_key( (string) $taxonomy );

		if ( ! in_array( $taxonomy, $configured, true ) ) {
			return array();
		}

		$configured = array( $taxonomy );
	}

	$taxonomies = array_values(
		array_filter(
			$configured,
			function ( $taxonomy_slug ) use ( $post ) {
				return taxonomy_exists( $taxonomy_slug ) && is_object_in_taxonomy( $post->post_type, $taxonomy_slug );
			}
		)
	);

	if ( ! $taxonomies ) {
		return array();
	}

	$terms = wp_get_object_terms( $post->ID, $taxonomies );

	if ( is_wp_error( $terms ) ) {
		return array();
	}

	return array_values(
		array_filter(
			$terms,
			function ( $term ) {
				return $term instanceof WP_Term;
			}
		)
	);
}

/**
 * Return published portfolio items related to a portfolio post.
 *
 * Relatedness is resolved in configured taxonomy order, then by normalized
 * portfolio type, and finally by publish date. Each query requests only the
 * number of posts still needed.
 *
 * @param int|WP_Post|null $post  Post ID, post object, or current post.
 * @param int              $limit Maximum result count from 1 through 12.
 * @return WP_Post[]
 */
function fahar_theme_get_related_portfolios( $post = null, $limit = 6 ) {
	$post = fahar_theme_normalize_portfolio_post( $post );

	if ( ! $post ) {
		return array();
	}

	$limit        = max( 1, min( 12, (int) $limit ) );
	$post_type    = fahar_theme_get_portfolio_post_type();
	$selected     = array();
	$excluded_ids = array( $post->ID );

	$append_results = function ( $query_args ) use ( &$selected, &$excluded_ids, $post_type, $limit ) {
		$remaining = $limit - count( $selected );

		if ( $remaining < 1 ) {
			return;
		}

		$query = fahar_theme_query_portfolios(
			array_merge(
				(array) $query_args,
				array(
					'post_type'           => $post_type,
					'post_status'         => 'publish',
					'posts_per_page'      => $remaining,
					'post__not_in'        => $excluded_ids,
					'orderby'             => array(
						'date' => 'DESC',
						'ID'   => 'DESC',
					),
					'no_found_rows'       => true,
					'ignore_sticky_posts' => true,
				)
			)
		);

		foreach ( $query->posts as $candidate ) {
			if (
				! $candidate instanceof WP_Post
				|| 'publish' !== $candidate->post_status
				|| $post_type !== $candidate->post_type
				|| in_array( $candidate->ID, $excluded_ids, true )
			) {
				continue;
			}

			$selected[]     = $candidate;
			$excluded_ids[] = $candidate->ID;

			if ( count( $selected ) >= $limit ) {
				break;
			}
		}
	};

	foreach ( fahar_theme_get_portfolio_taxonomies() as $taxonomy ) {
		if ( count( $selected ) >= $limit ) {
			break;
		}

		if (
			! taxonomy_exists( $taxonomy )
			|| ! is_object_in_taxonomy( $post_type, $taxonomy )
		) {
			continue;
		}

		$term_ids = array_values(
			array_unique(
				array_filter(
					array_map(
						function ( $term ) {
							return $term instanceof WP_Term ? absint( $term->term_id ) : 0;
						},
						fahar_theme_get_portfolio_terms( $post, $taxonomy )
					)
				)
			)
		);

		if ( ! $term_ids ) {
			continue;
		}

		$append_results(
			array(
				'tax_query' => array(
					array(
						'taxonomy' => $taxonomy,
						'field'    => 'term_id',
						'terms'    => $term_ids,
						'operator' => 'IN',
					),
				),
			)
		);
	}

	if ( count( $selected ) < $limit ) {
		$type = fahar_theme_get_portfolio_type( $post );

		if ( $type && in_array( $type, fahar_theme_get_portfolio_types(), true ) ) {
			$type_query_args = fahar_theme_get_portfolio_type_query_args( array( $type ) );

			if ( $type_query_args ) {
				$append_results( $type_query_args );
			}
		}
	}

	if ( count( $selected ) < $limit ) {
		$append_results( array() );
	}

	$filtered   = (array) apply_filters( 'fahar_theme_related_portfolios', $selected, $post, $limit );
	$normalized = array();
	$seen_ids   = array( $post->ID );

	foreach ( $filtered as $candidate ) {
		$candidate = fahar_theme_normalize_portfolio_post( $candidate );

		if (
			! $candidate
			|| 'publish' !== $candidate->post_status
			|| in_array( $candidate->ID, $seen_ids, true )
		) {
			continue;
		}

		$normalized[] = $candidate;
		$seen_ids[]   = $candidate->ID;

		if ( count( $normalized ) >= $limit ) {
			break;
		}
	}

	return $normalized;
}

/**
 * Determine whether a portfolio item supports its WordPress single permalink.
 *
 * @param int|WP_Post|null $post Post ID, post object, or current post.
 * @return bool
 */
function fahar_theme_portfolio_has_single_permalink( $post = null ) {
	return 'single-page' === fahar_theme_get_portfolio_type( $post );
}

/**
 * Resolve the page used for the Explore experience.
 *
 * @return int
 */
function fahar_theme_get_portfolio_page_id() {
	return absint( apply_filters( 'fahar_theme_portfolio_page_id', 0 ) );
}

/**
 * Resolve the fallback Explore page slug.
 *
 * @return string
 */
function fahar_theme_get_portfolio_page_slug() {
	return sanitize_title( (string) apply_filters( 'fahar_theme_portfolio_page_slug', 'portfolios' ) );
}

/**
 * Determine whether the current request is the Explore screen.
 *
 * @return bool
 */
function fahar_theme_is_portfolio_explore() {
	$page_id = fahar_theme_get_portfolio_page_id();
	$slug    = fahar_theme_get_portfolio_page_slug();
	$match   = is_page_template( 'templates/page-portfolios.php' );

	if ( $page_id ) {
		$match = $match || is_page( $page_id );
	} elseif ( $slug ) {
		$match = $match || is_page( $slug );
	}

	return (bool) apply_filters( 'fahar_theme_is_portfolio_explore', $match );
}

/**
 * Determine whether the current object is a configured portfolio item.
 *
 * @return bool
 */
function fahar_theme_is_portfolio_item() {
	$post_type = fahar_theme_get_portfolio_post_type();
	$match     = $post_type && post_type_exists( $post_type ) ? is_singular( $post_type ) : false;

	return (bool) apply_filters( 'fahar_theme_is_portfolio_item', $match, $post_type );
}

/**
 * Return the Explore URL without hardcoding a deployment domain.
 *
 * @return string
 */
function fahar_theme_get_portfolio_url() {
	$page_id = fahar_theme_get_portfolio_page_id();
	$slug    = fahar_theme_get_portfolio_page_slug();
	$url     = $page_id ? get_permalink( $page_id ) : home_url( $slug ? '/' . $slug . '/' : '/' );

	return (string) apply_filters( 'fahar_theme_portfolio_url', $url, $page_id );
}
