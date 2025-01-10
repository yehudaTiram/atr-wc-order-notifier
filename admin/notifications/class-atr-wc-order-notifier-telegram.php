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

            $telegram_bot_token = '';
            if ($encrypted_token) {
                $utils = new Atr_Wc_Order_Notifier_Admin_utils($this->plugin_name, $this->version);
                $encryption_key = $utils->decrypt_telegram_token($encrypted_token);
                $telegram_bot_token = $encryption_key;
            }
            $telegram_chat_id = isset($options['atr_wc_notifier_telegram_chat_id']) ? $options['atr_wc_notifier_telegram_chat_id'] : '';
        }
        // Send to Telegram
        if ($telegram_enabled && $telegram_bot_token && $telegram_chat_id) {
            $text_message = new Atr_Wc_Order_Notifier_Admin_Message($this->plugin_name, $this->plugin_slug);
            $notification_message = $text_message->notification_message($new_status, $order);





            if (empty($notification_message)) {
                error_log('Telegram notification failed: Empty message');
                return;
            }

            // Clean up HTML entities and tags
            $clean_message = strip_tags($notification_message);
            $clean_message = html_entity_decode($clean_message, ENT_QUOTES | ENT_HTML5, 'UTF-8');

            // Replace multiple newlines with single newlines
            $clean_message = preg_replace('/\n+/', "\n", $clean_message);

            // Replace "nn" with proper newlines
            $clean_message = str_replace('nn', "\n", $clean_message);
            $escaped_message = $this->escapeMarkdownV2($clean_message);

            // Prepare data for wp_remote_post
            $data = array(
                'method' => 'POST',
                'headers' => array(
                    'Content-Type' => 'application/json',
                ),
                'body' => json_encode(array(
                    'chat_id' => $telegram_chat_id,
                    'text' => $escaped_message,
                    'parse_mode' => 'MarkdownV2',
                    'disable_web_page_preview' => true
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


    // Function to escape MarkdownV2 characters while preserving intended formatting
    private function escapeMarkdownV2($text)
    {
        // Escape special characters except those used in our formatting
        $chars_to_escape = ['[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'];

        foreach ($chars_to_escape as $char) {
            $text = str_replace($char, '\\' . $char, $text);
        }

        // Convert newlines to Telegram format
        $text = str_replace("\n", "\n", $text);

        return $text;
    }
}
