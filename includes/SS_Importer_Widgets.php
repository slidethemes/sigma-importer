<?php

class SS_Importer_Widgets{

	/**
	 * Main import function for widgets
	 *
	 * @since 1.0.0
	 * @global string $widget_file
	 */
	public static function import( $widget_file, $log_path ){

		$results = self::process_widget_import_file( $widget_file );

		// Check for errors, else write the results to the log file.
		if ( is_wp_error( $results ) ) {
			$error_message = $results->get_error_message();

			SS_Importer()->ss_logger->error( 'Widget import error: ' . $error_message );

			SS_File_Manager::append_to_file(
				$error_message,
				$log_path,
				esc_html__( 'Importing widgets' , 'ss-importer' )
			);

		} else {

			ob_start();
			self::format_results_for_log( $results );
			$message = ob_get_clean();

			// Add this message to log file.
			SS_File_Manager::append_to_file(
				$message,
				$log_path,
				esc_html__( 'Importing widgets' , 'ss-importer' )
			);

		}

	}

	/**
	 * Available widgets
	 *
	 * Gather site's widgets into array with ID base, name, etc.
	 * Used by export and import functions.
	 *
	 * @since 1.0.0
	 * @global array $wp_registered_widget_updates
	 * @return array Widget information
	 */
	private static function get_available_widgets() {

		global $wp_registered_widget_controls;

		$widget_controls = $wp_registered_widget_controls;
		$available_widgets = array();

		foreach ( $widget_controls as $widget ) {

			// No duplicates.
			if ( !empty( $widget['id_base'] ) && !isset( $available_widgets[ $widget['id_base'] ] ) ) {
				$available_widgets[ $widget['id_base'] ]['id_base'] = $widget['id_base'];
				$available_widgets[ $widget['id_base'] ]['name']    = $widget['name'];
			}

		}

		return apply_filters( 'ss_importer/get_available_widgets', $available_widgets );

	}

	/**
	 * Process widget file
	 *
	 * This parses a file and triggers importation of its widgets.
	 *
	 * @since 1.0.0
	 * @param string $file Path to .wie file uploaded.
	 */
	private static function process_widget_import_file( $file ) {

		$fs = SS_File_Manager::get_file_system();

		// File exists?
		if ( !$fs->exists( $file ) ) {
			return new \WP_Error(
				'widget_import_file_not_found',
				__( 'Widget import file could not be found.', 'ss-importer' )
			);
		}

		// Get file contents and decode.
		$data = implode( '', file( $file ) );
		$data = json_decode( $data );

		if ( is_wp_error( $data ) ) {
			return $data;
		}

		// Import the widget data
		// Make results available for display on import/export page.
		return self::import_widget_data( $data );

	}

