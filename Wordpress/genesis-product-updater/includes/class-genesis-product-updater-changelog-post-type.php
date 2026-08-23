<?php
/**
 * @package   Genesis Product Updater
 * @author    Dazzle Software https://dazzlesoftware.org
 * @copyright Copyright (C) 2026 Dazzle Software, LLC
 * @license   GNU/GPLv3 and later
 */
/**
 * Registers the "Changelog" post type — a separate screen from Products,
 * one changelog per product, holding its full version history.
 */
class Product_Updater_Changelog_Post_Type {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'register' ) );
		add_filter( 'manage_' . Product_Updater_Changelog::POST_TYPE . '_posts_columns', array( $this, 'columns' ) );
		add_action( 'manage_' . Product_Updater_Changelog::POST_TYPE . '_posts_custom_column', array( $this, 'render_column' ), 10, 2 );
		add_filter( 'wp_insert_post_data', array( $this, 'auto_title' ), 10, 2 );
	}

	public function register() {
		register_post_type(
			Product_Updater_Changelog::POST_TYPE,
			array(
				'labels'          => array(
					'name'          => __( 'Changelogs', 'genesis-product-updater' ),
					'singular_name' => __( 'Changelog', 'genesis-product-updater' ),
					'add_new_item'  => __( 'Add New Changelog', 'genesis-product-updater' ),
					'edit_item'     => __( 'Edit Changelog', 'genesis-product-updater' ),
					'all_items'     => __( 'Changelogs', 'genesis-product-updater' ),
					'search_items'  => __( 'Search Changelogs', 'genesis-product-updater' ),
					'not_found'     => __( 'No changelogs found', 'genesis-product-updater' ),
				),
				'public'          => false,
				'show_ui'         => true,
				'show_in_menu'    => 'genesis-product-updater-updater',
				'capability_type' => 'post',
				'map_meta_cap'    => true,
				'supports'        => array( 'title' ),
				'has_archive'     => false,
			)
		);
	}

	/**
	 * The changelog post's title isn't user-facing anywhere except the admin
	 * list, so auto-derive it from the linked product instead of asking for
	 * a redundant title field.
	 */
	public function auto_title( $data, $postarr ) {
		if ( Product_Updater_Changelog::POST_TYPE !== $data['post_type'] ) {
			return $data;
		}
		$product_id = isset( $postarr['product_updater_changelog_product_id'] ) ? (int) $postarr['product_updater_changelog_product_id'] : 0;
		if ( $product_id ) {
			$product = get_post( $product_id );
			if ( $product ) {
				$data['post_title'] = sprintf( __( 'Changelog: %s', 'genesis-product-updater' ), $product->post_title );
				$data['post_name']  = sanitize_title( 'changelog-' . $product->post_name );
			}
		} elseif ( empty( $data['post_title'] ) ) {
			$data['post_title'] = __( '(no product selected)', 'genesis-product-updater' );
		}
		return $data;
	}

	public function columns( $columns ) {
		$new = array();
		foreach ( $columns as $key => $label ) {
			if ( 'title' === $key ) {
				$new['title']         = __( 'Changelog', 'genesis-product-updater' );
				$new['product_updater_product']   = __( 'Product', 'genesis-product-updater' );
				$new['product_updater_versions']  = __( 'Versions', 'genesis-product-updater' );
				$new['product_updater_files']     = __( 'Generated Files', 'genesis-product-updater' );
				continue;
			}
			$new[ $key ] = $label;
		}
		return $new;
	}

	public function render_column( $column, $post_id ) {
		$product_id = (int) get_post_meta( $post_id, '_product_updater_changelog_product_id', true );

		switch ( $column ) {
			case 'product_updater_product':
				if ( $product_id && get_post( $product_id ) ) {
					printf( '<a href="%s">%s</a>', esc_url( get_edit_post_link( $product_id ) ), esc_html( get_the_title( $product_id ) ) );
				} else {
					echo '&#8212;';
				}
				break;

			case 'product_updater_versions':
				$entries = Product_Updater_Changelog::get_entries( Product_Updater_Changelog::get_rows( $post_id ) );
				echo esc_html( implode( ', ', wp_list_pluck( $entries, 'version' ) ) );
				break;

			case 'product_updater_files':
				if ( ! $product_id || ! get_post( $product_id ) ) {
					echo '&#8212;';
					break;
				}
				$product = Product_Updater_Product_Metaboxes::get_product_data( $product_id );
				$writer  = Product_Updater_File_Writer::instance();
				$generator = Product_Updater_Generator_Manager::instance()->get_platform( $product['platform'] ?? '' );
				if ( $generator && $generator->supports_changelog() ) {
					$info = $writer->get_changelog_file_info( $generator, $product );
					printf(
						'<div><a href="%s" target="_blank">%s</a> %s</div>',
						esc_url( $info['url'] ),
						esc_html( $generator->get_changelog_subpath( $product ) ),
						$info['exists'] ? '<span style="color:#46b450;">&#10003;</span>' : '<span style="color:#dc3232;">(not generated)</span>'
					);
				} else {
					echo '&#8212;';
				}
				break;
		}
	}
}
