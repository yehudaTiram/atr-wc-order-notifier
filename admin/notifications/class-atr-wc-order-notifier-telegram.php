<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://atarimtr.co.il
 * @since      1.0.0
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Atr_Wc_Order_Notifier
 * @subpackage Atr_Wc_Order_Notifier/admin
 * @author     Yehuda Tiram <yehuda@atarimtr.co.il>
 */
class Atr_Wc_Order_Notifier_Admin_Telegram
{
    /**
     * The slug of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $textdomain    The current version of this plugin.
     */
    private $plugin_slug;
    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    protected $version;

    public function __construct($plugin_name, $plugin_slug)
    {
        $this->plugin_slug = $plugin_slug;
        $this->plugin_name = $plugin_name;
    }
    // Hook into WooCommerce order status changes
    public function atr_wc_notifier_order_status_changed($order_id, $old_status, $new_status)
    {
        $order = wc_get_order($order_id);
        $options = get_option($this->plugin_name, array());

        // Check if the specific key exists and get its value
        $selected_statuses = isset($options['atr_wc_notifier_statuses']) ? $options['atr_wc_notifier_statuses'] : array();

        if (in_array($new_status, $selected_statuses)) {
            $this->atr_wc_notifier_send_notification($order, $new_status, $order_id);
        }
    }

    private function atr_wc_notifier_send_notification($order, $new_status, $order_id)
    {
        // Get and Loop Over Order Items
        $order_items = $order->get_items();



        // Get configured notification settings
        $options = get_option($this->plugin_name);
        if ($options) {
            $telegram_enabled = isset($options['atr_wc_notifier_telegram_enabled']) ? $options['atr_wc_notifier_telegram_enabled'] : '';

            $encrypted_token = isset($options['atr_wc_notifier_telegram_bot_token']) ? $options['atr_wc_notifier_telegram_bot_token'] : '';
            $encryption_key = isset($options['atr_wc_notifier_encryption_key']) ? $options['atr_wc_notifier_encryption_key'] : '';

            $telegram_bot_token = '';
            if ($encrypted_token && $encryption_key) {
                $telegram_bot_token = $this->decrypt_telegram_token($encrypted_token, $encryption_key);
            }

            // Uencypted way - $telegram_bot_token = isset($options['atr_wc_notifier_telegram_bot_token']) ? $options['atr_wc_notifier_telegram_bot_token'] : '';
            $telegram_chat_id = isset($options['atr_wc_notifier_telegram_chat_id']) ? $options['atr_wc_notifier_telegram_chat_id'] : '';
        }
        // Send to Telegram
        if ($telegram_enabled && $telegram_bot_token && $telegram_chat_id) {
            $text_message = new Atr_Wc_Order_Notifier_Admin_Message($this->plugin_name, $this->plugin_slug);
            $notification_message = $text_message->notification_messaage($new_status, $order);
            // Prepare data for wp_remote_post
            $data = array(
                'method' => 'POST',
                'headers' => array(
                    'Content-Type' => 'application/json',
                ),
                'body' => json_encode(array(
                    'chat_id' => $telegram_chat_id,
                    'text' => $notification_message,
                )),
            );

            // Construct the Telegram API endpoint
            $telegram_api_url = 'https://api.telegram.org/bot' . $telegram_bot_token . '/sendMessage';

            // Send notification using wp_remote_post
            $response = wp_remote_post($telegram_api_url, $data);

            // Handle potential errors
            $error_code = wp_remote_retrieve_response_code($response);
            if (is_wp_error($response) || $error_code !== 200) {
                // Log or display an error message indicating notification failure
                error_log('Telegram notification failed: ' . print_r($response, true));
            }
        }
    }

    /**
     * Decrypts the Telegram bot token using the provided encryption key.
     *
     * @param string $encrypted_token The encrypted Telegram bot token.
     * @param string $key The encryption key.
     * @return string The decrypted Telegram bot token.
     */
    private function decrypt_telegram_token($encrypted_token, $key)
    {
        list($encrypted_data, $iv) = explode('::', base64_decode($encrypted_token), 2);
        return openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
    }
}
