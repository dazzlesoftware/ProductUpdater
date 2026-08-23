<?php
/**
 * @package   Genesis Product Updater
 * @author    Dazzle Software https://dazzlesoftware.org
 * @copyright Copyright (C) 2026 Dazzle Software, LLC
 * @license   GNU/GPLv3 and later
 */
class Product_Updater_Downloads {
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_shortcode( 'product_updater_downloads', array( $this, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'assets' ) );
		add_action( 'admin_post_pu_product_download', array( $this, 'track_download' ) );
		add_action( 'admin_post_nopriv_pu_product_download', array( $this, 'track_download' ) );
	}

	public static function refresh_file_size( $product_id, array $rows ) {
		$row = self::pick_current_row( $rows );
		$url = $row['download_url'] ?? '';
		$size = 0;
		if ( $url ) {
			$response = wp_remote_head( $url, array( 'timeout' => 4, 'redirection' => 3 ) );
			if ( ! is_wp_error( $response ) ) $size = (int) wp_remote_retrieve_header( $response, 'content-length' );
		}
		update_post_meta( $product_id, '_product_updater_download_size', $size );
	}

	private static function pick_current_row( array $rows ) {
		foreach ( $rows as $row ) if ( ! empty( $row['is_current'] ) ) return $row;
		usort( $rows, function ( $a, $b ) { return version_compare( $b['version'] ?? '0', $a['version'] ?? '0' ); } );
		return $rows[0] ?? array();
	}

	public function track_download() {
		$product_id = absint( $_GET['product_id'] ?? 0 );
		if ( ! $product_id || 'publish' !== get_post_status( $product_id ) ) wp_die( esc_html__( 'Download not found.', 'genesis-product-updater' ), '', array( 'response' => 404 ) );
		$row = self::pick_current_row( Product_Updater_Product_Metaboxes::get_rows( $product_id ) );
		$url = esc_url_raw( $row['download_url'] ?? '' );
		if ( ! $url ) wp_die( esc_html__( 'Download not found.', 'genesis-product-updater' ), '', array( 'response' => 404 ) );
		$count = (int) get_post_meta( $product_id, '_product_updater_download_count', true );
		update_post_meta( $product_id, '_product_updater_download_count', $count + 1 );
		wp_redirect( $url, 302, 'Genesis Product Updater' );
		exit;
	}

	public function assets() {
		if ( is_singular() && has_shortcode( (string) get_post_field( 'post_content', get_queried_object_id() ), 'product_updater_downloads' ) ) {
			wp_enqueue_style( 'genesis-product-updater-downloads', PRODUCT_UPDATER_PLUGIN_URL . 'assets/css/genesis-product-updater-downloads.css', array(), PRODUCT_UPDATER_VERSION );
			wp_enqueue_script( 'genesis-product-updater-downloads', PRODUCT_UPDATER_PLUGIN_URL . 'assets/js/genesis-product-updater-downloads.js', array(), PRODUCT_UPDATER_VERSION, true );
		}
	}

	private function current_row( array $rows ) {
		return self::pick_current_row( $rows );
	}

	private function changelog_url( array $product, array $row ) {
		if ( 'none' === ( $row['changelog_mode'] ?? 'generated' ) ) return '';
		if ( 'custom' === ( $row['changelog_mode'] ?? 'generated' ) ) return $row['changelog_url'] ?? '';
		$generator = Product_Updater_Generator_Manager::instance()->get_platform( $product['platform'] );
		return $generator ? Product_Updater_File_Writer::instance()->get_changelog_file_url( $generator, $product ) : '';
	}

	public function render( $atts ) {
		$atts = shortcode_atts( array( 'title' => '', 'intro' => '', 'category' => '' ), $atts, 'product_updater_downloads' );
		$term_args = array( 'taxonomy' => 'pu_download_category', 'hide_empty' => true, 'orderby' => 'term_order' );
		$category_slug = sanitize_title( $atts['category'] );
		if ( '' !== $category_slug ) {
			$term_args['slug'] = $category_slug;
		}
		$categories = get_terms( $term_args );
		if ( is_wp_error( $categories ) ) return '';
		$settings = Product_Updater_File_Writer::instance()->get_settings();
		$style = sprintf( '--pu-accent:%s;--pu-button-text:%s;--pu-download-hover-bg:%s;--pu-download-hover-text:%s;--pu-radius:%dpx;--pu-ios-bg:%s;--pu-ios-text:%s;--pu-ios-hover-bg:%s;--pu-ios-hover-text:%s;--pu-android-bg:%s;--pu-android-text:%s;--pu-android-hover-bg:%s;--pu-android-hover-text:%s', sanitize_hex_color( $settings['download_accent'] ) ?: '#914dad', sanitize_hex_color( $settings['download_text'] ) ?: '#ffffff', sanitize_hex_color( $settings['download_hover_accent'] ) ?: '#743d8a', sanitize_hex_color( $settings['download_hover_text'] ) ?: '#ffffff', (int) $settings['download_radius'], $settings['ios_bg'], $settings['ios_text'], $settings['ios_hover_bg'], $settings['ios_hover_text'], $settings['android_bg'], $settings['android_text'], $settings['android_hover_bg'], $settings['android_hover_text'] );
		$style .= sprintf( ';--pu-fab-bg:%s;--pu-fab-text:%s;--pu-fab-hover-bg:%s;--pu-fab-hover-text:%s', $settings['fab_bg'], $settings['fab_text'], $settings['fab_hover_bg'], $settings['fab_hover_text'] );
		$download_icon = '<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 3v12m0 0 5-5m-5 5-5-5M5 21h14"/></svg>';
		$changelog_icon = '<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M3 12a9 9 0 1 0 3-6.7L3 8m0 0h5M12 7v5l3 2"/></svg>';
		$apple_icon = '<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M16.7 13.2c0-2.4 2-3.6 2.1-3.7-1.1-1.7-2.9-1.9-3.6-1.9-1.5-.2-3 .9-3.8.9-.8 0-2-.9-3.3-.9-1.7 0-3.3 1-4.2 2.5-1.8 3.1-.5 7.8 1.3 10.3.9 1.2 1.9 2.6 3.3 2.5 1.3-.1 1.8-.8 3.4-.8 1.6 0 2 .8 3.4.8 1.4 0 2.3-1.3 3.2-2.5 1-1.4 1.4-2.9 1.4-3-.1 0-3.2-1.2-3.2-4.2zM14.2 6c.7-.9 1.2-2.1 1.1-3.3-1.1 0-2.4.7-3.2 1.6-.7.8-1.3 2-1.1 3.2 1.2.1 2.4-.6 3.2-1.5z"/></svg>';
		$android_icon = '<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M3 2.7v18.6L14.8 12 3 2.7zm13.2 8.2 2.7-2.1-3.4-2-2.1 3.3 2.8.8zm-2.8 3 2.1 3.3 3.4-2-2.7-2.1-2.8.8z"/></svg>';
		$bootstrap = ! empty( $settings['download_bootstrap'] );
		ob_start();
		?>
		<section class="pu-downloads<?php echo $bootstrap ? ' pu-use-bootstrap' : ''; ?>" style="<?php echo esc_attr( $style ); ?>">
			<?php if ( $atts['title'] ) : ?><header class="pu-downloads__header"><h1><?php echo esc_html( $atts['title'] ); ?></h1><?php if ( $atts['intro'] ) : ?><p><?php echo esc_html( $atts['intro'] ); ?></p><?php endif; ?></header><?php endif; ?>
			<?php foreach ( $categories as $category ) :
				$posts = get_posts( array( 'post_type' => Product_Updater_Post_Type::POST_TYPE, 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => array( 'menu_order' => 'ASC', 'title' => 'ASC' ), 'tax_query' => array( array( 'taxonomy' => 'pu_download_category', 'field' => 'term_id', 'terms' => $category->term_id ) ) ) );
				if ( ! $posts ) continue;
			?>
			<section class="pu-download-category">
				<h2><?php echo esc_html( $category->name ); ?></h2>
				<?php if ( $category->description ) : ?><div class="pu-download-category__description"><?php echo wp_kses_post( wpautop( $category->description ) ); ?></div><?php endif; ?>
				<div class="pu-download-grid<?php echo $bootstrap ? ' row g-4' : ''; ?>">
				<?php foreach ( $posts as $post ) : $product = Product_Updater_Product_Metaboxes::get_product_data( $post->ID ); $row = $this->current_row( Product_Updater_Product_Metaboxes::get_rows( $post->ID ) ); $entries = Product_Updater_Changelog::get_entries_for_product( $post->ID ); $size = (int) get_post_meta( $post->ID, '_product_updater_download_size', true ); $count = (int) get_post_meta( $post->ID, '_product_updater_download_count', true ); if ( empty( $row ) ) continue; ?>
					<article class="pu-download-card<?php echo $bootstrap ? ' col-md-6 col-xl-4 card h-100' : ''; ?>">
						<h3><?php echo esc_html( $product['name'] ); ?></h3>
						<?php if ( ! empty( $product['preview_image'] ) ) : ?><img class="pu-download-card__preview" src="<?php echo esc_url( $product['preview_image'] ); ?>" alt="<?php echo esc_attr( $product['name'] ); ?>"><?php endif; ?>
						<p class="pu-download-card__version"><?php printf( esc_html__( 'Version %s', 'genesis-product-updater' ), esc_html( $row['version'] ?? '' ) ); ?><?php if ( ! empty( $row['release_date'] ) ) : ?> &middot; <?php echo esc_html( $row['release_date'] ); ?><?php endif; ?></p>
						<?php if ( $product['description'] ) : ?><p><?php echo esc_html( $product['description'] ); ?></p><?php endif; ?>
						<?php if ( 'fab' === $product['platform'] && ! empty( $row['url_fab'] ) ) : ?><div class="pu-download-card__actions"><a class="pu-store-button pu-store-button--fab<?php echo $bootstrap ? ' btn' : ''; ?>" href="<?php echo esc_url( $row['url_fab'] ); ?>" target="_blank" rel="noopener"><?php if ( ! empty( $settings['download_icons'] ) ) : ?><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 2 22 8v8l-10 6L2 16V8l10-6zm0 4.1L6 9.6v4.8l6 3.5 6-3.5V9.6l-6-3.5zm0 2.3 4 2.3v2.6l-4 2.3-4-2.3v-2.6l4-2.3z"/></svg><?php endif; ?>Fab</a></div><?php endif; ?>
						<div class="pu-download-card__actions"><?php if ( 'mobile' === $product['platform'] ) : ?><?php if ( ! empty( $row['url_ios'] ) ) : ?><a class="pu-store-button pu-store-button--ios<?php echo $bootstrap ? ' btn' : ''; ?>" href="<?php echo esc_url( $row['url_ios'] ); ?>" target="_blank" rel="noopener"><?php if ( ! empty( $settings['download_icons'] ) ) echo $apple_icon; ?><?php esc_html_e( 'App Store', 'genesis-product-updater' ); ?></a><?php endif; ?><?php if ( ! empty( $row['url_android'] ) ) : ?><a class="pu-store-button pu-store-button--android<?php echo $bootstrap ? ' btn' : ''; ?>" href="<?php echo esc_url( $row['url_android'] ); ?>" target="_blank" rel="noopener"><?php if ( ! empty( $settings['download_icons'] ) ) echo $android_icon; ?><?php esc_html_e( 'Google Play', 'genesis-product-updater' ); ?></a><?php endif; ?><?php elseif ( ! empty( $row['download_url'] ) ) : ?><a class="pu-download-button<?php echo $bootstrap ? ' btn btn-primary' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin-post.php?action=pu_product_download&product_id=' . (int) $post->ID ) ); ?>"><?php if ( ! empty( $settings['download_icons'] ) ) echo $download_icon; ?><?php esc_html_e( 'Download', 'genesis-product-updater' ); ?></a><?php endif; ?><?php if ( $entries ) : ?><button class="pu-changelog-button<?php echo $bootstrap ? ' btn btn-outline-primary' : ''; ?>" type="button" data-pu-dialog="pu-changelog-<?php echo (int) $post->ID; ?>"><?php if ( ! empty( $settings['download_icons'] ) ) echo $changelog_icon; ?><?php esc_html_e( 'Changelog', 'genesis-product-updater' ); ?></button><?php endif; ?></div>
						<?php if ( 'mobile' !== $product['platform'] && ! empty( $row['download_url'] ) ) : ?><p class="pu-download-card__stats"><?php if ( $size ) { echo esc_html( size_format( $size, 2 ) ); echo ' / '; } ?><?php printf( esc_html( _n( '%s download', '%s downloads', $count, 'genesis-product-updater' ) ), number_format_i18n( $count ) ); ?></p><?php endif; ?>
					</article>
					<?php if ( $entries ) : ?><dialog class="pu-changelog-dialog" id="pu-changelog-<?php echo (int) $post->ID; ?>"><button class="pu-dialog-close" type="button" aria-label="<?php esc_attr_e( 'Close changelog', 'genesis-product-updater' ); ?>">&times;</button><h2><?php echo esc_html( $product['name'] ); ?> <?php esc_html_e( 'Changelog', 'genesis-product-updater' ); ?></h2><div class="pu-changelog-dialog__content"><?php echo wp_kses_post( Product_Updater_Changelog::render_html( $entries ) ); ?></div></dialog><?php endif; ?>
				<?php endforeach; ?>
				</div>
			</section>
			<?php endforeach; ?>
		</section>
		<?php
		return ob_get_clean();
	}
}
