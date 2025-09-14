<?php
/**
 * Plugin Name:       Sample License Server API (CPT Edition)
 * Description:       A sample implementation of a WordPress REST API endpoint for validating and serving plugin licenses, managed via a Custom Post Type.
 * Version:           2.0
 * Author:            Ross Dalangin
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class DM_License_Server_Manager {

    public function __construct() {
        // Register CPT and REST API endpoint
        add_action( 'init', array( $this, 'register_license_cpt' ) );
        add_action( 'rest_api_init', array( $this, 'register_license_api_endpoint' ) );

        // Add meta boxes for the CPT
        add_action( 'add_meta_boxes', array( $this, 'add_license_meta_boxes' ) );
        add_action( 'save_post_license', array( $this, 'save_license_meta_data' ) );

        // Add settings page for plugin update info
        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    /**
     * Register the "Licenses" Custom Post Type.
     */
    public function register_license_cpt() {
        $labels = array(
            'name'               => _x( 'Licenses', 'post type general name', 'dm-license-server' ),
            'singular_name'      => _x( 'License', 'post type singular name', 'dm-license-server' ),
            'menu_name'          => _x( 'Licenses', 'admin menu', 'dm-license-server' ),
            'name_admin_bar'     => _x( 'License', 'add new on admin bar', 'dm-license-server' ),
            'add_new'            => _x( 'Add New', 'license', 'dm-license-server' ),
            'add_new_item'       => __( 'Add New License', 'dm-license-server' ),
            'new_item'           => __( 'New License', 'dm-license-server' ),
            'edit_item'          => __( 'Edit License', 'dm-license-server' ),
            'view_item'          => __( 'View License', 'dm-license-server' ),
            'all_items'          => __( 'All Licenses', 'dm-license-server' ),
            'search_items'       => __( 'Search Licenses', 'dm-license-server' ),
            'not_found'          => __( 'No licenses found.', 'dm-license-server' ),
            'not_found_in_trash' => __( 'No licenses found in Trash.', 'dm-license-server' )
        );

        $args = array(
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => false,
            'rewrite'            => false,
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => 20,
            'menu_icon'          => 'dashicons-lock',
            'supports'           => array( 'title' )
        );

        register_post_type( 'license', $args );
    }

    /**
     * Add meta boxes for the License CPT.
     */
    public function add_license_meta_boxes() {
        add_meta_box(
            'dm_license_details',
            __( 'License Details', 'dm-license-server' ),
            array( $this, 'render_license_details_meta_box' ),
            'license',
            'normal',
            'high'
        );
    }

    /**
     * Render the content of the License Details meta box.
     */
    public function render_license_details_meta_box( $post ) {
        wp_nonce_field( 'dm_save_license_meta', 'dm_license_meta_nonce' );

        $license_key = get_post_meta( $post->ID, '_license_key', true );
        $status = get_post_meta( $post->ID, '_license_status', true );
        $expires = get_post_meta( $post->ID, '_license_expires', true );
        $max_domains = get_post_meta( $post->ID, '_license_max_domains', true );
        $activated_domains = get_post_meta( $post->ID, '_license_activated_domains', true );

        // Use post title as license key if meta is empty
        if ( empty( $license_key ) ) {
            $license_key = $post->post_title;
        }
        ?>
        <table class="form-table">
            <tr>
                <th><label for="dm_license_key">License Key</label></th>
                <td><input type="text" id="dm_license_key" name="dm_license_key" value="<?php echo esc_attr( $license_key ); ?>" class="regular-text" readonly>
                <p class="description">To change the key, change the post title and save.</p></td>
            </tr>
            <tr>
                <th><label for="dm_license_status">Status</label></th>
                <td>
                    <select name="dm_license_status" id="dm_license_status">
                        <option value="active" <?php selected( $status, 'active' ); ?>>Active</option>
                        <option value="inactive" <?php selected( $status, 'inactive' ); ?>>Inactive</option>
                        <option value="expired" <?php selected( $status, 'expired' ); ?>>Expired</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="dm_license_expires">Expires On</label></th>
                <td><input type="date" id="dm_license_expires" name="dm_license_expires" value="<?php echo esc_attr( $expires ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="dm_license_max_domains">Max Activations</label></th>
                <td><input type="number" id="dm_license_max_domains" name="dm_license_max_domains" value="<?php echo esc_attr( $max_domains ); ?>" class="small-text"></td>
            </tr>
            <tr>
                <th><label for="dm_activated_domains">Activated Domains</label></th>
                <td><textarea id="dm_activated_domains" name="dm_activated_domains" class="large-text" rows="5"><?php echo esc_textarea( implode( "\n", (array) $activated_domains ) ); ?></textarea>
                <p class="description">One domain per line.</p></td>
            </tr>
        </table>
        <?php
    }

    /**
     * Save meta data when a license post is saved.
     */
    public function save_license_meta_data( $post_id ) {
        if ( ! isset( $_POST['dm_license_meta_nonce'] ) || ! wp_verify_nonce( $_POST['dm_license_meta_nonce'], 'dm_save_license_meta' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Save the post title as the license key meta for easy querying
        $post_title = get_the_title( $post_id );
        update_post_meta( $post_id, '_license_key', sanitize_text_field( $post_title ) );

        // Save other fields
        update_post_meta( $post_id, '_license_status', sanitize_text_field( $_POST['dm_license_status'] ) );
        update_post_meta( $post_id, '_license_expires', sanitize_text_field( $_POST['dm_license_expires'] ) );
        update_post_meta( $post_id, '_license_max_domains', intval( $_POST['dm_license_max_domains'] ) );

        $domains = ! empty( $_POST['dm_activated_domains'] ) ? explode( "\n", $_POST['dm_activated_domains'] ) : array();
        $domains = array_map( 'trim', $domains );
        $domains = array_filter( $domains );
        update_post_meta( $post_id, '_license_activated_domains', $domains );
    }

    /**
     * Add the settings page for managing update information.
     */
    public function add_settings_page() {
        add_submenu_page(
            'edit.php?post_type=license',
            'Plugin Update Settings',
            'Update Settings',
            'manage_options',
            'dm-update-settings',
            array( $this, 'render_settings_page' )
        );
    }

    /**
     * Render the settings page.
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1>Plugin Update Settings</h1>
            <form action="options.php" method="post">
                <?php
                settings_fields( 'dm_update_settings_group' );
                do_settings_sections( 'dm-update-settings' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register settings, sections, and fields.
     */
    public function register_settings() {
        register_setting( 'dm_update_settings_group', 'dm_latest_version' );
        register_setting( 'dm_update_settings_group', 'dm_download_link' );

        add_settings_section( 'dm_update_section', 'Plugin Details', null, 'dm-update-settings' );

        add_settings_field( 'dm_latest_version_field', 'Latest Version', array( $this, 'render_version_field' ), 'dm-update-settings', 'dm_update_section' );
        add_settings_field( 'dm_download_link_field', 'Download URL', array( $this, 'render_download_link_field' ), 'dm-update-settings', 'dm_update_section' );
    }

    public function render_version_field() {
        $value = get_option( 'dm_latest_version', '1.0.0' );
        echo '<input type="text" name="dm_latest_version" value="' . esc_attr( $value ) . '" class="regular-text" />';
    }

    public function render_download_link_field() {
        $value = get_option( 'dm_download_link', '' );
        echo '<input type="url" name="dm_download_link" value="' . esc_attr( $value ) . '" class="large-text" placeholder="https://your-site.com/path/to/plugin.zip" />';
    }

    /**
     * Register the custom REST API endpoint for license validation.
     */
    public function register_license_api_endpoint() {
        register_rest_route( 'license/v1', '/validate', array(
            'methods' => 'POST',
            'callback' => array( $this, 'validate_license_key' ),
            'permission_callback' => '__return_true',
        ) );
    }

    /**
     * Callback function to validate the license key.
     */
    public function validate_license_key( WP_REST_Request $request ) {
        $license_key = sanitize_text_field( $request->get_param( 'license_key' ) );
        $domain      = esc_url_raw( $request->get_param( 'domain' ) );

        $response_data = array(
            'status'         => 'invalid',
            'expires'        => null,
            'latest_version' => get_option( 'dm_latest_version', '1.0.0' ),
            'download_link'  => get_option( 'dm_download_link', '' ),
        );

        $args = array(
            'post_type' => 'license',
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'meta_query' => array(
                array(
                    'key' => '_license_key',
                    'value' => $license_key,
                )
            )
        );
        $license_posts = get_posts( $args );

        if ( empty( $license_posts ) ) {
            return new WP_REST_Response( $response_data, 403 );
        }

        $license_post_id = $license_posts[0]->ID;
        $status = get_post_meta( $license_post_id, '_license_status', true );
        $expires = get_post_meta( $license_post_id, '_license_expires', true );
        $max_domains = (int) get_post_meta( $license_post_id, '_license_max_domains', true );
        $activated_domains = (array) get_post_meta( $license_post_id, '_license_activated_domains', true );

        if ( 'inactive' === $status ) {
            $response_data['status'] = 'inactive';
            return new WP_REST_Response( $response_data, 403 );
        }

        if ( 'expired' === $status || ( ! empty( $expires ) && strtotime( $expires ) < time() ) ) {
            $response_data['status'] = 'expired';
            return new WP_REST_Response( $response_data, 403 );
        }

        if ( in_array( $domain, $activated_domains ) ) {
            $response_data['status'] = 'active';
            $response_data['expires'] = $expires;
        } else {
            if ( count( $activated_domains ) < $max_domains ) {
                $activated_domains[] = $domain;
                update_post_meta( $license_post_id, '_license_activated_domains', $activated_domains );
                $response_data['status'] = 'active';
                $response_data['expires'] = $expires;
            } else {
                $response_data['status'] = 'max_domains_reached';
                return new WP_REST_Response( $response_data, 403 );
            }
        }

        return new WP_REST_Response( $response_data, 200 );
    }
}

new DM_License_Server_Manager();
