<?php
/**
 * @package   Genesis Product Updater
 * @author    Dazzle Software https://dazzlesoftware.org
 * @copyright Copyright (C) 2026 Dazzle Software, LLC
 * @license   GNU/GPLv3 and later
 */
/**
 * Registers the "Product" custom post type that backs every entry in the
 * updater (one product = one update feed per platform it targets).
 */
class Product_Updater_Post_Type {

	const POST_TYPE = 'pu_product';

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'register' ) );
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( $this, 'columns' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( $this, 'render_column' ), 10, 2 );
	}

	public function register() {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels'             => array(
					'name'               => __( 'Products', 'genesis-product-updater' ),
					'singular_name'      => __( 'Product', 'genesis-product-updater' ),
					'add_new_item'       => __( 'Add New Product', 'genesis-product-updater' ),
					'edit_item'          => __( 'Edit Product', 'genesis-product-updater' ),
					'all_items'          => __( 'Products', 'genesis-product-updater' ),
					'search_items'       => __( 'Search Products', 'genesis-product-updater' ),
					'not_found'          => __( 'No products found', 'genesis-product-updater' ),
				),
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => 'genesis-product-updater-updater',
				'capability_type'     => 'post',
				'map_meta_cap'        => true,
				'supports'            => array( 'title', 'page-attributes' ),
				'has_archive'         => false,
				'menu_icon'           => 'dashicons-update',
			)
		);

		register_taxonomy(
			'pu_download_category',
			self::POST_TYPE,
			array(
				'labels' => array(
					'name' => __( 'Download Categories', 'genesis-product-updater' ),
					'singular_name' => __( 'Download Category', 'genesis-product-updater' ),
				),
				'public' => false,
				'show_ui' => true,
				'show_admin_column' => true,
				'show_in_menu' => 'genesis-product-updater-updater',
				'hierarchical' => true,
			)
		);
	}

	public static function activate() {
		self::instance()->register();
		Product_Updater_Changelog_Post_Type::instance()->register();
		flush_rewrite_rules();
	}

	public static function deactivate() {
		flush_rewrite_rules();
	}

	public function columns( $columns ) {
		$new = array();
		foreach ( $columns as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['product_updater_element']   = __( 'Element / Slug', 'genesis-product-updater' );
				$new['product_updater_platforms'] = __( 'Platforms', 'genesis-product-updater' );
				$new['product_updater_files']     = __( 'Generated Files', 'genesis-product-updater' );
			}
		}
		return $new;
	}

	public function render_column( $column, $post_id ) {
		switch ( $column ) {
			case 'product_updater_element':
				echo esc_html( get_post_meta( $post_id, '_product_updater_element', true ) );
				break;

			case 'product_updater_platforms':
				$rows      = Product_Updater_Product_Metaboxes::get_rows( $post_id );
				$platforms = array_unique( wp_list_pluck( $rows, 'platform' ) );
				$labels    = array();
				foreach ( $platforms as $slug ) {
					$generator = Product_Updater_Generator_Manager::instance()->get_platform( $slug );
					$labels[]  = $generator ? esc_html( $generator->get_label() ) : esc_html( $slug );
				}
				echo $labels ? implode( ', ', $labels ) : '&#8212;';
				break;

			case 'product_updater_files':
				$product = Product_Updater_Product_Metaboxes::get_product_data( $post_id );
				$rows    = Product_Updater_Product_Metaboxes::get_rows( $post_id );
				$byplat  = array();
				foreach ( $rows as $row ) {
					$byplat[ $row['platform'] ][] = $row;
				}
				if ( empty( $byplat ) ) {
					echo '&#8212;';
					break;
				}
				foreach ( $byplat as $slug => $platform_rows ) {
					$generator = Product_Updater_Generator_Manager::instance()->get_platform( $slug );
					if ( ! $generator ) {
						continue;
					}
					$info = Product_Updater_File_Writer::instance()->get_file_info( $generator, $product );
					printf(
						'<div><a href="%s" target="_blank">%s</a> %s</div>',
						esc_url( $info['url'] ),
						esc_html( $generator->get_subpath( $product ) ),
						$info['exists'] ? '<span style="color:#46b450;">&#10003;</span>' : '<span style="color:#dc3232;">(not generated)</span>'
					);
				}
				break;
		}
	}
}
