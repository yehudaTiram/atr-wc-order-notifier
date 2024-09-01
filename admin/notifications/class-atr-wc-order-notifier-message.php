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

    public function notification_messaage($new_status, $order)
    {
        $order_items = $order->get_items();
        $order_id = $order->get_id();
        $first_name = $order->get_billing_first_name();
        $last_name = $order->get_billing_last_name();
        // Construct Telegram message with order details
        // $notification_message = $new_status == 'failed' ? '🔴 ' : '';
        $notification_message = '';
        switch ($new_status) {
            case 'pending':
                $notification_message = '🟡 ';
                break;
            case 'on-hold':
                $notification_message = '🟤 ';
                break;
            case 'processing':
                $notification_message = '🟠 ';
                break;
            case 'cancelled':
                $notification_message = '🔵 ';
                break;
            case 'completed':
                $notification_message = '🟢 ';
                break;
            case 'failed':
                $notification_message = '🔴 ';
                break;
            case 'refunded':
                $notification_message = '🟣 ';
                break;
            case
            'checkout-draft':
                $notification_message = '🟣 ';
                break;
            default:
                //code block
        }
        $notification_message .= "New order status: {$new_status}\nOrder ID: {$order_id}\nCustomer Name: {$first_name} {$last_name}";
        $notification_message .= "\nItems:\n";
        foreach ($order_items as $item_id => $item) {
            $product_name = $item->get_name();
            $quantity = $item->get_quantity();
            $total = $item->get_total();
            $notification_message .= "{$product_name} x {$quantity} = {$total}\n";
        }
        return $notification_message;
    }
}
