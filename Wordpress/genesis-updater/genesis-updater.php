<?php
/**
 * @package   Genesis Updater
 * @author    Dazzle Software https://dazzlesoftware.org
 * @copyright Copyright (C) 2026 Dazzle Software, LLC
 * @license   GNU/GPLv3 and later
 */
/**
 * Plugin Name: Genesis Updater
 * Description: Manage self-hosted update feeds (Joomla XML, WordPress JSON, and any other platform you register) for multiple products/platforms from one place, and generate the feed files to a configurable folder.
 * Version: 1.8.1
 * Author: Dazzle Software
 * Author URI: https://dazzlesoftware.org
 * License: GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: genesis-updater
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GENESIS_UPDATER_VERSION', '1.8.1' );
define( 'GENESIS_UPDATER_PLUGIN_FILE', __FILE__ );
define( 'GENESIS_UPDATER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'GENESIS_UPDATER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once GENESIS_UPDATER_PLUGIN_DIR . 'includes/class-genesis-updater-platform-base.php';
require_once GENESIS_UPDATER_PLUGIN_DIR . 'includes/class-genesis-updater-generator-manager.php';
require_once GENESIS_UPDATER_PLUGIN_DIR . 'includes/class-genesis-updater-post-type.php';
require_once GENESIS_UPDATER_PLUGIN_DIR . 'includes/class-genesis-updater-product-metaboxes.php';
require_once GENESIS_UPDATER_PLUGIN_DIR . 'includes/class-genesis-updater-file-writer.php';
require_once GENESIS_UPDATER_PLUGIN_DIR . 'includes/class-genesis-updater-changelog.php';
require_once GENESIS_UPDATER_PLUGIN_DIR . 'includes/class-genesis-updater-changelog-post-type.php';
require_once GENESIS_UPDATER_PLUGIN_DIR . 'includes/class-genesis-updater-changelog-metaboxes.php';
require_once GENESIS_UPDATER_PLUGIN_DIR . 'includes/class-genesis-updater-bundle.php';
require_once GENESIS_UPDATER_PLUGIN_DIR . 'includes/class-genesis-updater-admin.php';
require_once GENESIS_UPDATER_PLUGIN_DIR . 'includes/class-genesis-updater-downloads.php';

// Bundled platform generators.
require_once GENESIS_UPDATER_PLUGIN_DIR . 'includes/platforms/class-genesis-updater-platform-joomla.php';
require_once GENESIS_UPDATER_PLUGIN_DIR . 'includes/platforms/class-genesis-updater-platform-wordpress.php';
require_once GENESIS_UPDATER_PLUGIN_DIR . 'includes/platforms/class-genesis-updater-platform-mobile.php';
require_once GENESIS_UPDATER_PLUGIN_DIR . 'includes/platforms/class-genesis-updater-platform-fab.php';

/**
 * Boot the plugin. Everything hangs off these singletons so adding a new
 * platform or a new admin screen never means touching this file again.
 */
function genesis_updater_boot() {
	Genesis_Updater_Generator_Manager::instance();
	Genesis_Updater_Post_Type::instance();
	Genesis_Updater_Product_Metaboxes::instance();
	Genesis_Updater_Changelog_Post_Type::instance();
Genesis_Updater_Changelog_Metaboxes::instance();
Genesis_Updater_Bundle::instance();
	Genesis_Updater_Admin::instance();
	Genesis_Updater_Downloads::instance();
}
add_action( 'plugins_loaded', 'genesis_updater_boot' );

register_activation_hook( __FILE__, array( 'Genesis_Updater_Post_Type', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Genesis_Updater_Post_Type', 'deactivate' ) );
