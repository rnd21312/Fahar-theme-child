<?php
/**
 * Shared theme helpers.
 *
 * @package Fahar_Theme_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return a cache-safe version for a theme file.
 *
 * @param string $relative_path Path relative to the child theme root.
 * @return string
 */
function fahar_theme_asset_version( $relative_path ) {
	$file = FAHAR_THEME_DIR . '/' . ltrim( $relative_path, '/' );

	return is_readable( $file ) ? (string) filemtime( $file ) : FAHAR_THEME_VERSION;
}

/**
 * Return a child-theme asset URL.
 *
 * @param string $relative_path Path relative to the child theme root.
 * @return string
 */
function fahar_theme_asset_uri( $relative_path ) {
	return FAHAR_THEME_URI . '/' . ltrim( $relative_path, '/' );
}
