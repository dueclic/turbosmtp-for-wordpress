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


<div class="turbosmtp-setup-card">
    <div class="tswp-login-form tswp-migration-form">
        <div class="tswp-text-center">
            <img src="<?php echo plugins_url( "/admin/img/ts-logo.svg", TURBOSMTP_BASE_PATH ); ?>"
                 class="tswp-login-logo-migration" alt="">
        </div>
        <p><?php esc_html_e( "Connection successful! Ensure you copy your unique Key Name, Consumer Key, and Consumer Secret now - the Secret won't be displayed again. Save them safely for future access.", "turbosmtp" ); ?></p>

        <div class="tswp-login-form-control-group">
            <label for="name_key" class="tswp-credentials-label">
                <?php _e( "Key name", "turbosmtp" ); ?>:
                <span class="copy-message" id="message_name_key"></span>
            </label>
            <div class="tswp-input-group">
                <input class="tswp-input readonly tswp-input-group-element" type="text" value="<?php echo turbosmtp_get_label(); ?>" id="name_key"
                       name="name_key" readonly>
                <button class="button button-secondary copy-button tswp-input-group-element" data-target="name_key"><?php _e( "Copy", "turbosmtp" ); ?></button>
            </div>
        </div>

        <div class="tswp-login-form-control-group">
            <label for="consumer_key" class="tswp-credentials-label">
                <?php _e( "Consumer Key", "turbosmtp" ); ?>:
                <span class="copy-message" id="message_consumer_key"></span>
            </label>
            <div class="tswp-input-group">
                <input class="tswp-input readonly tswp-input-group-element" type="text" value="<?php echo esc_attr( $_GET['consumer_key'] ?? '' ); ?>" id="consumer_key"
                   name="consumer_key" readonly>
                <button class="button button-secondary copy-button tswp-input-group-element" data-target="consumer_key"><?php _e( "Copy", "turbosmtp" ); ?></button>
            </div>
        </div>

        <div class="notice notice-info notice-alt" style="margin: 2.5em 0 1em;">
            <?php esc_html_e( "The following Consumer Secret is only displayed here. Copy it now and keep it safe. If you lose this key, you'll need to delete it and generate a new one.", "turbosmtp" ); ?>
        </div>

        <div class="tswp-login-form-control-group">
            <label for="consumer_secret" class="tswp-credentials-label">
                <?php _e( "Consumer Secret", "turbosmtp" ); ?>:
                <span class="copy-message" id="message_consumer_secret"></span>
            </label>
            <div class="tswp-input-group">
                <input class="tswp-input readonly tswp-input-group-element" type="text" value="<?php echo esc_attr( $_GET['consumer_secret'] ?? '' ); ?>" id="consumer_secret"
                       name="consumer_secret" readonly>
                <button class="button button-secondary copy-button tswp-input-group-element" data-target="consumer_secret"><?php _e( "Copy", "turbosmtp" ); ?></button>
            </div>
        </div>

        <p class="tswp-text-center">
            <a class="button button-primary"
              href="<?php echo admin_url( 'admin.php?page=' . $this->plugin_name . '_config' ); ?>"><?php esc_html_e( "Go to configuration", "turbosmtp" ); ?></a>
        </p>

    </div>
</div>

<!--
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
-->
