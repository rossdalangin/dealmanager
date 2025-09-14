<?php
/**
 * The file that defines the cron jobs for the plugin.
 *
 * @link       https://wordpresstitans.com
 * @since      1.0.0
 *
 * @package    Deals_Manager
 * @subpackage Deals_Manager/includes
 */

/**
 * The Cron class.
 *
 * This class is responsible for scheduling and managing cron events for the plugin,
 * such as sending email reminders.
 *
 * @since      1.0.0
 * @package    Deals_Manager
 * @subpackage Deals_Manager/includes
 * @author     Ross Dalangin
 */
class Deals_Manager_Cron {

    /**
     * Schedule cron events.
     *
     * This method is called on plugin activation to schedule the daily
     * cron event for sending task reminders.
     *
     * @since 1.0.0
     */
    public static function schedule_events() {
        if ( ! wp_next_scheduled( 'dm_daily_reminder_check' ) ) {
            wp_schedule_event( time(), 'daily', 'dm_daily_reminder_check' );
        }
    }

    /**
     * Unschedule cron events.
     *
     * This method is called on plugin deactivation to unschedule the
     * daily cron event.
     *
     * @since 1.0.0
     */
    public static function unschedule_events() {
        wp_clear_scheduled_hook( 'dm_daily_reminder_check' );
    }

    /**
     * Daily reminder check.
     *
     * This method is the callback for the 'dm_daily_reminder_check' cron job.
     * It fetches all tasks that are due the next day and sends an email
     * reminder to the task assignee.
     *
     * @since 1.0.0
     */
    public static function daily_reminder_check() {
        $args = array(
            'post_type'      => 'task',
            'posts_per_page' => -1,
            'meta_query'     => array(
                'relation' => 'AND',
                array(
                    'key'     => '_task_due_date',
                    'value'   => date( 'Y-m-d', strtotime( '+1 day' ) ), // Due tomorrow
                    'compare' => '=',
                    'type'    => 'DATE',
                ),
                array(
                    'key'     => '_task_status',
                    'value'   => 'completed',
                    'compare' => '!=',
                ),
            ),
        );

        $tasks = get_posts( $args );

        if ( ! empty( $tasks ) ) {
            foreach ( $tasks as $task ) {
                $user = get_user_by( 'id', $task->post_author );
                if ( $user ) {
                    $to = $user->user_email;
                    $subject = sprintf(
                        /* translators: %s: Task title */
                        __( 'Task Reminder: %s', 'deals-manager' ),
                        $task->post_title
                    );
                    $body = sprintf(
                        /* translators: %1$s: Task title, %2$s: Due date, %3$s: Task URL */
                        __( 'This is a reminder that the task "%1$s" is due tomorrow, %2$s. You can view the task here: %3$s', 'deals-manager' ),
                        $task->post_title,
                        get_post_meta( $task->ID, '_task_due_date', true ),
                        get_edit_post_link( $task->ID )
                    );
                    wp_mail( $to, $subject, $body );
                }
            }
        }
    }
}
