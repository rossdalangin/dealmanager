<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Deals_Manager
 * @subpackage Deals_Manager/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Deals_Manager
 * @subpackage Deals_Manager/admin
 * @author     Jules <you@example.com>
 */
class Deals_Manager_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles( $hook ) {
		// Load main admin CSS.
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/deals-manager-admin.css', array(), $this->version, 'all' );

		// Load Kanban CSS only on the pipeline page.
		if ( 'deals-manager_page_deals-manager-pipeline' === $hook ) {
			wp_enqueue_style( $this->plugin_name . '-kanban', plugin_dir_url( __FILE__ ) . 'css/deals-manager-kanban.css', array(), $this->version, 'all' );
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts( $hook ) {
		// Load Kanban JS only on the pipeline page.
		if ( 'deals-manager_page_deals-manager-pipeline' === $hook ) {
			wp_enqueue_script( $this->plugin_name . '-kanban', plugin_dir_url( __FILE__ ) . 'js/deals-manager-kanban.js', array( 'jquery', 'jquery-ui-sortable' ), $this->version, true );

			wp_localize_script(
				$this->plugin_name . '-kanban',
				'kanban_ajax',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'kanban-nonce' ),
				)
			);
		}

		// Load Reports JS only on the reports page.
		if ( 'deals-manager_page_deals-manager-reports' === $hook ) {
			wp_register_script( 'chartjs', 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.5.0/chart.umd.min.js', array(), '4.5.0', true );
			wp_enqueue_script( $this->plugin_name . '-reports', plugin_dir_url( __FILE__ ) . 'js/deals-manager-reports.js', array( 'jquery', 'chartjs' ), $this->version, true );

			// Fetch and prepare data for reports
			$deals_by_stage  = array();
			$deals_by_user   = array();
			$total_won_value = 0;

			$stages = array(
				'lead'        => __( 'Lead', 'deals-manager' ),
				'proposal'    => __( 'Proposal', 'deals-manager' ),
				'negotiation' => __( 'Negotiation', 'deals-manager' ),
				'won'         => __( 'Won', 'deals-manager' ),
				'lost'        => __( 'Lost', 'deals-manager' ),
			);

			foreach ( array_keys( $stages ) as $stage_key ) {
				$deals_by_stage[ $stage_key ] = 0;
			}

			$args = array(
				'post_type'      => 'deal',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			);
			$deals = new WP_Query( $args );

			if ( $deals->have_posts() ) {
				while ( $deals->have_posts() ) {
					$deals->the_post();
					$deal_id = get_the_ID();
					$stage   = get_post_meta( $deal_id, '_deal_stage', true );
					$stage   = $stage ? $stage : 'lead';

					if ( array_key_exists( $stage, $deals_by_stage ) ) {
						$deals_by_stage[ $stage ]++;
					}

					$author_id = get_the_author_meta( 'ID' );
					if ( ! isset( $deals_by_user[ $author_id ] ) ) {
						$deals_by_user[ $author_id ] = array(
							'name'  => get_the_author(),
							'count' => 0,
						);
					}
					$deals_by_user[ $author_id ]['count']++;

					if ( 'won' === $stage ) {
						$total_won_value += (float) get_post_meta( $deal_id, '_deal_value', true );
					}
				}
				wp_reset_postdata();
			}

			// Prepare data for JS
			$report_data = array(
				'deals_by_stage'  => array(
					'labels' => array_values( $stages ),
					'data'   => array_values( $deals_by_stage ),
				),
				'deals_by_user'   => array_values( $deals_by_user ),
				'summary'         => array(
					'total_deals'     => $deals->found_posts,
					'total_won_value' => number_format( $total_won_value, 2 ),
					'conversion_rate' => ( $deals->found_posts > 0 ) ? round( ( $deals_by_stage['won'] / $deals->found_posts ) * 100, 2 ) : 0,
				),
			);

			wp_localize_script(
				$this->plugin_name . '-reports',
				'reports_data',
				$report_data
			);
		}
	}