	/**
	 * Import widget JSON data
	 *
	 * @since 1.0.0
	 * @global array $wp_registered_sidebars
	 * @param object $data JSON widget data from .wie file.
	 * @return array Results array
	 */
	private static function import_widget_data( $data ) {

		global $wp_registered_sidebars;

		// Have valid data?
		// If no data or could not decode.
		// Have valid data? If no data or could not decode.
		if ( empty( $data ) || ! is_object( $data ) ) {
			return new \WP_Error(
				'corrupted_widget_import_data',
				__( 'Error: Widget import data could not be read. Please try a different file.', 'ss-importer' )
			);
		}

		// Get all available widgets site supports.
		$available_widgets = self::get_available_widgets();

		// Get all existing widget instances.
		$widget_instances = array();
		foreach ( $available_widgets as $widget_data ) {
			$widget_instances[ $widget_data['id_base'] ] = get_option( 'widget_' . $widget_data['id_base'] );
		}

		// Begin results.
		$results = array();

		// Loop import data's sidebars.
		foreach ( $data as $sidebar_id => $widgets ) {

			// Skip inactive widgets (should not be in export file).
			if ( 'wp_inactive_widgets' === $sidebar_id ) {
				continue;
			}

			// Check if sidebar is available on this site.
			// Otherwise add widgets to inactive, and say so.
			if ( isset( $wp_registered_sidebars[ $sidebar_id ] ) ) {
				$sidebar_available    = true;
				$use_sidebar_id       = $sidebar_id;
				$sidebar_message_type = 'success';
				$sidebar_message      = '';
			} else {
				$sidebar_available    = false;
				$use_sidebar_id       = 'wp_inactive_widgets'; // Add to inactive if sidebar does not exist in theme.
				$sidebar_message_type = 'error';
				$sidebar_message      = esc_html__( 'Widget area does not exist in theme (using Inactive)', 'ss-importer' );
			}

			// Result for sidebar
			// Sidebar name if theme supports it; otherwise ID.
			$results[ $sidebar_id ]['name']         = ! empty( $wp_registered_sidebars[ $sidebar_id ]['name'] ) ? $wp_registered_sidebars[ $sidebar_id ]['name'] : $sidebar_id;
			$results[ $sidebar_id ]['message_type'] = $sidebar_message_type;
			$results[ $sidebar_id ]['message']      = $sidebar_message;
			$results[ $sidebar_id ]['widgets']      = array();

			// Loop widgets.
			foreach ( $widgets as $widget_instance_id => $widget ) {

				$fail = false;

				// Get id_base (remove -# from end) and instance ID number.
				$id_base            = preg_replace( '/-[0-9]+$/', '', $widget_instance_id );
				$instance_id_number = str_replace( $id_base . '-', '', $widget_instance_id );

				// Does site support this widget?
				if ( !$fail && ! isset( $available_widgets[ $id_base ] ) ) {
					$fail                = true;
					$widget_message = esc_html__( 'Site does not support widget', 'ss-importer' ); // Explain why widget not imported.
				}

				// Convert multidimensional objects to multidimensional arrays
				// Some plugins like Jetpack Widget Visibility store settings as multidimensional arrays
				// Without this, they are imported as objects and cause fatal error on Widgets page
				// If this creates problems for plugins that do actually intend settings in objects then may need to consider other approach: https://wordpress.org/support/topic/problem-with-array-of-arrays
				// It is probably much more likely that arrays are used than objects, however.
				$widget = json_decode( wp_json_encode( $widget ), true );

				// Does widget with identical settings already exist in same sidebar?
				if ( ! $fail && isset( $widget_instances[ $id_base ] ) ) {

					// Get existing widgets in this sidebar.
					$sidebars_widgets = get_option( 'sidebars_widgets' );
					$sidebar_widgets = isset( $sidebars_widgets[ $use_sidebar_id ] ) ? $sidebars_widgets[ $use_sidebar_id ] : array(); // Check Inactive if that's where will go.

					// Loop widgets with ID base.
					$single_widget_instances = ! empty( $widget_instances[ $id_base ] ) ? $widget_instances[ $id_base ] : array();
					foreach ( $single_widget_instances as $check_id => $check_widget ) {

						// Is widget in same sidebar and has identical settings?
						if ( in_array( "$id_base-$check_id", $sidebar_widgets, true ) && (array) $widget === $check_widget ) {
							$fail = true;
							$widget_message = __( 'Widget already exists', 'ss-importer' );
						}

					}

				}

				// No failure.
				if ( ! $fail ) {


					// Add widget instance
					$single_widget_instances = get_option( 'widget_' . $id_base ); // All instances for that widget ID base, get fresh every time.
					$single_widget_instances = ! empty( $single_widget_instances ) ? $single_widget_instances : array(
						'_multiwidget' => 1, // Start fresh if have to.
					);
					$single_widget_instances[] = $widget; // Add it.

					// Get the key it was given.
					end( $single_widget_instances );
					$new_instance_id_number = key( $single_widget_instances );

					// If key is 0, make it 1
					// When 0, an issue can occur where adding a widget causes data from other widget to load,
					// and the widget doesn't stick (reload wipes it).
					if ( '0' === strval( $new_instance_id_number ) ) {
						$new_instance_id_number = 1;
						$single_widget_instances[ $new_instance_id_number ] = $single_widget_instances[0];
						unset( $single_widget_instances[0] );
					}

					// Move _multiwidget to end of array for uniformity.
					if ( isset( $single_widget_instances['_multiwidget'] ) ) {
						$multiwidget = $single_widget_instances['_multiwidget'];
						unset( $single_widget_instances['_multiwidget'] );
						$single_widget_instances['_multiwidget'] = $multiwidget;
					}

					// Update option with new widget.
					update_option( 'widget_' . $id_base, $single_widget_instances );

					// Assign widget instance to sidebar.
					// Which sidebars have which widgets, get fresh every time.
					$sidebars_widgets = get_option( 'sidebars_widgets' );

					// Avoid rarely fatal error when the option is an empty string
					// https://github.com/churchthemes/widget-importer-exporter/pull/11.
					if ( ! $sidebars_widgets ) {
						$sidebars_widgets = array();
					}

					// Use ID number from new widget instance.
					$new_instance_id = $id_base . '-' . $new_instance_id_number;

					// Add new instance to sidebar.
					$sidebars_widgets[ $use_sidebar_id ][] = $new_instance_id;

					// Save the amended data.
					update_option( 'sidebars_widgets', $sidebars_widgets );

					// After widget import action.
					$after_widget_import = array(
						'sidebar'           => $use_sidebar_id,
						'sidebar_old'       => $sidebar_id,
						'widget'            => $widget,
						'widget_type'       => $id_base,
						'widget_id'         => $new_instance_id,
						'widget_id_old'     => $widget_instance_id,
						'widget_id_num'     => $new_instance_id_number,
						'widget_id_num_old' => $instance_id_number,
					);

					// Success message.
					$widget_message = $sidebar_available ? esc_html__( 'Imported', 'ss-importer' ) : esc_html__( 'Imported to Inactive', 'ss-importer' );

				}

				// Result for widget instance
				$results[ $sidebar_id ]['widgets'][ $widget_instance_id ]['name'] = isset( $available_widgets[ $id_base ]['name'] ) ? $available_widgets[ $id_base ]['name'] : $id_base; // Widget name or ID if name not available (not supported by site).
				$results[ $sidebar_id ]['widgets'][ $widget_instance_id ]['title'] = ! empty( $widget['title'] ) ? $widget['title'] : esc_html__( 'No Title', 'ss-importer' ); // Show "No Title" if widget instance is untitled.
				$results[ $sidebar_id ]['widgets'][ $widget_instance_id ]['message'] = $widget_message;

			}

		}

		return apply_filters( 'ss_importer/import_widget_data_results', $results );

	}

	/**
	 * Format results for log buffer
	 *
	 * @param array $results widget import results.
	 */
	private static function format_results_for_log( $results ) {

		if ( empty( $results ) ) {
			esc_html_e( 'No results for widget import!', 'ss-importer' );
		}

		// Loop sidebars.
		foreach ( $results as $sidebar ) {
			echo esc_html( $sidebar['name'] ) . ' : ' . esc_html( $sidebar['message'] ) . PHP_EOL . '.....................................' . PHP_EOL;
			// Loop widgets.
			foreach ( $sidebar['widgets'] as $widget ) {
				echo esc_html( $widget['name'] ) . ' - ' . esc_html( $widget['title'] ) . ' - ' . esc_html( $widget['message'] ) . PHP_EOL;
			}
			echo PHP_EOL;
		}

	}

}
