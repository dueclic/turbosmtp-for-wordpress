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

<div class="wrap">
	<div id="ts-history-table" style="">
		<?php
		wp_nonce_field( 'ajax-custom-list-nonce', '_ajax_custom_list_nonce' );


		$wp_list_table->prepare_items();
		$wp_list_table->display();

		?>
	</div>
</div>
