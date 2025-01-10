<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://atarimtr.co.il
 * @since      1.0.0
 *
 * Defines message functionality of the plugin.
 *
 * @package    Atr_Wc_Order_Notifier
 * @subpackage Atr_Wc_Order_Notifier/admin
 * @author     Yehuda Tiram <yehuda@atarimtr.co.il>
 */
class Atr_Wc_Order_Notifier_Admin_Message
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

    public function notification_message($new_status, $order)
    {
        $order_id = $order->get_id(); // Get the order ID

        $options = get_option($this->plugin_name, array()); // Retrieve plugin options

        // $notification_message = $this->get_status_emoji($new_status) . " *New Order #" . $order->get_id() . "*\n\n";
        $notification_message = $this->get_order_title($new_status, $order);

        // Check each setting and include it in the message if enabled
        if (isset($options['order_status_in_message']) && $options['order_status_in_message'] === 'on') {
            $notification_message .= "*Status:* " . ucfirst($new_status) . "\n";
        }

        if (isset($options['customer_name_in_message']) && $options['customer_name_in_message'] === 'on') {
            $notification_message .= "*Customer Name:* " . $order->get_billing_first_name() . " " . $order->get_billing_last_name() . "\n";
        }

        if (isset($options['email_in_message']) && $options['email_in_message'] === 'on') {
            $notification_message .= "*Email:* " . $order->get_billing_email() . "\n";
        }

        if (isset($options['phone_in_message']) && $options['phone_in_message'] === 'on') {
            $notification_message .= "*Phone:* " . $order->get_billing_phone() . "\n";
        }

        if (isset($options['billing_address_in_message']) && $options['billing_address_in_message'] === 'on') {
            $notification_message .= "*Billing Address:* " . $this->format_address([
                'Address 1' => $order->get_billing_address_1(),
                'Address 2' => $order->get_billing_address_2(),
                'City'      => $order->get_billing_city(),
                'State'     => $order->get_billing_state(),
                'Postcode'  => $order->get_billing_postcode(),
                'Country'   => $order->get_billing_country(),
            ]) . "\n";
        }

        if (isset($options['shipping_address_in_message']) && $options['shipping_address_in_message'] === 'on') {
            $notification_message .= "*Shipping Address:* " . $this->format_address([
                'Address 1' => $order->get_shipping_address_1(),
                'Address 2' => $order->get_shipping_address_2(),
                'City'      => $order->get_shipping_city(),
                'State'     => $order->get_shipping_state(),
                'Postcode'  => $order->get_shipping_postcode(),
                'Country'   => $order->get_shipping_country(),
            ]) . "\n";
        }

        if (isset($options['total_amount_in_message']) && $options['total_amount_in_message'] === 'on') {
            $notification_message .= "*Total Amount:* " . wc_price($order->get_total()) . "\n";
        }

        if (isset($options['date_created_in_message']) && $options['date_created_in_message'] === 'on') {
            $notification_message .= "*Date Created:* " . ($order->get_date_created() ? $order->get_date_created()->date('Y-m-d H:i:s') : '') . "\n";
        }

        if (isset($options['product_name_in_message']) && $options['product_name_in_message'] === 'on') {
            // Include product details
            $notification_message .= "\n*Order Items:*\n";
            foreach ($order->get_items() as $item_id => $item) {
                $product_name = $item->get_name();
                $quantity = $item->get_quantity();
                $total = wc_price($item->get_total());
                // Add product details to the message
                $notification_message .= "- {$product_name} x {$quantity} = {$total}\n";
            }
        }

        if (isset($options['payment_method_in_message']) && $options['payment_method_in_message'] === 'on') {
            // Include payment method
            $notification_message .= "\n*Payment Method:* " . esc_html($order->get_payment_method_title()) . "\n";
        }

        if (isset($options['customer_note_in_message']) && $options['customer_note_in_message'] === 'on') {
            // Include customer note
            if (!empty($order->get_customer_note())) {
                $notification_message .= "\n*Customer Note:* " . esc_html($order->get_customer_note()) . "\n";
            }
        }

        if (isset($options['order_link_in_message']) && $options['order_link_in_message'] === 'on') {
            // Add a link to the order in the admin dashboard
            $admin_order_url = add_query_arg(
                array(
                    'post' => $order_id,
                    'action' => 'edit'
                ),
                admin_url('post.php')
            );
            // Add clickable link using HTML syntax
            $notification_message .= "\nView Order Details: " . esc_url_raw($admin_order_url);
        }


        return trim($notification_message); // Return the formatted message
    }

    /**
     * Get the title of the order based on the status of the order
     *
     * @param [type] $new_status
     * @param [type] $order
     * @return void
     */
    private function get_order_title($new_status, $order)
    {
        // Get the website name
        $site_name = get_bloginfo('name');

        switch ($new_status) {
            case 'pending':
                return $this->get_status_emoji($new_status) . " *New Order #" . $order->get_id() . "* - " . $site_name . "\n\n";

            case 'processing':
                return $this->get_status_emoji($new_status) . " *Order #" . $order->get_id() . " Processing* - " . $site_name . "\n\n";

            case 'on-hold':
                return $this->get_status_emoji($new_status) . " *Order #" . $order->get_id() . " On Hold* - " . $site_name . "\n\n";

            case 'completed':
                return $this->get_status_emoji($new_status) . " *Order #" . $order->get_id() . " Completed* - " . $site_name . "\n\n";

            case 'cancelled':
                return $this->get_status_emoji($new_status) . " *Order #" . $order->get_id() . " Cancelled* - " . $site_name . "\n\n";

            case 'refunded':
                return $this->get_status_emoji($new_status) . " *Order #" . $order->get_id() . " Refunded* - " . $site_name . "\n\n";

            case 'failed':
                return $this->get_status_emoji($new_status) . " *Order #" . $order->get_id() . " Failed* - " . $site_name . "\n\n";

            case 'checkout-draft':
                return $this->get_status_emoji($new_status) . " *Draft Order #" . $order->get_id() . "* - " . $site_name . "\n\n";

            default:
                return $this->get_status_emoji($new_status) . " *Order #" . $order->get_id() . " Status Updated* - " . $site_name . "\n\n";
        }
    }


    /**
     * Get the emoji for the order status
     *
     * @param [type] $status
     * @return void
     */
    private function get_status_emoji1($status)
    {
        $emoji_map = [
            'pending' => '🟡',
            'on-hold' => '🟤',
            'processing' => '🟠',
            'cancelled' => '🔵',
            'completed' => '🟢',
            'failed' => '🔴',
            'refunded' => '🟣',
            'checkout-draft' => '⚪'
        ];

        return isset($emoji_map[$status]) ? $emoji_map[$status] : '❓';
    }

    private function get_status_emoji($status)
    {
        switch ($status) {
            case 'pending':
                return '🆕';
            case 'processing':
                return '🔄';
            case 'on-hold':
                return '⏸️';
            case 'completed':
                return '✅';
            case 'cancelled':
                return '❌';
            case 'refunded':
                return '💰';
            case 'failed':
                return '⚠️';
            case 'checkout-draft':
                return '📝';
            default:
                return '📦';
        }
    }

    /**
     * Format the address
     *
     * @param [type] $address
     * @return void
     */
    private function format_address($address)
    {
        $formatted = $address['Address 1'];
        if (!empty($address['Address 2'])) {
            $formatted .= ", " . $address['Address 2'];
        }
        $formatted .= ", " . $address['City'] . ", " . $address['State'] . " " . $address['Postcode'] . ", " . $address['Country'];
        return $formatted;
    }
}
