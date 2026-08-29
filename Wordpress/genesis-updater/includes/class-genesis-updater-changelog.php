<?php
/**
 * @package   Genesis Updater
 * @author    Dazzle Software https://dazzlesoftware.org
 * @copyright Copyright (C) 2026 Dazzle Software, LLC
 * @license   GNU/GPLv3 and later
 */
/**
 * Shared changelog data model: category list, grouping raw rows into
 * per-version entries, and rendering them to HTML. Used by the Changelog
 * CPT/metabox (to save + generate files) and by the WordPress platform
 * generator (to embed a changelog section in the main update feed).
 */
class Genesis_Updater_Changelog {

	const POST_TYPE = 'genesis_up_changelog';

	/**
	 * Category slug => label, in the order they should appear in generated
	 * files. Matches Joomla's changelog schema category names, which
	 * doubles as a sensible generic vocabulary for other platforms.
	 */
	public static function get_category_choices() {
		return array(
			'addition'   => __( 'Addition', 'genesis-updater' ),
			'change'     => __( 'Change', 'genesis-updater' ),
			'fix'        => __( 'Fix', 'genesis-updater' ),
			'remove'     => __( 'Remove', 'genesis-updater' ),
			'security'   => __( 'Security', 'genesis-updater' ),
			'deprecated' => __( 'Deprecated', 'genesis-updater' ),
			'language'   => __( 'Language', 'genesis-updater' ),
			'note'       => __( 'Note', 'genesis-updater' ),
		);
	}

	/**
	 * Group raw saved rows (one row per version+category) into one entry
	 * per version, newest version first, with categories in the canonical
	 * order from get_category_choices().
	 *
	 * @param array $rows Raw rows: version, date, category, items (array of strings).
	 * @return array List of array( 'version', 'date', 'categories' => array( slug => array of item strings ) )
	 */
	public static function get_entries( array $rows ) {
		$by_version = array();

		foreach ( $rows as $row ) {
			if ( empty( $row['version'] ) || empty( $row['category'] ) ) {
				continue;
			}
			$version = $row['version'];
			if ( ! isset( $by_version[ $version ] ) ) {
				$by_version[ $version ] = array(
					'version'    => $version,
					'date'       => ! empty( $row['date'] ) ? $row['date'] : '',
					'categories' => array(),
				);
			}
			if ( empty( $by_version[ $version ]['date'] ) && ! empty( $row['date'] ) ) {
				$by_version[ $version ]['date'] = $row['date'];
			}

			$items = isset( $row['items'] ) ? preg_split( '/\r\n|\r|\n/', $row['items'] ) : array();
			$items = array_values( array_filter( array_map( 'trim', $items ) ) );

			if ( ! isset( $by_version[ $version ]['categories'][ $row['category'] ] ) ) {
				$by_version[ $version ]['categories'][ $row['category'] ] = array();
			}
			$by_version[ $version ]['categories'][ $row['category'] ] = array_merge(
				$by_version[ $version ]['categories'][ $row['category'] ],
				$items
			);
		}

		$entries = array_values( $by_version );

		usort(
			$entries,
			function ( $a, $b ) {
				return version_compare( $b['version'], $a['version'] ); // newest first
			}
		);

		$order = array_keys( self::get_category_choices() );
		foreach ( $entries as &$entry ) {
			$ordered = array();
			foreach ( $order as $category ) {
				if ( ! empty( $entry['categories'][ $category ] ) ) {
					$ordered[ $category ] = $entry['categories'][ $category ];
				}
			}
			// Keep any custom/unknown category names too, at the end.
			foreach ( $entry['categories'] as $category => $items ) {
				if ( ! isset( $ordered[ $category ] ) && ! empty( $items ) ) {
					$ordered[ $category ] = $items;
				}
			}
			$entry['categories'] = $ordered;
		}
		unset( $entry );

		return $entries;
	}

	/**
	 * Find the changelog post linked to a product, if any.
	 */
	public static function get_changelog_post_id_for_product( $product_id ) {
		$posts = get_posts(
			array(
				'post_type'      => self::POST_TYPE,
				'posts_per_page' => 1,
				'post_status'    => 'publish',
				'fields'         => 'ids',
				'meta_key'       => '_genesis_updater_changelog_product_id',
				'meta_value'     => $product_id,
			)
		);
		return $posts ? $posts[0] : 0;
	}

	public static function get_rows( $changelog_post_id ) {
		$rows = get_post_meta( $changelog_post_id, '_genesis_updater_changelog_rows', true );
		return is_array( $rows ) ? $rows : array();
	}

	public static function get_entries_for_product( $product_id ) {
		$changelog_post_id = self::get_changelog_post_id_for_product( $product_id );
		if ( ! $changelog_post_id ) {
			return array();
		}
		return self::get_entries( self::get_rows( $changelog_post_id ) );
	}

	/**
	 * Render entries to simple HTML suitable for a WordPress update
	 * response's `sections.changelog`.
	 */
	public static function render_html( array $entries ) {
		if ( empty( $entries ) ) {
			return '';
		}

		$labels = self::get_category_choices();
		$html   = '';

		foreach ( $entries as $entry ) {
			$heading = $entry['version'];
			if ( ! empty( $entry['date'] ) ) {
				$heading .= ' &#8212; ' . $entry['date'];
			}
			$html .= '<h4>' . esc_html( $heading ) . '</h4>' . "\n";

			foreach ( $entry['categories'] as $category => $items ) {
				if ( empty( $items ) ) {
					continue;
				}
				$label = isset( $labels[ $category ] ) ? $labels[ $category ] : ucfirst( $category );
				$html .= '<p><strong>' . esc_html( $label ) . '</strong></p>' . "\n<ul>\n";
				foreach ( $items as $item ) {
					$html .= '<li>' . esc_html( $item ) . '</li>' . "\n";
				}
				$html .= "</ul>\n";
			}
		}

		return $html;
	}

	public static function get_changelog_html_for_product( $product_id ) {
		return self::render_html( self::get_entries_for_product( $product_id ) );
	}
}
