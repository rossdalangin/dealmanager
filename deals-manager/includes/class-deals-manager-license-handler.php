<?php
/**
 * The file that defines the license handler class.
 *
 * @link       https://wordpresstitans.com
 * @since      1.0.0
 *
 * @package    Deals_Manager
 * @subpackage Deals_Manager/includes
 */

/**
 * The License Handler class.
 *
 * This class is responsible for handling the plugin's license activation,
 * deactivation, and validation.
 *
 * @since      1.0.0
 * @package    Deals_Manager
 * @subpackage Deals_Manager/includes
 * @author     Ross Dalangin
 */
class Deals_Manager_License_Handler {

    /**
     * The option name for storing the license key.
     * @var string
     */
    private $option_key = 'dm_license_key';

    /**
     * The option name for storing the license status.
     * @var string
     */
    private $status_option_key = 'dm_license_status';

    /**
     * The URL of the license validation server.
     * @var string
     */
    private $server_url = 'https://mydomain.com/wp-json/license/v1/validate';

    /**
     * The plugin file path.
     * @var string
     */
    private $plugin_file;


    public function __construct() {
        // We need the main plugin file path to get the plugin's version and base name.
        $this->plugin_file = dirname( dirname( __DIR__ ) ) . '/deals-manager.php';
    }

    /**
     * Add the license page to the admin menu.
     *
     * @since 1.0.0
     */
    public function add_license_page() {
        add_options_page(
            __( 'Deals Manager License', 'deals-manager' ),
            __( 'Deals Manager License', 'deals-manager' ),
            'manage_options',
            'deals-manager-license',
            array( $this, 'render_license_page' )
        );
    }

