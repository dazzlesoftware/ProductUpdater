<?php
/**
 * @package   Genesis Product Updater
 * @author    Dazzle Software https://dazzlesoftware.org
 * @copyright Copyright (C) 2026 Dazzle Software, LLC
 * @license   GNU/GPLv3 and later
 */
/**
 * Plugin Name: Genesis Product Updater
 * Description: Manage self-hosted update feeds (Joomla XML, WordPress JSON, and any other platform you register) for multiple products/platforms from one place, and generate the feed files to a configurable folder.
 * Version: 1.5.2
 * Author: Dazzle Software
 * Author URI: https://dazzlesoftware.org
 * License: GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: genesis-product-updater
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PRODUCT_UPDATER_VERSION', '1.5.2' );
define( 'PRODUCT_UPDATER_PLUGIN_FILE', __FILE__ );
define( 'PRODUCT_UPDATER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PRODUCT_UPDATER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once PRODUCT_UPDATER_PLUGIN_DIR . 'includes/class-genesis-product-updater-platform-base.php';
require_once PRODUCT_UPDATER_PLUGIN_DIR . 'includes/class-genesis-product-updater-generator-manager.php';
require_once PRODUCT_UPDATER_PLUGIN_DIR . 'includes/class-genesis-product-updater-post-type.php';
require_once PRODUCT_UPDATER_PLUGIN_DIR . 'includes/class-genesis-product-updater-product-metaboxes.php';
require_once PRODUCT_UPDATER_PLUGIN_DIR . 'includes/class-genesis-product-updater-file-writer.php';
require_once PRODUCT_UPDATER_PLUGIN_DIR . 'includes/class-genesis-product-updater-changelog.php';
require_once PRODUCT_UPDATER_PLUGIN_DIR . 'includes/class-genesis-product-updater-changelog-post-type.php';
require_once PRODUCT_UPDATER_PLUGIN_DIR . 'includes/class-genesis-product-updater-changelog-metaboxes.php';
require_once PRODUCT_UPDATER_PLUGIN_DIR . 'includes/class-genesis-product-updater-admin.php';
require_once PRODUCT_UPDATER_PLUGIN_DIR . 'includes/class-genesis-product-updater-downloads.php';

// Bundled platform generators.
require_once PRODUCT_UPDATER_PLUGIN_DIR . 'includes/platforms/class-genesis-product-updater-platform-joomla.php';
require_once PRODUCT_UPDATER_PLUGIN_DIR . 'includes/platforms/class-genesis-product-updater-platform-wordpress.php';
require_once PRODUCT_UPDATER_PLUGIN_DIR . 'includes/platforms/class-genesis-product-updater-platform-mobile.php';
require_once PRODUCT_UPDATER_PLUGIN_DIR . 'includes/platforms/class-genesis-product-updater-platform-fab.php';

/**
 * Boot the plugin. Everything hangs off these singletons so adding a new
 * platform or a new admin screen never means touching this file again.
 */
function product_updater_boot() {
	Product_Updater_Generator_Manager::instance();
	Product_Updater_Post_Type::instance();
	Product_Updater_Product_Metaboxes::instance();
	Product_Updater_Changelog_Post_Type::instance();
	Product_Updater_Changelog_Metaboxes::instance();
	Product_Updater_Admin::instance();
	Product_Updater_Downloads::instance();
}
add_action( 'plugins_loaded', 'product_updater_boot' );

register_activation_hook( __FILE__, array( 'Product_Updater_Post_Type', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Product_Updater_Post_Type', 'deactivate' ) );
