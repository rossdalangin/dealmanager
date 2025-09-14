<?php
/**
 * The file that defines the custom taxonomies.
 *
 * @link       https://wordpresstitans.com
 * @since      1.0.0
 *
 * @package    Deals_Manager
 * @subpackage Deals_Manager/includes
 */

/**
 * The Custom Taxonomies class.
 *
 * This class is responsible for registering the custom taxonomies
 * used by the plugin.
 *
 * @since      1.0.0
 * @package    Deals_Manager
 * @subpackage Deals_Manager/includes
 * @author     Ross Dalangin
 */
class Deals_Manager_Taxonomies {

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
     * @param    Deals_Manager_Loader $loader Maintains and registers all hooks for the plugin.
     */
    public function __construct( $loader ) {
        $this->loader = $loader;
    }

    /**
     * Run all the hooks for this class.
     *
     * @since 1.0.0
     */
    public function run() {
        $this->loader->add_action( 'init', $this, 'register_taxonomies' );
    }

    /**
     * Register the 'Group' taxonomy.
     *
     * This taxonomy is used to group deals, contacts, and companies.
     *
     * @since 1.0.0
     */
    public function register_taxonomies() {
        $labels = array(
            'name'              => _x( 'Groups', 'taxonomy general name', 'deals-manager' ),
            'singular_name'     => _x( 'Group', 'taxonomy singular name', 'deals-manager' ),
            'search_items'      => __( 'Search Groups', 'deals-manager' ),
            'all_items'         => __( 'All Groups', 'deals-manager' ),
            'parent_item'       => __( 'Parent Group', 'deals-manager' ),
            'parent_item_colon' => __( 'Parent Group:', 'deals-manager' ),
            'edit_item'         => __( 'Edit Group', 'deals-manager' ),
            'update_item'       => __( 'Update Group', 'deals-manager' ),
            'add_new_item'      => __( 'Add New Group', 'deals-manager' ),
            'new_item_name'     => __( 'New Group Name', 'deals-manager' ),
            'menu_name'         => __( 'Groups', 'deals-manager' ),
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'group' ),
            'show_in_rest'      => true,
        );

        register_taxonomy( 'd_group', array( 'deal', 'contact', 'company' ), $args );
    }
}
