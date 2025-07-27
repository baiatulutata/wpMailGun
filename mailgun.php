<?php
/*
 * Plugin Name:       Email Override for Mailgun
 * Plugin URI:        https://github.com/baiatulutata/ptei
 * Description:       Replaces WordPress wp_mail with Mailgun API. Includes settings page with toggle, API key input, domain, and test button.
 * Version:           1.0.0
 * Author:            Ionut Baldazar
 * Author URI:        https://woomag.ro/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Requires PHP:      7.2
 * Requires at least: 5.0
 * Tested up to:      6.8
 * Text Domain:       mailgun-email-override
 */

if (!defined('ABSPATH')) exit;

class Mailgun_Email_Override {
    private $option_name = 'mailgun_email_override_options';

    public function __construct() {
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
        add_filter('pre_wp_mail', [$this, 'intercept_wp_mail'], 10, 2);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        add_action('wp_ajax_mailgun_send_test_email', [$this, 'ajax_send_test_email']);
    }

    public function add_settings_page() {
        add_options_page('Email Override for Mailgun', 'Mailgun Email', 'manage_options', 'mailgun-email-override', [$this, 'render_settings_page']);
    }

    public function register_settings() {
        register_setting('mailgun_email_override_group', $this->option_name, [
            'sanitize_callback' => [$this, 'sanitize_options']
        ]);

    }

    public function enqueue_admin_scripts($hook) {
        if ($hook === 'settings_page_mailgun-email-override') {
            wp_enqueue_script('mailgun-admin', plugin_dir_url(__FILE__) . 'mailgun-admin.js', ['jquery'], '1.0.0', true);
            wp_localize_script('mailgun-admin', 'mailgun_ajax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('mailgun_test_email_nonce')
            ]);
        }
    }

    public function render_settings_page() {
        $options = get_option($this->option_name);
        ?>
        <div class="wrap">
            <h1>Email Override for Mailgun</h1>
            <form method="post" action="options.php">
                <?php settings_fields('mailgun_email_override_group'); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Enable Mailgun Override</th>
                        <td><input type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[enabled]" value="1" <?php checked($options['enabled'] ?? '', '1'); ?>></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Mailgun API Key</th>
                        <td><input type="password" name="<?php echo esc_attr($this->option_name); ?>[api_key]" value="<?php echo esc_attr($options['api_key'] ?? ''); ?>" class="regular-text"></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Mailgun Domain</th>
                        <td><input type="text" name="<?php echo esc_attr($this->option_name); ?>[domain]" value="<?php echo esc_attr($options['domain'] ?? ''); ?>" class="regular-text"></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">From Email</th>
                        <td><input type="email" name="<?php echo esc_attr($this->option_name); ?>[from_email]" value="<?php echo esc_attr($options['from_email'] ?? get_bloginfo('admin_email')); ?>" class="regular-text"></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">From Name</th>
                        <td><input type="text" name="<?php echo esc_attr($this->option_name); ?>[from_name]" value="<?php echo esc_attr($options['from_name'] ?? get_bloginfo('name')); ?>" class="regular-text"></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
            <hr>
            <h2>Send Test Email</h2>
            <p><label for="mailgun-test-email-to">Recipient Email:</label>
                <input type="email" id="mailgun-test-email-to" class="regular-text"></p>
            <button id="mailgun-test-email" class="button button-secondary">Send Test Email</button>
            <div id="mailgun-test-result"></div>
        </div>
        <?php
    }

    public function intercept_wp_mail($null, $atts) {
        $options = get_option($this->option_name);
        if (empty($options['enabled']) || empty($options['api_key']) || empty($options['domain'])) {
            return null;
        }

        $to = is_array($atts['to']) ? implode(',', array_map('sanitize_email', $atts['to'])) : sanitize_email($atts['to']);
        $subject = sanitize_text_field($atts['subject']);
        $body = $atts['message'];
        $headers = $atts['headers'];

        $from_email = !empty($options['from_email']) ? sanitize_email($options['from_email']) : get_bloginfo('admin_email');
        $from_name = !empty($options['from_name']) ? sanitize_text_field($options['from_name']) : get_bloginfo('name');

        $from = $from_name . ' <' . $from_email . '>';

        $cc = '';
        $bcc = '';
        $is_html = false;

        if (!empty($headers)) {
            $parsed_headers = is_array($headers) ? $headers : explode("\n", str_replace("\r\n", "\n", $headers));

            foreach ($parsed_headers as $header) {
                if (stripos($header, 'Cc:') === 0) {
                    $cc = sanitize_email(trim(substr($header, 3)));
                } elseif (stripos($header, 'Bcc:') === 0) {
                    $bcc = sanitize_email(trim(substr($header, 4)));
                } elseif (stripos($header, 'Content-Type:') !== false && stripos($header, 'text/html') !== false) {
                    $is_html = true;
                }
            }
        }

        $api_url = 'https://api.mailgun.net/v3/' . sanitize_text_field($options['domain']) . '/messages';

        $args = [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode('api:' . sanitize_text_field($options['api_key'])),
            ],
            'body' => [
                'from' => $from,
                'to' => $to,
                'subject' => $subject,
                $is_html ? 'html' : 'text' => $body,
            ],
            'method' => 'POST',
            'timeout' => 15,
        ];

        if (!empty($cc)) {
            $args['body']['cc'] = $cc;
        }

        if (!empty($bcc)) {
            $args['body']['bcc'] = $bcc;
        }

        $response = wp_remote_post($api_url, $args);

        return [
            'to' => $atts['to'],
            'subject' => $subject,
            'message' => $body,
            'headers' => $headers,
            'attachments' => [],
            'send_result' => !is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200
        ];
    }

    public function ajax_send_test_email() {
        check_ajax_referer('mailgun_test_email_nonce', 'nonce');

        if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            wp_send_json_error('Invalid request method.');
        }

        $options = get_option($this->option_name);
        if (empty($options['enabled']) || empty($options['api_key']) || empty($options['domain'])) {
            wp_send_json_error('Mailgun not properly configured.');
        }

        $to = isset($_POST['to']) ? sanitize_email(wp_unslash($_POST['to'])) : get_bloginfo('admin_email');
        if (!is_email($to)) {
            wp_send_json_error('Invalid test email address');
        }

        $result = wp_mail($to, 'Mailgun Test Email', 'This is a test email sent using Mailgun.');
        wp_send_json_success($result ? 'Test email sent!' : 'Failed to send email.');
    }
}

new Mailgun_Email_Override();
