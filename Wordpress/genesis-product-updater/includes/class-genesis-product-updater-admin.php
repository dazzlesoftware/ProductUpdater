<?php
/**
 * @package   Genesis Product Updater
 * @author    Dazzle Software https://dazzlesoftware.org
 * @copyright Copyright (C) 2026 Dazzle Software, LLC
 * @license   GNU/GPLv3 and later
 */
/**
 * Top-level admin menu ("Genesis Product Updater") + Settings screen (where the
 * output folder for generated files is controlled).
 */
class Product_Updater_Admin {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_init', array( $this, 'handle_actions' ) );
		add_action( 'admin_notices', array( $this, 'notices' ) );
	}

	public function menu() {
		add_menu_page(
			__( 'Genesis Product Updater', 'genesis-product-updater' ),
			__( 'Genesis Product Updater', 'genesis-product-updater' ),
			'manage_options',
			'genesis-product-updater-updater',
			array( $this, 'render_dashboard' ),
			'dashicons-update',
			25
		);

		add_submenu_page(
			'genesis-product-updater-updater',
			__( 'Products', 'genesis-product-updater' ),
			__( 'All Products', 'genesis-product-updater' ),
			'manage_options',
			'edit.php?post_type=' . Product_Updater_Post_Type::POST_TYPE
		);

		add_submenu_page(
			'genesis-product-updater-updater',
			__( 'Add New Product', 'genesis-product-updater' ),
			__( 'Add New Product', 'genesis-product-updater' ),
			'manage_options',
			'post-new.php?post_type=' . Product_Updater_Post_Type::POST_TYPE
		);

		add_submenu_page(
			'genesis-product-updater-updater',
			__( 'Changelogs', 'genesis-product-updater' ),
			__( 'Changelogs', 'genesis-product-updater' ),
			'manage_options',
			'edit.php?post_type=' . Product_Updater_Changelog::POST_TYPE
		);

		add_submenu_page(
			'genesis-product-updater-updater',
			__( 'Add New Changelog', 'genesis-product-updater' ),
			__( 'Add New Changelog', 'genesis-product-updater' ),
			'manage_options',
			'post-new.php?post_type=' . Product_Updater_Changelog::POST_TYPE
		);

		add_submenu_page(
			'genesis-product-updater-updater',
			__( 'Download Categories', 'genesis-product-updater' ),
			__( 'Download Categories', 'genesis-product-updater' ),
			'manage_options',
			'edit-tags.php?taxonomy=pu_download_category&post_type=' . Product_Updater_Post_Type::POST_TYPE
		);

		add_submenu_page(
			'genesis-product-updater-updater',
			__( 'Settings', 'genesis-product-updater' ),
			__( 'Settings', 'genesis-product-updater' ),
			'manage_options',
			'genesis-product-updater-settings',
			array( $this, 'render_settings' )
		);

		remove_submenu_page( 'genesis-product-updater-updater', 'genesis-product-updater-updater' );
	}

	/* ---------------------------------------------------------------- */

	public function render_dashboard() {
		$writer = Product_Updater_File_Writer::instance();
		$counts = wp_count_posts( Product_Updater_Post_Type::POST_TYPE );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Genesis Product Updater', 'genesis-product-updater' ); ?></h1>
			<p><?php esc_html_e( 'Manage self-hosted update feeds for every product/platform and generate the feed files that Joomla, WordPress (and any platform you add) fetch to check for updates.', 'genesis-product-updater' ); ?></p>

			<table class="widefat" style="max-width:600px;">
				<tr>
					<th><?php esc_html_e( 'Products', 'genesis-product-updater' ); ?></th>
					<td><?php echo (int) ( $counts->publish ?? 0 ); ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Registered platforms', 'genesis-product-updater' ); ?></th>
					<td><?php echo esc_html( implode( ', ', Product_Updater_Generator_Manager::instance()->get_platform_choices() ) ); ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Output folder', 'genesis-product-updater' ); ?></th>
					<td><code><?php echo esc_html( $writer->get_base_dir() ); ?></code></td>
				</tr>
			</table>

			<p style="margin-top:20px;">
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=' . Product_Updater_Post_Type::POST_TYPE ) ); ?>" class="button"><?php esc_html_e( 'Manage Products', 'genesis-product-updater' ); ?></a>
				<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . Product_Updater_Post_Type::POST_TYPE ) ); ?>" class="button button-primary"><?php esc_html_e( 'Add New Product', 'genesis-product-updater' ); ?></a>
				<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=genesis-product-updater-updater&product_updater_action=generate_all' ), 'product_updater_generate_all' ) ); ?>" class="button"><?php esc_html_e( 'Regenerate All Files', 'genesis-product-updater' ); ?></a>
			</p>
		</div>
		<?php
	}

	public function render_settings() {
		$settings = Product_Updater_File_Writer::instance()->get_settings();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Genesis Product Updater Settings', 'genesis-product-updater' ); ?></h1>
			<p><?php esc_html_e( 'Create a normal WordPress page and add [product_updater_downloads]. Optional attributes: title, intro, and category. Use a category slug to make a filtered page, for example [product_updater_downloads category="mobile-apps"].', 'genesis-product-updater' ); ?></p>
			<form method="post" action="">
				<?php wp_nonce_field( 'product_updater_settings_save', 'product_updater_settings_nonce' ); ?>
				<table class="form-table">
					<tr>
						<th><label for="product_updater_output_path"><?php esc_html_e( 'Output folder', 'genesis-product-updater' ); ?></label></th>
						<td>
							<input type="text" id="product_updater_output_path" name="product_updater_output_path" class="regular-text" value="<?php echo esc_attr( $settings['output_path'] ); ?>" />
							<p class="description">
								<?php esc_html_e( 'Relative to the WordPress root by default (e.g. "updates" writes to /updates). You can also enter an absolute path (e.g. wp-content/uploads/updates, or a full server path).', 'genesis-product-updater' ); ?><br />
								<?php esc_html_e( 'Resolves to:', 'genesis-product-updater' ); ?> <code><?php echo esc_html( Product_Updater_File_Writer::instance()->get_base_dir() ); ?></code>
							</p>
						</td>
					</tr>
					<tr>
						<th><label for="product_updater_base_url"><?php esc_html_e( 'Public base URL (optional)', 'genesis-product-updater' ); ?></label></th>
						<td>
							<input type="text" id="product_updater_base_url" name="product_updater_base_url" class="regular-text" value="<?php echo esc_attr( $settings['base_url'] ); ?>" placeholder="<?php echo esc_attr( site_url( '/updates' ) ); ?>" />
							<p class="description"><?php esc_html_e( 'Leave blank to derive automatically from the site URL + output folder. Set this if files are served from a different domain/CDN, or if the output folder is an absolute filesystem path.', 'genesis-product-updater' ); ?></p>
						</td>
					</tr>
					<tr><th><?php esc_html_e( 'Download button accent', 'genesis-product-updater' ); ?></th><td><input type="color" name="product_updater_download_accent" value="<?php echo esc_attr( $settings['download_accent'] ); ?>"></td></tr>
					<tr><th><?php esc_html_e( 'Download button text', 'genesis-product-updater' ); ?></th><td><input type="color" name="product_updater_download_text" value="<?php echo esc_attr( $settings['download_text'] ); ?>"></td></tr>
					<tr><th><?php esc_html_e( 'Download button hover background', 'genesis-product-updater' ); ?></th><td><input type="color" name="product_updater_download_hover_accent" value="<?php echo esc_attr( $settings['download_hover_accent'] ); ?>"></td></tr>
					<tr><th><?php esc_html_e( 'Download button hover text', 'genesis-product-updater' ); ?></th><td><input type="color" name="product_updater_download_hover_text" value="<?php echo esc_attr( $settings['download_hover_text'] ); ?>"></td></tr>
					<tr><th><?php esc_html_e( 'Button corner radius', 'genesis-product-updater' ); ?></th><td><input type="number" min="0" max="50" name="product_updater_download_radius" value="<?php echo (int) $settings['download_radius']; ?>"> px</td></tr>
					<tr><th><?php esc_html_e( 'Button icons', 'genesis-product-updater' ); ?></th><td><label><input type="checkbox" name="product_updater_download_icons" value="1" <?php checked( ! empty( $settings['download_icons'] ) ); ?>> <?php esc_html_e( 'Show icons', 'genesis-product-updater' ); ?></label></td></tr>
					<tr><th><?php esc_html_e( 'Bootstrap styling', 'genesis-product-updater' ); ?></th><td><label><input type="checkbox" name="product_updater_download_bootstrap" value="1" <?php checked( ! empty( $settings['download_bootstrap'] ) ); ?>> <?php esc_html_e( 'Use Bootstrap classes and theme variables (requires a Bootstrap-enabled theme)', 'genesis-product-updater' ); ?></label></td></tr>
					<tr><th><?php esc_html_e( 'App Store colors', 'genesis-product-updater' ); ?></th><td><?php foreach ( array( 'ios_bg' => 'Background', 'ios_text' => 'Text', 'ios_hover_bg' => 'Hover background', 'ios_hover_text' => 'Hover text' ) as $key => $label ) : ?><label style="margin-right:16px;"><?php echo esc_html( $label ); ?> <input type="color" name="product_updater_<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $settings[ $key ] ); ?>"></label><?php endforeach; ?></td></tr>
					<tr><th><?php esc_html_e( 'Google Play colors', 'genesis-product-updater' ); ?></th><td><?php foreach ( array( 'android_bg' => 'Background', 'android_text' => 'Text', 'android_hover_bg' => 'Hover background', 'android_hover_text' => 'Hover text' ) as $key => $label ) : ?><label style="margin-right:16px;"><?php echo esc_html( $label ); ?> <input type="color" name="product_updater_<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $settings[ $key ] ); ?>"></label><?php endforeach; ?></td></tr>
					<tr><th><?php esc_html_e( 'Fab colors', 'genesis-product-updater' ); ?></th><td><?php foreach ( array( 'fab_bg' => 'Background', 'fab_text' => 'Text', 'fab_hover_bg' => 'Hover background', 'fab_hover_text' => 'Hover text' ) as $key => $label ) : ?><label style="margin-right:16px;"><?php echo esc_html( $label ); ?> <input type="color" name="product_updater_<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $settings[ $key ] ); ?>"></label><?php endforeach; ?></td></tr>
				</table>
				<?php submit_button( __( 'Save Settings', 'genesis-product-updater' ) ); ?>
			</form>

			<hr />
			<h2><?php esc_html_e( 'Registered Platforms', 'genesis-product-updater' ); ?></h2>
			<p><?php esc_html_e( 'New platforms are added by dropping a generator class into includes/platforms/ (see class-genesis-product-updater-platform-base.php) — no changes needed anywhere else.', 'genesis-product-updater' ); ?></p>
			<ul>
				<?php foreach ( Product_Updater_Generator_Manager::instance()->get_platforms() as $slug => $generator ) : ?>
					<li><strong><?php echo esc_html( $generator->get_label() ); ?></strong> (<?php echo esc_html( $slug ); ?>) &mdash; <?php echo esc_html( sprintf( '.%s files', $generator->get_file_extension() ) ); ?></li>
				<?php endforeach; ?>
			</ul>

			<p><a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=genesis-product-updater-settings&product_updater_action=generate_all' ), 'product_updater_generate_all' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Regenerate All Files Now', 'genesis-product-updater' ); ?></a></p>
		</div>
		<?php
	}

	/* ---------------------------------------------------------------- */

	public function handle_actions() {
		if ( isset( $_POST['product_updater_settings_nonce'] ) && wp_verify_nonce( $_POST['product_updater_settings_nonce'], 'product_updater_settings_save' ) ) {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			update_option(
				'product_updater_settings',
				array(
					'output_path' => sanitize_text_field( wp_unslash( $_POST['product_updater_output_path'] ?? 'updates' ) ),
					'base_url'    => esc_url_raw( wp_unslash( $_POST['product_updater_base_url'] ?? '' ) ),
					'download_accent' => sanitize_hex_color( wp_unslash( $_POST['product_updater_download_accent'] ?? '' ) ) ?: '#914dad',
					'download_text' => sanitize_hex_color( wp_unslash( $_POST['product_updater_download_text'] ?? '' ) ) ?: '#ffffff',
					'download_hover_accent' => sanitize_hex_color( wp_unslash( $_POST['product_updater_download_hover_accent'] ?? '' ) ) ?: '#743d8a',
					'download_hover_text' => sanitize_hex_color( wp_unslash( $_POST['product_updater_download_hover_text'] ?? '' ) ) ?: '#ffffff',
					'download_radius' => min( 50, max( 0, absint( $_POST['product_updater_download_radius'] ?? 3 ) ) ),
					'download_icons' => ! empty( $_POST['product_updater_download_icons'] ),
					'download_bootstrap' => ! empty( $_POST['product_updater_download_bootstrap'] ),
					'ios_bg' => sanitize_hex_color( $_POST['product_updater_ios_bg'] ?? '' ) ?: '#000000',
					'ios_text' => sanitize_hex_color( $_POST['product_updater_ios_text'] ?? '' ) ?: '#ffffff',
					'ios_hover_bg' => sanitize_hex_color( $_POST['product_updater_ios_hover_bg'] ?? '' ) ?: '#333333',
					'ios_hover_text' => sanitize_hex_color( $_POST['product_updater_ios_hover_text'] ?? '' ) ?: '#ffffff',
					'android_bg' => sanitize_hex_color( $_POST['product_updater_android_bg'] ?? '' ) ?: '#01875f',
					'android_text' => sanitize_hex_color( $_POST['product_updater_android_text'] ?? '' ) ?: '#ffffff',
					'android_hover_bg' => sanitize_hex_color( $_POST['product_updater_android_hover_bg'] ?? '' ) ?: '#016b4b',
					'android_hover_text' => sanitize_hex_color( $_POST['product_updater_android_hover_text'] ?? '' ) ?: '#ffffff',
					'fab_bg' => sanitize_hex_color( $_POST['product_updater_fab_bg'] ?? '' ) ?: '#252525',
					'fab_text' => sanitize_hex_color( $_POST['product_updater_fab_text'] ?? '' ) ?: '#ffffff',
					'fab_hover_bg' => sanitize_hex_color( $_POST['product_updater_fab_hover_bg'] ?? '' ) ?: '#3b3b3b',
					'fab_hover_text' => sanitize_hex_color( $_POST['product_updater_fab_hover_text'] ?? '' ) ?: '#ffffff',
				)
			);
			add_action(
				'admin_notices',
				function () {
					echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved.', 'genesis-product-updater' ) . '</p></div>';
				}
			);
		}

		if ( isset( $_GET['product_updater_action'] ) && 'generate_all' === $_GET['product_updater_action'] && check_admin_referer( 'product_updater_generate_all' ) ) {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			$results = Product_Updater_File_Writer::instance()->generate_all();
			$errors  = 0;
			foreach ( $results as $product_results ) {
				foreach ( $product_results as $result ) {
					if ( is_wp_error( $result ) ) {
						++$errors;
					}
				}
			}
			set_transient( 'product_updater_generate_all_notice', array( 'count' => count( $results ), 'errors' => $errors ), 60 );
			wp_safe_redirect( remove_query_arg( array( 'product_updater_action', '_wpnonce' ) ) );
			exit;
		}
	}

	public function notices() {
		$notice = get_transient( 'product_updater_generate_all_notice' );
		if ( ! $notice ) {
			return;
		}
		delete_transient( 'product_updater_generate_all_notice' );

		if ( $notice['errors'] > 0 ) {
			printf(
				'<div class="notice notice-warning is-dismissible"><p>%s</p></div>',
				esc_html( sprintf( __( 'Regenerated files for %1$d product(s), with %2$d error(s). Check folder permissions.', 'genesis-product-updater' ), $notice['count'], $notice['errors'] ) )
			);
		} else {
			printf(
				'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
				esc_html( sprintf( __( 'Regenerated files for %d product(s).', 'genesis-product-updater' ), $notice['count'] ) )
			);
		}
	}
}
