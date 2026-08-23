<?php
/**
 * @package   Genesis Product Updater
 * @author    Dazzle Software https://dazzlesoftware.org
 * @copyright Copyright (C) 2026 Dazzle Software, LLC
 * @license   GNU/GPLv3 and later
 */
/**
 * Base class every platform generator extends.
 *
 * To add a brand new platform (e.g. Drupal, PrestaShop, an internal app...):
 *   1. Copy includes/platforms/class-genesis-product-updater-platform-wordpress.php as a starting point.
 *   2. Extend Product_Updater_Platform_Base and implement get_slug(), get_label(),
 *      get_file_extension() and generate().
 *   3. Register it with the 'product_updater_register_platforms' filter (see bottom of
 *      class-genesis-product-updater-platform-joomla.php for the pattern), or just require the
 *      file from genesis-product-updater.php.
 * It will then automatically show up in the product editor's platform
 * dropdown and be included in "Generate files".
 */
abstract class Product_Updater_Platform_Base {

	/**
	 * Unique machine name, e.g. 'joomla'. Used as the row "platform" value,
	 * the generated file's base name, and the settings/admin identifier.
	 */
	abstract public function get_slug();

	/**
	 * Human readable label shown in the admin UI, e.g. 'Joomla'.
	 */
	abstract public function get_label();

	/**
	 * File extension (without the dot) the feed is written with, e.g. 'xml' or 'json'.
	 */
	abstract public function get_file_extension();

	/**
	 * Build the full contents of the update feed file for one product.
	 *
	 * @param array $product Product data: element, name, type, description,
	 *                        maintainer, maintainer_url, slug, post_id.
	 * @param array $rows    All version rows saved against this product for
	 *                        this platform (each row is an assoc array, see
	 *                        Product_Updater_Product_Metaboxes::get_row_fields()).
	 * @return string File contents to write.
	 */
	abstract public function generate( array $product, array $rows );

	/**
	 * File name (without directory) the feed is written to. Override if a
	 * platform expects a specific naming convention.
	 */
	public function get_filename( array $product ) {
		return sanitize_file_name( $this->get_slug() ) . '.' . $this->get_file_extension();
	}

	/**
	 * Subfolder (relative to the configured output base) this platform's
	 * files are written into. Default groups by platform then product,
	 * e.g. {output_base}/joomla/{product-slug}/joomla.xml
	 */
	public function get_subpath( array $product ) {
		$folder = ! empty( $product['element'] ) ? $product['element'] : $product['slug'];
		return sanitize_title( $this->get_slug() ) . '/' . sanitize_title( $folder ) . '/' . $this->get_filename( $product );
	}

	/**
	 * Whether this platform has a distinct changelog file format. Return
	 * true and implement generate_changelog() to support it; the Changelogs
	 * screen only offers/generates files for platforms that opt in.
	 */
	public function supports_changelog() {
		return false;
	}

	/**
	 * Whether this platform uses the Requires/Tested/Requires PHP
	 * compatibility fields on version rows. Return false for platforms with
	 * no notion of a "core version" to gate against (e.g. a flat file drop);
	 * the product editor hides those fields for rows on this platform.
	 */
	public function supports_compatibility_fields() {
		return true;
	}

	/**
	 * Whether this platform uses the mobile-app fields on version rows
	 * (Build Number / URL (iOS) / URL (Android) / Force Update / Release
	 * Notes). Only platforms that opt in (e.g. a combined iOS+Android
	 * updater) show these; the product editor hides them otherwise.
	 */
	public function supports_mobile_fields() {
		return false;
	}

	/**
	 * Build the full contents of the changelog file for one product.
	 *
	 * @param array $product Product data, see generate().
	 * @param array $entries Changelog entries, newest version first. Each
	 *                       entry: array( 'version' => '5.6.2', 'date' => '',
	 *                       'categories' => array( 'addition' => array( 'item one', ... ), ... ) ).
	 * @return string File contents to write.
	 */
	public function generate_changelog( array $product, array $entries ) {
		return '';
	}

	/**
	 * File name the changelog is written to. Override if a platform expects
	 * a specific naming convention.
	 */
	public function get_changelog_filename( array $product ) {
		return sanitize_file_name( $this->get_slug() . '_changelog' ) . '.' . $this->get_file_extension();
	}

	/**
	 * Subfolder (relative to the configured output base) the changelog is
	 * written into. Default: same product folder as the update feed.
	 */
	public function get_changelog_subpath( array $product ) {
		$folder = ! empty( $product['element'] ) ? $product['element'] : $product['slug'];
		return sanitize_title( $this->get_slug() ) . '/' . sanitize_title( $folder ) . '/' . $this->get_changelog_filename( $product );
	}

	/**
	 * Helper: pick the "current" row to represent a platform when the
	 * platform only supports a single latest version (e.g. plain JSON
	 * updaters). Falls back to the last row / highest version.
	 */
	protected function get_current_row( array $rows ) {
		if ( empty( $rows ) ) {
			return array();
		}
		foreach ( $rows as $row ) {
			if ( ! empty( $row['is_current'] ) ) {
				return $row;
			}
		}
		$sorted = $rows;
		usort(
			$sorted,
			function ( $a, $b ) {
				return version_compare( $a['version'] ?? '0', $b['version'] ?? '0' );
			}
		);
		return end( $sorted );
	}
}
