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

/**
 * Return safe media items authored in an Elementor document.
 *
 * This intentionally reads the stored document instead of Elementor runtime
 * APIs, keeping Elementor optional on frontend portfolio requests.
 *
 * @param WP_Post $post  Portfolio post.
 * @param string  $title Accessible fallback title.
 * @return array<int, array<string, mixed>>
 */
function fahar_theme_get_elementor_portfolio_media_items( $post, $title ) {
	static $cache = array();

	if ( ! $post instanceof WP_Post ) {
		return array();
	}

	if ( isset( $cache[ $post->ID ] ) ) {
		return $cache[ $post->ID ];
	}

	$cache[ $post->ID ] = array();
	$document           = get_post_meta( $post->ID, '_elementor_data', true );

	if ( ! is_string( $document ) || '' === trim( $document ) ) {
		return $cache[ $post->ID ];
	}

	$elements = json_decode( $document, true );

	if ( JSON_ERROR_NONE !== json_last_error() || ! is_array( $elements ) ) {
		return $cache[ $post->ID ];
	}

	$prepare_image = static function ( $image ) use ( $title ) {
		if ( ! is_array( $image ) ) {
			return array();
		}

		return fahar_theme_prepare_portfolio_content_image_item(
			isset( $image['id'] ) ? $image['id'] : 0,
			isset( $image['url'] ) ? $image['url'] : '',
			$title,
			array(
				'alt'    => isset( $image['alt'] ) ? $image['alt'] : ( isset( $image['title'] ) ? $image['title'] : '' ),
				'width'  => isset( $image['width'] ) ? $image['width'] : 0,
				'height' => isset( $image['height'] ) ? $image['height'] : 0,
			)
		);
	};
	$walk          = static function ( $element ) use ( &$walk, &$cache, $prepare_image, $title ) {
		if ( ! is_array( $element ) ) {
			return;
		}

		$settings    = isset( $element['settings'] ) && is_array( $element['settings'] ) ? $element['settings'] : array();
		$element_type = isset( $element['elType'] ) && is_scalar( $element['elType'] ) ? sanitize_key( (string) $element['elType'] ) : '';
		$widget_type = isset( $element['widgetType'] ) && is_scalar( $element['widgetType'] ) ? sanitize_key( (string) $element['widgetType'] ) : '';

		if ( 'widget' === $element_type && 'image' === $widget_type ) {
			$item = $prepare_image( isset( $settings['image'] ) ? $settings['image'] : array() );

			if ( $item ) {
				$item['origin'] = 'elementor';
				$cache[]        = $item;
			}
		} elseif ( 'widget' === $element_type && in_array( $widget_type, array( 'image-gallery', 'gallery', 'media-carousel' ), true ) ) {
			$images = array();

			foreach ( array( 'gallery', 'images', 'slides' ) as $setting_key ) {
				if ( isset( $settings[ $setting_key ] ) && is_array( $settings[ $setting_key ] ) ) {
					$images = $settings[ $setting_key ];
					break;
				}
			}

			foreach ( $images as $image ) {
				if ( is_array( $image ) && isset( $image['image'] ) && is_array( $image['image'] ) ) {
					$image = $image['image'];
				}

				$item = $prepare_image( $image );

				if ( $item ) {
					$item['origin'] = 'elementor';
					$cache[]        = $item;
				}
			}
		} elseif ( 'widget' === $element_type && 'video' === $widget_type ) {
			$video_type    = isset( $settings['video_type'] ) && is_scalar( $settings['video_type'] ) ? sanitize_key( (string) $settings['video_type'] ) : '';
			$video_url     = '';
			$attachment_id = 0;

			if ( in_array( $video_type, array( 'youtube', 'vimeo', 'aparat' ), true ) ) {
				$key       = 'aparat' === $video_type ? 'aparat_url' : $video_type . '_url';
				$video_url = isset( $settings[ $key ] ) ? $settings[ $key ] : '';
			} elseif ( in_array( $video_type, array( 'hosted', 'self-hosted' ), true ) ) {
				$hosted = isset( $settings['hosted_url'] ) ? $settings['hosted_url'] : ( isset( $settings['self_hosted'] ) ? $settings['self_hosted'] : '' );
				$attachment_id = is_array( $hosted ) && isset( $hosted['id'] ) ? fahar_theme_normalize_attachment_id( $hosted['id'] ) : 0;
				$video_url     = is_array( $hosted ) && isset( $hosted['url'] ) ? $hosted['url'] : $hosted;
			} elseif ( isset( $settings['external_url'] ) ) {
				$video_url = $settings['external_url'];
			}

			$item = fahar_theme_prepare_portfolio_video_media_item( $video_url, $title );

			if ( $item ) {
				if ( $attachment_id ) {
					$item['attachment_id'] = $attachment_id;
				}

				$item['origin'] = 'elementor';
				$cache[]        = $item;
			}
		}

		$children = isset( $element['elements'] ) && is_array( $element['elements'] ) ? $element['elements'] : array();

		foreach ( $children as $child ) {
			$walk( $child );
		}
	};

	foreach ( $elements as $element ) {
		$walk( $element );
	}

	return $cache[ $post->ID ];
}
