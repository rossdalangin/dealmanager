<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://wordpresstitans.com
 * @since      1.0.0
 *
 * @package    Deals_Manager
 * @subpackage Deals_Manager/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for enqueueing the
 * public-facing stylesheet and JavaScript, and for loading custom templates.
 *
 * @package    Deals_Manager
 * @subpackage Deals_Manager/public
 * @author     Ross Dalangin
 */
class Deals_Manager_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string $plugin_name The name of the plugin.
	 * @param    string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/deals-manager-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/deals-manager-public.js', array( 'jquery' ), $this->version, false );
	}

	/**
	 * Load a custom template for the invoice CPT.
	 *
	 * Checks if the current page is a single 'invoice' post and if the theme
	 * does not have its own 'single-invoice.php' template. If not, it loads
	 * the template provided by the plugin.
	 *
	 * @since    1.0.0
	 * @param    string $template The path of the template to include.
	 * @return   string The path of the template to use.
	 */
	public function load_invoice_template( $template ) {
		global $post;

		if ( is_singular( 'invoice' ) && locate_template( array( 'single-invoice.php' ) ) !== $template ) {
			$plugin_template = plugin_dir_path( dirname( __FILE__ ) ) . 'templates/public/single-invoice.php';
			if ( file_exists( $plugin_template ) ) {
				return $plugin_template;
			}
		}

		return $template;
	}

}
