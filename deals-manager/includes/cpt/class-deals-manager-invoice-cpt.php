<?php
/**
 * The file that defines the Invoice custom post type.
 *
 * @link       https://wordpresstitans.com
 * @since      1.0.0
 *
 * @package    Deals_Manager
 * @subpackage Deals_Manager/includes/cpt
 */

/**
 * The Invoice Custom Post Type class.
 *
 * @since      1.0.0
 * @package    Deals_Manager
 * @subpackage Deals_Manager/includes/cpt
 * @author     Ross Dalangin
 */
class Deals_Manager_Invoice_CPT {

    /**
     * The name for the custom post type.
     * @var string
     */
    private $post_type = 'invoice';

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
            'name'                  => _x( 'Invoices', 'Post type general name', 'deals-manager' ),
            'singular_name'         => _x( 'Invoice', 'Post type singular name', 'deals-manager' ),
            'menu_name'             => _x( 'Invoices', 'Admin Menu text', 'deals-manager' ),
            'name_admin_bar'        => _x( 'Invoice', 'Add New on Toolbar', 'deals-manager' ),
            'add_new'               => __( 'Add New', 'deals-manager' ),
            'add_new_item'          => __( 'Add New Invoice', 'deals-manager' ),
            'new_item'              => __( 'New Invoice', 'deals-manager' ),
            'edit_item'             => __( 'Edit Invoice', 'deals-manager' ),
            'view_item'             => __( 'View Invoice', 'deals-manager' ),
            'all_items'             => __( 'All Invoices', 'deals-manager' ),
            'search_items'          => __( 'Search Invoices', 'deals-manager' ),
            'parent_item_colon'     => __( 'Parent Invoices:', 'deals-manager' ),
            'not_found'             => __( 'No invoices found.', 'deals-manager' ),
            'not_found_in_trash'    => __( 'No invoices found in Trash.', 'deals-manager' ),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => 'deals-manager',
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'invoice' ),
            'capability_type'    => 'invoice',
            'map_meta_cap'       => true,
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'menu_icon'          => 'dashicons-media-text',
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
            'invoice_details',
            __( 'Invoice Details', 'deals-manager' ),
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
        wp_nonce_field( 'invoice_details_meta_box', 'invoice_details_meta_box_nonce' );

        $amount = get_post_meta( $post->ID, '_invoice_amount', true );
        $status = get_post_meta( $post->ID, '_invoice_status', true );
        $due_date = get_post_meta( $post->ID, '_invoice_due_date', true );
        $related_deal = get_post_meta( $post->ID, '_invoice_related_deal', true );
        $related_company = get_post_meta( $post->ID, '_invoice_related_company', true );
        $line_items = get_post_meta( $post->ID, '_invoice_line_items', true );

        ?>
        <p>
            <label for="invoice_amount"><?php _e( 'Amount', 'deals-manager' ); ?></label>
            <input type="text" id="invoice_amount" name="invoice_amount" value="<?php echo esc_attr( $amount ); ?>" size="25" />
        </p>
        <p>
            <label for="invoice_status"><?php _e( 'Status', 'deals-manager' ); ?></label>
            <select name="invoice_status" id="invoice_status">
                <option value="draft" <?php selected( $status, 'draft' ); ?>><?php _e( 'Draft', 'deals-manager' ); ?></option>
                <option value="sent" <?php selected( $status, 'sent' ); ?>><?php _e( 'Sent', 'deals-manager' ); ?></option>
                <option value="paid" <?php selected( $status, 'paid' ); ?>><?php _e( 'Paid', 'deals-manager' ); ?></option>
                <option value="cancelled" <?php selected( $status, 'cancelled' ); ?>><?php _e( 'Cancelled', 'deals-manager' ); ?></option>
            </select>
        </p>
        <p>
            <label for="invoice_due_date"><?php _e( 'Due Date', 'deals-manager' ); ?></label>
            <input type="date" id="invoice_due_date" name="invoice_due_date" value="<?php echo esc_attr( $due_date ); ?>" />
        </p>
        <hr>
        <p>
            <label for="invoice_related_deal"><?php _e( 'Related Deal', 'deals-manager' ); ?></label>
            <select name="invoice_related_deal" id="invoice_related_deal">
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
            <label for="invoice_related_company"><?php _e( 'Related Company', 'deals-manager' ); ?></label>
            <select name="invoice_related_company" id="invoice_related_company">
                <option value=""><?php _e( 'None', 'deals-manager' ); ?></option>
                <?php
                $companies = get_posts( array( 'post_type' => 'company', 'numberposts' => -1 ) );
                foreach ( $companies as $company ) {
                    echo '<option value="' . esc_attr( $company->ID ) . '"' . selected( $related_company, $company->ID, false ) . '>' . esc_html( $company->post_title ) . '</option>';
                }
                ?>
            </select>
        </p>
        <hr>
        <h4><?php _e( 'Line Items', 'deals-manager' ); ?></h4>
        <table id="invoice-line-items-wrapper" class="wp-list-table widefat fixed">
            <thead>
                <tr>
                    <th style="width: 50%;"><?php _e( 'Description', 'deals-manager' ); ?></th>
                    <th style="width: 15%;"><?php _e( 'Quantity', 'deals-manager' ); ?></th>
                    <th style="width: 15%;"><?php _e( 'Price', 'deals-manager' ); ?></th>
                    <th style="width: 15%;"><?php _e( 'Total', 'deals-manager' ); ?></th>
                    <th style="width: 5%;"></th>
                </tr>
            </thead>
            <tbody id="line-items-container">
                <?php
                if ( is_array( $line_items ) && ! empty( $line_items ) ) :
                    foreach ( $line_items as $i => $item ) :
                ?>
                        <tr class="line-item">
                            <td><input type="text" name="line_items[<?php echo esc_attr( $i ); ?>][description]" value="<?php echo esc_attr( $item['description'] ); ?>" class="widefat" /></td>
                            <td><input type="number" step="1" min="0" name="line_items[<?php echo esc_attr( $i ); ?>][quantity]" value="<?php echo esc_attr( $item['quantity'] ); ?>" class="widefat line-item-quantity" /></td>
                            <td><input type="number" step="0.01" min="0" name="line_items[<?php echo esc_attr( $i ); ?>][price]" value="<?php echo esc_attr( $item['price'] ); ?>" class="widefat line-item-price" /></td>
                            <td><span class="line-item-total"><?php echo esc_html( number_format( (float) $item['quantity'] * (float) $item['price'], 2 ) ); ?></span></td>
                            <td><a href="#" class="remove-line-item button">&times;</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" style="text-align:right;"><?php _e( 'Subtotal:', 'deals-manager' ); ?></th>
                    <th id="invoice-subtotal">0.00</th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
        <p>
            <a href="#" id="add-line-item" class="button"><?php _e( 'Add Item', 'deals-manager' ); ?></a>
        </p>

        <script type="text/template" id="tmpl-line-item-template">
            <tr class="line-item">
                <td><input type="text" name="line_items[{index}][description]" class="widefat" /></td>
                <td><input type="number" step="1" min="0" name="line_items[{index}][quantity]" value="1" class="widefat line-item-quantity" /></td>
                <td><input type="number" step="0.01" min="0" name="line_items[{index}][price]" value="0.00" class="widefat line-item-price" /></td>
                <td><span class="line-item-total">0.00</span></td>
                <td><a href="#" class="remove-line-item button">&times;</a></td>
            </tr>
        </script>
        <?php
    }

    /**
     * Save the meta when the post is saved.
     *
     * @param int $post_id The ID of the post being saved.
     */
    public function save_meta_data( $post_id ) {
        if ( ! isset( $_POST['invoice_details_meta_box_nonce'] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( $_POST['invoice_details_meta_box_nonce'], 'invoice_details_meta_box' ) ) {
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

        if ( isset( $_POST['invoice_amount'] ) ) {
            update_post_meta( $post_id, '_invoice_amount', sanitize_text_field( $_POST['invoice_amount'] ) );
        }

        if ( isset( $_POST['invoice_status'] ) ) {
            update_post_meta( $post_id, '_invoice_status', sanitize_text_field( $_POST['invoice_status'] ) );
        }

        if ( isset( $_POST['invoice_due_date'] ) ) {
            update_post_meta( $post_id, '_invoice_due_date', sanitize_text_field( $_POST['invoice_due_date'] ) );
        }

        if ( isset( $_POST['invoice_related_deal'] ) ) {
            update_post_meta( $post_id, '_invoice_related_deal', sanitize_text_field( $_POST['invoice_related_deal'] ) );
        }

        if ( isset( $_POST['invoice_related_company'] ) ) {
            update_post_meta( $post_id, '_invoice_related_company', sanitize_text_field( $_POST['invoice_related_company'] ) );
        }

        if ( isset( $_POST['line_items'] ) && is_array( $_POST['line_items'] ) ) {
			$sanitized_line_items = array();
			foreach ( $_POST['line_items'] as $item ) {
				if ( ! empty( $item['description'] ) ) {
					$sanitized_line_items[] = array(
						'description' => sanitize_text_field( $item['description'] ),
						'quantity'    => (int) $item['quantity'],
						'price'       => (float) $item['price'],
					);
				}
			}
			update_post_meta( $post_id, '_invoice_line_items', $sanitized_line_items );
		} else {
			// If no line items are submitted, delete the meta.
			delete_post_meta( $post_id, '_invoice_line_items' );
		}
    }
}
