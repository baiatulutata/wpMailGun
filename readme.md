# Email Override for Mailgun

A WordPress plugin that overrides `wp_mail()` to send all emails through the Mailgun API.

## 🔧 Features

- ✅ Enable or disable Mailgun override
- 🔐 Securely store and use your Mailgun API key
- 🌐 Configure your Mailgun sending domain
- 📬 Set "From" email and name
- ✉️ Test email function right in the admin settings
- 🎨 Uses native WordPress Settings API and styling

## 📦 Installation

1. Upload this plugin to your `/wp-content/plugins/` directory.
2. Activate it from the **Plugins** menu in WordPress.
3. Navigate to **Settings > Mailgun Email**.
4. Fill in your Mailgun domain and API key.
5. Enable the override and click "Save".

## ⚙️ How It Works

The plugin hooks into WordPress' `pre_wp_mail` filter and uses the Mailgun HTTP API to send emails. It replaces the default behavior only when enabled and properly configured.

## 🧪 Test Email

Use the built-in "Send Test Email" button to verify your Mailgun setup is working correctly.

## 📌 Requirements

- WordPress 5.0 or later
- A Mailgun account with a verified domain and API key

## 🚧 Roadmap

- [ ] Support HTML and rich content
- [ ] Add logging or debug view
- [ ] Add support for attachments

## 👨‍💻 Author

**Ionut Baldazar**

## 📜 License

GPLv2 or later — [https://www.gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html)
