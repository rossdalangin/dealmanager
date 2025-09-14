<?php
/**
 * The file that defines the roles and capabilities.
 *
 * @link       https://wordpresstitans.com
 * @since      1.0.0
 *
 * @package    Deals_Manager
 * @subpackage Deals_Manager/includes
 */

/**
 * The Roles and Capabilities class.
 *
 * This class is responsible for creating and removing the custom user roles
 * and their associated capabilities for the plugin.
 *
 * @since      1.0.0
 * @package    Deals_Manager
 * @subpackage Deals_Manager/includes
 * @author     Ross Dalangin
 */
class Deals_Manager_Roles {

    /**
     * Add the custom roles and capabilities.
     *
     * This method is called on plugin activation. It creates the 'Sales Rep'
     * and 'Manager' roles and assigns them the necessary capabilities to
     * interact with the plugin's custom post types. It also grants all
     * custom capabilities to the 'Administrator' role.
     *
     * @since 1.0.0
     */
    public static function add_roles() {
        $cpts = array( 'deal', 'contact', 'company', 'task', 'invoice' );
        $sales_rep_caps = array(
            'read' => true,
            'upload_files' => true,
        );
        $manager_caps = array();

        // Define capabilities for each CPT
        foreach ( $cpts as $cpt ) {
            $sales_rep_caps["edit_{$cpt}"] = true;
            $sales_rep_caps["edit_{$cpt}s"] = true;
            $sales_rep_caps["publish_{$cpt}s"] = true;
            $sales_rep_caps["edit_published_{$cpt}s"] = true;
            $sales_rep_caps["delete_{$cpt}"] = true;
            $sales_rep_caps["delete_{$cpt}s"] = true;
            $sales_rep_caps["delete_published_{$cpt}s"] = true;

            $manager_caps["edit_others_{$cpt}s"] = true;
            $manager_caps["delete_others_{$cpt}s"] = true;
            $manager_caps["read_private_{$cpt}s"] = true;
            $manager_caps["delete_private_{$cpt}s"] = true;
            $manager_caps["edit_private_{$cpt}s"] = true;
        }

        // Add the 'Sales Rep' role
        add_role( 'sales_rep', __( 'Sales Rep', 'deals-manager' ), $sales_rep_caps );

        // Add the 'Manager' role
        $manager_total_caps = array_merge( $sales_rep_caps, $manager_caps );
        add_role( 'manager', __( 'Manager', 'deals-manager' ), $manager_total_caps );

        // Add all caps to administrator
        $admin_role = get_role( 'administrator' );
        if ( $admin_role ) {
            $all_caps = array_merge( $sales_rep_caps, $manager_caps );
            foreach ( $all_caps as $cap => $grant ) {
                $admin_role->add_cap( $cap );
            }
        }
    }

    /**
     * Remove the custom roles and capabilities.
     *
     * This method is called on plugin deactivation. It removes the custom
     * roles and their capabilities to clean up the site.
     *
     * @since 1.0.0
     */
    public static function remove_roles() {
        remove_role( 'sales_rep' );
        remove_role( 'manager' );

        // Remove all caps from administrator
        $admin_role = get_role( 'administrator' );
        if ( $admin_role ) {
            $cpts = array( 'deal', 'contact', 'company', 'task', 'invoice' );
            $all_caps = array();
            foreach ( $cpts as $cpt ) {
                $all_caps[] = "edit_{$cpt}";
                $all_caps[] = "edit_{$cpt}s";
                $all_caps[] = "publish_{$cpt}s";
                $all_caps[] = "edit_published_{$cpt}s";
                $all_caps[] = "delete_{$cpt}";
                $all_caps[] = "delete_{$cpt}s";
                $all_caps[] = "delete_published_{$cpt}s";
                $all_caps[] = "edit_others_{$cpt}s";
                $all_caps[] = "delete_others_{$cpt}s";
                $all_caps[] = "read_private_{$cpt}s";
                $all_caps[] = "delete_private_{$cpt}s";
                $all_caps[] = "edit_private_{$cpt}s";
            }
            foreach ( $all_caps as $cap ) {
                $admin_role->remove_cap( $cap );
            }
        }
    }
}
