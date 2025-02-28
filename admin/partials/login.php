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


<div class="wrap turbosmtp-setup">

	<?php
	if (isset($_REQUEST['error'])){
		?>
		<p class="turbosmtp-warning">
			<?php
			switch($_REQUEST['error']){
				case "invalid_request":
					esc_html_e('Invalid request', 'turbosmtp');
					break;
				case "invalid_api_keys":
					esc_html_e('Invalid consumer key and / or consumer secret.', 'turbosmtp');
					break;
				case "provide_api_keys":
					esc_html_e('Please provide API keys.', 'turbosmtp');
					break;
				default:
					esc_html_e($_REQUEST['error']);
					break;

			}
			?>
		</p>
		<?php
	}
	?>

	<div class="turbosmtp-setup-card">

		<div style="margin:0 auto;">
			<img src="<?php echo plugins_url( "/admin/img/logo.png",
				TURBOSMTP_BASE_PATH ); ?>"
		</div>

		<p><?php echo esc_html__( 'Use API keys for login', 'turbosmtp' ); ?></p>

		<form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">

			<?php
			wp_nonce_field( 'turbosmtp_save_api_keys', 'turbosmtp_nonce');
			?>

			<input type="hidden" name="action" value="save_api_keys">

			<label for="consumer_key"><strong><?php esc_html_e( 'Enter Consumer Key:', 'turbosmtp' ); ?></strong></label>
			<input type="text" name="consumer_key" id="consumer_key" class="regular-text"
			       placeholder="<?php esc_attr_e( 'Enter your Consumer Key here...', 'turbosmtp' ); ?>"/> <br/>
			<label for="consumer_secret"><strong><?php esc_html_e( 'Enter Consumer Secret:', 'turbosmtp' ); ?></strong></label>
			<input type="text" name="consumer_secret" id="consumer_secret" class="regular-text"
			       placeholder="<?php esc_attr_e( 'Enter your Consumer Secret here...', 'turbosmtp' ); ?>"/>

			<button type="submit" name="save_api_keys" id="save_api_keys" class="button button-primary turbosmtp-button">
				<?php esc_html_e( 'Login', 'turbosmtp' ); ?>
			</button>
		</form>
	</div>
</div>
