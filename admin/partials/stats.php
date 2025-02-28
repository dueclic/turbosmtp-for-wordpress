<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.dueclic.com
 * @since      4.9.0
 *
 * @package    Turbosmtp
 * @subpackage Turbosmtp/admin/partials
 */

?>

<div id="ts-history-table" style="">
	<?php
	wp_nonce_field( 'ajax-custom-list-nonce', '_ajax_custom_list_nonce' );

	$end   = date( 'Y-m-d' );
	$begin = strtotime( '-7 days', strtotime( $end ) );
	$begin = date( 'Y-m-d', $begin );

	$wp_list_table = new Turbosmtp_Messages_List_Table( $api, $begin, $end, "all" );
	$wp_list_table->prepare_items();
	$wp_list_table->display();

	?>
</div>
