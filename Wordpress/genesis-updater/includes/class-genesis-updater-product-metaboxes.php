<?php
/**
 * @package   Genesis Updater
 * @author    Dazzle Software https://dazzlesoftware.org
 * @copyright Copyright (C) 2026 Dazzle Software, LLC
 * @license   GNU/GPLv3 and later
 */
/**
 * Product edit screen: general info fields + the repeatable "version rows"
 * table (one row per version for the product's selected platform).
 */
class Genesis_Updater_Product_Metaboxes {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_boxes' ) );
		add_action( 'save_post_' . Genesis_Updater_Post_Type::POST_TYPE, array( $this, 'save' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
	}

	public function assets( $hook ) {
		global $post_type;
		if ( Genesis_Updater_Post_Type::POST_TYPE !== $post_type ) {
			return;
		}
		wp_enqueue_style( 'genesis-updater-admin', GENESIS_UPDATER_PLUGIN_URL . 'assets/css/genesis-updater-admin.css', array(), GENESIS_UPDATER_VERSION );
		wp_enqueue_script( 'genesis-updater-admin', GENESIS_UPDATER_PLUGIN_URL . 'assets/js/genesis-updater-admin.js', array( 'jquery', 'jquery-ui-sortable' ), GENESIS_UPDATER_VERSION, true );
		wp_enqueue_media();
		wp_localize_script(
			'genesis-updater-admin',
			'genesisUpdaterPlatformMeta',
			Genesis_Updater_Generator_Manager::instance()->get_platform_meta()
		);
	}

	public function add_boxes() {
		add_meta_box( 'genesis_updater_product_info', __( 'Product Info', 'genesis-updater' ), array( $this, 'render_info' ), Genesis_Updater_Post_Type::POST_TYPE, 'normal', 'high' );
		add_meta_box( 'genesis_updater_versions', __( 'Platform Versions', 'genesis-updater' ), array( $this, 'render_versions' ), Genesis_Updater_Post_Type::POST_TYPE, 'normal', 'high' );
		add_meta_box( 'genesis_updater_generate', __( 'Generate Files', 'genesis-updater' ), array( $this, 'render_generate' ), Genesis_Updater_Post_Type::POST_TYPE, 'side', 'high' );
	}

	/* ---------------------------------------------------------------- */
	/* Field definitions                                                  */
	/* ---------------------------------------------------------------- */

	public static function get_product_fields() {
		return array(
			'element'        => __( 'Element / Slug', 'genesis-updater' ),
			'platform'       => __( 'Platform', 'genesis-updater' ),
			'type'           => __( 'Product Type', 'genesis-updater' ),
			'preview_image'  => __( 'Preview Image', 'genesis-updater' ),
			'description'    => __( 'Description', 'genesis-updater' ),
			'maintainer'     => __( 'Maintainer', 'genesis-updater' ),
			'maintainer_url' => __( 'Maintainer URL', 'genesis-updater' ),
			'download_count' => __( 'Download Count', 'genesis-updater' ),
		);
	}

	public static function get_row_fields() {
		return array(
			'version'       => __( 'Version', 'genesis-updater' ),
			'target_version' => __( 'Target Platform Version', 'genesis-updater' ),
			'tag'           => __( 'Tag', 'genesis-updater' ),
			'is_current'    => __( 'Current', 'genesis-updater' ),
			'download_url'  => __( 'Download URL', 'genesis-updater' ),
			'sha512'        => __( 'SHA512', 'genesis-updater' ),
			'info_url'      => __( 'Info URL', 'genesis-updater' ),
			'info_title'    => __( 'Info Title', 'genesis-updater' ),
			'changelog_mode' => __( 'Changelog', 'genesis-updater' ),
			'changelog_url' => __( 'Changelog URL', 'genesis-updater' ),
			'release_date'  => __( 'Release Date', 'genesis-updater' ),
			'requires'      => __( 'Requires Platform Version', 'genesis-updater' ),
			'tested'        => __( 'Tested up to Platform Version', 'genesis-updater' ),
			'requires_php'  => __( 'Requires PHP', 'genesis-updater' ),
			'build_number'  => __( 'Build Number', 'genesis-updater' ),
			'url_ios'       => __( 'URL (iOS)', 'genesis-updater' ),
			'url_android'   => __( 'URL (Android)', 'genesis-updater' ),
			'url_fab'       => __( 'Fab URL', 'genesis-updater' ),
			'force_update'  => __( 'Force Update', 'genesis-updater' ),
			'release_notes' => __( 'Release Notes', 'genesis-updater' ),
		);
	}

	public static function get_tag_choices() {
		return array( 'stable', 'beta', 'rc', 'dev' );
	}

	public static function get_type_choices() {
		return array(
			'template'        => __( 'Template', 'genesis-updater' ),
			'component'       => __( 'Component', 'genesis-updater' ),
			'module'          => __( 'Module', 'genesis-updater' ),
			'plugin'          => __( 'Plugin', 'genesis-updater' ),
			'library'         => __( 'Library', 'genesis-updater' ),
			'package'         => __( 'Package', 'genesis-updater' ),
			'file'            => __( 'File', 'genesis-updater' ),
			'language'        => __( 'Language', 'genesis-updater' ),
			'wordpress-plugin' => __( 'Plugin', 'genesis-updater' ),
			'wordpress-theme'  => __( 'Theme', 'genesis-updater' ),
			'mobile-app'       => __( 'Mobile App', 'genesis-updater' ),
			'fab-2d-assets'      => __( '2D Assets', 'genesis-updater' ),
			'fab-3d-models'      => __( '3D Models', 'genesis-updater' ),
			'fab-animations'     => __( 'Animations', 'genesis-updater' ),
			'fab-audio'          => __( 'Audio', 'genesis-updater' ),
			'fab-environments'   => __( 'Environments', 'genesis-updater' ),
			'fab-game-templates' => __( 'Game Templates', 'genesis-updater' ),
			'fab-tools-plugins'  => __( 'Tools & Plugins', 'genesis-updater' ),
			'fab-ui'             => __( 'UI', 'genesis-updater' ),
			'custom'           => __( 'Custom', 'genesis-updater' ),
		);
	}

	public static function get_type_platforms() {
		return array(
			'template' => 'joomla', 'component' => 'joomla', 'module' => 'joomla', 'plugin' => 'joomla',
			'library' => 'joomla', 'package' => 'joomla', 'file' => 'joomla', 'language' => 'joomla',
			'wordpress-plugin' => 'wordpress', 'wordpress-theme' => 'wordpress',
			'mobile-app' => 'mobile', 'custom' => 'all',
			'fab-2d-assets' => 'fab', 'fab-3d-models' => 'fab', 'fab-animations' => 'fab', 'fab-audio' => 'fab',
			'fab-environments' => 'fab', 'fab-game-templates' => 'fab', 'fab-tools-plugins' => 'fab', 'fab-ui' => 'fab',
		);
	}

	/* ---------------------------------------------------------------- */
	/* Data readers (used by CPT columns and the generator/file writer)  */
	/* ---------------------------------------------------------------- */

	public static function get_product_data( $post_id ) {
		$post = get_post( $post_id );
		$data = array(
			'post_id' => $post_id,
			'slug'    => $post->post_name ? $post->post_name : sanitize_title( $post->post_title ),
			'name'    => $post->post_title,
		);
		foreach ( array_keys( self::get_product_fields() ) as $key ) {
			$data[ $key ] = get_post_meta( $post_id, '_genesis_updater_' . $key, true );
		}
		$download_categories = wp_get_post_terms( $post_id, 'genesis_up_download_category', array( 'fields' => 'slugs' ) );
		$data['download_category'] = ! is_wp_error( $download_categories ) && ! empty( $download_categories )
			? (string) reset( $download_categories )
			: '';
		return $data;
	}

	public static function get_rows( $post_id ) {
		$rows = get_post_meta( $post_id, '_genesis_updater_rows', true );
		return is_array( $rows ) ? $rows : array();
	}

	/* ---------------------------------------------------------------- */
	/* Rendering                                                          */
	/* ---------------------------------------------------------------- */

	public function render_info( $post ) {
		wp_nonce_field( 'genesis_updater_save', 'genesis_updater_nonce' );
		$fields = self::get_product_fields();
		echo '<table class="form-table genesis-updater-form-table">';
		foreach ( $fields as $key => $label ) {
			$value = get_post_meta( $post->ID, '_genesis_updater_' . $key, true );
			echo '<tr><th><label for="genesis_updater_' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label></th><td>';
			if ( 'description' === $key ) {
				echo '<textarea class="large-text" rows="3" id="genesis_updater_' . esc_attr( $key ) . '" name="genesis_updater[' . esc_attr( $key ) . ']">' . esc_textarea( $value ) . '</textarea>';
			} elseif ( 'platform' === $key ) {
				$platforms = Genesis_Updater_Generator_Manager::instance()->get_platform_choices();
				echo '<select class="regular-text" id="genesis_updater_platform" name="genesis_updater[platform]">';
				foreach ( $platforms as $slug => $platform_label ) {
					echo '<option value="' . esc_attr( $slug ) . '" ' . selected( $value, $slug, false ) . '>' . esc_html( $platform_label ) . '</option>';
				}
				echo '</select>';
			} elseif ( 'type' === $key ) {
				$choices = self::get_type_choices();
				$type_platforms = self::get_type_platforms();
				echo '<select class="regular-text" id="genesis_updater_type" name="genesis_updater[type]">';
				echo '<option value="">' . esc_html__( 'Select a product type', 'genesis-updater' ) . '</option>';
				foreach ( $choices as $choice_value => $choice_label ) {
					echo '<option data-platform="' . esc_attr( $type_platforms[ $choice_value ] ) . '" value="' . esc_attr( $choice_value ) . '" ' . selected( $value, $choice_value, false ) . '>' . esc_html( $choice_label ) . '</option>';
				}
				echo '</select>';
			} elseif ( 'download_count' === $key ) {
				echo '<input type="number" min="0" step="1" class="small-text" id="genesis_updater_download_count" name="genesis_updater[download_count]" value="' . esc_attr( (int) $value ) . '" />';
				 echo '<p class="description">' . esc_html__( 'Set an imported starting total; tracked downloads continue from this number.', 'genesis-updater' ) . '</p>';
			} elseif ( 'preview_image' === $key ) {
				echo '<input type="url" class="regular-text" id="genesis_updater_preview_image" name="genesis_updater[preview_image]" value="' . esc_attr( $value ) . '" placeholder="https://" /> ';
				echo '<button type="button" class="button genesis-updater-select-image">' . esc_html__( 'Select Image', 'genesis-updater' ) . '</button>';
			} else {
				echo '<input type="text" class="regular-text" id="genesis_updater_' . esc_attr( $key ) . '" name="genesis_updater[' . esc_attr( $key ) . ']" value="' . esc_attr( $value ) . '" />';
			}
			echo '</td></tr>';
		}
		echo '</table>';
	}

	public function render_versions( $post ) {
		$rows      = self::get_rows( $post->ID );
		$tags      = self::get_tag_choices();

		if ( empty( $rows ) ) {
			$rows = array( array() ); // start with one empty row
		}
		?>
		<table class="widefat genesis-updater-rows-table">
			<thead>
				<tr>
					<?php foreach ( self::get_row_fields() as $field => $label ) : ?>
						<th data-field="<?php echo esc_attr( $field ); ?>"><?php echo esc_html( $label ); ?></th>
					<?php endforeach; ?>
					<th></th>
				</tr>
			</thead>
			<tbody id="genesis-updater-rows-body">
				<?php foreach ( $rows as $i => $row ) : ?>
					<?php $this->render_row( $i, $row, $tags ); ?>
				<?php endforeach; ?>
			</tbody>
		</table>
		<p><button type="button" class="button" id="genesis-updater-add-row"><?php esc_html_e( '+ Add Version Row', 'genesis-updater' ); ?></button></p>

		<script type="text/template" id="genesis-updater-row-template">
			<?php $this->render_row( '__INDEX__', array(), $tags ); ?>
		</script>
		<?php
	}

	private function render_row( $i, $row, $tags ) {
		$row = wp_parse_args(
			$row,
			array(
				'version'        => '',
				'target_version' => '',
				'tag'            => 'stable',
				'is_current'     => '',
				'download_url'   => '',
				'sha512'         => '',
				'info_url'       => '',
				'info_title'     => '',
				'changelog_mode' => 'generated',
				'changelog_url'  => '',
				'release_date'   => '',
				'requires'       => '',
				'tested'         => '',
				'requires_php'   => '',
				'build_number'   => '',
				'url_ios'        => '',
				'url_android'    => '',
				'url_fab'        => '',
				'force_update'   => '',
				'release_notes'  => '',
			)
		);
		?>
		<tr class="genesis-updater-row">
			<td><input type="text" size="8" name="genesis_updater_rows[<?php echo esc_attr( $i ); ?>][version]" value="<?php echo esc_attr( $row['version'] ); ?>" placeholder="5.6.2" /></td>
			<td><input type="text" size="6" name="genesis_updater_rows[<?php echo esc_attr( $i ); ?>][target_version]" value="<?php echo esc_attr( $row['target_version'] ); ?>" placeholder="5.*" /></td>
			<td>
				<select name="genesis_updater_rows[<?php echo esc_attr( $i ); ?>][tag]">
					<?php foreach ( $tags as $tag ) : ?>
						<option value="<?php echo esc_attr( $tag ); ?>" <?php selected( $row['tag'], $tag ); ?>><?php echo esc_html( $tag ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
			<td style="text-align:center;"><input type="checkbox" name="genesis_updater_rows[<?php echo esc_attr( $i ); ?>][is_current]" value="1" <?php checked( ! empty( $row['is_current'] ) ); ?> /></td>
			<td><input type="text" size="20" name="genesis_updater_rows[<?php echo esc_attr( $i ); ?>][download_url]" value="<?php echo esc_attr( $row['download_url'] ); ?>" /></td>
			<td><input type="text" size="14" name="genesis_updater_rows[<?php echo esc_attr( $i ); ?>][sha512]" value="<?php echo esc_attr( $row['sha512'] ); ?>" /></td>
			<td><input type="text" size="14" name="genesis_updater_rows[<?php echo esc_attr( $i ); ?>][info_url]" value="<?php echo esc_attr( $row['info_url'] ); ?>" /></td>
			<td><input type="text" size="10" name="genesis_updater_rows[<?php echo esc_attr( $i ); ?>][info_title]" value="<?php echo esc_attr( $row['info_title'] ); ?>" /></td>
			<td>
				<select class="genesis-updater-changelog-mode" name="genesis_updater_rows[<?php echo esc_attr( $i ); ?>][changelog_mode]">
					<option value="generated" <?php selected( $row['changelog_mode'], 'generated' ); ?>><?php esc_html_e( 'Generated changelog file', 'genesis-updater' ); ?></option>
					<option value="custom" <?php selected( $row['changelog_mode'], 'custom' ); ?>><?php esc_html_e( 'Custom URL', 'genesis-updater' ); ?></option>
					<option value="none" <?php selected( $row['changelog_mode'], 'none' ); ?>><?php esc_html_e( 'None', 'genesis-updater' ); ?></option>
				</select>
			</td>
			<td><input class="genesis-updater-changelog-url" type="text" size="14" name="genesis_updater_rows[<?php echo esc_attr( $i ); ?>][changelog_url]" value="<?php echo esc_attr( $row['changelog_url'] ); ?>" placeholder="https://" /></td>
			<td><input type="text" size="10" name="genesis_updater_rows[<?php echo esc_attr( $i ); ?>][release_date]" value="<?php echo esc_attr( $row['release_date'] ); ?>" placeholder="YYYY-MM-DD" /></td>
			<td class="genesis-updater-compat-cell" data-field="requires">
				<div class="genesis-updater-compat-label"></div>
				<input type="text" size="6" name="genesis_updater_rows[<?php echo esc_attr( $i ); ?>][requires]" value="<?php echo esc_attr( $row['requires'] ); ?>" placeholder="6.0" />
				<span class="genesis-updater-compat-na">&#8212;</span>
			</td>
			<td class="genesis-updater-compat-cell" data-field="tested">
				<div class="genesis-updater-compat-label"></div>
				<input type="text" size="6" name="genesis_updater_rows[<?php echo esc_attr( $i ); ?>][tested]" value="<?php echo esc_attr( $row['tested'] ); ?>" placeholder="6.6" />
				<span class="genesis-updater-compat-na">&#8212;</span>
			</td>
			<td class="genesis-updater-compat-cell" data-field="requires_php">
				<div class="genesis-updater-compat-label"></div>
				<input type="text" size="6" name="genesis_updater_rows[<?php echo esc_attr( $i ); ?>][requires_php]" value="<?php echo esc_attr( $row['requires_php'] ); ?>" placeholder="7.4" />
				<span class="genesis-updater-compat-na">&#8212;</span>
			</td>
			<td class="genesis-updater-mobile-cell" data-field="build_number">
				<input type="text" size="6" name="genesis_updater_rows[<?php echo esc_attr( $i ); ?>][build_number]" value="<?php echo esc_attr( $row['build_number'] ); ?>" placeholder="42" />
				<span class="genesis-updater-mobile-na">&#8212;</span>
			</td>
			<td class="genesis-updater-mobile-cell" data-field="url_ios">
				<input type="text" size="14" name="genesis_updater_rows[<?php echo esc_attr( $i ); ?>][url_ios]" value="<?php echo esc_attr( $row['url_ios'] ); ?>" />
				<span class="genesis-updater-mobile-na">&#8212;</span>
			</td>
			<td class="genesis-updater-mobile-cell" data-field="url_android">
				<input type="text" size="14" name="genesis_updater_rows[<?php echo esc_attr( $i ); ?>][url_android]" value="<?php echo esc_attr( $row['url_android'] ); ?>" />
				<span class="genesis-updater-mobile-na">&#8212;</span>
			</td>
			<td data-field="url_fab"><input type="url" size="20" name="genesis_updater_rows[<?php echo esc_attr( $i ); ?>][url_fab]" value="<?php echo esc_attr( $row['url_fab'] ); ?>" placeholder="https://www.fab.com/" /></td>
			<td class="genesis-updater-mobile-cell" style="text-align:center;" data-field="force_update">
				<input type="checkbox" name="genesis_updater_rows[<?php echo esc_attr( $i ); ?>][force_update]" value="1" <?php checked( ! empty( $row['force_update'] ) ); ?> />
				<span class="genesis-updater-mobile-na">&#8212;</span>
			</td>
			<td class="genesis-updater-mobile-cell" data-field="release_notes">
				<input type="text" size="16" name="genesis_updater_rows[<?php echo esc_attr( $i ); ?>][release_notes]" value="<?php echo esc_attr( $row['release_notes'] ); ?>" />
				<span class="genesis-updater-mobile-na">&#8212;</span>
			</td>
			<td><button type="button" class="button-link genesis-updater-remove-row" title="<?php esc_attr_e( 'Remove row', 'genesis-updater' ); ?>">&times;</button></td>
		</tr>
		<?php
	}

	public function render_generate( $post ) {
		$writer  = Genesis_Updater_File_Writer::instance();
		$product = self::get_product_data( $post->ID );
		$rows    = self::get_rows( $post->ID );

		$byplat = array();
		foreach ( $rows as $row ) {
			if ( ! empty( $row['platform'] ) ) {
				$byplat[ $row['platform'] ][] = $row;
			}
		}

		echo '<p>' . esc_html__( 'Output folder:', 'genesis-updater' ) . '<br /><code>' . esc_html( $writer->get_base_dir() ) . '</code></p>';

		if ( empty( $byplat ) ) {
			echo '<p>' . esc_html__( 'Add at least one version row and save/update this product to enable generation.', 'genesis-updater' ) . '</p>';
			return;
		}

		echo '<ul>';
		foreach ( $byplat as $slug => $platform_rows ) {
			$generator = Genesis_Updater_Generator_Manager::instance()->get_platform( $slug );
			if ( ! $generator ) {
				continue;
			}
			$info = $writer->get_file_info( $generator, $product );
			echo '<li>' . esc_html( $generator->get_label() ) . ': ';
			if ( $info['exists'] ) {
				echo '<a href="' . esc_url( $info['url'] ) . '" target="_blank">' . esc_html( $generator->get_subpath( $product ) ) . '</a>';
			} else {
				echo esc_html__( 'not generated yet', 'genesis-updater' );
			}
			echo '</li>';
		}
		echo '</ul>';

		echo '<p>' . get_submit_button( __( 'Save & Generate Files', 'genesis-updater' ), 'primary', 'genesis_updater_save_and_generate', false ) . '</p>';
		echo '<p class="description">' . esc_html__( 'Files also regenerate automatically whenever you update this product.', 'genesis-updater' ) . '</p>';
	}

	/* ---------------------------------------------------------------- */
	/* Saving                                                             */
	/* ---------------------------------------------------------------- */

	public function save( $post_id ) {
		if ( ! isset( $_POST['genesis_updater_nonce'] ) || ! wp_verify_nonce( $_POST['genesis_updater_nonce'], 'genesis_updater_save' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset( $_POST['genesis_updater'] ) && is_array( $_POST['genesis_updater'] ) ) {
			foreach ( array_keys( self::get_product_fields() ) as $key ) {
				$value = isset( $_POST['genesis_updater'][ $key ] ) ? wp_unslash( $_POST['genesis_updater'][ $key ] ) : '';
				if ( 'description' === $key ) {
					$value = sanitize_textarea_field( $value );
				} elseif ( 'download_count' === $key ) {
					$value = absint( $value );
				} elseif ( 'preview_image' === $key ) {
					$value = esc_url_raw( $value );
				} else {
					$value = sanitize_text_field( $value );
				}
				update_post_meta( $post_id, '_genesis_updater_' . $key, $value );
			}
		}

		$rows        = array();
		$row_fields  = array_keys( self::get_row_fields() );
		$posted_rows = isset( $_POST['genesis_updater_rows'] ) && is_array( $_POST['genesis_updater_rows'] ) ? wp_unslash( $_POST['genesis_updater_rows'] ) : array();
		$platform    = sanitize_key( $_POST['genesis_updater']['platform'] ?? '' );

		foreach ( $posted_rows as $posted_row ) {
			if ( empty( $posted_row['version'] ) ) {
				continue; // skip fully empty rows
			}
			$row = array();
			foreach ( $row_fields as $field ) {
				if ( in_array( $field, array( 'is_current', 'force_update' ), true ) ) {
					$row[ $field ] = ! empty( $posted_row[ $field ] );
					continue;
				}
				$value = isset( $posted_row[ $field ] ) ? $posted_row[ $field ] : '';
				if ( 'changelog_mode' === $field ) {
					$row[ $field ] = in_array( $value, array( 'generated', 'custom', 'none' ), true ) ? $value : 'generated';
				} elseif ( in_array( $field, array( 'download_url', 'info_url', 'changelog_url', 'url_ios', 'url_android', 'url_fab' ), true ) ) {
					$row[ $field ] = esc_url_raw( $value );
				} else {
					$row[ $field ] = sanitize_text_field( $value );
				}
			}
			$row['platform'] = $platform;
			$rows[] = $row;
		}

		update_post_meta( $post_id, '_genesis_updater_rows', $rows );
		Genesis_Updater_Downloads::refresh_file_size( $post_id, $rows );

		// Regenerate this product's feed files every time it's saved.
		remove_action( 'save_post_' . Genesis_Updater_Post_Type::POST_TYPE, array( $this, 'save' ) );
		Genesis_Updater_File_Writer::instance()->generate_product( $post_id );
		add_action( 'save_post_' . Genesis_Updater_Post_Type::POST_TYPE, array( $this, 'save' ) );
	}
}
