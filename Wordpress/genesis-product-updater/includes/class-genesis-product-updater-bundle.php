<?php
/**
 * @package   Genesis Product Updater
 * @author    Dazzle Software https://dazzlesoftware.org
 * @copyright Copyright (C) 2026 Dazzle Software, LLC
 * @license   GNU/GPLv3 and later
 */

class Product_Updater_Bundle {
	const POST_TYPE = 'pu_bundle';
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'register' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post_' . self::POST_TYPE, array( $this, 'save' ), 10, 2 );
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( $this, 'columns' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( $this, 'render_column' ), 10, 2 );
	}

	public function register() {
		register_post_type( self::POST_TYPE, array(
			'labels' => array(
				'name' => __( 'Bundles', 'genesis-product-updater' ),
				'singular_name' => __( 'Bundle', 'genesis-product-updater' ),
				'add_new_item' => __( 'Add New Bundle', 'genesis-product-updater' ),
				'edit_item' => __( 'Edit Bundle', 'genesis-product-updater' ),
				'all_items' => __( 'Bundles', 'genesis-product-updater' ),
			),
			'public' => false,
			'show_ui' => true,
			'show_in_menu' => 'genesis-product-updater-updater',
			'supports' => array( 'title', 'page-attributes' ),
			'map_meta_cap' => true,
		) );
	}

	public function add_meta_boxes() {
		add_meta_box( 'product_updater_bundle_details', __( 'Bundle Details', 'genesis-product-updater' ), array( $this, 'render_details' ), self::POST_TYPE, 'normal', 'high' );
		add_meta_box( 'product_updater_bundle_generate', __( 'Generated File', 'genesis-product-updater' ), array( $this, 'render_generate' ), self::POST_TYPE, 'side', 'high' );
	}

	public static function get_data( $post_id ) {
		$post = get_post( $post_id );
		return array(
			'id' => (int) $post_id,
			'name' => $post ? $post->post_title : '',
			'description' => (string) get_post_meta( $post_id, '_product_updater_bundle_description', true ),
			'platform' => (string) ( get_post_meta( $post_id, '_product_updater_bundle_platform', true ) ?: 'joomla' ),
			'output_slug' => (string) get_post_meta( $post_id, '_product_updater_bundle_output_slug', true ),
			'filename' => (string) get_post_meta( $post_id, '_product_updater_bundle_filename', true ),
			'product_ids' => array_map( 'absint', (array) get_post_meta( $post_id, '_product_updater_bundle_product_ids', true ) ),
		);
	}

	public function render_details( $post ) {
		$data = self::get_data( $post->ID );
		wp_nonce_field( 'product_updater_bundle_save', 'product_updater_bundle_nonce' );
		$products = get_posts( array( 'post_type' => Product_Updater_Post_Type::POST_TYPE, 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
		?>
		<table class="form-table">
			<tr><th><label for="pu_bundle_description"><?php esc_html_e( 'Description', 'genesis-product-updater' ); ?></label></th><td><input class="regular-text" id="pu_bundle_description" name="pu_bundle_description" value="<?php echo esc_attr( $data['description'] ); ?>"></td></tr>
			<tr><th><label for="pu_bundle_platform"><?php esc_html_e( 'Platform', 'genesis-product-updater' ); ?></label></th><td><select id="pu_bundle_platform" name="pu_bundle_platform"><?php foreach ( array( 'joomla' => 'Joomla', 'wordpress' => 'WordPress', 'mobile' => 'Mobile', 'fab' => 'Fab' ) as $slug => $label ) : ?><option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $data['platform'], $slug ); ?>><?php echo esc_html( $label ); ?></option><?php endforeach; ?></select></td></tr>
			<tr><th><label for="pu_bundle_output_slug"><?php esc_html_e( 'Output Folder', 'genesis-product-updater' ); ?></label></th><td><input class="regular-text" id="pu_bundle_output_slug" name="pu_bundle_output_slug" value="<?php echo esc_attr( $data['output_slug'] ); ?>" placeholder="<?php echo esc_attr( sanitize_title( $post->post_title ) ); ?>"><p class="description"><?php esc_html_e( 'Generated below /updates/{platform}/. Leave blank to use the bundle title slug.', 'genesis-product-updater' ); ?></p></td></tr>
			<tr><th><label for="pu_bundle_filename"><?php esc_html_e( 'Filename', 'genesis-product-updater' ); ?></label></th><td><input class="regular-text" id="pu_bundle_filename" name="pu_bundle_filename" value="<?php echo esc_attr( $data['filename'] ?: ( 'joomla' === $data['platform'] ? 'list.xml' : 'list.json' ) ); ?>"></td></tr>
			<tr><th><label for="pu_bundle_products"><?php esc_html_e( 'Products', 'genesis-product-updater' ); ?></label></th><td><select class="widefat" id="pu_bundle_products" name="pu_bundle_product_ids[]" multiple size="10">
			<?php foreach ( $products as $product_post ) : $product = Product_Updater_Product_Metaboxes::get_product_data( $product_post->ID ); ?>
				<option data-platform="<?php echo esc_attr( $product['platform'] ?? '' ); ?>" value="<?php echo (int) $product_post->ID; ?>" <?php selected( in_array( (int) $product_post->ID, $data['product_ids'], true ) ); ?>><?php echo esc_html( $product_post->post_title . ' [' . ucfirst( $product['platform'] ?? '' ) . '] (' . ( $product['element'] ?? '' ) . ')' ); ?></option>
			<?php endforeach; ?></select><p class="description"><?php esc_html_e( 'Use Ctrl/Cmd to select multiple products.', 'genesis-product-updater' ); ?></p></td></tr>
		</table>
		<script>document.addEventListener('DOMContentLoaded',function(){var p=document.getElementById('pu_bundle_platform'),s=document.getElementById('pu_bundle_products'),f=document.getElementById('pu_bundle_filename');function sync(){Array.prototype.forEach.call(s.options,function(o){o.hidden=o.dataset.platform!==p.value;if(o.hidden)o.selected=false;});var ext=p.value==='joomla'?'.xml':'.json';if(f.value.match(/\.(xml|json)$/i))f.value=f.value.replace(/\.(xml|json)$/i,ext);}p.addEventListener('change',sync);sync();});</script>
		<?php
	}

	public function render_generate( $post ) {
		if ( ! $post->ID || 'auto-draft' === $post->post_status ) { echo '<p>' . esc_html__( 'Save the bundle to generate list.xml.', 'genesis-product-updater' ) . '</p>'; return; }
		$info = Product_Updater_Bundle_Generator::instance()->get_file_info( self::get_data( $post->ID ) );
		printf( '<p><a href="%s" target="_blank">%s</a></p><p>%s</p>', esc_url( $info['url'] ), esc_html( $info['subpath'] ), $info['exists'] ? esc_html__( 'Generated', 'genesis-product-updater' ) : esc_html__( 'Not generated yet', 'genesis-product-updater' ) );
	}

	public function save( $post_id, $post ) {
		if ( wp_is_post_revision( $post_id ) || ! isset( $_POST['product_updater_bundle_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['product_updater_bundle_nonce'] ) ), 'product_updater_bundle_save' ) || ! current_user_can( 'edit_post', $post_id ) ) { return; }
		update_post_meta( $post_id, '_product_updater_bundle_description', sanitize_text_field( wp_unslash( $_POST['pu_bundle_description'] ?? '' ) ) );
		$platform = sanitize_key( wp_unslash( $_POST['pu_bundle_platform'] ?? 'joomla' ) );
		if ( ! in_array( $platform, array( 'joomla', 'wordpress', 'mobile', 'fab' ), true ) ) { $platform = 'joomla'; }
		update_post_meta( $post_id, '_product_updater_bundle_platform', $platform );
		update_post_meta( $post_id, '_product_updater_bundle_output_slug', sanitize_title( wp_unslash( $_POST['pu_bundle_output_slug'] ?? '' ) ) );
		$extension = 'joomla' === $platform ? 'xml' : 'json';
		$filename = sanitize_file_name( wp_unslash( $_POST['pu_bundle_filename'] ?? 'list.' . $extension ) );
		$filename = preg_replace( '/\.(xml|json)$/i', '', $filename ) . '.' . $extension;
		update_post_meta( $post_id, '_product_updater_bundle_filename', $filename );
		update_post_meta( $post_id, '_product_updater_bundle_product_ids', array_values( array_filter( array_map( 'absint', (array) ( $_POST['pu_bundle_product_ids'] ?? array() ) ) ) ) );
		if ( 'publish' === $post->post_status ) { Product_Updater_Bundle_Generator::instance()->generate_bundle( $post_id ); }
	}

	public function columns( $columns ) {
		$columns['pu_bundle_products'] = __( 'Products', 'genesis-product-updater' );
		$columns['pu_bundle_file'] = __( 'Generated File', 'genesis-product-updater' );
		return $columns;
	}

	public function render_column( $column, $post_id ) {
		$data = self::get_data( $post_id );
		if ( 'pu_bundle_products' === $column ) { echo (int) count( $data['product_ids'] ); }
		if ( 'pu_bundle_file' === $column ) { $info = Product_Updater_Bundle_Generator::instance()->get_file_info( $data ); printf( '<a href="%s" target="_blank">%s</a> %s', esc_url( $info['url'] ), esc_html( $info['subpath'] ), $info['exists'] ? '&#10003;' : esc_html__( '(not generated)', 'genesis-product-updater' ) ); }
	}
}

class Product_Updater_Bundle_Generator {
	private static $instance = null;
	public static function instance() { if ( null === self::$instance ) { self::$instance = new self(); } return self::$instance; }
	private function __construct() {}

	public function get_file_info( array $bundle ) {
		$writer = Product_Updater_File_Writer::instance();
		$platform = sanitize_key( $bundle['platform'] ?? 'joomla' );
		$slug = sanitize_title( $bundle['output_slug'] ?: $bundle['name'] );
		$filename = sanitize_file_name( $bundle['filename'] ?: ( 'joomla' === $platform ? 'list.xml' : 'list.json' ) );
		$subpath = $platform . '/' . $slug . '/' . $filename;
		return array( 'subpath' => $subpath, 'path' => $writer->get_base_dir() . '/' . str_replace( '/', DIRECTORY_SEPARATOR, $subpath ), 'url' => $writer->get_base_url() . '/' . $subpath, 'exists' => file_exists( $writer->get_base_dir() . '/' . str_replace( '/', DIRECTORY_SEPARATOR, $subpath ) ) );
	}

	public function generate_bundle( $bundle_id ) {
		$bundle = Product_Updater_Bundle::get_data( $bundle_id );
		$platform_slug = $bundle['platform'] ?? 'joomla';
		if ( 'joomla' !== $platform_slug ) { return $this->generate_json_bundle( $bundle ); }
		$xml = new XMLWriter(); $xml->openMemory(); $xml->setIndent( true ); $xml->setIndentString( '    ' ); $xml->startDocument( '1.0', 'UTF-8', 'no' );
		$xml->startElement( 'extensionset' ); $xml->writeAttribute( 'name', $bundle['name'] ); $xml->writeAttribute( 'description', $bundle['description'] );
		$joomla = Product_Updater_Generator_Manager::instance()->get_platform( 'joomla' );
		foreach ( $bundle['product_ids'] as $product_id ) {
			if ( 'publish' !== get_post_status( $product_id ) ) { continue; }
			$product = Product_Updater_Product_Metaboxes::get_product_data( $product_id );
			if ( 'joomla' !== ( $product['platform'] ?? '' ) || ! $joomla ) { continue; }
			foreach ( Product_Updater_Product_Metaboxes::get_rows( $product_id ) as $row ) {
				if ( 'joomla' !== ( $row['platform'] ?? '' ) || empty( $row['version'] ) ) { continue; }
				$xml->startElement( 'extension' );
				foreach ( array( 'name' => $product['name'], 'description' => $product['description'], 'element' => $product['element'], 'type' => $product['type'], 'client' => 'site', 'version' => $row['version'], 'targetplatformversion' => $row['target_version'] ?? '*', 'detailsurl' => Product_Updater_File_Writer::instance()->get_file_url( $joomla, $product ) ) as $key => $value ) { $xml->writeAttribute( $key, (string) $value ); }
				if ( ! empty( $row['info_url'] ) ) { $xml->writeAttribute( 'infourl', $row['info_url'] ); }
				$xml->endElement();
			}
		}
		$xml->endElement(); $xml->endDocument();
		$info = $this->get_file_info( $bundle );
		if ( ! wp_mkdir_p( dirname( $info['path'] ) ) || false === file_put_contents( $info['path'], $xml->outputMemory() ) ) { return new WP_Error( 'bundle_write_failed', sprintf( __( 'Could not write bundle file: %s', 'genesis-product-updater' ), $info['path'] ) ); }
		return $info['path'];
	}

	private function generate_json_bundle( array $bundle ) {
		$slug = $bundle['platform']; $platform = Product_Updater_Generator_Manager::instance()->get_platform( $slug ); $products = array();
		if ( ! $platform ) { return new WP_Error( 'bundle_platform_missing', __( 'Bundle platform generator is unavailable.', 'genesis-product-updater' ) ); }
		foreach ( $bundle['product_ids'] as $product_id ) {
			if ( 'publish' !== get_post_status( $product_id ) ) { continue; }
			$product = Product_Updater_Product_Metaboxes::get_product_data( $product_id );
			if ( $slug !== ( $product['platform'] ?? '' ) ) { continue; }
			$row = $this->current_row( Product_Updater_Product_Metaboxes::get_rows( $product_id ), $slug ); if ( ! $row ) { continue; }
			$products[] = array( 'name' => $product['name'], 'element' => $product['element'], 'type' => $product['type'], 'version' => $row['version'], 'preview_image' => $product['preview_image'] ?? '', 'feed_url' => Product_Updater_File_Writer::instance()->get_file_url( $platform, $product ), 'info_url' => $row['info_url'] ?? '' );
		}
		$contents = wp_json_encode( array( 'name' => $bundle['name'], 'description' => $bundle['description'], 'platform' => $slug, 'products' => $products ), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
		$info = $this->get_file_info( $bundle ); if ( ! wp_mkdir_p( dirname( $info['path'] ) ) || false === file_put_contents( $info['path'], $contents . "\n" ) ) { return new WP_Error( 'bundle_write_failed', __( 'Could not write bundle file.', 'genesis-product-updater' ) ); } return $info['path'];
	}

	private function current_row( array $rows, $platform ) { $rows = array_values( array_filter( $rows, function( $row ) use ( $platform ) { return $platform === ( $row['platform'] ?? '' ) && ! empty( $row['version'] ); } ) ); foreach ( $rows as $row ) { if ( ! empty( $row['is_current'] ) ) { return $row; } } usort( $rows, function( $a, $b ) { return version_compare( $b['version'] ?? '0', $a['version'] ?? '0' ); } ); return $rows[0] ?? null; }

	public function generate_all() {
		$ids = get_posts( array( 'post_type' => Product_Updater_Bundle::POST_TYPE, 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids' ) );
		$results = array(); foreach ( $ids as $id ) { $results[ $id ] = $this->generate_bundle( $id ); } return $results;
	}
}
