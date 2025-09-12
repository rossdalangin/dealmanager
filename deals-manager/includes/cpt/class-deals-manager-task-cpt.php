<?php
/**
 * The file that defines the Task custom post type.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Deals_Manager
 * @subpackage Deals_Manager/includes/cpt
 */

/**
 * The Task Custom Post Type class.
 *
 * @since      1.0.0
 * @package    Deals_Manager
 * @subpackage Deals_Manager/includes/cpt
 * @author     Jules <you@example.com>
 */
class Deals_Manager_Task_CPT {

    /**
     * The name for the custom post type.
     * @var string
     */
    private $post_type = 'task';

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
            'name'                  => _x( 'Tasks', 'Post type general name', 'deals-manager' ),
            'singular_name'         => _x( 'Task', 'Post type singular name', 'deals-manager' ),
            'menu_name'             => _x( 'Tasks', 'Admin Menu text', 'deals-manager' ),
            'name_admin_bar'        => _x( 'Task', 'Add New on Toolbar', 'deals-manager' ),
            'add_new'               => __( 'Add New', 'deals-manager' ),
            'add_new_item'          => __( 'Add New Task', 'deals-manager' ),
            'new_item'              => __( 'New Task', 'deals-manager' ),
            'edit_item'             => __( 'Edit Task', 'deals-manager' ),
            'view_item'             => __( 'View Task', 'deals-manager' ),
            'all_items'             => __( 'All Tasks', 'deals-manager' ),
            'search_items'          => __( 'Search Tasks', 'deals-manager' ),
            'parent_item_colon'     => __( 'Parent Tasks:', 'deals-manager' ),
            'not_found'             => __( 'No tasks found.', 'deals-manager' ),
            'not_found_in_trash'    => __( 'No tasks found in Trash.', 'deals-manager' ),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => 'deals-manager',
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'task' ),
            'capability_type'    => 'task',
            'map_meta_cap'       => true,
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'menu_icon'          => 'dashicons-list-view',
            'supports'           => array( 'title', 'editor' ),
            'show_in_rest'       => true,
        );

        register_post_type( $this->post_type, $args );
    }

    /**
     * Adds the meta box container.
     */
    public function add_meta_boxes() {
        add_meta_box(
            'task_details',
            __( 'Task Details', 'deals-manager' ),
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
        wp_nonce_field( 'task_details_meta_box', 'task_details_meta_box_nonce' );

        $due_date = get_post_meta( $post->ID, '_task_due_date', true );
        $status = get_post_meta( $post->ID, '_task_status', true );
        $related_deal = get_post_meta( $post->ID, '_task_related_deal', true );
        $related_contact = get_post_meta( $post->ID, '_task_related_contact', true );
        $related_company = get_post_meta( $post->ID, '_task_related_company', true );

        ?>
        <p>
            <label for="task_due_date"><?php _e( 'Due Date', 'deals-manager' ); ?></label>
            <input type="date" id="task_due_date" name="task_due_date" value="<?php echo esc_attr( $due_date ); ?>" />
        </p>
        <p>
            <label for="task_status"><?php _e( 'Status', 'deals-manager' ); ?></label>
            <select name="task_status" id="task_status">
                <option value="to-do" <?php selected( $status, 'to-do' ); ?>><?php _e( 'To Do', 'deals-manager' ); ?></option>
                <option value="in-progress" <?php selected( $status, 'in-progress' ); ?>><?php _e( 'In Progress', 'deals-manager' ); ?></option>
                <option value="completed" <?php selected( $status, 'completed' ); ?>><?php _e( 'Completed', 'deals-manager' ); ?></option>
            </select>
        </p>
        <hr>
        <p>
            <label for="task_related_deal"><?php _e( 'Related Deal', 'deals-manager' ); ?></label>
            <select name="task_related_deal" id="task_related_deal">
                <option value=""><?php _e( 'None', 'deals-manager' ); ?></option>
                <?php
                $deals = get_posts( array( 'post_type' => 'deal', 'numberposts' => -1 ) );
                foreach ( $deals as $deal ) {
                    echo '<option value="' . esc_attr( $deal->ID ) . '"' . selected( $related_deal, $deal->ID, false ) . '>' . esc_html( $deal->post_title ) . '</option>';
                }
                ?>
            </select>
        </p>
        <p>
            <label for="task_related_contact"><?php _e( 'Related Contact', 'deals-manager' ); ?></label>
            <select name="task_related_contact" id="task_related_contact">
                <option value=""><?php _e( 'None', 'deals-manager' ); ?></option>
                <?php
                $contacts = get_posts( array( 'post_type' => 'contact', 'numberposts' => -1 ) );
                foreach ( $contacts as $contact ) {
                    echo '<option value="' . esc_attr( $contact->ID ) . '"' . selected( $related_contact, $contact->ID, false ) . '>' . esc_html( $contact->post_title ) . '</option>';
                }
                ?>
            </select>
        </p>
        <p>
            <label for="task_related_company"><?php _e( 'Related Company', 'deals-manager' ); ?></label>
            <select name="task_related_company" id="task_related_company">
                <option value=""><?php _e( 'None', 'deals-manager' ); ?></option>
                <?php
                $companies = get_posts( array( 'post_type' => 'company', 'numberposts' => -1 ) );
                foreach ( $companies as $company ) {
                    echo '<option value="' . esc_attr( $company->ID ) . '"' . selected( $related_company, $company->ID, false ) . '>' . esc_html( $company->post_title ) . '</option>';
                }
                ?>
            </select>
        </p>
        <?php
    }

    /**
     * Save the meta when the post is saved.
     *
     * @param int $post_id The ID of the post being saved.
     */
    public function save_meta_data( $post_id ) {
        if ( ! isset( $_POST['task_details_meta_box_nonce'] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( $_POST['task_details_meta_box_nonce'], 'task_details_meta_box' ) ) {
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

        if ( isset( $_POST['task_due_date'] ) ) {
            update_post_meta( $post_id, '_task_due_date', sanitize_text_field( $_POST['task_due_date'] ) );
        }

        if ( isset( $_POST['task_status'] ) ) {
            update_post_meta( $post_id, '_task_status', sanitize_text_field( $_POST['task_status'] ) );
        }

        if ( isset( $_POST['task_related_deal'] ) ) {
            update_post_meta( $post_id, '_task_related_deal', sanitize_text_field( $_POST['task_related_deal'] ) );
        }

        if ( isset( $_POST['task_related_contact'] ) ) {
            update_post_meta( $post_id, '_task_related_contact', sanitize_text_field( $_POST['task_related_contact'] ) );
        }

        if ( isset( $_POST['task_related_company'] ) ) {
            update_post_meta( $post_id, '_task_related_company', sanitize_text_field( $_POST['task_related_company'] ) );
        }
    }
}
