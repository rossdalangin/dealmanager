<?php
/**
 * The file that defines the sample data installer for the plugin.
 *
 * @link       https://wordpresstitans.com
 * @since      1.0.0
 *
 * @package    Deals_Manager
 * @subpackage Deals_Manager/includes
 */

/**
 * The Sample Data class.
 *
 * @since      1.0.0
 * @package    Deals_Manager
 * @subpackage Deals_Manager/includes
 * @author     Ross Dalangin
 */
class Deals_Manager_Sample_Data {

    /**
     * Install sample data.
     *
     * This method creates a set of sample data to demonstrate the plugin's
     * features. It should only be run once.
     *
     * @since 1.0.0
     */
    public static function install() {
        // Check if sample data has already been installed.
        if ( get_option( 'dm_sample_data_installed' ) ) {
            return;
        }

        // 1. Create users
        $sales_rep_id = wp_insert_user( array(
            'user_login' => 'salesrep',
            'user_pass'  => wp_generate_password(),
            'user_email' => 'salesrep@wordpresstitans.com',
            'first_name' => 'Sales',
            'last_name'  => 'Rep',
            'role'       => 'sales_rep',
        ) );

        $manager_id = wp_insert_user( array(
            'user_login' => 'manager',
            'user_pass'  => wp_generate_password(),
            'user_email' => 'manager@wordpresstitans.com',
            'first_name' => 'Manager',
            'last_name'  => 'User',
            'role'       => 'manager',
        ) );

        // 2. Create companies
        $company1_id = wp_insert_post( array( 'post_title' => 'Acme Inc.', 'post_type' => 'company', 'post_status' => 'publish' ) );
        update_post_meta( $company1_id, '_company_website', 'https://acme.wordpresstitans.com' );
        update_post_meta( $company1_id, '_company_address', "123 Main St\nAnytown, USA 12345" );

        $company2_id = wp_insert_post( array( 'post_title' => 'Stark Industries', 'post_type' => 'company', 'post_status' => 'publish' ) );
        update_post_meta( $company2_id, '_company_website', 'https://stark.wordpresstitans.com' );
        update_post_meta( $company2_id, '_company_address', "1 Stark Tower\nNew York, NY 10001" );

        // 3. Create contacts
        $contact1_id = wp_insert_post( array( 'post_title' => 'John Doe', 'post_type' => 'contact', 'post_status' => 'publish' ) );
        update_post_meta( $contact1_id, '_contact_email', 'john.doe@acme.wordpresstitans.com' );
        update_post_meta( $contact1_id, '_contact_phone', '555-1111' );
        update_post_meta( $contact1_id, '_contact_job_title', 'CEO' );

        $contact2_id = wp_insert_post( array( 'post_title' => 'Jane Smith', 'post_type' => 'contact', 'post_status' => 'publish' ) );
        update_post_meta( $contact2_id, '_contact_email', 'jane.smith@stark.wordpresstitans.com' );
        update_post_meta( $contact2_id, '_contact_phone', '555-2222' );
        update_post_meta( $contact2_id, '_contact_job_title', 'VP of Engineering' );

        // 4. Create deals
        $deal1_id = wp_insert_post( array( 'post_title' => 'Website Redesign Project', 'post_type' => 'deal', 'post_status' => 'publish', 'post_author' => $sales_rep_id ) );
        update_post_meta( $deal1_id, '_deal_value', '15000' );
        update_post_meta( $deal1_id, '_deal_owner', $sales_rep_id );
        update_post_meta( $deal1_id, '_deal_priority', 'high' );
        update_post_meta( $deal1_id, '_deal_stage', 'proposal' );

        $deal2_id = wp_insert_post( array( 'post_title' => 'Marketing Campaign', 'post_type' => 'deal', 'post_status' => 'publish', 'post_author' => $sales_rep_id ) );
        update_post_meta( $deal2_id, '_deal_value', '5000' );
        update_post_meta( $deal2_id, '_deal_owner', $sales_rep_id );
        update_post_meta( $deal2_id, '_deal_priority', 'normal' );
        update_post_meta( $deal2_id, '_deal_stage', 'lead' );

        $deal3_id = wp_insert_post( array( 'post_title' => 'Iron Man Suit Maintenance Contract', 'post_type' => 'deal', 'post_status' => 'publish', 'post_author' => $manager_id ) );
        update_post_meta( $deal3_id, '_deal_value', '1000000' );
        update_post_meta( $deal3_id, '_deal_owner', $manager_id );
        update_post_meta( $deal3_id, '_deal_priority', 'high' );
        update_post_meta( $deal3_id, '_deal_stage', 'won' );

        // 5. Create tasks
        $task1_id = wp_insert_post( array( 'post_title' => 'Follow up with John Doe', 'post_type' => 'task', 'post_status' => 'publish', 'post_author' => $sales_rep_id ) );
        update_post_meta( $task1_id, '_task_due_date', date( 'Y-m-d', strtotime( '+3 days' ) ) );
        update_post_meta( $task1_id, '_task_status', 'to-do' );
        update_post_meta( $task1_id, '_task_related_deal', $deal1_id );

        // 6. Create invoices
        $invoice1_id = wp_insert_post( array( 'post_title' => 'Invoice #1001', 'post_type' => 'invoice', 'post_status' => 'publish', 'post_author' => $manager_id ) );
        update_post_meta( $invoice1_id, '_invoice_amount', '1000000' );
        update_post_meta( $invoice1_id, '_invoice_status', 'paid' );
        update_post_meta( $invoice1_id, '_invoice_due_date', date( 'Y-m-d', strtotime( '-10 days' ) ) );
        update_post_meta( $invoice1_id, '_invoice_related_deal', $deal3_id );
        update_post_meta( $invoice1_id, '_invoice_related_company', $company2_id );

        // Mark sample data as installed
        update_option( 'dm_sample_data_installed', true );
    }
}
