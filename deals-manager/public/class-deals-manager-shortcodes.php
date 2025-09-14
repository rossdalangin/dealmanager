<?php
/**
 * The file that defines the shortcodes for the plugin.
 *
 * @link       https://wordpresstitans.com
 * @since      1.0.0
 *
 * @package    Deals_Manager
 * @subpackage Deals_Manager/public
 */

/**
 * The Shortcodes class.
 *
 * @since      1.0.0
 * @package    Deals_Manager
 * @subpackage Deals_Manager/public
 * @author     Ross Dalangin
 */
class Deals_Manager_Shortcodes {

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        add_shortcode( 'dm_lead_form', array( $this, 'render_lead_form' ) );
    }

    /**
     * Render the lead capture form.
     *
     * @since 1.0.0
     * @param array $atts Shortcode attributes.
     * @return string The form HTML.
     */
    public function render_lead_form( $atts ) {
        ob_start();
        ?>
        <div class="dm-lead-form-container">
            <h3><?php _e( 'Contact Us', 'deals-manager' ); ?></h3>

            <?php
            // Display success or error messages based on URL query parameter
            if ( isset( $_GET['submission'] ) ) {
                if ( 'success' === $_GET['submission'] ) {
                    echo '<div class="dm-lead-form-message success">' . esc_html__( 'Thank you for your submission!', 'deals-manager' ) . '</div>';
                } else {
                    echo '<div class="dm-lead-form-message error">' . esc_html__( 'There was an error with your submission. Please try again.', 'deals-manager' ) . '</div>';
                }
            }
            ?>

            <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post" id="dm-lead-form">
                <input type="hidden" name="action" value="dm_handle_lead_form">
                <input type="hidden" name="dm_redirect_url" value="<?php echo esc_url( get_permalink() ); ?>">
                <?php wp_nonce_field( 'dm_lead_form_nonce_action', 'dm_lead_form_nonce' ); ?>
                <input type="hidden" name="dm_ref_user" value="<?php echo isset( $_GET['ref'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET['ref'] ) ) ) : ''; ?>">
                <div class="form-row">
                    <label for="dm_name"><?php _e( 'Your Name', 'deals-manager' ); ?></label>
                    <input type="text" name="dm_name" id="dm_name" required />
                </div>
                <div class="form-row">
                    <label for="dm_email"><?php _e( 'Your Email', 'deals-manager' ); ?></label>
                    <input type="email" name="dm_email" id="dm_email" required />
                </div>
                <div class="form-row">
                    <label for="dm_phone"><?php _e( 'Your Phone', 'deals-manager' ); ?></label>
                    <input type="text" name="dm_phone" id="dm_phone" />
                </div>
                <div class="form-row">
                    <label for="dm_message"><?php _e( 'Message', 'deals-manager' ); ?></label>
                    <textarea name="dm_message" id="dm_message" rows="5"></textarea>
                </div>
                <div class="form-row">
                    <input type="submit" name="dm_lead_form_submit" class="dm-submit-button" value="<?php _e( 'Send Inquiry', 'deals-manager' ); ?>" />
                </div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Handle the lead form submission via admin-post.
     *
     * @since 1.0.0
     */
    public function process_lead_form_submission() {
        // Verify nonce
        if ( ! isset( $_POST['dm_lead_form_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['dm_lead_form_nonce'] ), 'dm_lead_form_nonce_action' ) ) {
            wp_die( 'Security check failed.' );
        }

        $redirect_url = isset( $_POST['dm_redirect_url'] ) ? esc_url_raw( $_POST['dm_redirect_url'] ) : home_url();

        $name = sanitize_text_field( $_POST['dm_name'] );
        $email = sanitize_email( $_POST['dm_email'] );
        $phone = sanitize_text_field( $_POST['dm_phone'] );
        $message = sanitize_textarea_field( $_POST['dm_message'] );
        $ref_user_login = isset( $_POST['dm_ref_user'] ) ? sanitize_text_field( $_POST['dm_ref_user'] ) : '';

        $author_id = 1; // Default to admin
        if ( ! empty( $ref_user_login ) ) {
            $user = get_user_by( 'login', $ref_user_login );
            if ( $user ) {
                $author_id = $user->ID;
            }
        }

        // Check if contact with this email already exists
        $existing_contacts = get_posts( array(
            'post_type' => 'contact',
            'meta_key' => '_contact_email',
            'meta_value' => $email,
            'posts_per_page' => 1,
            'fields' => 'ids',
        ) );

        if ( ! empty( $existing_contacts ) ) {
            $contact_id = $existing_contacts[0];
        } else {
            $contact_id = wp_insert_post( array(
                'post_title' => $name,
                'post_type' => 'contact',
                'post_status' => 'publish',
                'post_author' => $author_id,
            ) );

            if ( $contact_id && ! is_wp_error( $contact_id ) ) {
                update_post_meta( $contact_id, '_contact_email', $email );
                update_post_meta( $contact_id, '_contact_phone', $phone );
            }
        }

        if ( ! $contact_id || is_wp_error( $contact_id ) ) {
            wp_safe_redirect( add_query_arg( 'submission', 'error', $redirect_url ) );
            exit;
        }

        // Create a new Deal associated with the contact
        $deal_title = sprintf( 'New Lead from %s', $name );
        $deal_id = wp_insert_post( array(
            'post_title' => $deal_title,
            'post_content' => $message,
            'post_type' => 'deal',
            'post_status' => 'publish',
            'post_author' => $author_id,
        ) );

        if ( ! $deal_id || is_wp_error( $deal_id ) ) {
            wp_safe_redirect( add_query_arg( 'submission', 'error', $redirect_url ) );
            exit;
        }

        update_post_meta( $deal_id, '_deal_stage', 'lead' );
        update_post_meta( $deal_id, '_deal_related_contact', $contact_id );
        update_post_meta( $deal_id, '_deal_owner', $author_id );

        wp_safe_redirect( add_query_arg( 'submission', 'success', $redirect_url ) );
        exit;
    }
}
