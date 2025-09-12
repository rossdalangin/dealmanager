<?php
/**
 * The file that defines the shortcodes for the plugin.
 *
 * @link       https://example.com
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
 * @author     Jules <you@example.com>
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
        // Handle form submission
        if ( isset( $_POST['dm_lead_form_submit'] ) && wp_verify_nonce( $_POST['dm_lead_form_nonce'], 'dm_lead_form' ) ) {
            $this->handle_form_submission();
        }

        ob_start();
        ?>
        <form action="" method="post" id="dm-lead-form">
            <?php wp_nonce_field( 'dm_lead_form', 'dm_lead_form_nonce' ); ?>
            <p>
                <label for="dm_name"><?php _e( 'Your Name', 'deals-manager' ); ?></label>
                <input type="text" name="dm_name" id="dm_name" required />
            </p>
            <p>
                <label for="dm_email"><?php _e( 'Your Email', 'deals-manager' ); ?></label>
                <input type="email" name="dm_email" id="dm_email" required />
            </p>
            <p>
                <label for="dm_phone"><?php _e( 'Your Phone', 'deals-manager' ); ?></label>
                <input type="text" name="dm_phone" id="dm_phone" />
            </p>
            <p>
                <label for="dm_message"><?php _e( 'Message', 'deals-manager' ); ?></label>
                <textarea name="dm_message" id="dm_message" rows="5"></textarea>
            </p>
            <p>
                <input type="submit" name="dm_lead_form_submit" value="<?php _e( 'Send', 'deals-manager' ); ?>" />
            </p>
        </form>
        <?php
        return ob_get_clean();
    }

    /**
     * Handle the lead form submission.
     *
     * @since 1.0.0
     */
    private function handle_form_submission() {
        $name = sanitize_text_field( $_POST['dm_name'] );
        $email = sanitize_email( $_POST['dm_email'] );
        $phone = sanitize_text_field( $_POST['dm_phone'] );
        $message = sanitize_textarea_field( $_POST['dm_message'] );

        // Create a new Contact
        $contact_id = wp_insert_post( array(
            'post_title' => $name,
            'post_type' => 'contact',
            'post_status' => 'publish',
        ) );

        if ( $contact_id && ! is_wp_error( $contact_id ) ) {
            update_post_meta( $contact_id, '_contact_email', $email );
            update_post_meta( $contact_id, '_contact_phone', $phone );

            // Create a new Deal
            $deal_title = sprintf( 'New Lead from %s', $name );
            $deal_id = wp_insert_post( array(
                'post_title' => $deal_title,
                'post_content' => $message,
                'post_type' => 'deal',
                'post_status' => 'publish',
            ) );

            if ( $deal_id && ! is_wp_error( $deal_id ) ) {
                update_post_meta( $deal_id, '_deal_stage', 'lead' );
                update_post_meta( $deal_id, '_deal_related_contact', $contact_id );
                echo '<p class="dm-success">' . __( 'Thank you for your submission!', 'deals-manager' ) . '</p>';
            } else {
                echo '<p class="dm-error">' . __( 'There was an error creating the deal.', 'deals-manager' ) . '</p>';
            }
        } else {
            echo '<p class="dm-error">' . __( 'There was an error creating the contact.', 'deals-manager' ) . '</p>';
        }
    }
}
