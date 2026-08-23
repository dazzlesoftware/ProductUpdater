<?php
/**
 * @package   Genesis Product Updater
 * @author    Dazzle Software https://dazzlesoftware.org
 * @copyright Copyright (C) 2026 Dazzle Software, LLC
 * @license   GNU/GPLv3 and later
 */
/**
 * Combined iOS + Android update feed generator. Both apps hit the same JSON
 * file and compare their own local version (CFBundleShortVersionString on
 * iOS, android:versionName on Android) against 'latest_version', then follow
 * whichever url_ios/url_android field is theirs.
 */
class Product_Updater_Platform_Mobile extends Product_Updater_Platform_Base {

	public function get_slug() {
		return 'mobile';
	}

	public function get_label() {
		return 'Mobile (iOS & Android)';
	}

	public function get_file_extension() {
		return 'json';
	}

	public function supports_compatibility_fields() {
		return false;
	}

	public function supports_mobile_fields() {
		return true;
	}

	public function supports_changelog() {
		return true;
	}

	public function generate( array $product, array $rows ) {
		$row = $this->get_current_row( $rows );

		$data = array(
			'type'           => $product['type'] ?? '',
			'latest_version' => isset( $row['version'] ) ? $row['version'] : '',
			'build_number'   => isset( $row['build_number'] ) && '' !== $row['build_number'] ? (int) $row['build_number'] : 0,
			'url_ios'        => isset( $row['url_ios'] ) ? $row['url_ios'] : '',
			'url_android'    => isset( $row['url_android'] ) ? $row['url_android'] : '',
			'force_update'   => ! empty( $row['force_update'] ),
			'changelog'      => ! empty( $row['release_notes'] ) ? $row['release_notes'] : ( ! empty( $product['description'] ) ? $product['description'] : '' ),
		);

		$changelog_mode = $row['changelog_mode'] ?? 'generated';
		if ( 'custom' === $changelog_mode && ! empty( $row['changelog_url'] ) ) {
			$data['changelog_url'] = $row['changelog_url'];
		} elseif ( 'generated' === $changelog_mode ) {
			$data['changelog_url'] = Product_Updater_File_Writer::instance()->get_changelog_file_url( $this, $product );
		}

		return wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . "\n";
	}

	public function generate_changelog( array $product, array $entries ) {
		return wp_json_encode( array(
			'name' => $product['name'] ?? '',
			'type' => $product['type'] ?? '',
			'entries' => $entries,
		), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . "\n";
	}
}

add_filter(
	'product_updater_register_platforms',
	function ( $platforms ) {
		$platforms['mobile'] = new Product_Updater_Platform_Mobile();
		return $platforms;
	}
);
