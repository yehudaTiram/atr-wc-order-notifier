# ATR WooCommerce Order Notifier
A wordpress plugin

Tags: Woocommerce, Order notification

Requires at least: 3.0.1

Tested up to: 6.7.1

Stable tag: 1.0.0

License: GPLv2 or later

License URI: http://www.gnu.org/licenses/gpl-2.0.html
## Key Features
- **Instant Notifications**: Get immediate alerts for various order statuses, including new orders, processing, completed, on-hold, cancelled, refunded, and failed orders. Stay informed and responsive to customer needs.
- **Customizable Messages**: Tailor the notification messages to include essential order details such as order ID, customer information, total amount, and payment method. You can also include your website name for easy identification.
- **User-Friendly Setup**: The plugin is easy to install and configure. Simply enter your Telegram bot token and chat ID, and you’re ready to start receiving notifications.
- **Support for Multiple Statuses**: Receive notifications for all relevant order statuses. Whether you want to track new orders or be alerted when an order is cancelled, this plugin has you covered.
- **Emoji Support**: Enhance your notifications with emojis that provide a visual cue for different order statuses, making it easier to scan through updates at a glance.
- **Secure and Reliable**: Built with security in mind, the plugin ensures that sensitive data is handled securely while providing reliable notifications through Telegram’s robust messaging platform.
## Use Cases
- **E-commerce Managers**: Keep track of order statuses without constantly checking the WooCommerce dashboard.
- **Customer Support Teams**: Quickly respond to customer inquiries about order statuses using real-time updates.
- **Business Owners**: Stay updated on your store’s performance and customer interactions from anywhere via Telegram.
[Donate link](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=T6VTA75GTS3YA)
# ATR WooCommerce Order Notifier settings
1. Install the plugin
   1. Download the plugin zip file from [https://github.com/yehudaTiram/atr-wc-order-notifier]
   2. Go to wordpress Plugins -> Add New Plugin
   3. At the top of the page you'll see "Add Plugins" "Upload Plugin" click it. Upload the plugin zip file.
   4. Activate the new plugin

Move on to create the Telegram Bot you'll use.
# Creating and Using a Telegram Bot: A Non-Technical Guide

## Step 1: Set Up Your Bot

1. Open Telegram and search for "BotFather"
2. Start a chat with BotFather and type <span style="color:#1b75d0"><ins>/newbot</ins></span>
3. Follow the prompts to name your bot and choose a username
4. BotFather will provide you with a bot token - save this securely

## Step 2: Configure Your Bot

1. Set a profile picture for your bot (optional)
2. Add a description of what your bot does
3. Set up commands your bot can respond to (if applicable)
   
#### <span style="color:red">IMPORTANT!</span> You MUST first send a message (anything) to the bot before the bot can send messages to you.

## Step 3: Get Your Chat ID

1. Start a conversation with your new bot
2. Send a message to your bot
3. Open a web browser and enter:
   ```
   https://api.telegram.org/bot<YourBotToken>/getUpdates
   ```
   (Replace <YourBotToken> with your actual bot token)
4. Look for the "id" field in the "chat" section of the response
5. This number is your chat ID

## Step 4: Add Bot to the Plugin Settings
1. Go to the plugin's settings in admin menu WooCommerce -> ATR WC Order Notifier
### General tab
1. In <ins>**Bot Token**</ins> enter the bot token you received from BotFather
2. In <ins>**Chat ID**</ins> enter the chat ID you found in Step 3
3. Check <ins>**Enable Telegram**</ins> option
4. In the <ins>**Select statuses**</ins> check Woocommerce statuses you want to be notified on
5. Save the settings. From now on every order status change you selected will send a message to your Telegram bot in your telegram account.
### Message details tab
Check the details you want to see in the notifications


## More info about the encrypting of your Telegram Bot Token

To enhance the security of your Telegram bot token in the plugin settings, we use an encryption process. Here's what you need to know:
1. **Bot Token Encryption**: Once you enter the bot token and set all your preferences and save the settings, the plugin will automatically encrypt your Telegram bot token.
2. **Security Benefits**:
   - The encrypted token is stored instead of the plain text version
   - Even if someone gains access to your database, they can't use the token
   - Adds an extra layer of protection for your bot's security

By using this encryption method, you significantly reduce the risk of your Telegram bot token being compromised, even if there's unauthorized access to your plugin settings or database[1].