	/**
	 * Add filters to the CPT list tables.
	 *
	 * @param string $post_type The current post type.
	 */
	public function add_cpt_filters( $post_type ) {
		if ( 'deal' === $post_type ) {
			// Stage filter
			$stages        = array( 'lead', 'proposal', 'negotiation', 'won', 'lost' );
			$current_stage = isset( $_GET['deal_stage'] ) ? sanitize_text_field( wp_unslash( $_GET['deal_stage'] ) ) : '';
			echo "<select name='deal_stage' id='deal_stage'>";
			echo "<option value=''>" . esc_html__( 'All Stages', 'deals-manager' ) . '</option>';
			foreach ( $stages as $stage ) {
				printf(
					'<option value="%s"%s>%s</option>',
					esc_attr( $stage ),
					$stage === $current_stage ? ' selected="selected"' : '',
					esc_html( ucfirst( $stage ) )
				);
			}
			echo '</select>';

			// Priority filter
			$priorities        = array( 'low', 'normal', 'high' );
			$current_priority = isset( $_GET['deal_priority'] ) ? sanitize_text_field( wp_unslash( $_GET['deal_priority'] ) ) : '';
			echo "<select name='deal_priority' id='deal_priority'>";
			echo "<option value=''>" . esc_html__( 'All Priorities', 'deals-manager' ) . '</option>';
			foreach ( $priorities as $priority ) {
				printf(
					'<option value="%s"%s>%s</option>',
					esc_attr( $priority ),
					$priority === $current_priority ? ' selected="selected"' : '',
					esc_html( ucfirst( $priority ) )
				);
			}
			echo '</select>';

			// Owner filter
			wp_dropdown_users(
				array(
					'show_option_all' => 'All Owners',
					'name'            => 'deal_owner',
					'selected'        => isset( $_GET['deal_owner'] ) ? (int) $_GET['deal_owner'] : 0,
				)
			);
		}

		if ( 'task' === $post_type ) {
			// Status filter
			$statuses       = array( 'to-do', 'in-progress', 'completed' );
			$current_status = isset( $_GET['task_status'] ) ? sanitize_text_field( wp_unslash( $_GET['task_status'] ) ) : '';
			echo "<select name='task_status'>";
			echo "<option value=''>" . esc_html__( 'All Statuses', 'deals-manager' ) . '</option>';
			foreach ( $statuses as $status ) {
				printf(
					'<option value="%s"%s>%s</option>',
					esc_attr( $status ),
					$status === $current_status ? ' selected="selected"' : '',
					esc_html( ucfirst( str_replace( '-', ' ', $status ) ) )
				);
			}
			echo '</select>';

			// Due date filter
			$current_due = isset( $_GET['task_due'] ) ? sanitize_text_field( wp_unslash( $_GET['task_due'] ) ) : '';
			echo "<select name='task_due'>";
			echo "<option value=''>" . esc_html__( 'All Due Dates', 'deals-manager' ) . '</option>';
			echo "<option value='overdue'" . selected( 'overdue', $current_due, false ) . '>' . esc_html__( 'Overdue', 'deals-manager' ) . '</option>';
			echo '</select>';
		}
	}

	/**
	 * Handle CPT list table filters.
	 *
	 * @param WP_Query $query The WP_Query instance.
	 */
	public function handle_cpt_list_filters( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		global $typenow;

		// Sales Rep filter
		$user = wp_get_current_user();
		if ( in_array( 'sales_rep', (array) $user->roles, true ) ) {
			$cpts = array( 'deal', 'contact', 'company', 'task', 'invoice' );
			if ( in_array( $typenow, $cpts, true ) ) {
				$query->set( 'author', $user->ID );
			}
		}

		// Deal filters
		if ( 'deal' === $typenow ) {
			$meta_query = $query->get( 'meta_query' ) ?: array();

			if ( ! empty( $_GET['deal_stage'] ) ) {
				$meta_query[] = array(
					'key'   => '_deal_stage',
					'value' => sanitize_text_field( wp_unslash( $_GET['deal_stage'] ) ),
				);
			}
			if ( ! empty( $_GET['deal_priority'] ) ) {
				$meta_query[] = array(
					'key'   => '_deal_priority',
					'value' => sanitize_text_field( wp_unslash( $_GET['deal_priority'] ) ),
				);
			}
			if ( ! empty( $_GET['deal_owner'] ) ) {
				$meta_query[] = array(
					'key'   => '_deal_owner',
					'value' => (int) $_GET['deal_owner'],
				);
			}

			if ( ! empty( $meta_query ) ) {
				$query->set( 'meta_query', $meta_query );
			}
		}

		// Task filters
		if ( 'task' === $typenow ) {
			$meta_query = $query->get( 'meta_query' ) ?: array();

			if ( ! empty( $_GET['task_status'] ) ) {
				$meta_query[] = array(
					'key'   => '_task_status',
					'value' => sanitize_text_field( wp_unslash( $_GET['task_status'] ) ),
				);
			}

			if ( ! empty( $_GET['task_due'] ) && 'overdue' === $_GET['task_due'] ) {
				$meta_query[] = array(
					'key'     => '_task_due_date',
					'value'   => date( 'Y-m-d' ),
					'compare' => '<',
					'type'    => 'DATE',
				);
				$meta_query[] = array(
					'key'     => '_task_due_date',
					'value'   => '',
					'compare' => '!=',
				);
			}

			if ( ! empty( $meta_query ) ) {
				$query->set( 'meta_query', $meta_query );
			}
		}
	}

