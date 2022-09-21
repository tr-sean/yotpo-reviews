<?php

/**
 * The plugin bootstrap files
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.seanrsullivan.com
 * @since             1.0.0
 * @package           Yotpo_Reviews
 *
 * @wordpress-plugin
 * Plugin Name:       Yotpo Reviews
 * Plugin URI:        https://www.seanrsullivan.com
 * Description:       Allows the use of the WooCommerce native reviews using Yotpo.
 * Version:           1.6.1
 * Author:            Sean Sullivan
 * Author URI:        https://www.seanrsullivan.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       yotpo-reviews
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) die;

define('YPR_FOLDER', dirname(plugin_basename(__FILE__)));
define('YPR_URL', get_bloginfo("url") . '/wp-content/plugins/' . YPR_FOLDER);
define('YPR_FILE_PATH', dirname(__FILE__));
define('YPR_DIR_NAME', basename(YPR_FILE_PATH));

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'YOTPO_REVIEWS_VERSION', '1.6.1' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-yotpo-reviews-activator.php
 */
function activate_yotpo_reviews() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-yotpo-reviews-activator.php';
	Yotpo_Reviews_Activator::create_logs_table(); // Creates log table
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-yotpo-reviews-deactivator.php
 */
function deactivate_yotpo_reviews() {
	// require_once plugin_dir_path( __FILE__ ) . 'includes/class-yotpo-reviews-deactivator.php';
	// Yotpo_Reviews_Deactivator::deactivate(); // Delete the webhook.
}

register_activation_hook( __FILE__, 'activate_yotpo_reviews' );
register_deactivation_hook( __FILE__, 'deactivate_yotpo_reviews' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-yotpo-reviews.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_yotpo_reviews() {

	$plugin = new Yotpo_Reviews();
	$plugin->run();

}
run_yotpo_reviews();
