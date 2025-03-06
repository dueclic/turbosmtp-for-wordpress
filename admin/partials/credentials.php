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
    <div class="turbosmtp-setup-card">
        <div style="margin:0 auto;">
            <img src="<?php echo plugins_url( '/admin/img/logo.png', TURBOSMTP_BASE_PATH ); ?>"/>
        </div>

        <p><?php esc_html_e( 'Your API Keys have been generated. Copy them for future use.', 'turbosmtp' ); ?></p>

        <p>Label: <?php echo turbosmtp_get_label(); ?></p>

        <label for="consumer_key"><strong><?php esc_html_e( 'Consumer Key:', 'turbosmtp' ); ?></strong></label>
        <input type="text" id="consumer_key" class="regular-text"
               value="<?php echo esc_attr( $_GET['consumer_key'] ?? '' ); ?>" readonly/>
        <button class="button button-secondary copy-button" data-target="consumer_key">Copy</button>
        <span class="copy-message" id="message_consumer_key"></span>
        <br/>

        <label for="consumer_secret"><strong><?php esc_html_e( 'Consumer Secret:', 'turbosmtp' ); ?></strong></label>
        <input type="text" id="consumer_secret" class="regular-text"
               value="<?php echo esc_attr( $_GET['consumer_secret'] ?? '' ); ?>" readonly/>
        <button class="button button-secondary copy-button" data-target="consumer_secret">Copy</button>
        <span class="copy-message" id="message_consumer_secret"></span>
        <br/>

        <p>

            <a class="turbo-button"
               href="<?php echo admin_url( 'admin.php?page=' . $this->plugin_name . '_config' ); ?>"><?php esc_html_e( "Go to configuration", "turbosmtp" ); ?></a>
        </p>
    </div>
</div>