	/**
	 * Extend the CPT search to include custom fields.
	 *
	 * @param WP_Query $query The WP_Query instance.
	 */
	public function extend_cpt_search( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
			return;
		}

		$cpts = array( 'deal', 'contact', 'company', 'task' );
		if ( in_array( $query->get( 'post_type' ), $cpts, true ) ) {
			add_filter( 'posts_join', array( $this, 'search_join' ) );
			add_filter( 'posts_where', array( $this, 'search_where' ) );
		}
	}

	/**
	 * Join postmeta table for search.
	 *
	 * @param string $join The JOIN clause.
	 * @return string
	 */
	public function search_join( $join ) {
		global $wpdb;
		$join .= " LEFT JOIN {$wpdb->postmeta} ON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id ";
		return $join;
	}

	/**
	 * Modify the search WHERE clause.
	 *
	 * @param string $where The WHERE clause.
	 * @return string
	 */
	public function search_where( $where ) {
		global $wpdb;
		$search_term = $wpdb->prepare( '%s', '%' . get_query_var( 's' ) . '%' );
		$where      .= $wpdb->prepare( " OR ({$wpdb->postmeta}.meta_value LIKE %s)", $search_term );

		// Remove the filters to avoid affecting other queries.
		remove_filter( 'posts_join', array( $this, 'search_join' ) );
		remove_filter( 'posts_where', array( $this, 'search_where' ) );
		return $where;
	}

	/**
	 * Add a widget to the dashboard.
	 */
	public function add_dashboard_widget() {
		wp_add_dashboard_widget(
			'dm_tasks_dashboard_widget',
			__( 'Your Upcoming Tasks', 'deals-manager' ),
			array( $this, 'render_dashboard_widget' )
		);
	}

	/**
	 * Render the dashboard widget.
	 */
	public function render_dashboard_widget() {
		$user_id = get_current_user_id();
		$args    = array(
			'post_type'      => 'task',
			'posts_per_page' => 5,
			'author'         => $user_id,
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => '_task_status',
					'value'   => 'completed',
					'compare' => '!=',
				),
				array(
					'relation' => 'OR',
					array( // Overdue tasks
						'key'     => '_task_due_date',
						'value'   => date( 'Y-m-d' ),
						'compare' => '<',
						'type'    => 'DATE',
					),
					array( // Tasks due in the next 7 days
						'key'     => '_task_due_date',
						'value'   => array( date( 'Y-m-d' ), date( 'Y-m-d', strtotime( '+7 days' ) ) ),
						'compare' => 'BETWEEN',
						'type'    => 'DATE',
					),
				),
			),
			'orderby'        => 'meta_value',
			'meta_key'       => '_task_due_date',
			'order'          => 'ASC',
		);

		$tasks = get_posts( $args );

		if ( $tasks ) {
			echo '<ul>';
			foreach ( $tasks as $task ) {
				$due_date   = get_post_meta( $task->ID, '_task_due_date', true );
				$is_overdue = strtotime( $due_date ) < time();
				printf(
					'<li><a href="%s">%s</a> - Due: <span%s>%s</span></li>',
					esc_url( get_edit_post_link( $task->ID ) ),
					esc_html( $task->post_title ),
					$is_overdue ? ' style="color:red;"' : '',
					esc_html( $due_date )
				);
			}
			echo '</ul>';
		} else {
			echo '<p>' . esc_html__( 'No upcoming or overdue tasks.', 'deals-manager' ) . '</p>';
		}
	}

	/**
	 * Add the main plugin menu page.
	 */
	public function add_plugin_menu() {
		add_menu_page(
			__( 'Deals Manager', 'deals-manager' ),
			'Deals Manager',
			'read',
			'deals-manager',
			array( $this, 'render_dashboard_page' ),
			'dashicons-businesswoman',
			20
		);
	}

	/**
	 * Render the dashboard page.
	 */
	public function render_dashboard_page() {
		?>
		<div class="wrap">
			<h1><?php _e( 'Deals Manager Dashboard', 'deals-manager' ); ?></h1>
			<p><?php _e( 'Welcome to the Deals Manager dashboard. More widgets and reports coming soon!', 'deals-manager' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Add the pipeline page to the admin menu.
	 */
	public function add_pipeline_page() {
		add_submenu_page(
			'deals-manager',
			__( 'Pipeline', 'deals-manager' ),
			__( 'Pipeline', 'deals-manager' ),
			'edit_deals',
			'deals-manager-pipeline',
			array( $this, 'render_pipeline_page' )
		);
	}

	/**
	 * Add the reports page to the admin menu.
	 */
	public function add_reports_page() {
		add_submenu_page(
			'deals-manager',
			__( 'Reports', 'deals-manager' ),
			__( 'Reports', 'deals-manager' ),
			'edit_deals',
			'deals-manager-reports',
			array( $this, 'render_reports_page' )
		);
	}

	/**
	 * Render the reports page.
	 */
	public function render_reports_page() {
		?>
		<div class="wrap">
			<h1><?php _e( 'Reports & Analytics', 'deals-manager' ); ?></h1>
			<div id="reports-container">
				<div class="report-widget">
					<h2><?php _e( 'Deals by Stage', 'deals-manager' ); ?></h2>
					<canvas id="deals-by-stage-chart"></canvas>
				</div>
				<div class="report-widget">
					<h2><?php _e( 'Deals by User', 'deals-manager' ); ?></h2>
					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th><?php _e( 'User', 'deals-manager' ); ?></th>
								<th><?php _e( 'Deals Count', 'deals-manager' ); ?></th>
							</tr>
						</thead>
						<tbody id="deals-by-user-table">
							<!-- Data will be inserted by JS -->
						</tbody>
					</table>
				</div>
				<div class="report-widget">
					<h2><?php _e( 'Summary', 'deals-manager' ); ?></h2>
					<ul>
						<li><strong><?php _e( 'Total Deals:', 'deals-manager' ); ?></strong> <span id="summary-total-deals"></span></li>
						<li><strong><?php _e( 'Total Value of Won Deals:', 'deals-manager' ); ?></strong> $<span id="summary-total-won-value"></span></li>
						<li><strong><?php _e( 'Conversion Rate:', 'deals-manager' ); ?></strong> <span id="summary-conversion-rate"></span>%</li>
					</ul>
				</div>
			</div>
		</div>
		<style>
			#reports-container { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px; }
			.report-widget { background: #fff; padding: 20px; }
			.report-widget h2 { margin-top: 0; }
		</style>
		<?php
	}

	/**
	 * Render the pipeline page.
	 */
	public function render_pipeline_page() {
		?>
		<div class="wrap">
			<h1><?php _e( 'Deal Pipeline', 'deals-manager' ); ?></h1>
			<div id="kanban-board">
				<?php
				$stages = array(
					'lead'        => __( 'Lead', 'deals-manager' ),
					'proposal'    => __( 'Proposal', 'deals-manager' ),
					'negotiation' => __( 'Negotiation', 'deals-manager' ),
					'won'         => __( 'Won', 'deals-manager' ),
					'lost'        => __( 'Lost', 'deals-manager' ),
				);

				$deals_by_stage = array();
				foreach ( array_keys( $stages ) as $stage_key ) {
					$deals_by_stage[ $stage_key ] = array();
				}

				$args = array(
					'post_type'      => 'deal',
					'posts_per_page' => -1,
					'post_status'    => 'publish',
				);
				$deals = new WP_Query( $args );

				if ( $deals->have_posts() ) {
					while ( $deals->have_posts() ) {
						$deals->the_post();
						$stage = get_post_meta( get_the_ID(), '_deal_stage', true );
						if ( ! $stage ) {
							$stage = 'lead'; // Default stage
						}
						if ( array_key_exists( $stage, $deals_by_stage ) ) {
							$deals_by_stage[ $stage ][] = get_post();
						}
					}
					wp_reset_postdata();
				}

				foreach ( $stages as $stage_key => $stage_name ) {
					?>
					<div class="kanban-column" id="stage-<?php echo esc_attr( $stage_key ); ?>">
						<h2><?php echo esc_html( $stage_name ); ?></h2>
						<div class="kanban-cards-container">
							<?php
							if ( ! empty( $deals_by_stage[ $stage_key ] ) ) {
								foreach ( $deals_by_stage[ $stage_key ] as $deal_post ) {
									$deal_value = get_post_meta( $deal_post->ID, '_deal_value', true );
									?>
									<div class="kanban-card" id="deal-<?php echo esc_attr( $deal_post->ID ); ?>">
										<h4><?php echo esc_html( $deal_post->post_title ); ?></h4>
										<p><?php echo esc_html( '$' . number_format( (float) $deal_value, 2 ) ); ?></p>
									</div>
									<?php
								}
							}
							?>
						</div>
					</div>
					<?php
				}
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Handle the AJAX request to update deal stage.
	 */
	public function handle_update_deal_stage() {
		check_ajax_referer( 'kanban-nonce', 'nonce' );

		if ( ! isset( $_POST['deal_id'] ) || ! isset( $_POST['new_stage'] ) ) {
			wp_send_json_error( 'Missing parameters.' );
		}

		$deal_id   = intval( $_POST['deal_id'] );
		$new_stage = sanitize_text_field( $_POST['new_stage'] );

		if ( ! current_user_can( 'edit_deal', $deal_id ) ) {
			wp_send_json_error( 'Permission denied.' );
		}

		update_post_meta( $deal_id, '_deal_stage', $new_stage );

		wp_send_json_success( 'Stage updated.' );
	}

	/**
	 * Add an export button to the CPT list tables.
	 *
	 * @param string $which 'top' or 'bottom'.
	 */
	public function add_export_button( $which ) {
		if ( 'top' !== $which ) {
			return;
		}

		$screen = get_current_screen();
		$cpts   = array( 'deal', 'contact', 'company' );

		if ( $screen && in_array( $screen->post_type, $cpts, true ) ) {
			$export_url = add_query_arg(
				array(
					'export'    => 'csv',
					'post_type' => $screen->post_type,
					'nonce'     => wp_create_nonce( 'dm-export-nonce' ),
				)
			);
			?>
			<div class="alignleft actions">
				<a href="<?php echo esc_url( $export_url ); ?>" class="button"><?php _e( 'Export to CSV', 'deals-manager' ); ?></a>
			</div>
			<?php
		}
	}

	/**
	 * Handle the CSV export request.
	 */
	public function handle_csv_export() {
		if ( ! isset( $_GET['export'] ) || 'csv' !== $_GET['export'] ) {
			return;
		}

		if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['nonce'] ), 'dm-export-nonce' ) ) {
			return;
		}

		if ( ! isset( $_GET['post_type'] ) ) {
			return;
		}

		$post_type = sanitize_text_field( wp_unslash( $_GET['post_type'] ) );
		$cpts      = array( 'deal', 'contact', 'company' );

		if ( ! in_array( $post_type, $cpts, true ) ) {
			return;
		}

		$post_type_object = get_post_type_object( $post_type );
		if ( ! current_user_can( $post_type_object->cap->edit_posts ) ) {
			wp_die( esc_html__( 'You do not have permission to export this data.', 'deals-manager' ) );
		}

		$args = array(
			'post_type'      => $post_type,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		);
		$posts = get_posts( $args );

		if ( ! $posts ) {
			return;
		}

		$filename = $post_type . 's-export-' . date( 'Y-m-d' ) . '.csv';

		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

		$output = fopen( 'php://output', 'w' );

		// Add headers.
		$first_post_meta = get_post_meta( $posts[0]->ID );
		$headers         = array( 'ID', 'Title', 'Author', 'Date' );
		$meta_keys       = array();
		foreach ( $first_post_meta as $key => $value ) {
			if ( '_' !== $key[0] ) {
				$headers[]   = $key;
				$meta_keys[] = $key;
			}
		}
		fputcsv( $output, $headers );

		// Add data.
		foreach ( $posts as $post ) {
			$row = array(
				$post->ID,
				$post->post_title,
				get_the_author_meta( 'display_name', $post->post_author ),
				$post->post_date,
			);
			foreach ( $meta_keys as $key ) {
				$row[] = get_post_meta( $post->ID, $key, true );
			}
			fputcsv( $output, $row );
		}

		fclose( $output );
		die();
	}
}
