<?php
/**
 * @package   Genesis Updater
 * @author    Dazzle Software https://dazzlesoftware.org
 * @copyright Copyright (C) 2026 Dazzle Software, LLC
 * @license   GNU/GPLv3 and later
 */
/**
 * Resolves the configured output location and writes generated feed files
 * to disk.
 */
class Genesis_Updater_File_Writer {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	/**
	 * Settings, with sane defaults: files land in {site root}/updates
	 * unless changed on the Settings screen.
	 */
	public function get_settings() {
		$defaults = array(
			'output_path' => 'updates', // relative to ABSPATH by default.
			'base_url'    => '',        // blank = derive from site_url() + path.
			'download_accent' => '#914dad',
			'download_text' => '#ffffff',
			'download_hover_accent' => '#743d8a',
			'download_hover_text' => '#ffffff',
			'download_radius' => 3,
			'download_icons' => 1,
			'download_bootstrap' => 0,
			'ios_bg' => '#000000', 'ios_text' => '#ffffff', 'ios_hover_bg' => '#333333', 'ios_hover_text' => '#ffffff',
			'android_bg' => '#01875f', 'android_text' => '#ffffff', 'android_hover_bg' => '#016b4b', 'android_hover_text' => '#ffffff',
			'fab_bg' => '#252525', 'fab_text' => '#ffffff', 'fab_hover_bg' => '#3b3b3b', 'fab_hover_text' => '#ffffff',
		);
		$settings = get_option( 'genesis_updater_settings', array() );
		return wp_parse_args( $settings, $defaults );
	}

	/**
	 * Absolute filesystem directory files are written under.
	 */
	public function get_base_dir() {
		$settings = $this->get_settings();
		$path     = trim( $settings['output_path'] );

		if ( '' === $path ) {
			$path = 'updates';
		}

		// Absolute path (Windows drive letter or leading slash) used as-is,
		// otherwise resolved relative to the WordPress root.
		$is_absolute = ( isset( $path[1] ) && ':' === $path[1] ) || '/' === $path[0] || '\\' === $path[0];

		return $is_absolute ? untrailingslashit( $path ) : untrailingslashit( ABSPATH . ltrim( $path, '/\\' ) );
	}

	/**
	 * Base URL matching get_base_dir(), used to build public links to the
	 * generated files.
	 */
	public function get_base_url() {
		$settings = $this->get_settings();

		if ( ! empty( $settings['base_url'] ) ) {
			return untrailingslashit( $settings['base_url'] );
		}

		$path        = trim( $settings['output_path'] );
		$is_absolute = ( isset( $path[1] ) && ':' === $path[1] ) || '/' === $path[0] || '\\' === $path[0];

		if ( $is_absolute ) {
			// Can't reliably derive a URL from an arbitrary filesystem path;
			// admin should set "Public base URL" explicitly in that case.
			return '';
		}

		return untrailingslashit( site_url( '/' . ltrim( $path, '/\\' ) ) );
	}

	public function get_file_path( Genesis_Updater_Platform_Base $generator, array $product ) {
		return $this->get_base_dir() . '/' . str_replace( '/', DIRECTORY_SEPARATOR, $generator->get_subpath( $product ) );
	}

	public function get_file_url( Genesis_Updater_Platform_Base $generator, array $product ) {
		$base_url = $this->get_base_url();
		if ( '' === $base_url ) {
			return '';
		}
		return $base_url . '/' . $generator->get_subpath( $product );
	}

	public function get_file_info( Genesis_Updater_Platform_Base $generator, array $product ) {
		$path = $this->get_file_path( $generator, $product );
		return array(
			'path'   => $path,
			'url'    => $this->get_file_url( $generator, $product ),
			'exists' => file_exists( $path ),
		);
	}

	public function get_changelog_file_path( Genesis_Updater_Platform_Base $generator, array $product ) {
		return $this->get_base_dir() . '/' . str_replace( '/', DIRECTORY_SEPARATOR, $generator->get_changelog_subpath( $product ) );
	}

	public function get_changelog_file_url( Genesis_Updater_Platform_Base $generator, array $product ) {
		$base_url = $this->get_base_url();
		if ( '' === $base_url ) {
			return '';
		}
		return $base_url . '/' . $generator->get_changelog_subpath( $product );
	}

	public function get_changelog_file_info( Genesis_Updater_Platform_Base $generator, array $product ) {
		$path = $this->get_changelog_file_path( $generator, $product );
		return array(
			'path'   => $path,
			'url'    => $this->get_changelog_file_url( $generator, $product ),
			'exists' => file_exists( $path ),
		);
	}

