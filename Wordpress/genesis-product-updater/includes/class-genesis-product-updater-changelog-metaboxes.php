<?php
/**
 * @package   Genesis Product Updater
 * @author    Dazzle Software https://dazzlesoftware.org
 * @copyright Copyright (C) 2026 Dazzle Software, LLC
 * @license   GNU/GPLv3 and later
 */
/**
 * Changelog edit screen: pick the product it belongs to, then a repeatable
 * table of version/date/category/items rows. Multiple rows can share the
 * same version (one per category) — they're grouped into a single release
 * entry when the file is generated.
 */
class Product_Updater_Changelog_Metaboxes {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_boxes' ) );
		add_action( 'save_post_' . Product_Updater_Changelog::POST_TYPE, array( $this, 'save' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
	}

	public function assets( $hook ) {
		global $post_type;
		if ( Product_Updater_Changelog::POST_TYPE !== $post_type ) {
			return;
		}
		wp_enqueue_style( 'genesis-product-updater-admin', PRODUCT_UPDATER_PLUGIN_URL . 'assets/css/genesis-product-updater-admin.css', array(), PRODUCT_UPDATER_VERSION );
		wp_enqueue_script( 'genesis-product-updater-changelog-admin', PRODUCT_UPDATER_PLUGIN_URL . 'assets/js/genesis-product-updater-changelog-admin.js', array( 'jquery' ), PRODUCT_UPDATER_VERSION, true );
	}

	public function add_boxes() {
		add_meta_box( 'product_updater_changelog_product', __( 'Linked Product', 'genesis-product-updater' ), array( $this, 'render_product' ), Product_Updater_Changelog::POST_TYPE, 'normal', 'high' );
		add_meta_box( 'product_updater_changelog_rows', __( 'Version History', 'genesis-product-updater' ), array( $this, 'render_rows' ), Product_Updater_Changelog::POST_TYPE, 'normal', 'high' );
		add_meta_box( 'product_updater_changelog_generate', __( 'Generate Files', 'genesis-product-updater' ), array( $this, 'render_generate' ), Product_Updater_Changelog::POST_TYPE, 'side', 'high' );
	}

	public function render_product( $post ) {
		wp_nonce_field( 'product_updater_changelog_save', 'product_updater_changelog_nonce' );
		$selected = (int) get_post_meta( $post->ID, '_product_updater_changelog_product_id', true );

		$products = get_posts(
			array(
				'post_type'      => Product_Updater_Post_Type::POST_TYPE,
				'posts_per_page' => -1,
				'post_status'    => array( 'publish', 'draft', 'pending' ),
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);
		?>
		<p>
			<label for="product_updater_changelog_product_id"><strong><?php esc_html_e( 'Product', 'genesis-product-updater' ); ?></strong></label><br />
			<select name="product_updater_changelog_product_id" id="product_updater_changelog_product_id" style="min-width:300px;">
				<option value=""><?php esc_html_e( '&mdash; Select a product &mdash;', 'genesis-product-updater' ); ?></option>
				<?php foreach ( $products as $product ) : ?>
					<option value="<?php echo esc_attr( $product->ID ); ?>" <?php selected( $selected, $product->ID ); ?>>
						<?php echo esc_html( $product->post_title ); ?>
						<?php $element = get_post_meta( $product->ID, '_product_updater_element', true ); ?>
						<?php echo $element ? ' (' . esc_html( $element ) . ')' : ''; ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>
		<p class="description"><?php esc_html_e( 'The changelog file is generated into that product\'s folder, using its element/type and reusing whichever platforms it targets.', 'genesis-product-updater' ); ?></p>
		<?php
	}

	public function render_rows( $post ) {
		$rows       = Product_Updater_Changelog::get_rows( $post->ID );
		$categories = Product_Updater_Changelog::get_category_choices();

		if ( empty( $rows ) ) {
			$rows = array( array() );
		}
		?>
		<table class="widefat genesis-product-updater-rows-table">
			<thead>
				<tr>
					<th style="width:12%;"><?php esc_html_e( 'Version', 'genesis-product-updater' ); ?></th>
					<th style="width:12%;"><?php esc_html_e( 'Date', 'genesis-product-updater' ); ?></th>
					<th style="width:14%;"><?php esc_html_e( 'Category', 'genesis-product-updater' ); ?></th>
					<th><?php esc_html_e( 'Items (one per line)', 'genesis-product-updater' ); ?></th>
					<th></th>
				</tr>
			</thead>
			<tbody id="genesis-product-updater-changelog-rows-body">
				<?php foreach ( $rows as $i => $row ) : ?>
					<?php $this->render_row( $i, $row, $categories ); ?>
				<?php endforeach; ?>
			</tbody>
		</table>
		<p><button type="button" class="button" id="genesis-product-updater-changelog-add-row"><?php esc_html_e( '+ Add Row', 'genesis-product-updater' ); ?></button></p>
		<p class="description"><?php esc_html_e( 'Add one row per version/category combination (e.g. 5.6.2 + Addition, 5.6.2 + Note). Rows sharing the same version are combined into a single release entry, newest version first, regardless of row order.', 'genesis-product-updater' ); ?></p>

		<script type="text/template" id="genesis-product-updater-changelog-row-template">
			<?php $this->render_row( '__INDEX__', array(), $categories ); ?>
		</script>
		<?php
	}

	private function render_row( $i, $row, $categories ) {
		$row = wp_parse_args(
			$row,
			array(
				'version'  => '',
				'date'     => '',
				'category' => 'fix',
				'items'    => '',
			)
		);
		?>
		<tr class="genesis-product-updater-row">
			<td><input type="text" name="product_updater_changelog_rows[<?php echo esc_attr( $i ); ?>][version]" value="<?php echo esc_attr( $row['version'] ); ?>" placeholder="5.6.2" style="width:100%;" /></td>
			<td><input type="text" name="product_updater_changelog_rows[<?php echo esc_attr( $i ); ?>][date]" value="<?php echo esc_attr( $row['date'] ); ?>" placeholder="YYYY-MM-DD" style="width:100%;" /></td>
			<td>
				<select name="product_updater_changelog_rows[<?php echo esc_attr( $i ); ?>][category]" style="width:100%;">
					<?php foreach ( $categories as $slug => $label ) : ?>
						<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $row['category'], $slug ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
			<td><textarea name="product_updater_changelog_rows[<?php echo esc_attr( $i ); ?>][items]" rows="2" style="width:100%;"><?php echo esc_textarea( $row['items'] ); ?></textarea></td>
			<td><button type="button" class="button-link genesis-product-updater-remove-row" title="<?php esc_attr_e( 'Remove row', 'genesis-product-updater' ); ?>">&times;</button></td>
		</tr>
		<?php
	}

	public function render_generate( $post ) {
		$product_id = (int) get_post_meta( $post->ID, '_product_updater_changelog_product_id', true );

		if ( ! $product_id || ! get_post( $product_id ) ) {
			echo '<p>' . esc_html__( 'Select and save a linked product first.', 'genesis-product-updater' ) . '</p>';
			return;
		}

		$product = Product_Updater_Product_Metaboxes::get_product_data( $product_id );
		$writer  = Product_Updater_File_Writer::instance();

		echo '<ul>';
		foreach ( Product_Updater_Generator_Manager::instance()->get_platforms() as $generator ) {
			if ( $generator->get_slug() !== ( $product['platform'] ?? '' ) ) {
				continue;
			}
			if ( ! $generator->supports_changelog() ) {
				continue;
			}
			$info = $writer->get_changelog_file_info( $generator, $product );
			echo '<li>' . esc_html( $generator->get_label() ) . ': ';
			if ( $info['exists'] ) {
				echo '<a href="' . esc_url( $info['url'] ) . '" target="_blank">' . esc_html( $generator->get_changelog_subpath( $product ) ) . '</a>';
			} else {
				echo esc_html__( 'not generated yet', 'genesis-product-updater' );
			}
			echo '</li>';
		}
		echo '</ul>';
		echo '<p class="description">' . esc_html__( 'Files regenerate automatically whenever you update this changelog.', 'genesis-product-updater' ) . '</p>';
	}

	public function save( $post_id ) {
		if ( ! isset( $_POST['product_updater_changelog_nonce'] ) || ! wp_verify_nonce( $_POST['product_updater_changelog_nonce'], 'product_updater_changelog_save' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$product_id = isset( $_POST['product_updater_changelog_product_id'] ) ? (int) $_POST['product_updater_changelog_product_id'] : 0;
		update_post_meta( $post_id, '_product_updater_changelog_product_id', $product_id );

		$rows        = array();
		$posted_rows = isset( $_POST['product_updater_changelog_rows'] ) && is_array( $_POST['product_updater_changelog_rows'] ) ? wp_unslash( $_POST['product_updater_changelog_rows'] ) : array();

		foreach ( $posted_rows as $posted_row ) {
			if ( empty( $posted_row['version'] ) ) {
				continue;
			}
			$rows[] = array(
				'version'  => sanitize_text_field( $posted_row['version'] ),
				'date'     => sanitize_text_field( $posted_row['date'] ?? '' ),
				'category' => sanitize_key( $posted_row['category'] ?? 'fix' ),
				'items'    => sanitize_textarea_field( $posted_row['items'] ?? '' ),
			);
		}

		update_post_meta( $post_id, '_product_updater_changelog_rows', $rows );

		remove_action( 'save_post_' . Product_Updater_Changelog::POST_TYPE, array( $this, 'save' ) );
		if ( $product_id ) {
			Product_Updater_File_Writer::instance()->generate_changelog_for_product( $product_id );
			// The main update feed's embedded changelog section may also
			// need to change, so refresh it too.
			Product_Updater_File_Writer::instance()->generate_product( $product_id );
		}
		add_action( 'save_post_' . Product_Updater_Changelog::POST_TYPE, array( $this, 'save' ) );
	}
}
