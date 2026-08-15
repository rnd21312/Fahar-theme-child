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
