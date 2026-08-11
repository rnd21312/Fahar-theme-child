<?php
/**
 * Navigation helpers.
 *
 * @package Fahar_Theme_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load the desktop header stylesheet through the existing asset boundary.
 *
 * @return bool
 */
function fahar_theme_enable_header_assets() {
	return ! fahar_theme_is_elementor_canvas_template();
}
add_filter( 'fahar_theme_load_header_assets', 'fahar_theme_enable_header_assets', 10, 0 );

/**
 * Resolve a public WordPress page without inventing a destination.
 *
 * @param int    $page_id Preferred page ID.
 * @param string $slug    Fallback page slug.
 * @return WP_Post|null
 */
function fahar_theme_resolve_public_page( $page_id, $slug ) {
	$page_id = absint( $page_id );
	$slug    = sanitize_title( $slug );
	$page    = $page_id ? get_post( $page_id ) : null;

	if ( ! $page && $slug ) {
		$page = get_page_by_path( $slug, OBJECT, 'page' );
	}

	if ( ! $page instanceof WP_Post || 'page' !== $page->post_type || 'publish' !== $page->post_status ) {
		return null;
	}

	return $page;
}

/**
 * Return safe fallback items when no mobile WordPress menu is assigned.
 *
 * Explore and Contact are included only when their pages resolve publicly.
 *
 * @return array<string, array<string, mixed>>
 */
function fahar_theme_get_mobile_navigation_items() {
	$items = array(
		'home' => array(
			'label'  => __( 'خانه', 'fahar-theme-child' ),
			'url'    => home_url( '/' ),
			'active' => is_front_page(),
			'icon'   => 'home',
		),
	);

	$explore_page = fahar_theme_resolve_public_page(
		fahar_theme_get_portfolio_page_id(),
		fahar_theme_get_portfolio_page_slug()
	);

	if ( $explore_page ) {
		$items['explore'] = array(
			'label'  => __( 'کاوش', 'fahar-theme-child' ),
			'url'    => get_permalink( $explore_page ),
			'active' => fahar_theme_is_portfolio_explore(),
			'icon'   => 'explore',
		);
	}

	$contact_page = fahar_theme_resolve_public_page(
		absint( apply_filters( 'fahar_theme_contact_page_id', 0 ) ),
		sanitize_title( (string) apply_filters( 'fahar_theme_contact_page_slug', 'contact' ) )
	);

	if ( $contact_page ) {
		$items['contact'] = array(
			'label'  => __( 'تماس', 'fahar-theme-child' ),
			'url'    => get_permalink( $contact_page ),
			'active' => is_page( $contact_page->ID ),
			'icon'   => 'contact',
		);
	}

	$items = (array) apply_filters( 'fahar_theme_mobile_navigation_items', $items );

	return array_filter(
		$items,
		function ( $item, $key ) {
			return 'search' !== sanitize_key( (string) $key )
				&& ! fahar_theme_mobile_navigation_item_is_search( $item );
		},
		ARRAY_FILTER_USE_BOTH
	);
}

/**
 * Determine whether an assigned mobile-menu item is Search.
 *
 * @param WP_Post|array<string, mixed> $item Navigation item.
 * @return bool
 */
function fahar_theme_mobile_navigation_item_is_search( $item ) {
	if ( $item instanceof WP_Post ) {
		$title   = (string) $item->title;
		$url     = (string) $item->url;
		$classes = (array) $item->classes;
	} elseif ( is_array( $item ) ) {
		$title   = isset( $item['label'] ) ? (string) $item['label'] : '';
		$url     = isset( $item['url'] ) ? (string) $item['url'] : '';
		$classes = isset( $item['classes'] ) ? (array) $item['classes'] : array();

		if ( isset( $item['icon'] ) && 'search' === sanitize_key( (string) $item['icon'] ) ) {
			return true;
		}
	} else {
		return false;
	}

	$title      = strtolower( trim( wp_strip_all_tags( $title ) ) );
	$classes    = array_map( 'sanitize_html_class', $classes );
	$query      = (string) wp_parse_url( $url, PHP_URL_QUERY );
	$query_args = array();

	if ( $query ) {
		wp_parse_str( $query, $query_args );
	}

	$is_search = in_array( $title, array( 'search', 'جستجو' ), true )
		|| in_array( 'menu-item-search', $classes, true )
		|| array_key_exists( 's', $query_args );

	return $is_search;
}

/**
 * Remove Search from the dedicated assigned mobile menu before rendering.
 *
 * @param WP_Post[] $items Menu items.
 * @param stdClass  $args  Menu arguments.
 * @return WP_Post[]
 */
