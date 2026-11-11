(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	$(document).on('click', '.tswp-password-toggle', function () {
		var $button = $(this);
		var $input = $('#' + $button.attr('aria-controls'));
		var show = $input.attr('type') === 'password';

		$input.attr('type', show ? 'text' : 'password');
		$button
			.attr('aria-pressed', show ? 'true' : 'false')
			.attr('aria-label', show ? $button.data('label-hide') : $button.data('label-show'));
	});

	var $sendingMethod = $("#send_method");
	var $smtpSettings = $("#smtp_settings");

	function toggleSMTPFields() {
		if ($sendingMethod.val() === "api") {
			$smtpSettings.hide();
		} else {
			$smtpSettings.show();
		}
	}

	$sendingMethod.on("change", toggleSMTPFields);
	toggleSMTPFields();

	$(document).on('click', '#turbosmtp_send_test_email', function() {
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'turbosmtp_send_test_email',
				to: $('#ts_mail_to').val(),
				turbosmtp_send_test_email_nonce: $("#turbosmtp_send_test_email_nonce").val()
			},
			beforeSend: function() {
				$("#turbosmtp_send_test_email").prop("disabled", true);
				$('#turbosmtp-email-result').html(ts.i18n.test_email_send_loading);
			},
			success: function(response) {
				if (response.success) {
					$('#turbosmtp-email-result').html('<span style="color: green;">' + response.data.message + '</span>');
				} else {
					$('#turbosmtp-email-result').html('<span style="color: red;">'+response.data.message + '<br>' +( response.data.error || '') + '</span>');
				}
				$("#turbosmtp_send_test_email").attr("disabled", false);
			},
			error: function() {
				$('#turbosmtp-email-result').html('<span style="color: red;">'+ts.i18n.connection_request_error+'</span>');
				$("#turbosmtp_send_test_email").prop("disabled", false);
			}
		});
	});

	$(document).on('click', '#turbosmtp_disconnect', function(evt) {
		evt.preventDefault();
		if (!confirm(ts.i18n.disconnect_confirm)){
			return;
		}
		$.ajax({
			url: $(this).data("action"),
			type: 'POST',
			data: {},
			beforeSend: function() {
				$("#turbosmtp_send_test_email").prop("disabled", true);
			},
			success: function(response) {
				if (response.success) {
					location.href = response.data.redirect_url;
				}
			},
			error: function() {
				alert(ts.i18n.connection_request_error);
			}
		});
	});

})( jQuery );

