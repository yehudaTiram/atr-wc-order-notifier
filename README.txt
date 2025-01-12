=== ATR Shop Order Notifier for Woocommerce ===
Contributors: yehudaT
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=T6VTA75GTS3YA
Tags: woocommerce, order notification, telegram
Requires at least: 3.0.1
Tested up to: 6.7.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Author: Yehuda Tiram

Seamlessly integrating your WooCommerce store with Telegram for real-time order notifications.

== Description ==

ATR Shop Order Notifier for woocommerce is a plugin that allows store owners to receive real-time notifications about order status updates directly in their Telegram chat, ensuring that you never miss an important event related to your business.

Key Features:

* Instant Notifications: Get immediate alerts for various order statuses, including new orders, processing, completed, on-hold, cancelled, refunded, and failed orders.
* Customizable Messages: Tailor the notification messages to include essential order details such as order ID, customer information, total amount, and payment method.
* User-Friendly Setup: Easy to install and configure. Simply enter your Telegram bot token and chat ID to start receiving notifications.
* Support for Multiple Statuses: Receive notifications for all relevant order statuses.
* Emoji Support: Enhance your notifications with emojis that provide a visual cue for different order statuses.
* Secure and Reliable: Built with security in mind, ensuring sensitive data is handled securely.

Use Cases:

* E-commerce Managers: Keep track of order statuses without constantly checking the WooCommerce dashboard.
* Customer Support Teams: Quickly respond to customer inquiries about order statuses using real-time updates.
* Business Owners: Stay updated on your store's performance and customer interactions from anywhere via Telegram.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/atr-wc-order-notifier` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the WooCommerce -> ATR WC Order Notifier screen to configure the plugin.

== Frequently Asked Questions ==

= How do I set up my Telegram bot? =

1. Open Telegram and search for "BotFather"
2. Start a chat with BotFather and type /newbot
3. Follow the prompts to name your bot and choose a username
4. BotFather will provide you with a bot token - save this securely

= How do I get my Chat ID? =

1. Start a conversation with your new bot
2. Send a message to your bot
3. Open a web browser and enter: https://api.telegram.org/bot<YourBotToken>/getUpdates (Replace <YourBotToken> with your actual bot token)
4. Look for the "id" field in the "chat" section of the response
5. This number is your chat ID

= Is my bot token secure? =

Yes, we use an encryption process to enhance the security of your Telegram bot token. Once you enter the bot token and save the settings, the plugin will automatically encrypt your Telegram bot token.

== Screenshots ==

1. This screen shot description corresponds to screenshot-1.(png|jpg|jpeg|gif).
2. This is the second screen shot

== Changelog ==

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.0.0 =
This is the initial release of ATR WooCommerce Order Notifier.