	/**
	 * Write raw contents to an absolute path, creating the directory first.
	 * Returns WP_Error on failure, or the path written on success.
	 */
	private function write_file( $path, $contents ) {
		$dir = dirname( $path );

		if ( ! wp_mkdir_p( $dir ) ) {
			return new WP_Error( 'genesis_updater_mkdir_failed', sprintf( __( 'Could not create directory: %s', 'genesis-updater' ), $dir ) );
		}

		$result = @file_put_contents( $path, $contents ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		if ( false === $result ) {
			return new WP_Error( 'genesis_updater_write_failed', sprintf( __( 'Could not write file: %s', 'genesis-updater' ), $path ) );
		}

		return $path;
	}

	/**
	 * Write one platform's feed file for a product. Returns WP_Error on
	 * failure, or the absolute path written on success.
	 */
	public function write( Genesis_Updater_Platform_Base $generator, array $product, array $rows ) {
		return $this->write_file( $this->get_file_path( $generator, $product ), $generator->generate( $product, $rows ) );
	}

	/**
	 * Write one platform's changelog file for a product. Returns WP_Error on
	 * failure, or the absolute path written on success.
	 */
	public function write_changelog( Genesis_Updater_Platform_Base $generator, array $product, array $entries ) {
		return $this->write_file( $this->get_changelog_file_path( $generator, $product ), $generator->generate_changelog( $product, $entries ) );
	}

	/**
	 * Generate every platform's file for a single product, grouping its
	 * version rows by platform first.
	 *
	 * @return array platform_slug => true|WP_Error
	 */
	public function generate_product( $post_id ) {
		$product = Genesis_Updater_Product_Metaboxes::get_product_data( $post_id );
		$rows    = Genesis_Updater_Product_Metaboxes::get_rows( $post_id );

		$byplat = array();
		foreach ( $rows as $row ) {
			if ( empty( $row['platform'] ) ) {
				continue;
			}
			$byplat[ $row['platform'] ][] = $row;
		}

		$results = array();
		foreach ( $byplat as $slug => $platform_rows ) {
			$generator = Genesis_Updater_Generator_Manager::instance()->get_platform( $slug );
			if ( ! $generator ) {
				$results[ $slug ] = new WP_Error( 'genesis_updater_unknown_platform', sprintf( __( 'Unknown platform "%s"', 'genesis-updater' ), $slug ) );
				continue;
			}
			$results[ $slug ] = $this->write( $generator, $product, $platform_rows );
		}

		return $results;
	}

	/**
	 * Generate every changelog-capable platform's changelog file for a
	 * product, from whichever changelog post (if any) is linked to it.
	 *
	 * @return array platform_slug => true|WP_Error
	 */
	public function generate_changelog_for_product( $product_id ) {
		$product = Genesis_Updater_Product_Metaboxes::get_product_data( $product_id );
		$entries = Genesis_Updater_Changelog::get_entries_for_product( $product_id );

		$results = array();
		if ( empty( $entries ) ) {
			return $results;
		}

		foreach ( Genesis_Updater_Generator_Manager::instance()->get_platforms() as $slug => $generator ) {
			if ( $slug !== ( $product['platform'] ?? '' ) ) {
				continue;
			}
			if ( ! $generator->supports_changelog() ) {
				continue;
			}
			$results[ $slug ] = $this->write_changelog( $generator, $product, $entries );
		}

		return $results;
	}

	/**
	 * Generate feed files for every product that has at least one version row.
	 *
	 * @return array post_id => results (see generate_product())
	 */
	public function generate_all() {
		$posts = get_posts(
			array(
				'post_type'      => Genesis_Updater_Post_Type::POST_TYPE,
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'fields'         => 'ids',
			)
		);

		$all = array();
		foreach ( $posts as $post_id ) {
			$all[ $post_id ] = $this->generate_product( $post_id );
		}

		$changelog_posts = get_posts(
			array(
				'post_type'      => Genesis_Updater_Changelog::POST_TYPE,
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'fields'         => 'ids',
			)
		);
		foreach ( $changelog_posts as $changelog_post_id ) {
			$product_id = (int) get_post_meta( $changelog_post_id, '_genesis_updater_changelog_product_id', true );
			if ( ! $product_id ) {
				continue;
			}
			$all[ $product_id . '_changelog' ] = $this->generate_changelog_for_product( $product_id );
		}

		$all['bundles'] = Genesis_Updater_Bundle_Generator::instance()->generate_all();

		return $all;
	}

	/**
	 * Force regeneration of every published changelog.
	 *
	 * @return array changelog post ID => generation results
	 */
	public function generate_all_changelogs() {
		$posts = get_posts(
			array(
				'post_type'      => Genesis_Updater_Changelog::POST_TYPE,
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'fields'         => 'ids',
			)
		);

		$all = array();
		foreach ( $posts as $changelog_post_id ) {
			$product_id = (int) get_post_meta( $changelog_post_id, '_genesis_updater_changelog_product_id', true );
			if ( $product_id ) {
				$all[ $changelog_post_id ] = $this->generate_changelog_for_product( $product_id );
			}
		}

		return $all;
	}
}
