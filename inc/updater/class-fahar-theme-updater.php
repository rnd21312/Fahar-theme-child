<?php
/**
 * GitHub Releases integration for WordPress theme updates.
 *
 * @package Fahar_Theme_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Supplies Fahar Theme Child releases to the native WordPress updater.
 */
final class Fahar_Theme_Updater {
	const CACHE_KEY = 'fahar_theme_github_release';

	/** @var string */
	private $owner;

	/** @var string */
	private $repository;

	/** @var string */
	private $asset_name;

	/** @var string */
	private $stylesheet;

	/** @var WP_Theme */
	private $theme;

	/**
	 * @param string $owner       GitHub repository owner.
	 * @param string $repository  GitHub repository name.
	 * @param string $asset_name  Required release asset filename.
	 */
	public function __construct( $owner, $repository, $asset_name ) {
		$this->owner       = $owner;
		$this->repository  = $repository;
		$this->asset_name  = $asset_name;
		$this->stylesheet  = get_stylesheet();
		$this->theme       = wp_get_theme( $this->stylesheet );
	}

	/**
	 * Registers update and information hooks.
	 */
	public function register() {
		add_filter( 'update_themes_github.com', array( $this, 'filter_update' ), 10, 4 );
		add_filter( 'themes_api', array( $this, 'filter_theme_information' ), 10, 3 );
		add_action( 'fahar_theme_clear_updater_cache', array( $this, 'clear_cache' ) );
	}

	/**
	 * Returns update data for this theme when a newer stable release is available.
	 *
	 * @param array|false $update           Existing update value.
	 * @param array       $theme_data       Theme headers.
	 * @param string      $theme_stylesheet Theme stylesheet slug.
	 * @param array       $locales          Installed locales.
	 * @return array|false
	 */
	public function filter_update( $update, $theme_data, $theme_stylesheet, $locales ) {
		unset( $theme_data, $locales );

		if ( $this->stylesheet !== $theme_stylesheet ) {
			return $update;
		}

		$release = $this->get_release();
		if ( ! $release ) {
			return $update;
		}

		$installed_version = (string) $this->theme->get( 'Version' );
		if ( '' === $installed_version || ! version_compare( $release['version'], $installed_version, '>' ) ) {
			return $update;
		}

		return array(
			'theme'       => $this->stylesheet,
			'new_version' => $release['version'],
			'url'         => $release['url'],
			'package'     => $release['package'],
		);
	}

	/**
	 * Supplies the details modal for the custom update.
	 *
	 * @param false|object|array|WP_Error $result Existing API result.
	 * @param string                      $action Requested action.
	 * @param object                      $args   API arguments.
	 * @return false|object|array|WP_Error
	 */
	public function filter_theme_information( $result, $action, $args ) {
		if (
			'theme_information' !== $action ||
			empty( $args->slug ) ||
			$this->stylesheet !== $args->slug
		) {
			return $result;
		}

		$release = $this->get_release();
		if ( ! $release ) {
			return $result;
		}

		return (object) array(
			'name'          => $this->theme->get( 'Name' ),
			'slug'          => $this->stylesheet,
			'version'       => $release['version'],
			'author'        => $this->theme->get( 'Author' ),
			'homepage'      => $release['url'],
			'download_link' => $release['package'],
			'external'      => true,
			'sections'      => array(
				'description' => $this->theme->get( 'Description' ),
			),
		);
	}

	/**
	 * Deletes cached release data.
	 */
	public function clear_cache() {
		delete_site_transient( self::CACHE_KEY );
	}

	/**
	 * Gets a validated release from cache or GitHub.
	 *
	 * @return array|null
	 */
	private function get_release() {
		$cached = get_site_transient( self::CACHE_KEY );
		if ( is_array( $cached ) && isset( $cached['status'] ) ) {
			return ( 'success' === $cached['status'] && $this->is_valid_release( $cached ) ) ? $cached : null;
		}

		if ( false !== $cached ) {
			delete_site_transient( self::CACHE_KEY );
		}

		$release = $this->request_release();
		if ( ! $release ) {
			set_site_transient(
				self::CACHE_KEY,
				array( 'status' => 'failure' ),
				HOUR_IN_SECONDS
			);
			return null;
		}

		$release['status'] = 'success';
		$cache_ttl         = (int) apply_filters( 'fahar_theme_updater_cache_ttl', 12 * HOUR_IN_SECONDS );
		set_site_transient( self::CACHE_KEY, $release, max( HOUR_IN_SECONDS, $cache_ttl ) );

		return $release;
	}

	/**
	 * Requests and validates the latest stable GitHub release.
	 *
	 * @return array|null
	 */
	private function request_release() {
		$endpoint = sprintf(
			'https://api.github.com/repos/%s/%s/releases/latest',
			rawurlencode( $this->owner ),
			rawurlencode( $this->repository )
		);
		$response = wp_remote_get(
			$endpoint,
			array(
				'timeout' => 10,
				'headers' => array(
					'Accept'     => 'application/vnd.github+json',
					'User-Agent' => 'FaharTheme-Updater',
				),
			)
		);

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return null;
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		if (
			! is_array( $data ) ||
			! empty( $data['draft'] ) ||
			! empty( $data['prerelease'] ) ||
			empty( $data['tag_name'] ) ||
			! is_string( $data['tag_name'] ) ||
			empty( $data['html_url'] ) ||
			empty( $data['assets'] ) ||
			! is_array( $data['assets'] )
		) {
			return null;
		}

		$version = ltrim( trim( $data['tag_name'] ), 'vV' );
		if ( ! preg_match( '/^\d+\.\d+\.\d+(?:\.\d+)?$/D', $version ) ) {
			return null;
		}

		$package = '';
		foreach ( $data['assets'] as $asset ) {
			if (
				is_array( $asset ) &&
				isset( $asset['name'], $asset['browser_download_url'] ) &&
				$this->asset_name === $asset['name']
			) {
				$package = $asset['browser_download_url'];
				break;
			}
		}

		$release = array(
			'version' => $version,
			'url'     => esc_url_raw( $data['html_url'] ),
			'package' => esc_url_raw( $package ),
		);

		return $this->is_valid_release( $release ) ? $release : null;
	}

	/**
	 * Validates normalized release data, including cached values.
	 *
	 * @param array $release Release data.
	 * @return bool
	 */
	private function is_valid_release( $release ) {
		return ! empty( $release['version'] ) &&
			! empty( $release['url'] ) &&
			! empty( $release['package'] ) &&
			(bool) preg_match( '/^\d+\.\d+\.\d+(?:\.\d+)?$/D', $release['version'] ) &&
			(bool) wp_http_validate_url( $release['url'] ) &&
			(bool) wp_http_validate_url( $release['package'] );
	}
}
