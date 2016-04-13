<?php
/**
 * Easy Digital Downloads WP-CLI Migrator
 *
 * This class provides an integration point with the WP-CLI plugin allowing
 * access to EDD from the command line.
 *
 * @package     EDD
 * @subpackage  Classes/CLI
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/license/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

WP_CLI::add_command( 'edd', 'EDD_CLI_Toolbox' );

/**
 * Work with EDD through WP-CLI
 *
 * EDD_CLI Class
 *
 * Adds CLI support to EDD through WP-CL
 *
 * @since   1.0
 */
class EDD_CLI_Toolbox extends EDD_CLI {

	/**
	 * Interact with the EDD_Logging entries
	 *
	 * ## OPTIONS
	 *
	 * --type=<log_type>: A specific log type to interact with
	 * --before=<string>: A strtotime keyword or date
	 * --after=<string>: A strtotime keyword or date
	 *
	 * ## EXAMPLES
	 *
	 * wp edd logs prune --type=api_reqeusts --before="-1 year"
	 * wp edd logs prune --type=api_reqeusts --before=today
	 * wp edd logs prune --type=api_reqeusts --after=today
	 * wp edd logs count --type=api_reqeusts --before=today
	 */
	public function logs( $args, $assoc_args ) {
		global $edd_logs, $wpdb;

		$available_actions = array( 'prune', 'count' );
		if ( empty( $args[0] ) || ! in_array( $args[0], $available_actions ) ) {
			$list_actions = implode( ', ', $available_actions );
			WP_CLI::error( sprintf( __( 'Invalid action. Available actions are: %s' ), $list_actions ) );
		} else {
			$action = $args[0];
		}

		$log_type     = ! empty( $assoc_args['type'] )    ? $assoc_args['type']    : false;
		$logs_before  = ! empty( $assoc_args['before'] )  ? $assoc_args['before']  : false;
		$logs_after   = ! empty( $assoc_args['after'] )   ? $assoc_args['after']   : false;

		if ( empty( $log_type ) ) {
			WP_CLI::error_multi_line(
				array(
					__( 'Please specify a type' ),
					__( 'Example: To remove api request logs' ),
					__( 'wp edd logs prune --type=api_request' ),
				)
			);
		}

		$term = get_term_by( 'slug', $log_type, 'edd_log_type' );
		if ( false === $term ) {
			WP_CLI::error( 'Invalid log type provided: ' . $log_type );
		}

		$logs_args = array(
			'log_type'               => $log_type,
			'paged'                  => -1,
			'posts_per_page'         => -1,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => array( 'ID' ),
		);

		if ( false !== $logs_before ) {
			$logs_args['date_query']['before'] = strtotime( $logs_before );
		}

		if ( false !== $logs_after ) {
			$logs_args['date_query']['after'] = strtotime( $logs_after );
		}

		$logs = $edd_logs->get_connected_logs( $logs_args );

		if ( empty( $logs ) ) {
			WP_CLI::error( __( 'No logs found' ) );
		}

		switch( $action ) {

			case 'prune':
				WP_CLI::success( 'Found ' . count( $logs ) . ' entries' ) );
				WP_CLI::confirm( 'Are you sure you want to prune these logs?', $assoc_args );

				$progress = new \cli\progress\Bar( 'Deleting log entires', count( $license_ids ) );

				foreach ( $logs as $log ) {
					$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->posts WHERE ID = %d", $log->ID ) );
					$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE post_id = %d", $log->ID ) );
					$progress->tick();
				}

				$progress->finish();
				WP_CLI::line( 'Recounting terms' );
				wp_update_term_count_now( array( $term->term_id ), 'edd_log_type' );
				break;

			case 'count':
				WP_CLI::success( 'Found ' . count( $logs ) . ' entries' ) );
				break;

		}
	}

}
