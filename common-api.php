<?php

/**
 * @return bool
 */
function turbosmtp_migration_has_done() {

	$auth_options      = get_option( "ts_auth_options" );
	$plugin_setup_done = get_option( 'ts_migration_done' );

	return ! ( $auth_options &&
	           isset($auth_options['op_ts_validapi']) &&
	           (bool)$auth_options['op_ts_validapi'] &&
	           isset($auth_options['op_ts_email']) &&
	           $auth_options['op_ts_email'] &&
	           ! $plugin_setup_done );

}

function turbosmtp_get_label(){
	return apply_filters('turbosmtp_get_label', 'turboSMTP (WordPress Plugin)');
}

function turbosmtp_validapi() {
	$auth_options      = get_option( "ts_auth_options" );
	return isset( $auth_options['valid_api'] ) && (bool)$auth_options['valid_api'];
}
