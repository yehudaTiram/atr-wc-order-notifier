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
        // Get order details
        $order = wc_get_order($order_id);
        // $this->atr_wc_notifier_send_notification($order, $new_status, $order_id);
        // Check if the order status is one we want to notify about
        // Retrieve the entire array from the database
        $options = get_option($this->plugin_name, array());

        // Check if the specific key exists and get its value
        $selected_statuses = isset($options['atr_wc_notifier_statuses']) ? $options['atr_wc_notifier_statuses'] : array();

        // Now, you can work with $selected_statuses as an array
        if (in_array($new_status, $selected_statuses)) {
            $this->atr_wc_notifier_send_notification($order, $new_status, $order_id);
        }

        // if (in_array($new_status, array('pending', 'on-hold', 'processing', 'cancelled', 'completed', 'failed', 'refunded', 'checkout-draft'))) {
        //     // Send notification
        //      //$this->atr_wc_notifier_send_notification($order, $new_status, $order_id);

        // }
    }

    private function atr_wc_notifier_send_notification($order, $new_status, $order_id)
    {
        // Get and Loop Over Order Items
        $order_items = $order->get_items();
        // Get configured notification settings
        $options = get_option($this->plugin_name);
        if ($options) {
            $telegram_enabled = isset($options['atr_wc_notifier_telegram_enabled']) ? $options['atr_wc_notifier_telegram_enabled'] : '';
            // $telegram_webhook_url = isset($options['atr_wc_notifier_telegram_webhook_url']) ? $options['atr_wc_notifier_telegram_webhook_url'] : '';
            $telegram_bot_token = isset($options['atr_wc_notifier_telegram_bot_token']) ? $options['atr_wc_notifier_telegram_bot_token'] : '';
            $telegram_chat_id = isset($options['atr_wc_notifier_telegram_chat_id']) ? $options['atr_wc_notifier_telegram_chat_id'] : '';
        }
        // Send to Telegram
        if ($telegram_enabled && $telegram_bot_token && $telegram_chat_id) {
            // Construct Telegram message with order details
            $notification_message = $new_status == 'failed' ? '🔴 ' : '';
            $notification_message .= "New order status: {$new_status}\nOrder ID: {$order_id}\nCustomer Name: {$order->get_billing_first_name()} {$order->get_billing_last_name()}";
            $notification_message .= "\nItems:\n";
            foreach ($order_items as $item_id => $item) {
                $product_name = $item->get_name();
                $quantity = $item->get_quantity();
                $total = $item->get_total();
                $notification_message .= "{$product_name} x {$quantity} = {$total}\n";
            }

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
}
