<?php
/**
 * The file that defines the Contact custom post type.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Deals_Manager
 * @subpackage Deals_Manager/includes/cpt
 */

/**
 * The Contact Custom Post Type class.
 *
 * @since      1.0.0
 * @package    Deals_Manager
 * @subpackage Deals_Manager/includes/cpt
 * @author     Jules <you@example.com>
 */
class Deals_Manager_Contact_CPT {

    /**
     * The name for the custom post type.
     * @var string
     */
    private $post_type = 'contact';

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
        $this->loader->add_action( 'init', $this, 'register_cpt' );
        $this->loader->add_action( 'add_meta_boxes', $this, 'add_meta_boxes' );
        $this->loader->add_action( 'save_post_contact', $this, 'save_meta_data' );
    }

    /**
     * Register the custom post type.
     *
     * @since 1.0.0
     */
    public function register_cpt() {
        $labels = array(
            'name'                  => _x( 'Contacts', 'Post type general name', 'deals-manager' ),
            'singular_name'         => _x( 'Contact', 'Post type singular name', 'deals-manager' ),
            'menu_name'             => _x( 'Contacts', 'Admin Menu text', 'deals-manager' ),
            'name_admin_bar'        => _x( 'Contact', 'Add New on Toolbar', 'deals-manager' ),
            'add_new'               => __( 'Add New', 'deals-manager' ),
            'add_new_item'          => __( 'Add New Contact', 'deals-manager' ),
            'new_item'              => __( 'New Contact', 'deals-manager' ),
            'edit_item'             => __( 'Edit Contact', 'deals-manager' ),
            'view_item'             => __( 'View Contact', 'deals-manager' ),
            'all_items'             => __( 'All Contacts', 'deals-manager' ),
            'search_items'          => __( 'Search Contacts', 'deals-manager' ),
            'parent_item_colon'     => __( 'Parent Contacts:', 'deals-manager' ),
            'not_found'             => __( 'No contacts found.', 'deals-manager' ),
            'not_found_in_trash'    => __( 'No contacts found in Trash.', 'deals-manager' ),
            'featured_image'        => _x( 'Contact Photo', 'Overrides the “Featured Image” phrase for this post type.', 'deals-manager' ),
            'set_featured_image'    => _x( 'Set contact photo', 'Overrides the “Set featured image” phrase for this post type.', 'deals-manager' ),
            'remove_featured_image' => _x( 'Remove contact photo', 'Overrides the “Remove featured image” phrase for this post type.', 'deals-manager' ),
            'use_featured_image'    => _x( 'Use as contact photo', 'Overrides the “Use as featured image” phrase for this post type.', 'deals-manager' ),
            'archives'              => _x( 'Contact archives', 'The post type archive label used in nav menus.', 'deals-manager' ),
            'insert_into_item'      => _x( 'Insert into contact', 'Overrides the “Insert into post”/”Insert into page” phrase.', 'deals-manager' ),
            'uploaded_to_this_item' => _x( 'Uploaded to this contact', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase.', 'deals-manager' ),
            'filter_items_list'     => _x( 'Filter contacts list', 'Screen reader text for the filter links heading on the post type listing screen.', 'deals-manager' ),
            'items_list_navigation' => _x( 'Contacts list navigation', 'Screen reader text for the pagination heading on the post type listing screen.', 'deals-manager' ),
            'items_list'            => _x( 'Contacts list', 'Screen reader text for the items list heading on the post type listing screen.', 'deals-manager' ),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => 'deals-manager',
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'contact' ),
            'capability_type'    => 'contact',
            'map_meta_cap'       => true,
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'menu_icon'          => 'dashicons-id',
            'supports'           => array( 'title', 'editor', 'thumbnail' ),
            'show_in_rest'       => true,
        );

        register_post_type( $this->post_type, $args );
    }

    /**
     * Adds the meta box container.
     *
     * @since 1.0.0
     */
    public function add_meta_boxes() {
        add_meta_box(
            'contact_details',
            __( 'Contact Details', 'deals-manager' ),
            array( $this, 'render_meta_box' ),
            $this->post_type,
            'advanced',
            'high'
        );

        // Add custom field groups
		$field_groups = get_posts( array(
			'post_type' => 'dm_field_group',
			'posts_per_page' => -1,
			'meta_key' => '_dm_location',
			'meta_value' => 'contact',
		) );

		foreach ( $field_groups as $group ) {
			add_meta_box(
				'dm_field_group_' . $group->ID,
				$group->post_title,
				array( $this, 'render_dynamic_meta_box' ),
				$this->post_type,
				'normal',
				'default',
				array( 'fields' => get_post_meta( $group->ID, '_dm_fields', true ) )
			);
		}
    }

    /**
     * Render Meta Box content.
     *
     * @since 1.0.0
     * @param WP_Post $post The post object.
     */
    public function render_meta_box( $post ) {
        wp_nonce_field( 'contact_details_meta_box', 'contact_details_meta_box_nonce' );

        $email = get_post_meta( $post->ID, '_contact_email', true );
        $phone = get_post_meta( $post->ID, '_contact_phone', true );
        $job_title = get_post_meta( $post->ID, '_contact_job_title', true );
        ?>
        <p>
            <label for="contact_email"><?php _e( 'Email', 'deals-manager' ); ?></label>
            <input type="email" id="contact_email" name="contact_email" value="<?php echo esc_attr( $email ); ?>" size="25" />
        </p>
        <p>
            <label for="contact_phone"><?php _e( 'Phone', 'deals-manager' ); ?></label>
            <input type="text" id="contact_phone" name="contact_phone" value="<?php echo esc_attr( $phone ); ?>" size="25" />
        </p>
        <p>
            <label for="contact_job_title"><?php _e( 'Job Title', 'deals-manager' ); ?></label>
            <input type="text" id="contact_job_title" name="contact_job_title" value="<?php echo esc_attr( $job_title ); ?>" size="25" />
        </p>
        <?php
    }

    /**
     * Save the meta when the post is saved.
     *
     * @since 1.0.0
     * @param int $post_id The ID of the post being saved.
     */
    public function save_meta_data( $post_id ) {
        if ( ! isset( $_POST['contact_details_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['contact_details_meta_box_nonce'], 'contact_details_meta_box' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        if ( isset( $_POST['contact_email'] ) ) {
            update_post_meta( $post_id, '_contact_email', sanitize_email( $_POST['contact_email'] ) );
        }

        if ( isset( $_POST['contact_phone'] ) ) {
            update_post_meta( $post_id, '_contact_phone', sanitize_text_field( $_POST['contact_phone'] ) );
        }

        if ( isset( $_POST['contact_job_title'] ) ) {
            update_post_meta( $post_id, '_contact_job_title', sanitize_text_field( $_POST['contact_job_title'] ) );
        }

		// Save custom fields data
		if ( isset( $_POST['dm_custom_fields_meta_box_nonce'] ) && wp_verify_nonce( $_POST['dm_custom_fields_meta_box_nonce'], 'dm_custom_fields_meta_box' ) ) {
			$field_groups = get_posts(
				array(
					'post_type'      => 'dm_field_group',
					'posts_per_page' => -1,
					'meta_key'       => '_dm_location',
					'meta_value'     => 'contact',
				)
			);

			foreach ( $field_groups as $group ) {
				$fields = get_post_meta( $group->ID, '_dm_fields', true );
				if ( is_array( $fields ) ) {
					foreach ( $fields as $field ) {
						if ( isset( $_POST[ $field['name'] ] ) ) {
							$value = sanitize_text_field( wp_unslash( $_POST[ $field['name'] ] ) );
							update_post_meta( $post_id, '_' . $field['name'], $value );
						}
					}
				}
			}
		}
    }

	/**
	 * Render the meta box for a dynamic field group.
	 *
	 * @since 1.0.0
	 * @param WP_Post $post    The post object.
	 * @param array   $metabox The meta box arguments.
	 */
	public function render_dynamic_meta_box( $post, $metabox ) {
		wp_nonce_field( 'dm_custom_fields_meta_box', 'dm_custom_fields_meta_box_nonce' );

		$fields = isset( $metabox['args']['fields'] ) ? $metabox['args']['fields'] : array();

		if ( is_array( $fields ) ) {
			foreach ( $fields as $field ) {
				$this->render_field( $field, $post->ID );
			}
		}
	}

	/**
	 * Helper to render a custom field.
	 *
	 * @since 1.0.0
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
