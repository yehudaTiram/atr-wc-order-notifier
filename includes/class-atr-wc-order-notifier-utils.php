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
     * Encrypt data
     *
     * @param [type] $data
     * @return void
     */
    function encrypt_data($data)
    {
        $key = 'your_strong_encryption_key'; // Replace with a strong, unique key
        $ivlen = openssl_cipher_iv_length('AES-256-CBC');
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext = openssl_encrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv . $ciphertext);
    }

    /**
     * Decrypt data
     *
     * @param [type] $data
     * @return void
     */
    function decrypt_data($encrypted_data)
    {
        $key = 'your_strong_encryption_key'; // Replace with the same key
        $ivlen = openssl_cipher_iv_length('AES-256-CBC');
        $iv = substr($encrypted_data, 0, $ivlen);
        $ciphertext = substr($encrypted_data, $ivlen);
        $decrypted = openssl_decrypt(base64_decode($ciphertext), 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return $decrypted;
    }

    /**
     * Check if post exist  and not empty
     */
    public function check_isset_post($field_name)
    {
        if (isset($_POST[$field_name]) && !empty($_POST[$field_name])) {
            return true;
        } else {
            return false;
        }
    }



    /**
     * Options getter
     * @return array Options, either saved or default ones.
     */
    public function get_options()
    {

        $options = get_option($this->plugin_name);
        if (isset($this->settings)) {
            if (!$options && is_array($this->settings)) {
                $options = array();
                foreach ($this->settings as $section => $data) {
                    foreach ($data['fields'] as $field) {
                        $options[$field['id']] = $field['default'];
                    }
                }

                add_option($this->plugin_name, $options);
            }
        }


        return $options;
    }

    public function show_development_comments()
    {
        $show_development_comments = false;
        $options = $this->get_options();
        if ($options) {
            if ((isset($options['atr_show_development_comments'])) && (!empty($options['atr_show_development_comments']))) {
                ($options['atr_show_development_comments'] == 'on') ? $show_development_comments = true : $show_development_comments = false;
            }
        }
        return $show_development_comments;
    }

    public function check_wp_version($ver_num)
    {
        $wp_version = get_bloginfo('version');
        if ($wp_version < $ver_num) {
            return true;
        } else {
            return false;
        }
    }
}
