<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.seanrsullivan.com
 * @since      1.0.0
 *
 * @package    Yotpo_Reviews
 * @subpackage Yotpo_Reviews/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Yotpo_Reviews
 * @subpackage Yotpo_Reviews/includes
 * @author     Sean Sullivan
 */
class Yotpo_Reviews {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Yotpo_Reviews_Loader    $loader    Maintains and registers all hooks for the plugin.
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
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'YOTPO_REVIEWS_VERSION' ) ) {
			$this->version = YOTPO_REVIEWS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'yotpo-reviews';

        $this->options = get_option( 'yotpo_reviews_settings' );

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Yotpo_Reviews_Loader. Orchestrates the hooks of the plugin.
	 * - Yotpo_Reviews_i18n. Defines internationalization functionality.
	 * - Yotpo_Reviews_Admin. Defines all hooks for the admin area.
	 * - Yotpo_Reviews_Import. Defines all hooks for the import process.
	 * - Yotpo_Reviews_Webhook_Functions. Defines all hooks for the webhook process.
	 * - Yotpo_Reviews_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-yotpo-reviews-loader.php'; // Actions/Filters loader
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-yotpo-reviews-i18n.php'; // Internationalization
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-yotpo-reviews-admin.php'; // Admin area functions
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-yotpo-reviews-import.php'; // Import functions
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-yotpo-reviews-webhook-functions.php'; // Webhook callback functions
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-yotpo-reviews-public.php'; // Public area functions

		$this->loader = new Yotpo_Reviews_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Yotpo_Reviews_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Yotpo_Reviews_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}


	/**
	 * Register all of the hooks related to the admin area functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Yotpo_Reviews_Admin( $this->get_plugin_name(), $this->get_version() );

		// $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		// $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' ); // Add submenu item to Settings
        $this->loader->add_action( 'admin_init', $plugin_admin, 'register_plugin_settings' ); // Add submenu item to Settings
        $this->loader->add_action( 'add_meta_boxes_comment', $plugin_admin, 'add_comment_title_meta_box' ); // Add the title to our admin area, for editing, etc
        $this->loader->add_action( 'edit_comment', $plugin_admin, 'add_comment_title_admin_save' ); // Save our comment (from the admin area)
        $this->loader->add_action( 'load-edit-comments.php', $plugin_admin, 'comment_title_load' ); // Put title in comments list table
        $this->loader->add_action( 'manage_comments_custom_column', $plugin_admin, 'comment_title_column_cb', 10, 2 );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Yotpo_Reviews_Public( $this->get_plugin_name(), $this->get_version() );

		// $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        $this->loader->add_action( 'init', $plugin_public, 'output_buffer' ); // Allow redirection
        $this->loader->add_filter( 'woocommerce_template_loader_files', $plugin_public, 'get_template_name', 10, 2 );
        $this->loader->add_filter( 'template_include', $plugin_public, 'clear_template_cache', 11 );
        $this->loader->add_filter( 'wc_get_template_part', $plugin_public, 'override_template_parts', 10, 3 );
        $this->loader->add_filter( 'woocommerce_locate_template', $plugin_public, 'display_custom_template', 10, 2 );
        $this->loader->add_filter( 'comments_template', $plugin_public, 'override_reviews_template', 100, 1 );

	}

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
	 * @return    Yotpo_Reviews_Loader    Orchestrates the hooks of the plugin.
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
