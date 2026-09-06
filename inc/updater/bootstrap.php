<?php
/**
 * GitHub theme updater bootstrap.
 *
 * @package Fahar_Theme_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'FAHAR_THEME_GITHUB_OWNER' ) ) {
	define( 'FAHAR_THEME_GITHUB_OWNER', 'rnd21312' );
}

if ( ! defined( 'FAHAR_THEME_GITHUB_REPOSITORY' ) ) {
	define( 'FAHAR_THEME_GITHUB_REPOSITORY', 'Fahar-theme-child' );
}

if ( ! defined( 'FAHAR_THEME_GITHUB_ASSET' ) ) {
	define( 'FAHAR_THEME_GITHUB_ASSET', 'fahartheme.zip' );
}

if ( is_admin() || wp_doing_cron() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
	require_once __DIR__ . '/class-fahar-theme-updater.php';

	$fahar_theme_updater = new Fahar_Theme_Updater(
		FAHAR_THEME_GITHUB_OWNER,
		FAHAR_THEME_GITHUB_REPOSITORY,
		FAHAR_THEME_GITHUB_ASSET
	);
	$fahar_theme_updater->register();
}
