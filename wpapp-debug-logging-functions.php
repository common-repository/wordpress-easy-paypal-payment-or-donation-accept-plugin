<?php 

function wpapp_log_payment_debug( $message, $success, $end = false ) {
	$logfile = wpapp_get_log_file_path();

	$debug = ( get_option( 'wpapp_enable_debug_logging' ) == '1' ) ? true : false;
	if ( ! $debug ) {
		//Debug is not enabled.
		return;
	}

	// Timestamp
	$text = '[' . date( 'm/d/Y g:i A' ) . '] - ' . ( ( $success ) ? 'SUCCESS :' : 'FAILURE :' ) . $message . "\n";
	if ( $end ) {
		$text .= "\n------------------------------------------------------------------\n\n";
	}
	// Write to log
	$fp = fopen( $logfile, 'a' );
	fwrite( $fp, $text );
	fclose( $fp );
}

function wpapp_log_debug_array( $array_to_write, $success, $end = false ) {
	$logfile = wpapp_get_log_file_path();
	$debug = ( get_option( 'wpapp_enable_debug_logging' ) == '1' ) ? true : false;
	if ( ! $debug ) {
		//Debug is not enabled.
		return;
	}
	$text = '[' . date( 'm/d/Y g:i A' ) . '] - ' . ( ( $success ) ? 'SUCCESS :' : 'FAILURE :' ) . "\n";
	ob_start();
	print_r( $array_to_write );
	$var = ob_get_contents();
	ob_end_clean();
	$text .= $var;

	if ($end) {
		$text .= "\n------------------------------------------------------------------\n\n";
	}
	// Write to log
	$fp = fopen( $logfile, 'a' );
	fwrite( $fp, $text );
	fclose( $fp ); // close filee
}

function wpapp_get_log_file_path(){
	$log_file_path = WPAPP_PLUGIN_PATH . 'wpapp-log-'. wpapp_get_log_file_suffix() .'.txt';
	return $log_file_path;
}
/**
 * Generates a unique suffix for filename.
 * @return string File name suffix.
 */
function wpapp_get_log_file_suffix() {
	$suffix = get_option( 'wpapp_logfile_suffix' );
	if ( $suffix ) {
		return $suffix;
	}

	$suffix = uniqid();
	update_option( 'wpapp_logfile_suffix', $suffix );

	return $suffix;
}

/**
 * Read debug log file. If log file doesn't exits, reset it.
 */
function wpapp_read_log_file() {
	if ( ! file_exists( wpapp_get_log_file_path() ) ) {
		wpapp_reset_logfile();
	}
	$logfile = fopen( wpapp_get_log_file_path(), 'rb' );
	if ( ! $logfile ) {
		wp_die( __( 'Log file dosen\'t exists.', 'wordpress-simple-paypal-shopping-cart' ) );
	}
	header( 'Content-Type: text/plain' );
	fpassthru( $logfile );
	die;
}

/**
 * Resets debug log file. Create log file if not present.
 */
function wpapp_reset_logfile() {
	$log_reset = true;
	$logfile   = wpapp_get_log_file_path();
	$text = '[' . date( 'm/d/Y g:i A' ) . '] - SUCCESS : Log file reset';
	$text .= "\n------------------------------------------------------------------\n\n";
	// Write to log
	$fp = fopen( $logfile, 'w' );
	if ( $fp != false ) {
		@fwrite( $fp, $text );
		@fclose( $fp );
	} else {
		$log_reset = false;
	}

	return $log_reset;
}


