<?php
/**
 * @package   Genesis Updater
 * @author    Dazzle Software https://dazzlesoftware.org
 * @copyright Copyright (C) 2026 Dazzle Software, LLC
 * @license   GNU/GPLv3 and later
 */
class Genesis_Updater_Platform_Fab extends Genesis_Updater_Platform_Base {
	public function get_slug() { return 'fab'; }
	public function get_label() { return 'Fab'; }
	public function get_file_extension() { return 'json'; }
	public function supports_compatibility_fields() { return false; }
	public function supports_changelog() { return true; }
	public function generate( array $product, array $rows ) {
		$row = $this->get_current_row( $rows );
		$type = $product['type'] ?? '';
		$changelog = trim( (string) ( $row['release_notes'] ?? ( $product['description'] ?? '' ) ) );
		if ( '' === $changelog && ! empty( $product['post_id'] ) ) {
			$entries = Genesis_Updater_Changelog::get_entries_for_product( $product['post_id'] );
			foreach ( $entries as $entry ) {
				if ( (string) ( $entry['version'] ?? '' ) !== (string) ( $row['version'] ?? '' ) ) {
					continue;
				}
				$items = array();
				foreach ( $entry['categories'] ?? array() as $category_items ) {
					$items = array_merge( $items, (array) $category_items );
				}
				$changelog = implode( "\n", array_filter( array_map( 'trim', $items ) ) );
				break;
			}
		}
		$data = array(
			'type' => $type,
			'category' => preg_replace( '/^fab-/', '', $type ),
			'download_category' => $product['download_category'] ?? '',
			'preview_image' => $product['preview_image'] ?? '',
			'latest_version' => $row['version'] ?? '',
			'fab_url' => $row['url_fab'] ?? '',
			'changelog' => $changelog,
		);
		$changelog_mode = $row['changelog_mode'] ?? 'generated';
		if ( 'custom' === $changelog_mode && ! empty( $row['changelog_url'] ) ) {
			$data['changelog_url'] = $row['changelog_url'];
		} elseif ( 'generated' === $changelog_mode ) {
			$data['changelog_url'] = Genesis_Updater_File_Writer::instance()->get_changelog_file_url( $this, $product );
		}
		return wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . "\n";
	}
	public function generate_changelog( array $product, array $entries ) {
		return wp_json_encode( array(
			'name' => $product['name'] ?? '',
			'type' => $product['type'] ?? '',
			'category' => preg_replace( '/^fab-/', '', $product['type'] ?? '' ),
			'preview_image' => $product['preview_image'] ?? '',
			'entries' => $entries,
		), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . "\n";
	}
}
add_filter( 'genesis_updater_register_platforms', function ( $platforms ) { $platforms['fab'] = new Genesis_Updater_Platform_Fab(); return $platforms; } );
