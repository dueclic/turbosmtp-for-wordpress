<?php

/**
 * HTML body of the test email.
 *
 * Email clients ignore <style> blocks and most modern CSS, so the layout uses
 * tables and inline styles only. Line breaks between blocks keep the plain
 * text version readable: the HTTP API path builds it with wp_strip_all_tags().
 *
 * @package    Turbosmtp
 * @subpackage Turbosmtp/admin/partials
 *
 * @var string $site_name
 * @var string $site_url
 * @var string $method_label
 * @var string $sender_name
 * @var string $sender_email
 * @var string $sent_at
 * @var string $logo_url
 */

$turbosmtp_cell_label = 'padding:10px 0;border-bottom:1px solid #e6e8ea;color:#50575e;font-size:14px;width:40%;vertical-align:top;';
$turbosmtp_cell_value = 'padding:10px 0;border-bottom:1px solid #e6e8ea;color:#1d2327;font-size:14px;font-weight:600;vertical-align:top;word-break:break-word;';

?>
<!DOCTYPE html>
<html lang="<?php echo esc_attr( str_replace( '_', '-', determine_locale() ) ); ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php esc_html_e( 'Email sent with WordPress and turboSMTP', 'turbosmtp' ); ?></title>
</head>
<body style="margin:0;padding:0;background-color:#f0f2f5;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
<div role="article" aria-roledescription="email" aria-label="<?php esc_attr_e( 'Email sent with WordPress and turboSMTP', 'turbosmtp' ); ?>" lang="<?php echo esc_attr( str_replace( '_', '-', determine_locale() ) ); ?>">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;">
<?php esc_html_e( 'Your WordPress site can send emails through turboSMTP.', 'turbosmtp' ); ?>
</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f0f2f5;">
<tr>
<td align="center" style="padding:32px 16px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:560px;background-color:#ffffff;border-radius:8px;overflow:hidden;">

<tr>
<td style="background-color:#ffffff;padding:24px 32px 20px;border-bottom:4px solid #004d8b;">
<img src="<?php echo esc_url( $logo_url ); ?>" width="180" height="35" alt="turboSMTP" style="display:block;width:180px;height:35px;border:0;outline:none;text-decoration:none;color:#004d8b;font-size:22px;font-weight:700;">
</td>
</tr>

<tr>
<td style="padding:36px 32px 8px;text-align:center;">
<table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center">
<tr>
<td width="56" height="56" align="center" valign="middle" style="width:56px;height:56px;border-radius:28px;background-color:#e7f6ec;color:#00762e;font-size:30px;line-height:56px;font-weight:700;" aria-hidden="true">✓</td>
</tr>
</table>
</td>
</tr>

<tr>
<td style="padding:16px 32px 0;text-align:center;">
<h1 style="margin:0;font-size:24px;line-height:1.3;color:#1d2327;font-weight:600;"><?php esc_html_e( 'Your turboSMTP setup works', 'turbosmtp' ); ?></h1>
</td>
</tr>

<tr>
<td style="padding:12px 32px 28px;text-align:center;">
<p style="margin:0;font-size:16px;line-height:1.6;color:#3c434a;"><?php esc_html_e( 'If you are reading this email, the turboSMTP plugin is working properly and your WordPress site can deliver its emails.', 'turbosmtp' ); ?></p>
</td>
</tr>

<tr>
<td style="padding:0 32px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-top:1px solid #e6e8ea;">
<tr>
<td style="<?php echo esc_attr( $turbosmtp_cell_label ); ?>"><?php esc_html_e( 'Website', 'turbosmtp' ); ?>:</td>
<td style="<?php echo esc_attr( $turbosmtp_cell_value ); ?>"><?php echo esc_html( $site_name ); ?><br>
<a href="<?php echo esc_url( $site_url ); ?>" style="color:#004d8b;font-weight:400;text-decoration:underline;"><?php echo esc_html( $site_url ); ?></a></td>
</tr>
<tr>
<td style="<?php echo esc_attr( $turbosmtp_cell_label ); ?>"><?php esc_html_e( 'Sending method', 'turbosmtp' ); ?>:</td>
<td style="<?php echo esc_attr( $turbosmtp_cell_value ); ?>"><?php echo esc_html( $method_label ); ?></td>
</tr>
<tr>
<td style="<?php echo esc_attr( $turbosmtp_cell_label ); ?>"><?php esc_html_e( 'Sender', 'turbosmtp' ); ?>:</td>
<td style="<?php echo esc_attr( $turbosmtp_cell_value ); ?>"><?php echo esc_html( $sender_name ); ?><br>
<span style="font-weight:400;"><?php echo esc_html( $sender_email ); ?></span></td>
</tr>
<tr>
<td style="<?php echo esc_attr( $turbosmtp_cell_label ); ?>"><?php esc_html_e( 'Sent at', 'turbosmtp' ); ?>:</td>
<td style="<?php echo esc_attr( $turbosmtp_cell_value ); ?>"><?php echo esc_html( $sent_at ); ?></td>
</tr>
</table>
</td>
</tr>

<tr>
<td align="center" style="padding:32px 32px 36px;">
<table role="presentation" cellpadding="0" cellspacing="0" border="0">
<tr>
<td style="border-radius:4px;background-color:#004d8b;">
<a href="https://dashboard.serversmtp.com/analytics/overview" style="display:inline-block;padding:12px 24px;font-size:15px;font-weight:600;color:#ffffff;text-decoration:none;border-radius:4px;"><?php esc_html_e( 'Open the turboSMTP dashboard', 'turbosmtp' ); ?></a>
</td>
</tr>
</table>
</td>
</tr>

<tr>
<td style="padding:20px 32px;background-color:#f6f7f7;border-top:1px solid #e6e8ea;text-align:center;">
<p style="margin:0;font-size:12px;line-height:1.6;color:#50575e;"><?php
/* translators: %s: website name */
echo esc_html( sprintf( __( 'This test email was sent by the turboSMTP plugin for WordPress on %s.', 'turbosmtp' ), $site_name ) );
?></p>
</td>
</tr>

</table>
</td>
</tr>
</table>
</div>
</body>
</html>
