<?php
/**
 * A printable list of deals based on filters.
 *
 * @package Deals_Manager
 */

// Load WordPress environment
require_once( realpath( '../../../wp-load.php' ) );

if ( ! current_user_can( 'edit_deals' ) ) {
    wp_die( esc_html__( 'You do not have permission to view this page.', 'deals-manager' ) );
}

$start_date = isset( $_GET['start_date'] ) ? sanitize_text_field( $_GET['start_date'] ) : '';
$end_date   = isset( $_GET['end_date'] ) ? sanitize_text_field( $_GET['end_date'] ) : '';
$user_id    = isset( $_GET['user_id'] ) ? (int) $_GET['user_id'] : 0;

$args = array(
    'post_type'      => 'deal',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
);

if ( $user_id ) {
    $args['author'] = $user_id;
}

if ( $start_date || $end_date ) {
    $args['date_query'] = array();
    if ( $start_date ) {
        $args['date_query']['after'] = $start_date;
    }
    if ( $end_date ) {
        $args['date_query']['before'] = $end_date;
    }
    $args['date_query']['inclusive'] = true;
}

$deals = new WP_Query( $args );

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php _e( 'Printable Deals Report', 'deals-manager' ); ?></title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <button class="no-print" onclick="window.print();"><?php _e( 'Print', 'deals-manager' ); ?></button>
    <h1><?php _e( 'Deals Report', 'deals-manager' ); ?></h1>
    <p>
        <?php
        if ( $start_date && $end_date ) {
            printf( '<strong>%s:</strong> %s to %s', esc_html__( 'Date Range', 'deals-manager' ), esc_html( $start_date ), esc_html( $end_date ) );
        }
        if ( $user_id ) {
            $user_info = get_userdata( $user_id );
            printf( '<br><strong>%s:</strong> %s', esc_html__( 'User', 'deals-manager' ), esc_html( $user_info->display_name ) );
        }
        ?>
    </p>
    <table>
        <thead>
            <tr>
                <th><?php _e( 'Deal', 'deals-manager' ); ?></th>
                <th><?php _e( 'Owner', 'deals-manager' ); ?></th>
                <th><?php _e( 'Stage', 'deals-manager' ); ?></th>
                <th><?php _e( 'Value', 'deals-manager' ); ?></th>
                <th><?php _e( 'Date Created', 'deals-manager' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( $deals->have_posts() ) : ?>
                <?php while ( $deals->have_posts() ) : $deals->the_post(); ?>
                    <?php
                    $deal_owner_id = get_the_author_meta( 'ID' );
                    $deal_owner_info = get_userdata( $deal_owner_id );
                    ?>
                    <tr>
                        <td><?php the_title(); ?></td>
                        <td><?php echo esc_html( $deal_owner_info->display_name ); ?></td>
                        <td><?php echo esc_html( get_post_meta( get_the_ID(), '_deal_stage', true ) ); ?></td>
                        <td>$<?php echo esc_html( number_format( (float) get_post_meta( get_the_ID(), '_deal_value', true ), 2 ) ); ?></td>
                        <td><?php echo get_the_date(); ?></td>
                    </tr>
                <?php endwhile; ?>
                <?php wp_reset_postdata(); ?>
            <?php else : ?>
                <tr>
                    <td colspan="5"><?php _e( 'No deals found matching your criteria.', 'deals-manager' ); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
