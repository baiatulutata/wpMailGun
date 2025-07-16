jQuery(document).ready(function($) {
    $('#mailgun-test-email').on('click', function(e) {
        e.preventDefault();

        const $btn = $(this);
        const $result = $('#mailgun-test-result');
        const toEmail = $('#mailgun-test-email-to').val().trim();

        if (!toEmail || !/^[^@\\s]+@[^@\\s]+\\.[^@\\s]+$/.test(toEmail)) {
            $result.text('Please enter a valid email address.');
            return;
        }

        $btn.prop('disabled', true);
        $result.text('Sending...');

        $.post(mailgun_ajax.ajax_url, {
            action: 'mailgun_send_test_email',
            to: toEmail,
            nonce: mailgun_ajax.nonce
        })
            .done(function(response) {
                $result.text(response.success ? response.data : 'Error: ' + response.data);
            })
            .fail(function(xhr) {
                $result.text('Unexpected error occurred. Please try again.');
            })
            .always(function() {
                $btn.prop('disabled', false);
            });
    });
});
