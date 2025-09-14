<?php
/**
 * Plugin Name: Sample License Server API
 * Description: A sample implementation of a WordPress REST API endpoint for validating and serving plugin licenses.
 * Version: 1.0
 * Author: Ross Dalangin
 *
 * Instructions:
 * 1. Install this file as a plugin on your main website (the license server).
 * 2. This code assumes you have a way to manage licenses (e.g., a custom database table or integration with a service like WooCommerce).
 * 3. For this example, licenses are stored in a simple hardcoded array. In a real-world scenario, you would replace this with a database query.
 * 4. The endpoint will be available at: https://yourdomain.com/wp-json/license/v1/validate
 */

// Hook into the REST API initialization
add_action( 'rest_api_init', 'dm_register_license_api_endpoint' );

/**
 * Register the custom REST API endpoint for license validation.
 */
function dm_register_license_api_endpoint() {
    register_rest_route( 'license/v1', '/validate', array(
        'methods' => 'POST',
        'callback' => 'dm_validate_license_key',
        'permission_callback' => '__return_true', // Publicly accessible, security is handled by license key check.
    ) );
}

/**
 * Callback function to validate the license key.
 *
 * @param WP_REST_Request $request The incoming request object.
 * @return WP_REST_Response The response object.
 */
function dm_validate_license_key( WP_REST_Request $request ) {

    // ---
    // 1. In a real application, you would query your database here.
    //    For this example, we'll use a hardcoded array of license keys.
    // ---
    $licenses = array(
        'VALID-KEY-12345' => array(
            'status'        => 'active',
            'expires'       => date('Y-m-d', strtotime('+1 year')),
            'domains'       => array( 'http://customer-site-1.com' ), // List of activated domains
            'max_domains'   => 1,
        ),
        'EXPIRED-KEY-67890' => array(
            'status'        => 'expired',
            'expires'       => date('Y-m-d', strtotime('-1 month')),
            'domains'       => array(),
            'max_domains'   => 1,
        ),
        'MULTI-SITE-KEY' => array(
            'status'        => 'active',
            'expires'       => date('Y-m-d', strtotime('+2 years')),
            'domains'       => array( 'http://site-a.com', 'http://site-b.com' ),
            'max_domains'   => 5,
        ),
    );

    // ---
    // 2. Get parameters from the client plugin's request.
    // ---
    $license_key = sanitize_text_field( $request->get_param( 'license_key' ) );
    $domain      = esc_url_raw( $request->get_param( 'domain' ) );

    // ---
    // 3. Prepare the response data.
    // ---
    $response_data = array(
        'status'         => 'inactive',
        'expires'        => null,
        'latest_version' => '1.1.0', // The latest version of your plugin
        'download_link'  => 'https://your-server.com/path/to/deals-manager-1.1.0.zip', // The direct download link
    );

    // ---
    // 4. Perform the license validation logic.
    // ---
    if ( ! array_key_exists( $license_key, $licenses ) ) {
        $response_data['status'] = 'invalid';
        return new WP_REST_Response( $response_data, 403 ); // 403 Forbidden - Invalid Key
    }

    $license = $licenses[$license_key];

    // Check if expired
    if ( 'expired' === $license['status'] || strtotime( $license['expires'] ) < time() ) {
        $response_data['status'] = 'expired';
        return new WP_REST_Response( $response_data, 403 );
    }

    // Check if domain is already activated
    if ( in_array( $domain, $license['domains'] ) ) {
        // Domain is already active and valid
        $response_data['status'] = 'active';
        $response_data['expires'] = $license['expires'];
    } else {
        // Domain is not yet activated for this key. Check if there's space.
        if ( count( $license['domains'] ) < $license['max_domains'] ) {
            // There is space. Activate it.
            // In a real app, you would save the new domain to the database here.
            // For example: $licenses[$license_key]['domains'][] = $domain; update_option('my_licenses', $licenses);
            $response_data['status'] = 'active'; // Activation successful
            $response_data['expires'] = $license['expires'];
        } else {
            // No more activations left for this key.
            $response_data['status'] = 'max_domains_reached';
            return new WP_REST_Response( $response_data, 403 );
        }
    }

    // ---
    // 5. Return the final response.
    // ---
    return new WP_REST_Response( $response_data, 200 );
}
