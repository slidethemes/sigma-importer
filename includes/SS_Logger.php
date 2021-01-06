<?php
/**
 * Class SS_Logger
 *
 * Class responsible for logging importer errors/warnings/status
 *
 * @author     Slidesigma
 * @copyright  (c) Copyright by Slidesigma
 * @link       http://www.slidesigma.com
 * @since      1.0.0
 */

class SS_Logger extends WPImporterLoggerCLI{

  /**
   * Stores all the logs in a buffer which will then be printed to the final log file and frontend
   *
   * @since    1.0.0
   * @access   public
   * @var      array
   */
  public $error_log_buffer = [];

  /**
	 * Overwritten log function from WP_Importer_Logger_CLI.
	 *
	 * Logs with an arbitrary level.
	 *
	 * @param mixed  $level level of reporting.
	 * @param string $message log message.
	 * @param array  $context context to the log message.
	 */
	public function log( $level, $message, array $context = array() ) {
		// Save error messages for front-end display.
		$this->add_wpcli_log_to_error_log_buffer( $level, $message, $context = array() );

		if ( $this->level_to_numeric( $level ) < $this->level_to_numeric( $this->min_level ) ) {
			return;
		}

    $this->messages[] = array(
			'timestamp' => time(),
			'level'     => $level,
			'message'   => $message,
			'context'   => $context,
		);

	}

	/**
	 * Save messages for error output.
	 * Only the messages greater then Error.
	 *
	 * @param mixed  $level level of reporting.
	 * @param string $message log message.
	 * @param array  $context context to the log message.
	 */
	public function add_wpcli_log_to_error_log_buffer( $level, $message, array $context = array() ) {

		if ( $this->level_to_numeric( $level ) < $this->level_to_numeric( 'warning' ) ) {
      return;
		}

    $this->error_log_buffer[] = sprintf( '[%s] %s'.PHP_EOL, strtoupper( $level ), $message );

	}

  /**
	 * Add logs to the $error_log_buffer
	 *
	 * @param string $text The text that will be added to the $error_log_buffer.
	 */
	public function add_to_error_log_buffer( $text ) {
		$lines = array();

		if ( ! empty( $text ) ) {
			$text = str_replace( '<br>', PHP_EOL, $text );
			$lines = explode( PHP_EOL, $text );
		}

		foreach ( $lines as $line ) {
			if ( ! empty( $line ) && ! in_array( $line , $this->error_log_buffer ) ) {
				$this->error_log_buffer[] = $line;
			}
		}

	}

  /**
	 * Display the frontend error messages.
	 *
	 * @return string Text with HTML markup.
	 */
  public function get_error_log_buffer(){

    $output = '';

		if ( ! empty( $this->error_log_buffer ) ) {
			foreach ( $this->error_log_buffer as $line ) {
				$output .= esc_html( $line );
				$output .= '<br>';
			}
		}

		return $output;

  }

}
