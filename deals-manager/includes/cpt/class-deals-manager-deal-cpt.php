<?php
/**
 * The file that defines the Deal custom post type.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Deals_Manager
 * @subpackage Deals_Manager/includes/cpt
 */

/**
 * The Deal Custom Post Type class.
 *
 * @since      1.0.0
 * @package    Deals_Manager
 * @subpackage Deals_Manager/includes/cpt
 * @author     Jules <you@example.com>
 */
class Deals_Manager_Deal_CPT {

    /**
     * The name for the custom post type.
     * @var string
     */
    private $post_type = 'deal';

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
        $this->loader->add_action( 'save_post_deal', $this, 'save_meta_data' );
		$this->loader->add_action( 'save_post_deal', $this, 'save_deal_activity' );
    }

    /**
     * Register the custom post type.
     *
     * @since 1.0.0
     */
    public function register_cpt() {
        $labels = array(
            'name'                  => _x( 'Deals', 'Post type general name', 'deals-manager' ),
            'singular_name'         => _x( 'Deal', 'Post type singular name', 'deals-manager' ),
            'menu_name'             => _x( 'Deals', 'Admin Menu text', 'deals-manager' ),
            'name_admin_bar'        => _x( 'Deal', 'Add New on Toolbar', 'deals-manager' ),
            'add_new'               => __( 'Add New', 'deals-manager' ),
            'add_new_item'          => __( 'Add New Deal', 'deals-manager' ),
            'new_item'              => __( 'New Deal', 'deals-manager' ),
            'edit_item'             => __( 'Edit Deal', 'deals-manager' ),
            'view_item'             => __( 'View Deal', 'deals-manager' ),
            'all_items'             => __( 'All Deals', 'deals-manager' ),
            'search_items'          => __( 'Search Deals', 'deals-manager' ),
            'parent_item_colon'     => __( 'Parent Deals:', 'deals-manager' ),
            'not_found'             => __( 'No deals found.', 'deals-manager' ),
            'not_found_in_trash'    => __( 'No deals found in Trash.', 'deals-manager' ),
            'featured_image'        => _x( 'Deal Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'deals-manager' ),
            'set_featured_image'    => _x( 'Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'deals-manager' ),
            'remove_featured_image' => _x( 'Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'deals-manager' ),
            'use_featured_image'    => _x( 'Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'deals-manager' ),
            'archives'              => _x( 'Deal archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'deals-manager' ),
            'insert_into_item'      => _x( 'Insert into deal', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'deals-manager' ),
            'uploaded_to_this_item' => _x( 'Uploaded to this deal', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'deals-manager' ),
            'filter_items_list'     => _x( 'Filter deals list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'deals-manager' ),
            'items_list_navigation' => _x( 'Deals list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'deals-manager' ),
            'items_list'            => _x( 'Deals list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'deals-manager' ),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => 'deals-manager',
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'deal' ),
            'capability_type'    => 'deal',
            'map_meta_cap'       => true,
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'menu_icon'          => 'dashicons-money-alt',
            'supports'           => array( 'title', 'editor', 'author', 'thumbnail' ),
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
            'deal_details',
            __( 'Deal Details', 'deals-manager' ),
            array( $this, 'render_meta_box' ),
            $this->post_type,
            'advanced',
            'high'
        );

		add_meta_box(
			'deal_activity',
			__( 'Activity', 'deals-manager' ),
			array( $this, 'render_activity_meta_box' ),
			$this->post_type,
			'normal',
			'default'
		);

		// Add custom field groups
		$field_groups = get_posts( array(
			'post_type' => 'dm_field_group',
			'posts_per_page' => -1,
			'meta_key' => '_dm_location',
			'meta_value' => 'deal',
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
     * Render Meta Box content for the main deal details.
     *
     * @since 1.0.0
     * @param WP_Post $post The post object.
     */
    public function render_meta_box( $post ) {
        wp_nonce_field( 'deal_details_meta_box', 'deal_details_meta_box_nonce' );

        $value = get_post_meta( $post->ID, '_deal_value', true );
        $owner = get_post_meta( $post->ID, '_deal_owner', true );
        $priority = get_post_meta( $post->ID, '_deal_priority', true );
        $stage = get_post_meta( $post->ID, '_deal_stage', true );
        $related_contact = get_post_meta( $post->ID, '_deal_related_contact', true );
        ?>
        <p>
            <label for="deal_related_contact"><?php _e( 'Related Contact', 'deals-manager' ); ?></label>
            <select name="deal_related_contact" id="deal_related_contact">
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
            <label for="deal_value"><?php _e( 'Value', 'deals-manager' ); ?></label>
            <input type="text" id="deal_value" name="deal_value" value="<?php echo esc_attr( $value ); ?>" size="25" />
        </p>
        <p>
            <label for="deal_owner"><?php _e( 'Owner', 'deals-manager' ); ?></label>
            <?php
            wp_dropdown_users( array(
                'name' => 'deal_owner',
                'selected' => $owner,
                'show_option_none' => 'Select an Owner',
            ) );
            ?>
        </p>
        <p>
            <label for="deal_priority"><?php _e( 'Priority', 'deals-manager' ); ?></label>
            <select name="deal_priority" id="deal_priority">
                <option value="low" <?php selected( $priority, 'low' ); ?>><?php _e( 'Low', 'deals-manager' ); ?></option>
                <option value="normal" <?php selected( $priority, 'normal' ); ?>><?php _e( 'Normal', 'deals-manager' ); ?></option>
                <option value="high" <?php selected( $priority, 'high' ); ?>><?php _e( 'High', 'deals-manager' ); ?></option>
            </select>
        </p>
        <p>
            <label for="deal_stage"><?php _e( 'Stage', 'deals-manager' ); ?></label>
            <select name="deal_stage" id="deal_stage">
                <option value="lead" <?php selected( $stage, 'lead' ); ?>><?php _e( 'Lead', 'deals-manager' ); ?></option>
                <option value="proposal" <?php selected( $stage, 'proposal' ); ?>><?php _e( 'Proposal', 'deals-manager' ); ?></option>
                <option value="negotiation" <?php selected( $stage, 'negotiation' ); ?>><?php _e( 'Negotiation', 'deals-manager' ); ?></option>
                <option value="won" <?php selected( $stage, 'won' ); ?>><?php _e( 'Won', 'deals-manager' ); ?></option>
                <option value="lost" <?php selected( $stage, 'lost' ); ?>><?php _e( 'Lost', 'deals-manager' ); ?></option>
            </select>
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
        if ( ! isset( $_POST['deal_details_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['deal_details_meta_box_nonce'], 'deal_details_meta_box' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        if ( isset( $_POST['deal_related_contact'] ) ) {
            update_post_meta( $post_id, '_deal_related_contact', sanitize_text_field( $_POST['deal_related_contact'] ) );
        }

        if ( isset( $_POST['deal_value'] ) ) {
            update_post_meta( $post_id, '_deal_value', sanitize_text_field( $_POST['deal_value'] ) );
        }

        if ( isset( $_POST['deal_owner'] ) ) {
            update_post_meta( $post_id, '_deal_owner', sanitize_text_field( $_POST['deal_owner'] ) );
        }

        if ( isset( $_POST['deal_priority'] ) ) {
            update_post_meta( $post_id, '_deal_priority', sanitize_text_field( $_POST['deal_priority'] ) );
        }

        if ( isset( $_POST['deal_stage'] ) ) {
			$old_stage = get_post_meta( $post_id, '_deal_stage', true );
			$new_stage = sanitize_text_field( wp_unslash( $_POST['deal_stage'] ) );

			if ( $old_stage !== $new_stage && $old_stage ) {
				$activity_note = sprintf(
					/* translators: %1$s: Old stage, %2$s: New stage. */
					__( 'Stage changed from %1$s to %2$s.', 'deals-manager' ),
					ucfirst( $old_stage ),
					ucfirst( $new_stage )
				);

				$activity_id = wp_insert_post(
					array(
						'post_type'    => 'dm_activity',
						'post_title'   => __( 'Stage Change', 'deals-manager' ),
						'post_content' => $activity_note,
						'post_status'  => 'publish',
					)
				);

				if ( $activity_id && ! is_wp_error( $activity_id ) ) {
					update_post_meta( $activity_id, '_activity_related_deal', $post_id );
					update_post_meta( $activity_id, '_activity_type', 'stage_change' );
					do_action( 'dm_deal_stage_changed', $post_id, $new_stage, $old_stage );
					do_action( 'dm_activity_created', $activity_id, $post_id );
				}
			}
			update_post_meta( $post_id, '_deal_stage', $new_stage );
		}

		// Save custom fields data
		if ( isset( $_POST['dm_custom_fields_meta_box_nonce'] ) && wp_verify_nonce( $_POST['dm_custom_fields_meta_box_nonce'], 'dm_custom_fields_meta_box' ) ) {
			$field_groups = get_posts(
				array(
					'post_type'      => 'dm_field_group',
					'posts_per_page' => -1,
					'meta_key'       => '_dm_location',
					'meta_value'     => 'deal',
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
	 * Render the Activity meta box.
	 *
	 * @since 1.0.0
	 * @param WP_Post $post The post object.
	 */
	public function render_activity_meta_box( $post ) {
		wp_nonce_field( 'deal_activity_meta_box', 'deal_activity_meta_box_nonce' );
		?>
		<h4><?php _e( 'Add New Activity', 'deals-manager' ); ?></h4>
		<p>
			<label for="activity_type"><?php _e( 'Type:', 'deals-manager' ); ?></label>
			<select name="activity_type" id="activity_type">
				<option value="note"><?php _e( 'Note', 'deals-manager' ); ?></option>
				<option value="call"><?php _e( 'Call', 'deals-manager' ); ?></option>
				<option value="email"><?php _e( 'Email', 'deals-manager' ); ?></option>
			</select>
		</p>
		<p>
			<label for="activity_note"><?php _e( 'Note:', 'deals-manager' ); ?></label>
			<textarea name="activity_note" id="activity_note" rows="5" style="width:100%;"></textarea>
		</p>

		<hr>

		<h4><?php _e( 'Activity History', 'deals-manager' ); ?></h4>
		<div class="activity-history-list">
			<?php
			$activities = get_posts(
				array(
					'post_type'      => 'dm_activity',
					'posts_per_page' => -1,
					'meta_key'       => '_activity_related_deal',
					'meta_value'     => $post->ID,
					'orderby'        => 'date',
					'order'          => 'DESC',
				)
			);

			if ( $activities ) {
				echo '<ul>';
				foreach ( $activities as $activity ) {
					?>
					<li>
						<strong><?php echo esc_html( get_the_title( $activity->ID ) ); ?></strong>
						<em>(<?php echo esc_html( get_the_date( '', $activity->ID ) ); ?>)</em>
						by <?php echo esc_html( get_the_author_meta( 'display_name', $activity->post_author ) ); ?>
						<div><?php echo wpautop( esc_html( $activity->post_content ) ); ?></div>
					</li>
					<?php
				}
				echo '</ul>';
			} else {
				echo '<p>' . esc_html__( 'No activities found.', 'deals-manager' ) . '</p>';
			}
			?>
		</div>
		<style>.activity-history-list ul { list-style: none; margin-left: 0; } .activity-history-list li { border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 10px; }</style>
		<?php
	}

	/**
	 * Save the deal activity.
	 *
	 * @since 1.0.0
	 * @param int $post_id The ID of the post being saved.
	 */
	public function save_deal_activity( $post_id ) {
		if ( ! isset( $_POST['deal_activity_meta_box_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['deal_activity_meta_box_nonce'] ), 'deal_activity_meta_box' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_deal', $post_id ) ) {
			return;
		}

		if ( isset( $_POST['activity_note'] ) && ! empty( trim( $_POST['activity_note'] ) ) ) {
			$activity_type = sanitize_text_field( wp_unslash( $_POST['activity_type'] ) );
			$activity_note = sanitize_textarea_field( wp_unslash( $_POST['activity_note'] ) );

			$activity_id = wp_insert_post(
				array(
					'post_type'    => 'dm_activity',
					'post_title'   => ucfirst( $activity_type ),
					'post_content' => $activity_note,
					'post_status'  => 'publish',
				)
			);

			if ( $activity_id && ! is_wp_error( $activity_id ) ) {
				update_post_meta( $activity_id, '_activity_related_deal', $post_id );
				update_post_meta( $activity_id, '_activity_type', $activity_type );
				do_action( 'dm_activity_created', $activity_id, $post_id );
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

	/**
	 * Render the meta box for a dynamic field group.
	 *
	 * @since 1.0.0
	 * @param WP_Post $post    The post object.
	 * @param array   $metabox The meta box arguments.
	 */
	public function render_dynamic_meta_box( $post, $metabox ) {
		// Add a nonce field so we can check for it later.
		wp_nonce_field( 'dm_custom_fields_meta_box', 'dm_custom_fields_meta_box_nonce' );

		$fields = isset( $metabox['args']['fields'] ) ? $metabox['args']['fields'] : array();

		if ( is_array( $fields ) ) {
			foreach ( $fields as $field ) {
				$this->render_field( $field, $post->ID );
			}
		}
	}
}