    /**
     * Render the license page.
     *
     * @since 1.0.0
     */
    public function render_license_page() {
        $license_key = get_option( $this->option_key, '' );
        $license_status = get_option( $this->status_option_key, 'inactive' );
        ?>
        <div class="wrap">
            <h1><?php _e( 'Deals Manager License Settings', 'deals-manager' ); ?></h1>
            <p><?php _e( 'Please enter your license key to activate the plugin and receive automatic updates.', 'deals-manager' ); ?></p>

            <?php
            // Display feedback messages
            if ( isset( $_GET['status'] ) ) {
                if ( 'success' === $_GET['status'] ) {
                    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'License activated successfully!', 'deals-manager' ) . '</p></div>';
                } elseif ( 'deactivated' === $_GET['status'] ) {
                     echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'License deactivated successfully.', 'deals-manager' ) . '</p></div>';
                } elseif ( 'error' === $_GET['status'] ) {
                    echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Invalid license key or an error occurred. Please try again.', 'deals-manager' ) . '</p></div>';
                }
            }
            ?>

            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <input type="hidden" name="action" value="dm_activate_license">
                <?php wp_nonce_field( 'dm_license_nonce', 'dm_license_nonce' ); ?>

                <table class="form-table">
                    <tbody>
                        <tr valign="top">
                            <th scope="row" valign="top">
                                <?php _e( 'License Key', 'deals-manager' ); ?>
                            </th>
                            <td>
                                <input type="text" id="<?php echo esc_attr( $this->option_key ); ?>" name="<?php echo esc_attr( $this->option_key ); ?>" value="<?php echo esc_attr( $license_key ); ?>" class="regular-text" <?php echo ( 'active' === $license_status ) ? 'disabled' : ''; ?> />
                                <p class="description">
                                    <?php _e( 'Enter your license key.', 'deals-manager' ); ?>
                                </p>
                            </td>
                        </tr>
                        <?php if ( ! empty( $license_key ) ) : ?>
                        <tr valign="top">
                            <th scope="row" valign="top">
                                <?php _e( 'License Status', 'deals-manager' ); ?>
                            </th>
                            <td>
                                <?php if ( 'active' === $license_status ) : ?>
                                    <span style="color: green; font-weight: bold;"><?php _e( 'Active', 'deals-manager' ); ?></span>
                                <?php else : ?>
                                    <span style="color: red; font-weight: bold;"><?php _e( 'Inactive', 'deals-manager' ); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <p class="submit">
                    <?php if ( 'active' === $license_status ) : ?>
                        <button type="submit" name="submit" id="submit" class="button button-secondary" formaction="<?php echo esc_url( admin_url( 'admin-post.php?action=dm_deactivate_license' ) ); ?>"><?php _e( 'Deactivate License', 'deals-manager' ); ?></button>
                    <?php else : ?>
                        <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( 'Activate License', 'deals-manager' ); ?>">
                    <?php endif; ?>
                </p>
            </form>
        </div>
        <?php
    }

    /**
     * Handle the license activation request.
     *
     * @since 1.0.0
     */
    public function handle_activation() {
        if ( ! isset( $_POST['dm_license_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['dm_license_nonce'] ), 'dm_license_nonce' ) ) {
            wp_die( 'Security check failed' );
        }

        $license_key = isset( $_POST[ $this->option_key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->option_key ] ) ) : '';
        $redirect_url = admin_url( 'options-general.php?page=deals-manager-license' );

        if ( empty( $license_key ) ) {
            wp_safe_redirect( add_query_arg( 'status', 'error', $redirect_url ) );
            exit;
        }

        $api_params = array(
            'license_key' => $license_key,
            'domain'      => home_url(),
            'plugin_version' => '1.0.0', // This should be dynamic later
        );

        $response = wp_remote_post( $this->server_url, array( 'timeout' => 15, 'sslverify' => true, 'body' => $api_params ) );

        if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
            wp_safe_redirect( add_query_arg( 'status', 'error', $redirect_url ) );
            exit;
        }

        $license_data = json_decode( wp_remote_retrieve_body( $response ) );

        if ( $license_data && isset( $license_data->status ) && 'active' === $license_data->status ) {
            update_option( $this->option_key, $license_key );
            update_option( $this->status_option_key, 'active' );
            wp_safe_redirect( add_query_arg( 'status', 'success', $redirect_url ) );
        } else {
            delete_option( $this->option_key );
            delete_option( $this->status_option_key );
            wp_safe_redirect( add_query_arg( 'status', 'error', $redirect_url ) );
        }
        exit;
    }

    /**
     * Handle the license deactivation request.
     *
     * @since 1.0.0
     */
    public function handle_deactivation() {
        if ( ! isset( $_POST['dm_license_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['dm_license_nonce'] ), 'dm_license_nonce' ) ) {
            wp_die( 'Security check failed' );
        }

        // For now, deactivation is just local. A real implementation would
        // send a request to the server to free up the license for another domain.
        delete_option( $this->option_key );
        delete_option( $this->status_option_key );

        $redirect_url = admin_url( 'options-general.php?page=deals-manager-license' );
        wp_safe_redirect( add_query_arg( 'status', 'deactivated', $redirect_url ) );
        exit;
    }

    /**
     * Display an admin notice if the license is not active.
     *
     * @since 1.0.0
     */
    public function show_license_notice() {
        $license_page_url = admin_url( 'options-general.php?page=deals-manager-license' );
        ?>
        <div class="notice notice-error">
            <p>
                <?php
                printf(
                    /* translators: %s: License page URL */
                    __( 'The <strong>Deals Manager</strong> license is not active. Please <a href="%s">activate your license</a> to enable all features and receive updates.', 'deals-manager' ),
                    esc_url( $license_page_url )
                );
                ?>
            </p>
        </div>
        <?php
    }

    /**
     * Check if the license is currently active.
     *
     * @since 1.0.0
     * @return bool True if active, false otherwise.
     */
    public static function is_license_active() {
        return 'active' === get_option( 'dm_license_status', 'inactive' );
    }

    /**
     * Hook into the update check transient.
     *
     * @param object $transient The update transient.
     * @return object The modified transient.
     */
    public function check_for_updates( $transient ) {
        if ( empty( $transient->checked ) ) {
            return $transient;
        }

        $plugin_data = get_plugin_data( $this->plugin_file );
        $current_version = $plugin_data['Version'];

        $request_args = array(
            'license_key' => get_option( $this->option_key ),
            'domain'      => home_url(),
        );

        // This should be a different endpoint, but we'll use the same for the example.
        $response = wp_remote_post( $this->server_url, array( 'body' => $request_args ) );

        if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
            $license_data = json_decode( wp_remote_retrieve_body( $response ) );

            if ( $license_data && version_compare( $license_data->latest_version, $current_version, '>' ) ) {
                $transient->response[ plugin_basename( $this->plugin_file ) ] = (object) array(
                    'slug'        => 'deals-manager',
                    'new_version' => $license_data->latest_version,
                    'url'         => 'https://wordpresstitans.com',
                    'package'     => $license_data->download_link,
                );
            }
        }

        return $transient;
    }

    /**
     * Hook into the plugin info API.
     *
     * @param bool|object|array $result The result object or array.
     * @param string            $action The type of information being requested.
     * @param object            $args   An object of arguments.
     * @return object The modified result object.
     */
    public function plugin_info( $result, $action, $args ) {
        if ( 'plugin_information' !== $action || empty( $args->slug ) || 'deals-manager' !== $args->slug ) {
            return $result;
        }

        // This would be another API call to get plugin details (e.g., changelog)
        // For now, we'll return a placeholder object.
        $result = (object) array(
            'name'          => 'Deals Manager',
            'slug'          => 'deals-manager',
            'version'       => '1.1.0', // Placeholder
            'author'        => 'Ross Dalangin',
            'download_link' => 'https://example.com/deals-manager-1.1.0.zip', // Placeholder
            'sections'      => array(
                'description' => 'A complete Deals & Lead Management System.',
                'changelog'   => '<p><strong>Version 1.1.0</strong> - Added new features and bug fixes.</p>',
            ),
        );

        return $result;
    }
}
