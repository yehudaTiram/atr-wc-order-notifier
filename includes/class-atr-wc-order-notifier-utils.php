<?php

/**
 *  The admin-facing settings of the plugin.
 *
 * @link       https://atarimtr.co.il
 * @since      1.0.0
 * The utilities of the plugin.
 * @package    Atr_Wc_Order_Notifier
 * @subpackage Atr_Wc_Order_Notifier/includes
 * @author     Yehuda Tiram <yehuda@atarimtr.co.il>
 */

class Atr_Wc_Order_Notifier_Admin_utils
{
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

    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

     /**
     * Decrypt telegram token
     */
    // Add a method to decrypt the token when needed
    public function decrypt_telegram_token($encrypted_token)
    {
        $key = $this->get_encryption_key();
        list($encrypted_data, $iv) = explode('::', base64_decode($encrypted_token), 2);
        return openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
    }

    public function get_encryption_key()
    {
        $wp_salt = defined('LOGGED_IN_SALT') ? LOGGED_IN_SALT : wp_salt('logged_in');
        return hash('sha256', $wp_salt);
    } 
}
