<?php
/**
 * The template for displaying single invoices.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Deals_Manager
 * @subpackage Deals_Manager/templates/public
 */

get_header(); ?>

<style>
    .invoice-box {
        max-width: 800px;
        margin: auto;
        padding: 30px;
        border: 1px solid #eee;
        box-shadow: 0 0 10px rgba(0, 0, 0, .15);
        font-size: 16px;
        line-height: 24px;
        font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
        color: #555;
    }

    .invoice-box table {
        width: 100%;
        line-height: inherit;
        text-align: left;
    }

    .invoice-box table td {
        padding: 5px;
        vertical-align: top;
    }

    .invoice-box table tr td:nth-child(2) {
        text-align: right;
    }

    .invoice-box table tr.top table td {
        padding-bottom: 20px;
    }

    .invoice-box table tr.top table td.title {
        font-size: 45px;
        line-height: 45px;
        color: #333;
    }

    .invoice-box table tr.information table td {
        padding-bottom: 40px;
    }

    .invoice-box table tr.heading td {
        background: #eee;
        border-bottom: 1px solid #ddd;
        font-weight: bold;
    }

    .invoice-box table tr.details td {
        padding-bottom: 20px;
    }

    .invoice-box table tr.item td{
        border-bottom: 1px solid #eee;
    }

    .invoice-box table tr.item.last-child td {
        border-bottom: none;
    }

    .invoice-box table tr.total td:nth-child(2) {
        border-top: 2px solid #eee;
        font-weight: bold;
    }
</style>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <?php
        while ( have_posts() ) : the_post();

            $invoice_id = get_the_ID();
            $amount = get_post_meta( $invoice_id, '_invoice_amount', true );
            $status = get_post_meta( $invoice_id, '_invoice_status', true );
            $due_date = get_post_meta( $invoice_id, '_invoice_due_date', true );
            $related_deal_id = get_post_meta( $invoice_id, '_invoice_related_deal', true );
            $related_company_id = get_post_meta( $invoice_id, '_invoice_related_company', true );
            $line_items_raw = get_post_meta( $invoice_id, '_invoice_line_items', true );

            $company_name = $related_company_id ? get_the_title( $related_company_id ) : '';
            $company_address = $related_company_id ? get_post_meta( $related_company_id, '_company_address', true ) : '';

            ?>
            <div class="invoice-box">
                <table>
                    <tr class="top">
                        <td colspan="2">
                            <table>
                                <tr>
                                    <td class="title">
                                        <h1><?php _e( 'INVOICE', 'deals-manager' ); ?></h1>
                                    </td>
                                    <td>
                                        Invoice #: <?php echo esc_html( $invoice_id ); ?><br>
                                        Created: <?php echo get_the_date(); ?><br>
                                        Due: <?php echo esc_html( $due_date ); ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr class="information">
                        <td colspan="2">
                            <table>
                                <tr>
                                    <td>
                                        <strong><?php _e( 'Bill To:', 'deals-manager' ); ?></strong><br>
                                        <?php echo esc_html( $company_name ); ?><br>
                                        <?php echo nl2br( esc_html( $company_address ) ); ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr class="heading">
                        <td><?php _e( 'Payment Method', 'deals-manager' ); ?></td>
                        <td><?php _e( 'Status', 'deals-manager' ); ?></td>
                    </tr>

                    <tr class="details">
                        <td><?php _e( 'N/A', 'deals-manager' ); ?></td>
                        <td><?php echo esc_html( ucfirst( $status ) ); ?></td>
                    </tr>

                    <tr class="heading">
                        <td><?php _e( 'Item', 'deals-manager' ); ?></td>
                        <td><?php _e( 'Price', 'deals-manager' ); ?></td>
                    </tr>

                    <?php
                    $line_items = explode( "\n", $line_items_raw );
                    $total = 0;
                    foreach ( $line_items as $item ) {
                        $parts = explode( '|', $item );
                        if ( count( $parts ) === 3 ) {
                            $description = trim( $parts[0] );
                            $quantity = trim( $parts[1] );
                            $price = trim( $parts[2] );
                            $item_total = $quantity * $price;
                            $total += $item_total;
                            ?>
                            <tr class="item">
                                <td><?php echo esc_html( $description ); ?> (Qty: <?php echo esc_html( $quantity ); ?>)</td>
                                <td>$<?php echo esc_html( number_format( $item_total, 2 ) ); ?></td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                    <tr class="total">
                        <td></td>
                        <td>Total: $<?php echo esc_html( number_format( $total, 2 ) ); ?></td>
                    </tr>
                    <tr class="total">
                        <td></td>
                        <td><strong>Amount Due: $<?php echo esc_html( number_format( $amount, 2 ) ); ?></strong></td>
                    </tr>
                </table>
            </div>
            <?php
        endwhile;
        ?>
    </main>
</div>

<?php get_footer(); ?>
