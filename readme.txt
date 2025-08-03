=== Email Override for Mailgun ===
Contributors: baiatulutata
Tags: mailgun, email, smtp, wp_mail, api
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Replaces WordPress `wp_mail()` with MailGun's API. Adds a settings page to manage API key, sender info, and test email functionality.

== Description ==

This plugin overrides the default WordPress email sending function with MailGuns’s Mail Send API.

**Key Features:**

- Toggle Email Override for Mailgun on/off
- Enter your Mailgun API key and domain
- Customize the "From" email and name
- Send a test email directly from the settings page
- Clean and native WordPress admin UI

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/mailgun-email-override/` directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Go to **Settings > Mailgun Email**.
4. Enter your Mailgun API key and domain, set the sender info, and enable the override.

== Frequently Asked Questions ==

= What if I disable the override? =
The default WordPress email system will be used again.

= Where can I find my Mailgun domain and API key? =
In your Mailgun account, under **Sending > Domains** and **API Security** settings.

= Can I send HTML emails or add attachments? =
Not in this version. The plugin currently sends plain text emails only.

== Screenshots ==

1. Settings panel with Mailgun API key and sender inputs
2. Section to send a test email and view the result

== Changelog ==

= 1.0 =
* Initial release with API override, test email function, and admin settings page.

== Upgrade Notice ==

= 1.0 =
Initial stable release.

== External Services ==

This plugin integrates with the [Mailgun](https://www.mailgun.com/) email delivery service to send outgoing emails via its API instead of the default `wp_mail()` function.

When enabled, the plugin communicates with the following Mailgun API endpoint: https://api.mailgun.net/v3/YOUR_DOMAIN/messages

**Data sent to Mailgun includes:**
- Sender name and email
- Recipient email(s)
- Email subject
- Message body (text or HTML)
- Optional headers (Cc, Bcc)

The plugin requires a valid Mailgun API key and domain to function. These credentials are stored in your WordPress database and used only to authenticate API requests to Mailgun.

**Privacy Notice:**
By using this plugin, you agree to Mailgun’s [Terms of Service](https://www.mailgun.com/terms/) and [Privacy Policy](https://www.mailgun.com/privacy-policy/). Ensure your use of Mailgun complies with local privacy laws and your own data protection policies.
