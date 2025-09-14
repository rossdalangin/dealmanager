<?php
/**
 * The file that defines the Field Group custom post type.
 *
 * @link       https://wordpresstitans.com
 * @since      1.0.0
 *
 * @package    Deals_Manager
 * @subpackage Deals_Manager/includes/cpt
 */

/**
 * The Field Group Custom Post Type class.
 *
 * @since      1.0.0
 * @package    Deals_Manager
 * @subpackage Deals_Manager/includes/cpt
 * @author     Ross Dalangin
 */
class Deals_Manager_Field_Group_CPT {

    /**
     * The name for the custom post type.
     * @var string
     */
    private $post_type = 'dm_field_group';

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
        $this->loader->add_action( 'save_post', $this, 'save_meta_data' );
    }

    /**
     * Register the custom post type.
     *
     * @since 1.0.0
     */
    public function register_cpt() {
        $labels = array(
            'name'               => _x( 'Field Groups', 'post type general name', 'deals-manager' ),
            'singular_name'      => _x( 'Field Group', 'post type singular name', 'deals-manager' ),
            'menu_name'          => _x( 'Custom Fields', 'admin menu', 'deals-manager' ),
            'name_admin_bar'     => _x( 'Field Group', 'add new on admin bar', 'deals-manager' ),
            'add_new'            => _x( 'Add New', 'field group', 'deals-manager' ),
            'add_new_item'       => __( 'Add New Field Group', 'deals-manager' ),
            'new_item'           => __( 'New Field Group', 'deals-manager' ),
            'edit_item'          => __( 'Edit Field Group', 'deals-manager' ),
            'view_item'          => __( 'View Field Group', 'deals-manager' ),
            'all_items'          => __( 'Field Groups', 'deals-manager' ),
            'search_items'       => __( 'Search Field Groups', 'deals-manager' ),
            'not_found'          => __( 'No field groups found.', 'deals-manager' ),
            'not_found_in_trash' => __( 'No field groups found in Trash.', 'deals-manager' ),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => false,
            'show_ui'            => true,
            'show_in_menu'       => 'deals-manager',
            'capability_type'    => 'post',
            'hierarchical'       => false,
            'supports'           => array( 'title' ),
            'menu_icon'          => 'dashicons-admin-generic',
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
            'dm_field_group_location',
            __( 'Location', 'deals-manager' ),
            array( $this, 'render_location_meta_box' ),
            $this->post_type,
            'side',
            'high'
        );

		add_meta_box(
			'dm_field_group_fields',
			__( 'Fields', 'deals-manager' ),
			array( $this, 'render_fields_meta_box' ),
			$this->post_type,
			'advanced',
			'high'
		);
    }

    /**
     * Render Location Meta Box content.
     *
     * @since 1.0.0
     * @param WP_Post $post The post object.
     */
    public function render_location_meta_box( $post ) {
        wp_nonce_field( 'dm_field_group_location_meta_box', 'dm_field_group_location_meta_box_nonce' );
        $location = get_post_meta( $post->ID, '_dm_location', true );
        ?>
        <p>
            <label for="dm_location"><?php _e( 'Show this field group if Post Type is equal to', 'deals-manager' ); ?></label>
            <select name="dm_location" id="dm_location" class="widefat">
                <option value="deal" <?php selected( $location, 'deal' ); ?>><?php _e( 'Deal', 'deals-manager' ); ?></option>
                <option value="contact" <?php selected( $location, 'contact' ); ?>><?php _e( 'Contact', 'deals-manager' ); ?></option>
                <option value="company" <?php selected( $location, 'company' ); ?>><?php _e( 'Company', 'deals-manager' ); ?></option>
            </select>
        </p>
        <?php
    }

	/**
	 * Render Fields Meta Box content.
	 *
	 * @since 1.0.0
	 * @param WP_Post $post The post object.
	 */
	public function render_fields_meta_box( $post ) {
		wp_nonce_field( 'dm_field_group_fields_meta_box', 'dm_field_group_fields_meta_box_nonce' );

		$fields = get_post_meta( $post->ID, '_dm_fields', true );
		$fields = is_array( $fields ) ? $fields : array();
		?>
		<div id="field-editor-wrapper">
			<div id="fields-container">
				<?php if ( ! empty( $fields ) ) : ?>
					<?php foreach ( $fields as $i => $field ) : ?>
						<div class="field-item">
							<div class="field-header">
								<span class="field-label"><?php echo esc_html( $field['label'] ); ?></span>
								<span class="field-type"><?php echo esc_html( $field['type'] ); ?></span>
								<a href="#" class="edit-field">Edit</a>
								<a href="#" class="remove-field">Remove</a>
							</div>
							<div class="field-settings" style="display: none;">
								<p>
									<label><?php _e( 'Field Label', 'deals-manager' ); ?></label>
									<input type="text" name="fields[<?php echo esc_attr( $i ); ?>][label]" value="<?php echo esc_attr( $field['label'] ); ?>" class="widefat field-label-input" />
								</p>
								<p>
									<label><?php _e( 'Field Name', 'deals-manager' ); ?></label>
									<input type="text" name="fields[<?php echo esc_attr( $i ); ?>][name]" value="<?php echo esc_attr( $field['name'] ); ?>" class="widefat field-name-input" />
								</p>
								<p>
									<label><?php _e( 'Field Type', 'deals-manager' ); ?></label>
									<select name="fields[<?php echo esc_attr( $i ); ?>][type]" class="field-type-input">
										<option value="text" <?php selected( $field['type'], 'text' ); ?>>Text</option>
										<option value="textarea" <?php selected( $field['type'], 'textarea' ); ?>>Text Area</option>
										<option value="number" <?php selected( $field['type'], 'number' ); ?>>Number</option>
									</select>
								</p>
							</div>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
			<p>
				<a href="#" id="add-field" class="button"><?php _e( 'Add Field', 'deals-manager' ); ?></a>
			</p>
		</div>
		<script type="text/template" id="field-item-template">
			<div class="field-item">
				<div class="field-header">
					<span class="field-label">New Field</span>
					<span class="field-type">text</span>
					<a href="#" class="edit-field">Edit</a>
					<a href="#" class="remove-field">Remove</a>
				</div>
				<div class="field-settings">
					<p>
						<label><?php _e( 'Field Label', 'deals-manager' ); ?></label>
						<input type="text" name="fields[{index}][label]" class="widefat field-label-input" />
					</p>
					<p>
						<label><?php _e( 'Field Name', 'deals-manager' ); ?></label>
						<input type="text" name="fields[{index}][name]" class="widefat field-name-input" />
					</p>
					<p>
						<label><?php _e( 'Field Type', 'deals-manager' ); ?></label>
						<select name="fields[{index}][type]" class="field-type-input">
							<option value="text">Text</option>
							<option value="textarea">Text Area</option>
							<option value="number">Number</option>
						</select>
					</p>
				</div>
			</div>
		</script>
		<style>
			.field-item { border: 1px solid #ccc; margin-bottom: 10px; }
			.field-header { background: #f9f9f9; padding: 10px; display: flex; justify-content: space-between; align-items: center; }
			.field-settings { padding: 10px; }
		</style>
		<?php
	}

    /**
     * Save the meta when the post is saved.
     *
     * @since 1.0.0
     * @param int $post_id The ID of the post being saved.
     */
    public function save_meta_data( $post_id ) {
        if ( ! isset( $_POST['dm_field_group_location_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['dm_field_group_location_meta_box_nonce'], 'dm_field_group_location_meta_box' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        if ( isset( $_POST['dm_location'] ) ) {
            update_post_meta( $post_id, '_dm_location', sanitize_text_field( $_POST['dm_location'] ) );
        }

		// Save fields
		if ( isset( $_POST['fields'] ) && is_array( $_POST['fields'] ) && isset( $_POST['dm_field_group_fields_meta_box_nonce'] ) && wp_verify_nonce( $_POST['dm_field_group_fields_meta_box_nonce'], 'dm_field_group_fields_meta_box' ) ) {
			$sanitized_fields = array();
			foreach ( $_POST['fields'] as $field ) {
				if ( ! empty( $field['label'] ) && ! empty( $field['name'] ) ) {
					$sanitized_fields[] = array(
						'label' => sanitize_text_field( $field['label'] ),
						'name'  => sanitize_key( $field['name'] ),
						'type'  => sanitize_text_field( $field['type'] ),
					);
				}
			}
			update_post_meta( $post_id, '_dm_fields', $sanitized_fields );
		}
    }
}
