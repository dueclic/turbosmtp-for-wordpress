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

	$(document).on('click', '#generate_api_keys', function () {
		var nonce = $(this).data('nonce');

		$.ajax({
			url: ajaxurl,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'turbosmtp_generate_api_keys',
				turbosmtp_nonce: nonce
			},
			beforeSend: function () {
				$('#generate_api_keys').prop('disabled', true).text(ts.i18n.api_key_generate_loading);
			},
			success: function (response) {
				if (response.success) {
					location.href=response.data.next_url;
				} else {
					alert('Error: ' + (response.message || ts.i18n.api_key_generate_unknown_error));
				}
			},
			error: function (xhr, status, error) {
				alert('AJAX Error: ' + error);
			},
			complete: function () {
				$('#generate_api_keys').prop('disabled', false).text(ts.i18n.api_key_generate_button);
			}
		});
	});

	$(document).on('click', '.copy-button', function () {
		var targetId = $(this).data('target');
		var copyText = $('#' + targetId);
		copyText.select();
		document.execCommand('copy');

		var messageSpan = $('#message_' + targetId);
		messageSpan.text(ts.i18n.api_key_copied_text).fadeIn().delay(2000).fadeOut();
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

	$('#turbosmtp_send_test_email').click(function() {
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
				$('#turbosmtp-email-result').html('<span style="color: red;">'+ts.i18n.test_email_send_ajax_connection_error+'</span>');
				$("#turbosmtp_send_test_email").prop("disabled", false);
			}
		});
	});

})( jQuery );
