<?php
/**
 * The file that defines the Company custom post type.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Deals_Manager
 * @subpackage Deals_Manager/includes/cpt
 */

/**
 * The Company Custom Post Type class.
 *
 * @since      1.0.0
 * @package    Deals_Manager
 * @subpackage Deals_Manager/includes/cpt
 * @author     Jules <you@example.com>
 */
class Deals_Manager_Company_CPT {

    /**
     * The name for the custom post type.
     * @var string
     */
    private $post_type = 'company';

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
        $this->loader->add_action( 'add_meta_boxes', $this, 'add_meta_boxes' );
        $this->loader->add_action( 'save_post', $this, 'save_meta_data' );
    }

    /**
     * Register the custom post type.
     */
    public function register_cpt() {
        $labels = array(
            'name'                  => _x( 'Companies', 'Post type general name', 'deals-manager' ),
            'singular_name'         => _x( 'Company', 'Post type singular name', 'deals-manager' ),
            'menu_name'             => _x( 'Companies', 'Admin Menu text', 'deals-manager' ),
            'name_admin_bar'        => _x( 'Company', 'Add New on Toolbar', 'deals-manager' ),
            'add_new'               => __( 'Add New', 'deals-manager' ),
            'add_new_item'          => __( 'Add New Company', 'deals-manager' ),
            'new_item'              => __( 'New Company', 'deals-manager' ),
            'edit_item'             => __( 'Edit Company', 'deals-manager' ),
            'view_item'             => __( 'View Company', 'deals-manager' ),
            'all_items'             => __( 'All Companies', 'deals-manager' ),
            'search_items'          => __( 'Search Companies', 'deals-manager' ),
            'parent_item_colon'     => __( 'Parent Companies:', 'deals-manager' ),
            'not_found'             => __( 'No companies found.', 'deals-manager' ),
            'not_found_in_trash'    => __( 'No companies found in Trash.', 'deals-manager' ),
            'featured_image'        => _x( 'Company Logo', 'Overrides the “Featured Image” phrase for this post type.', 'deals-manager' ),
            'set_featured_image'    => _x( 'Set company logo', 'Overrides the “Set featured image” phrase for this post type.', 'deals-manager' ),
            'remove_featured_image' => _x( 'Remove company logo', 'Overrides the “Remove featured image” phrase for this post type.', 'deals-manager' ),
            'use_featured_image'    => _x( 'Use as company logo', 'Overrides the “Use as featured image” phrase for this post type.', 'deals-manager' ),
            'archives'              => _x( 'Company archives', 'The post type archive label used in nav menus.', 'deals-manager' ),
            'insert_into_item'      => _x( 'Insert into company', 'Overrides the “Insert into post”/”Insert into page” phrase.', 'deals-manager' ),
            'uploaded_to_this_item' => _x( 'Uploaded to this company', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase.', 'deals-manager' ),
            'filter_items_list'     => _x( 'Filter companies list', 'Screen reader text for the filter links heading on the post type listing screen.', 'deals-manager' ),
            'items_list_navigation' => _x( 'Companies list navigation', 'Screen reader text for the pagination heading on the post type listing screen.', 'deals-manager' ),
            'items_list'            => _x( 'Companies list', 'Screen reader text for the items list heading on the post type listing screen.', 'deals-manager' ),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => 'deals-manager',
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'company' ),
            'capability_type'    => 'company',
            'map_meta_cap'       => true,
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'menu_icon'          => 'dashicons-building',
            'supports'           => array( 'title', 'editor', 'thumbnail' ),
            'show_in_rest'       => true,
        );

        register_post_type( $this->post_type, $args );
    }

    /**
     * Adds the meta box container.
     */
    public function add_meta_boxes() {
        add_meta_box(
            'company_details',
            __( 'Company Details', 'deals-manager' ),
            array( $this, 'render_meta_box' ),
            $this->post_type,
            'advanced',
            'high'
        );
    }

    /**
     * Render Meta Box content.
     *
     * @param WP_Post $post The post object.
     */
    public function render_meta_box( $post ) {
        wp_nonce_field( 'company_details_meta_box', 'company_details_meta_box_nonce' );

        $website = get_post_meta( $post->ID, '_company_website', true );
        $address = get_post_meta( $post->ID, '_company_address', true );
        ?>
        <p>
            <label for="company_website"><?php _e( 'Website', 'deals-manager' ); ?></label>
            <input type="url" id="company_website" name="company_website" value="<?php echo esc_attr( $website ); ?>" size="25" />
        </p>
        <p>
            <label for="company_address"><?php _e( 'Address', 'deals-manager' ); ?></label>
            <textarea id="company_address" name="company_address" rows="5" cols="30"><?php echo esc_textarea( $address ); ?></textarea>
        </p>
        <?php
		/**
		 * Filter to add custom fields to the Company CPT.
		 *
		 * @since 1.0.0
		 *
		 * @param array   $fields Array of custom fields.
		 * @param WP_Post $post   The current post object.
		 */
		$custom_fields = apply_filters( 'dm_company_custom_fields', array(), $post );

		if ( ! empty( $custom_fields ) ) {
			echo '<hr>';
			foreach ( $custom_fields as $field ) {
				$this->render_field( $field, $post->ID );
			}
		}
    }

    /**
     * Save the meta when the post is saved.
     *
     * @param int $post_id The ID of the post being saved.
     */
    public function save_meta_data( $post_id ) {
        if ( ! isset( $_POST['company_details_meta_box_nonce'] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( $_POST['company_details_meta_box_nonce'], 'company_details_meta_box' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! isset( $_POST['post_type'] ) || $this->post_type != $_POST['post_type'] ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        if ( isset( $_POST['company_website'] ) ) {
            update_post_meta( $post_id, '_company_website', sanitize_url( $_POST['company_website'] ) );
        }

        if ( isset( $_POST['company_address'] ) ) {
            update_post_meta( $post_id, '_company_address', sanitize_textarea_field( $_POST['company_address'] ) );
        }

		// Save custom fields
		$custom_fields = apply_filters( 'dm_company_custom_fields', array(), get_post( $post_id ) );
		foreach ( $custom_fields as $field ) {
			if ( isset( $_POST[ $field['name'] ] ) ) {
				// Basic sanitization, can be improved with a filter or more logic based on field type.
				$value = sanitize_text_field( $_POST[ $field['name'] ] );
				update_post_meta( $post_id, '_' . $field['name'], $value );
			}
		}
    }

	/**
	 * Helper to render a custom field.
	 *
	 * @param array   $field   The field definition.
	 * @param int     $post_id The post ID.
	 */
	private function render_field( $field, $post_id ) {
		$value = get_post_meta( $post_id, '_' . $field['name'], true );
		?>
		<p>
			<label for="<?php echo esc_attr( $field['name'] ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
			<br>
			<?php
			switch ( $field['type'] ) {
				case 'text':
					?>
					<input type="text" id="<?php echo esc_attr( $field['name'] ); ?>" name="<?php echo esc_attr( $field['name'] ); ?>" value="<?php echo esc_attr( $value ); ?>" size="25" />
					<?php
					break;
				case 'textarea':
					?>
					<textarea id="<?php echo esc_attr( $field['name'] ); ?>" name="<?php echo esc_attr( $field['name'] ); ?>" rows="5" cols="30"><?php echo esc_textarea( $value ); ?></textarea>
					<?php
					break;
				case 'number':
					?>
					<input type="number" id="<?php echo esc_attr( $field['name'] ); ?>" name="<?php echo esc_attr( $field['name'] ); ?>" value="<?php echo esc_attr( $value ); ?>" />
					<?php
					break;
			}
			?>
		</p>
		<?php
	}
}
