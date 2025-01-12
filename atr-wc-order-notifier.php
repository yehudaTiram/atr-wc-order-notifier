<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://atarimtr.co.il
 * @since             1.0.0
 * @package           Atr_Wc_Order_Notifier
 *
 * @wordpress-plugin
 * Plugin Name:       ATR Shop Order Notifier for Woocommerce
 * Plugin URI:        https://atarimtr.co.il
 * Description:       Sends notifications for WooCommerce order events to Telegram, Slack, (and other platforms).
 * Version:           1.0.0
 * Author:            Yehuda Tiram
 * Author URI:        https://atarimtr.co.il/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       atr-shop-order-notifier
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'ATR_WC_ORDER_NOTIFIER_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-atr-wc-order-notifier-activator.php
 */
function activate_atr_wc_order_notifier() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-atr-wc-order-notifier-activator.php';
	Atr_Wc_Order_Notifier_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-atr-wc-order-notifier-deactivator.php
 */
function deactivate_atr_wc_order_notifier() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-atr-wc-order-notifier-deactivator.php';
	Atr_Wc_Order_Notifier_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_atr_wc_order_notifier' );
register_deactivation_hook( __FILE__, 'deactivate_atr_wc_order_notifier' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-atr-wc-order-notifier.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_atr_wc_order_notifier() {

	$plugin = new Atr_Wc_Order_Notifier();
	$plugin->run();

}
run_atr_wc_order_notifier();
