<?php
/**
 * @package   Genesis Updater
 * @author    Dazzle Software https://dazzlesoftware.org
 * @copyright Copyright (C) 2026 Dazzle Software, LLC
 * @license   GNU/GPLv3 and later
 */
/**
 * Joomla update feed generator (Joomla "extension update sites" XML format).
 * One <update> block per version row, so a single file can list several
 * Joomla major-version targets the way tpl_g5_helium.xml does.
 */
class Genesis_Updater_Platform_Joomla extends Genesis_Updater_Platform_Base {

	public function get_slug() {
		return 'joomla';
	}

	public function get_label() {
		return 'Joomla';
	}

	public function get_file_extension() {
		return 'xml';
	}

	/**
	 * Joomla extension packages are conventionally prefixed by type
	 * (tpl_g5_helium, com_something, mod_something...). Mirrors that so
	 * generated file names match what Joomla developers expect.
	 */
	private function get_type_prefix( array $product ) {
		$prefixes = array(
			'template'  => 'tpl_',
			'component' => 'com_',
			'module'    => 'mod_',
			'plugin'    => 'plg_',
			'library'   => 'lib_',
			'package'   => 'pkg_',
			'file'      => 'file_',
			'language'  => 'lang_',
		);
		$type = strtolower( trim( $product['type'] ?? '' ) );
		return isset( $prefixes[ $type ] ) ? $prefixes[ $type ] : '';
	}

	public function get_filename( array $product ) {
		$base = ! empty( $product['element'] ) ? $product['element'] : $product['slug'];
		return sanitize_file_name( $this->get_type_prefix( $product ) . $base ) . '.' . $this->get_file_extension();
	}

	public function get_changelog_filename( array $product ) {
		$base = ! empty( $product['element'] ) ? $product['element'] : $product['slug'];
		return sanitize_file_name( $this->get_type_prefix( $product ) . $base . '_changelog' ) . '.' . $this->get_file_extension();
	}

	public function supports_changelog() {
		return true;
	}

	public function generate( array $product, array $rows ) {
		$xml = new XMLWriter();
		$xml->openMemory();
		$xml->setIndent( true );
		$xml->setIndentString( '    ' );
		$xml->startDocument( '1.0', 'utf-8' );
		$xml->startElement( 'updates' );

		foreach ( $rows as $row ) {
			if ( ! empty( $row['tag'] ) ) {
				$xml->writeComment( sprintf( ' Joomla %s - %s ', $row['target_version'] ?? '', $row['version'] ?? '' ) );
			}

			$xml->startElement( 'update' );

			$xml->writeElement( 'name', $product['name'] );
			$xml->writeElement( 'description', ! empty( $row['description'] ) ? $row['description'] : $product['description'] );
			$xml->writeElement( 'element', $product['element'] );
			$xml->writeElement( 'type', ! empty( $product['type'] ) ? $product['type'] : 'template' );
			$xml->writeElement( 'version', $row['version'] );

			if ( ! empty( $row['info_url'] ) ) {
				$xml->startElement( 'infourl' );
				if ( ! empty( $row['info_title'] ) ) {
					$xml->writeAttribute( 'title', $row['info_title'] );
				}
				$xml->text( $row['info_url'] );
				$xml->endElement();
			}

			$changelog_mode = $row['changelog_mode'] ?? 'generated';
			$changelog_url  = 'custom' === $changelog_mode ? ( $row['changelog_url'] ?? '' ) : '';
			if ( 'generated' === $changelog_mode ) {
				// Fall back to the auto-generated changelog file for this
				// product, if the output folder resolves to a public URL.
				$base_url = Genesis_Updater_File_Writer::instance()->get_base_url();
				if ( ! empty( $base_url ) ) {
					$changelog_url = $base_url . '/' . $this->get_changelog_subpath( $product );
				}
			}
			if ( ! empty( $changelog_url ) ) {
				$xml->writeElement( 'changelogurl', $changelog_url );
			}

			if ( ! empty( $row['sha512'] ) ) {
				$xml->writeElement( 'sha512', strtoupper( $row['sha512'] ) );
			}

			if ( ! empty( $row['download_url'] ) ) {
				$xml->startElement( 'downloads' );
				$xml->startElement( 'downloadurl' );
				$xml->writeAttribute( 'type', 'full' );
				$xml->writeAttribute( 'format', 'zip' );
				$xml->text( $row['download_url'] );
				$xml->endElement();
				$xml->endElement();
			}

			$xml->startElement( 'tags' );
			$xml->writeElement( 'tag', ! empty( $row['tag'] ) ? $row['tag'] : 'stable' );
			$xml->endElement();

			if ( ! empty( $product['maintainer'] ) ) {
				$xml->writeElement( 'maintainer', $product['maintainer'] );
			}
			if ( ! empty( $product['maintainer_url'] ) ) {
				$xml->writeElement( 'maintainerurl', $product['maintainer_url'] );
			}

			$xml->startElement( 'targetplatform' );
			$xml->writeAttribute( 'name', 'joomla' );
			$xml->writeAttribute( 'version', ! empty( $row['target_version'] ) ? $row['target_version'] : '*' );
			$xml->endElement();

			if ( ! empty( $row['requires_php'] ) ) {
				$xml->writeElement( 'php_minimum', $row['requires_php'] );
			}
			if ( ! empty( $row['requires'] ) ) {
				$xml->writeElement( 'requires', $row['requires'] );
			}
			if ( ! empty( $row['tested'] ) ) {
				$xml->writeElement( 'tested', $row['tested'] );
			}

			$xml->endElement(); // update
		}

		$xml->endElement(); // updates
		$xml->endDocument();

		return $xml->outputMemory();
	}

	public function generate_changelog( array $product, array $entries ) {
		$xml = new XMLWriter();
		$xml->openMemory();
		$xml->setIndent( true );
		$xml->setIndentString( '    ' );
		$xml->startDocument( '1.0', 'UTF-8' );
		$xml->startElement( 'changelogs' );

		foreach ( $entries as $entry ) {
			$xml->startElement( 'changelog' );
			$xml->writeElement( 'element', $product['element'] );
			$xml->writeElement( 'type', ! empty( $product['type'] ) ? $product['type'] : 'template' );
			$xml->writeElement( 'version', $entry['version'] );

			foreach ( $entry['categories'] as $category => $items ) {
				if ( empty( $items ) ) {
					continue;
				}
				$xml->startElement( $category );
				foreach ( $items as $item ) {
					$xml->writeElement( 'item', $item );
				}
				$xml->endElement();
			}

			$xml->endElement(); // changelog
		}

		$xml->endElement(); // changelogs
		$xml->endDocument();

		return $xml->outputMemory();
	}
}

add_filter(
	'genesis_updater_register_platforms',
	function ( $platforms ) {
		$platforms['joomla'] = new Genesis_Updater_Platform_Joomla();
		return $platforms;
	}
);
