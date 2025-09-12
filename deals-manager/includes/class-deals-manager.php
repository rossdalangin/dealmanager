<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Deals_Manager
 * @subpackage Deals_Manager/includes
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
 * @package    Deals_Manager
 * @subpackage Deals_Manager/includes
 * @author     Jules <you@example.com>
 */
class Deals_Manager {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Deals_Manager_Loader    $loader    Maintains and registers all hooks for the plugin.
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

		$this->plugin_name = 'deals-manager';
		$this->version = '1.0.0';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_cpt_hooks();
		$this->define_taxonomy_hooks();
		$this->define_cron_hooks();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Deals_Manager_Loader. Orchestrates the hooks of the plugin.
	 * - Deals_Manager_i18n. Defines internationalization functionality.
	 * - Deals_Manager_Admin. Defines all hooks for the admin area.
	 * - Deals_Manager_Public. Defines all hooks for the public side of the site.
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-deals-manager-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-deals-manager-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-deals-manager-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-deals-manager-public.php';

		/**
		 * The class responsible for defining the Deal CPT.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/cpt/class-deals-manager-deal-cpt.php';

		/**
		 * The class responsible for defining the Contact CPT.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/cpt/class-deals-manager-contact-cpt.php';

		/**
		 * The class responsible for defining the Company CPT.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/cpt/class-deals-manager-company-cpt.php';

		/**
		 * The class responsible for defining the Task CPT.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/cpt/class-deals-manager-task-cpt.php';

		/**
		 * The class responsible for defining the Invoice CPT.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/cpt/class-deals-manager-invoice-cpt.php';

		/**
		 * The class responsible for defining the custom taxonomies.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-deals-manager-taxonomies.php';

		/**
		 * The class responsible for defining the Activity CPT.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/cpt/class-deals-manager-activity-cpt.php';

		/**
		 * The class responsible for handling cron jobs.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-deals-manager-cron.php';

		$this->loader = new Deals_Manager_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Deals_Manager_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Deals_Manager_i18n();

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

		$plugin_admin = new Deals_Manager_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'pre_get_posts', $plugin_admin, 'handle_cpt_list_filters' );
		$this->loader->add_action( 'pre_get_posts', $plugin_admin, 'extend_cpt_search' );
		$this->loader->add_action( 'restrict_manage_posts', $plugin_admin, 'add_cpt_filters' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_menu' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_pipeline_page' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_reports_page' );
		$this->loader->add_action( 'wp_ajax_update_deal_stage', $plugin_admin, 'handle_update_deal_stage' );
		$this->loader->add_action( 'manage_posts_extra_tablenav', $plugin_admin, 'add_export_button' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'handle_csv_export' );
		$this->loader->add_action( 'wp_dashboard_setup', $plugin_admin, 'add_dashboard_widget' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Deals_Manager_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_filter( 'single_template', $plugin_public, 'load_invoice_template' );

	}

	/**
	 * Register all of the hooks related to the custom post types.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_cpt_hooks() {

		$plugin_cpt_deal = new Deals_Manager_Deal_CPT( $this->get_loader() );
		$plugin_cpt_deal->run();

		$plugin_cpt_contact = new Deals_Manager_Contact_CPT( $this->get_loader() );
		$plugin_cpt_contact->run();

		$plugin_cpt_company = new Deals_Manager_Company_CPT( $this->get_loader() );
		$plugin_cpt_company->run();

		$plugin_cpt_task = new Deals_Manager_Task_CPT( $this->get_loader() );
		$plugin_cpt_task->run();

		$plugin_cpt_invoice = new Deals_Manager_Invoice_CPT( $this->get_loader() );
		$plugin_cpt_invoice->run();

		$plugin_cpt_activity = new Deals_Manager_Activity_CPT( $this->get_loader() );
		$plugin_cpt_activity->run();

	}

	/**
	 * Register all of the hooks related to the custom taxonomies.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_taxonomy_hooks() {

		$plugin_taxonomies = new Deals_Manager_Taxonomies( $this->get_loader() );
		$plugin_taxonomies->run();

	}

	/**
	 * Register all of the hooks related to cron jobs.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_cron_hooks() {
		$this->loader->add_action( 'dm_daily_reminder_check', 'Deals_Manager_Cron', 'daily_reminder_check' );
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
	 * @return    Deals_Manager_Loader    Orchestrates the hooks of the plugin.
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
