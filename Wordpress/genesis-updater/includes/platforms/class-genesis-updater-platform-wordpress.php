<?php
/**
 * @package   Genesis Updater
 * @author    Dazzle Software https://dazzlesoftware.org
 * @copyright Copyright (C) 2026 Dazzle Software, LLC
 * @license   GNU/GPLv3 and later
 */
/**
 * WordPress update feed generator. Matches the flat JSON format WordPress
 * custom updaters expect (version / details_url / download_url), built from
 * whichever row is flagged "current" (or the highest version found) for this
 * product, plus the compatibility fields (requires / tested / requires_php)
 * and changelog section most self-hosted WP update checkers also look for.
 */
class Genesis_Updater_Platform_WordPress extends Genesis_Updater_Platform_Base {

	public function get_slug() {
		return 'wordpress';
	}

	public function get_label() {
		return 'WordPress';
	}

	public function get_file_extension() {
		return 'json';
	}

	public function generate( array $product, array $rows ) {
		$row = $this->get_current_row( $rows );

		$data = array(
			'name'         => $product['name'],
			'type'         => $product['type'] ?? '',
			'slug'         => $product['element'] ? $product['element'] : $product['slug'],
			'version'      => isset( $row['version'] ) ? $row['version'] : '',
			'details_url'  => ! empty( $row['info_url'] ) ? $row['info_url'] : '',
			'download_url' => isset( $row['download_url'] ) ? $row['download_url'] : '',
		);

		foreach ( array( 'requires', 'tested', 'requires_php' ) as $field ) {
			if ( ! empty( $row[ $field ] ) ) {
				$data[ $field ] = $row[ $field ];
			}
		}

		if ( ! empty( $row['release_date'] ) ) {
			$data['last_updated'] = $row['release_date'];
		}

		if ( ! empty( $product['maintainer'] ) ) {
			$data['author'] = $product['maintainer'];
		}
		if ( ! empty( $product['maintainer_url'] ) ) {
			$data['homepage'] = $product['maintainer_url'];
		}

		$changelog_mode = $row['changelog_mode'] ?? 'generated';
		$changelog_url  = 'custom' === $changelog_mode ? ( $row['changelog_url'] ?? '' ) : '';
		if ( 'generated' === $changelog_mode ) {
			$base_url = Genesis_Updater_File_Writer::instance()->get_base_url();
			if ( ! empty( $base_url ) ) {
				$changelog_url = $base_url . '/' . $this->get_changelog_subpath( $product );
			}
		}
		if ( ! empty( $changelog_url ) ) {
			$data['changelog_url'] = $changelog_url;
		}

		$changelog_html = Genesis_Updater_Changelog::get_changelog_html_for_product( $product['post_id'] );
		$sections       = array();
		if ( ! empty( $product['description'] ) ) {
			$sections['description'] = wpautop( esc_html( $product['description'] ) );
		}
		if ( ! empty( $changelog_html ) ) {
			$sections['changelog'] = $changelog_html;
		}
		if ( ! empty( $sections ) ) {
			$data['sections'] = $sections;
		}

		return wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . "\n";
	}

	public function supports_changelog() {
		return true;
	}

	public function generate_changelog( array $product, array $entries ) {
		$data = array(
			'name'    => $product['name'],
			'type'    => $product['type'] ?? '',
			'slug'    => $product['element'] ? $product['element'] : $product['slug'],
			'entries' => array(),
		);

		foreach ( $entries as $entry ) {
			$data['entries'][] = array(
				'version'    => $entry['version'],
				'date'       => $entry['date'],
				'categories' => $entry['categories'],
			);
		}

		$data['changelog_html'] = Genesis_Updater_Changelog::render_html( $entries );

		return wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . "\n";
	}
}

add_filter(
	'genesis_updater_register_platforms',
	function ( $platforms ) {
		$platforms['wordpress'] = new Genesis_Updater_Platform_WordPress();
		return $platforms;
	}
);
