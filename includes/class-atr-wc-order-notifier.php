<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://atarimtr.co.il
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Atr_Wc_Order_Notifier
 * @subpackage Atr_Wc_Order_Notifier/includes
 * @author     Yehuda Tiram <yehuda@atarimtr.co.il>
 */
class Atr_Wc_Order_Notifier {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Atr_Wc_Order_Notifier_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * The plugin slug
	 *
	 * @var string
	 */
	private $plugin_slug = 'atr-wc-order-notifier';

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'ATR_WC_ORDER_NOTIFIER_VERSION' ) ) {
			$this->version = ATR_WC_ORDER_NOTIFIER_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'atr-wc-order-notifier';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		//$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Atr_Wc_Order_Notifier_Loader. Orchestrates the hooks of the plugin.
	 * - Atr_Wc_Order_Notifier_i18n. Defines internationalization functionality.
	 * - Atr_Wc_Order_Notifier_Admin. Defines all hooks for the admin area.
	 * - Atr_Wc_Order_Notifier_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-atr-wc-order-notifier-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-atr-wc-order-notifier-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-atr-wc-order-notifier-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-atr-wc-order-notifier-public.php';

		/**
		 * The utility class
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-atr-wc-order-notifier-utils.php';

		/**
		 * Text message class
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/notifications/class-atr-wc-order-notifier-message.php';

		/**
		 * Telegram notification class
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/notifications/class-atr-wc-order-notifier-telegram.php';

		$this->loader = new Atr_Wc_Order_Notifier_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Atr_Wc_Order_Notifier_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Atr_Wc_Order_Notifier_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Atr_Wc_Order_Notifier_Admin( $this->get_plugin_name(), $this->get_version() );

		// $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		// $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$notification_telegram = new Atr_Wc_Order_Notifier_Admin_Telegram($this->get_plugin_name(), $this->get_version());
		// Hook into WooCommerce order status changes
		$this->loader->add_action('woocommerce_order_status_changed', $notification_telegram, 'atr_wc_notifier_order_status_changed', 10, 3);

		$plugin_settings = new Atr_Wc_Order_Notifier_Admin_Settings($this->get_plugin_name(), $this->plugin_slug, $this->get_version());
		$this->loader->add_action('admin_menu', $plugin_settings, 'add_menu_item');
		$plugin_basename = $this->plugin_name . '/' . 'atr-wc-order-notifier.php';
		$this->loader->add_filter('plugin_action_links_' . $plugin_basename, $plugin_settings, 'add_action_links');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	// private function define_public_hooks() {

	// 	$plugin_public = new Atr_Wc_Order_Notifier_Public( $this->get_plugin_name(), $this->get_version() );

	// 	$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
	// 	$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	// }

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Atr_Wc_Order_Notifier_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
