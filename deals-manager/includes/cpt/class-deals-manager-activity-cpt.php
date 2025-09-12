<?php
/**
 * The file that defines the Activity custom post type.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Deals_Manager
 * @subpackage Deals_Manager/includes/cpt
 */

/**
 * The Activity Custom Post Type class.
 *
 * @since      1.0.0
 * @package    Deals_Manager
 * @subpackage Deals_Manager/includes/cpt
 * @author     Jules <you@example.com>
 */
class Deals_Manager_Activity_CPT {

	/**
	 * The name for the custom post type.
	 * @var string
	 */
	private $post_type = 'dm_activity';

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
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $loader ) {
		$this->loader = $loader;
	}

	/**
	 * Run all the hooks for this class.
	 */
	public function run() {
		$this->loader->add_action( 'init', $this, 'register_cpt' );
	}

	/**
	 * Register the custom post type.
	 */
	public function register_cpt() {
		$labels = array(
			'name'          => _x( 'Activities', 'Post type general name', 'deals-manager' ),
			'singular_name' => _x( 'Activity', 'Post type singular name', 'deals-manager' ),
		);

		$args = array(
			'labels'              => $labels,
			'public'              => false,
			'publicly_queryable'  => false,
			'show_ui'             => false,
			'show_in_menu'        => false,
			'query_var'           => false,
			'rewrite'             => false,
			'capability_type'     => 'dm_activity',
			'map_meta_cap'        => true,
			'has_archive'         => false,
			'hierarchical'        => false,
			'supports'            => array( 'title', 'editor' ),
		);

		register_post_type( $this->post_type, $args );
	}
}