function fahar_theme_filter_mobile_navigation_menu_items( $items, $args ) {
	if ( ! isset( $args->theme_location ) || 'fahar-mobile-bottom' !== $args->theme_location ) {
		return $items;
	}

	return array_values(
		array_filter(
			$items,
			function ( $item ) {
				return ! fahar_theme_mobile_navigation_item_is_search( $item );
			}
		)
	);
}
add_filter( 'wp_nav_menu_objects', 'fahar_theme_filter_mobile_navigation_menu_items', 10, 2 );

/**
 * Return the existing inline icon markup for a mobile-navigation role.
 *
 * @param string $icon Icon role.
 * @return string
 */
function fahar_theme_get_mobile_navigation_icon_markup( $icon ) {
	$icons = array(
		'home'    => '<span class="fahar-mobile-nav__icon" aria-hidden="true"><svg viewBox="0 0 24 24" focusable="false"><path d="M3 11.5 12 4l9 7.5v8a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z" /></svg></span>',
		'explore' => '<span class="fahar-mobile-nav__icon" aria-hidden="true"><svg viewBox="0 0 24 24" focusable="false"><circle cx="12" cy="12" r="9" /><path d="m15.5 8.5-2.1 4.9-4.9 2.1 2.1-4.9z" /></svg></span>',
		'contact' => '<span class="fahar-mobile-nav__icon" aria-hidden="true"><svg viewBox="0 0 24 24" focusable="false"><path d="M4 5h16v11H9l-5 4z" /></svg></span>',
	);
	$icon  = sanitize_key( (string) $icon );

	return isset( $icons[ $icon ] ) ? $icons[ $icon ] : $icons['explore'];
}

/**
 * Infer an icon role without changing an assigned menu item's destination.
 *
 * @param WP_Post $item WordPress menu item.
 * @return string
 */
function fahar_theme_get_mobile_navigation_menu_item_icon( $item ) {
	$item_url = untrailingslashit( (string) $item->url );

	foreach ( fahar_theme_get_mobile_navigation_items() as $key => $fallback_item ) {
		$fallback_url = isset( $fallback_item['url'] ) ? untrailingslashit( (string) $fallback_item['url'] ) : '';

		if ( $fallback_url && $item_url === $fallback_url ) {
			return sanitize_key( (string) $key );
		}
	}

	return 'explore';
}

/**
 * Add the existing icon system to assigned mobile-menu items.
 *
 * The menu title remains in the screen-reader-only label wrapper supplied by
 * the template, preserving each link's accessible name.
 *
 * @param string   $item_output Menu-item HTML.
 * @param WP_Post  $item        WordPress menu item.
 * @param int      $depth       Menu depth.
 * @param stdClass $args        Menu arguments.
 * @return string
 */
function fahar_theme_add_mobile_navigation_menu_icon( $item_output, $item, $depth, $args ) {
	if (
		0 !== $depth
		|| ! isset( $args->theme_location )
		|| 'fahar-mobile-bottom' !== $args->theme_location
	) {
		return $item_output;
	}

	$icon_markup = fahar_theme_get_mobile_navigation_icon_markup(
		fahar_theme_get_mobile_navigation_menu_item_icon( $item )
	);

	$output = preg_replace( '/(<a\b[^>]*>)/', '$1' . $icon_markup, $item_output, 1 );

	return is_string( $output ) ? $output : $item_output;
}
add_filter( 'walker_nav_menu_start_el', 'fahar_theme_add_mobile_navigation_menu_icon', 10, 4 );

/**
 * Determine whether a mobile navigation can render on the current request.
 *
 * Elementor Canvas intentionally owns its entire shell. Elementor editor mode
 * is suppressed by a stable body class in the component stylesheet.
 *
 * @return bool
 */
function fahar_theme_should_show_mobile_navigation() {
	$show = ! is_admin() && ! fahar_theme_is_elementor_canvas_template();

	if ( $show ) {
		$show = has_nav_menu( 'fahar-mobile-bottom' ) || ! empty( fahar_theme_get_mobile_navigation_items() );
	}

	return (bool) apply_filters( 'fahar_theme_show_mobile_navigation', $show );
}

/**
 * Add page clearance only when the mobile navigation can render.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function fahar_theme_mobile_navigation_body_class( $classes ) {
	if ( fahar_theme_should_show_mobile_navigation() ) {
		$classes[] = 'fahar-has-mobile-nav';
	}

	return $classes;
}
add_filter( 'body_class', 'fahar_theme_mobile_navigation_body_class' );

/**
 * Render the single global mobile-navigation instance in the footer.
 *
 * @return void
 */
function fahar_theme_render_mobile_navigation() {
	if ( ! fahar_theme_should_show_mobile_navigation() ) {
		return;
	}

	get_template_part( 'template-parts/navigation/mobile-bottom-nav' );
}
add_action( 'wp_footer', 'fahar_theme_render_mobile_navigation', 5 );
