<?php

/**
 *  The admin-facing settings of the plugin.
 *
 * @link       https://atarimtr.co.il
 * @since      1.0.0
 * The settings of the plugin.
 * @package    Atr_Wc_Order_Notifier
 * @subpackage Atr_Wc_Order_Notifier/admin
 * @author     Yehuda Tiram <yehuda@atarimtr.co.il>
 */


class Atr_Wc_Order_Notifier_Admin_Settings
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * The text domain of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $textdomain    The current version of this plugin.
     */
    private $textdomain;

    /**
     * The slug of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $textdomain    The current version of this plugin.
     */
    private $plugin_slug;

    /**
     * Undocumented variable
     *
     * @var [type]
     */
    private $settings;

    /**
     * Undocumented variable
     *
     * @var [type]
     */
    private $options;

    /**
     * Undocumented variable
     *
     * @var [type]
     */
    private $dir;

    /**
     * Undocumented variable
     *
     * @var [type]
     */
    private $file;

    /*
     * Fired during plugins_loaded (very very early),
     * so don't miss-use this, only actions and filters,
     * current ones speak for themselves.
     */
    public function __construct($plugin_name, $plugin_slug, $file)
    {
        $this->file = $file;
        $this->plugin_slug = $plugin_slug;
        $this->plugin_name = $plugin_name;
        $this->textdomain = str_replace('_', '-', $plugin_slug);

        // Initialise settings
        add_action('admin_init', array($this, 'init'));

        // Add settings page to menu
        add_action('admin_menu', array($this, 'add_menu_item'));

        // Add settings link to plugins page
        add_filter('plugin_action_links_' . plugin_basename($this->file), array($this, 'add_settings_link'));
    }

    /**
     * Initialise settings
     * @return void
     */
    public function init()
    {
        $this->settings = $this->settings_fields();
        $this->options = $this->get_options();
        $this->register_settings();
    }

    /**
     * Add settings page to admin menu
     * @return void
     */
    public function add_menu_item()
    {
        add_submenu_page(
            'woocommerce', // The slug for this menu parent item
            'ATR WooCommerce Order Notifier Options', // The title to be displayed in the browser window for this page.
            'ATR WC Order Notifier', // The text to be displayed for this menu item
            'manage_options', // Which type of users can see this menu item
            $this->plugin_slug, // The unique ID - that is, the slug - for this menu item
            array($this, 'settings_page') //The name of the function to call when rendering this menu's page
        );
    }
    /**
     * Add settings link to plugin list table
     * @param  array $links Existing links
     * @return array 		Modified links
     */
    public function add_action_links($links)
    {
        $links[] = '<a href="' . esc_url(get_admin_url(null, 'admin.php?page=' . $this->plugin_name)) . '">' . __('Settings', $this->textdomain) . '</a>';
        $links[] = '<a href="http://atarimtr.com" target="_blank">More plugins by Yehuda Tiram</a>';
        return $links;
    }



    /**
     * Build settings fields
     * @return array Fields to be displayed on settings page
     */
    private function settings_fields()
    {
        $settings['easy'] = array(
            'title'                    => __('General', $this->textdomain),
            'description'            => __('General settings', $this->textdomain),
            'fields'                => array(
                array(
                    'id'             => 'atr_wc_notifier_telegram_bot_token',
                    'label'            => __('Bot Token', $this->textdomain),
                    'description'    => __('telegram Bot Token', $this->textdomain),
                    'type' => 'text',
                    'default' => '',
                    'placeholder' => 'Bot Token',
                ),
                array(
                    'id'             => 'atr_wc_notifier_telegram_chat_id',
                    'label'            => __('Chat ID', $this->textdomain),
                    'description'    => __('telegram Chat ID', $this->textdomain),
                    'type' => 'text',
                    'default' => '',
                    'placeholder' => 'Chat ID',
                ),
                array(
                    'id'             => 'atr_wc_notifier_telegram_enabled',
                    'label'            => __('Enable Telegram', $this->textdomain),
                    'description'    => __('Enable Telegram', $this->textdomain),
                    'type' => 'checkbox',
                    'default' => 'off',
                ),
                array(
                    'id' => 'atr_wc_notifier_encryption_key',
                    'label' => __('Encryption Key', $this->textdomain),
                    'description' => __('Enter a strong encryption key.', $this->textdomain),
                    'type' => 'text',
                    'default' => '',
                    'placeholder' => 'Encryption Key',
                ),
                array(
                    'id' => 'atr_wc_notifier_statuses',
                    'label' => __('Select statuses', $this->textdomain),
                    'description' => __('Select statuses to notify about.', $this->textdomain),
                    'type' => 'checkbox_multi',
                    'options' => array(
                        'pending' => __('Pending payment', $this->textdomain),
                        'on-hold' => __('On hold', $this->textdomain),
                        'processing' => __('Processing', $this->textdomain),
                        'cancelled' => __('Cancelled', $this->textdomain),
                        'completed' => __('Completed', $this->textdomain),
                        'failed' => __('Failed', $this->textdomain),
                        'refunded' => __('Refunded', $this->textdomain),
                        'checkout-draft' => __('Checkout draft', $this->textdomain),
                    ),
                    'default' => '',
                ),
            )
        );

        $settings = apply_filters('plugin_settings_fields', $settings);

        return $settings;
    }

    /**
     * Options getter
     * @return array Options, either saved or default ones.
     */
    public function get_options()
    {
        $options = get_option($this->plugin_name);
        if (!$options && is_array($this->settings)) {
            $options = array();
            foreach ($this->settings as $section => $data) {
                foreach ($data['fields'] as $field) {
                    $options[$field['id']] = $field['default'];
                }
            }

            add_option($this->plugin_name, $options);
        } elseif ($options && is_array($this->settings)) {
            foreach ($this->settings as $section => $data) {
                foreach ($data['fields'] as $field) {
                    if (!array_key_exists($field['id'], $options)) {
                        $options[$field['id']] = $field['default'];
                    }
                }
            }

            add_option($this->plugin_name, $options);
        }
        return $options;
    }

    /**
     * Register plugin settings
     * @return void
     */
    public function register_settings()
    {
        if (is_array($this->settings)) {

            register_setting($this->plugin_slug, $this->plugin_slug, array($this, 'validate_fields'));

            foreach ($this->settings as $section => $data) {

                // Add section to page
                add_settings_section($section, $data['title'], array($this, 'settings_section'), $this->plugin_slug);

                foreach ($data['fields'] as $field) {

                    // Add field to page
                    add_settings_field($field['id'], $field['label'], array($this, 'display_field'), $this->plugin_slug, $section, array('field' => $field));
                }
            }
        }
    }

    public function settings_section($section)
    {
        $html = '<p> ' . $this->settings[$section['id']]['description'] . '</p>' . "\n";
        echo $html;
    }

    /**
     * Generate HTML for displaying fields
     * @param  array $args Field data
     * @return void
     */
    public function display_field($args)
    {

        $field = $args['field'];

        $html = '';

        $option_name = $this->plugin_slug . "[" . $field['id'] . "]";

        $data = (isset($this->options[$field['id']])) ? $this->options[$field['id']] : '';

        switch ($field['type']) {

            case 'text':
            case 'password':
            case 'number':
                // Special handling for the Bot Token field
                if ($field['id'] === 'atr_wc_notifier_telegram_bot_token') {
                    $html .= '<input id="' . esc_attr($field['id']) . '" type="text" name="' . esc_attr($option_name) . '" placeholder="' . esc_attr($field['placeholder']) . '" value="" />' . "\n";
                    $html .= '<p class="description">' . __('Enter your Telegram Bot Token. If left empty, the current token will be retained.', $this->textdomain) . '</p>';
                } else {
                    $html .= '<input id="' . esc_attr($field['id']) . '" type="' . $field['type'] . '" name="' . esc_attr($option_name) . '" placeholder="' . esc_attr($field['placeholder']) . '" value="' . esc_attr($data) . '"/>' . "\n";
                }
                break;

            case 'text_secret':
                $html .= '<input id="' . esc_attr($field['id']) . '" type="text" name="' . esc_attr($option_name) . '" placeholder="' . esc_attr($field['placeholder']) . '" value=""/>' . "\n";
                break;

            case 'textarea':
                $html .= '<textarea id="' . esc_attr($field['id']) . '" rows="15" cols="150" name="' . esc_attr($option_name) . '" placeholder="' . esc_attr($field['placeholder']) . '">' . $data . '</textarea><br/>' . "\n";
                break;

            case 'checkbox':
                $checked = '';
                if ($data && 'on' == $data) {
                    $checked = 'checked="checked"';
                }
                $html .= '<input id="' . esc_attr($field['id']) . '" type="' . $field['type'] . '" name="' . esc_attr($option_name) . '" ' . $checked . '/>' . "\n";
                break;

            case 'checkbox_multi':
                foreach ($field['options'] as $k => $v) {
                    $checked = false;
                    if (is_array($data) && in_array($k, $data)) {
                        $checked = true;
                    }
                    $html .= '<label for="' . esc_attr($field['id'] . '_' . $k) . '"><input type="checkbox" ' . checked($checked, true, false) . ' name="' . esc_attr($option_name) . '[]" value="' . esc_attr($k) . '" id="' . esc_attr($field['id'] . '_' . $k) . '" /> ' . $v . '</label></br> ';
                }
                break;

            case 'user_roles_multi':
                global $wp_roles;
                $all_roles = $wp_roles->roles;
                $editable_roles = apply_filters('editable_roles', $all_roles);

                foreach ($editable_roles as $k => $v) {
                    $checked = false;
                    if (is_array($data) && in_array($k, $data)) {
                        $checked = true;
                    }
                    $html .= '<label for="' . esc_attr($field['id'] . '_' . $k) . '"><input type="checkbox" ' . checked($checked, true, false) . ' name="' . esc_attr($option_name) . '[]" value="' . esc_attr($k) . '" id="' . esc_attr($field['id'] . '_' . $k) . '" /> ' . $v['name'] . '</label> ';
                }
                $html .= '<br />';
                break;

            case 'radio':
                foreach ($field['options'] as $k => $v) {
                    $checked = false;
                    if ($k == $data) {
                        $checked = true;
                    }
                    $html .= '<label for="' . esc_attr($field['id'] . '_' . $k) . '"><input type="radio" ' . checked($checked, true, false) . ' name="' . esc_attr($option_name) . '" value="' . esc_attr($k) . '" id="' . esc_attr($field['id'] . '_' . $k) . '" /> ' . $v . '</label> ';
                }
                break;

            case 'select':
                $html .= '<select name="' . esc_attr($option_name) . '" id="' . esc_attr($field['id']) . '">';
                foreach ($field['options'] as $k => $v) {
                    $selected = false;
                    if ($k == $data) {
                        $selected = true;
                    }
                    $html .= '<option ' . selected($selected, true, false) . ' value="' . esc_attr($k) . '">' . $v . '</option>';
                }
                $html .= '</select> ';
                break;

            case 'select_multi':
                $html .= '<select name="' . esc_attr($option_name) . '[]" id="' . esc_attr($field['id']) . '" multiple="multiple">';
                foreach ($field['options'] as $k => $v) {
                    $selected = false;
                    if (in_array($k, $data)) {
                        $selected = true;
                    }
                    $html .= '<option ' . selected($selected, true, false) . ' value="' . esc_attr($k) . '" />' . $v . '</label> ';
                }
                $html .= '</select> ';
                break;
        }

        switch ($field['type']) {

            case 'checkbox_multi':
            case 'radio':
            case 'select_multi':
                $html .= '<br/><span class="description">' . $field['description'] . '</span>';
                break;

            default:
                $html .= '<label for="' . esc_attr($field['id']) . '"><span class="description">' . $field['description'] . '</span></label>' . "\n";
                break;
        }

        echo $html;
    }

    /**
     * Validate individual settings field
     * @param  array $data Inputted value
     * @return array       Validated value
     */
    public function validate_fields($data)
    {
        /**
         * Dev not: the following commented if statement can be used only if you want to prevent the encryption key from being changed after it's set.
         * If you want to allow the encryption key to be changed, you can remove the if statement.
         * If you want to prevent the encryption key from being changed, you can uncomment the if statement and comment the next if statement.
         * If you use this if statement, remember to add the explanation on the settings field description line "This can only be set once."
         * Note: If you use this if statement, the setting can be updated only once. The reason for that is to prevent the encryption key from being changed after it's set.
         */
        // Prevent changing the encryption key after it's set
        // if (isset($this->options['atr_wc_notifier_encryption_key']) && !empty($this->options['atr_wc_notifier_encryption_key'])) {
        //     $data['atr_wc_notifier_encryption_key'] = $this->options['atr_wc_notifier_encryption_key'];
        // }


        // Check if the encryption key has changed or if it's the first time setting it
        if (
            !empty($data['atr_wc_notifier_encryption_key']) &&
            (!isset($this->options['atr_wc_notifier_encryption_key']) ||
            $data['atr_wc_notifier_encryption_key'] !== $this->options['atr_wc_notifier_encryption_key'])
        ) {
            // If there is an existing token and key, decrypt and re-encrypt with the new key
            if (isset($this->options['atr_wc_notifier_telegram_bot_token']) && !empty($this->options['atr_wc_notifier_telegram_bot_token'])) {
                $old_key = $this->options['atr_wc_notifier_encryption_key'];
                $old_encrypted_token = $this->options['atr_wc_notifier_telegram_bot_token'];

                // Decrypt the token using the old key
                $decrypted_token = $this->decrypt_telegram_token($old_encrypted_token, $old_key);

                // Re-encrypt the token with the new key
                $new_encrypted_token = $this->encrypt_telegram_token($decrypted_token, $data['atr_wc_notifier_encryption_key']);

                // Update the data array with the new encrypted token
                $data['atr_wc_notifier_telegram_bot_token'] = $new_encrypted_token;
            }
        }

        // Encrypt the bot token if a new one is provided (when first saving or if re-entered)
        if (!empty($data['atr_wc_notifier_telegram_bot_token']) && !empty($data['atr_wc_notifier_encryption_key'])) {
            $data['atr_wc_notifier_telegram_bot_token'] = $this->encrypt_telegram_token($data['atr_wc_notifier_telegram_bot_token'], $data['atr_wc_notifier_encryption_key']);
        } else {
            // If the token field is empty, retain the existing encrypted token
            unset($data['atr_wc_notifier_telegram_bot_token']); // Prevent overwriting with an empty value
        }


        return $data;
    }

    /**
     * Check if WooCommerce is activated
     */

    private function is_woocommerce_activated()
    {
        if (class_exists('woocommerce')) {
            return true;
        } else {
            return false;
        }
    }

    private function encrypt_telegram_token($token, $key)
    {
        $iv_length = openssl_cipher_iv_length('aes-256-cbc');
        $iv = openssl_random_pseudo_bytes($iv_length);
        $encrypted_token = openssl_encrypt($token, 'aes-256-cbc', $key, 0, $iv);
        return base64_encode($encrypted_token . '::' . $iv);
    }

    private function decrypt_telegram_token($encrypted_token, $key)
    {
        list($encrypted_data, $iv) = explode('::', base64_decode($encrypted_token), 2);
        return openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
    }

    public function get_decrypted_telegram_token()
    {
        $encrypted_token = $this->options['atr_wc_notifier_telegram_bot_token'];
        $encryption_key = $this->options['atr_wc_notifier_encryption_key'];
        return $this->decrypt_telegram_token($encrypted_token, $encryption_key);
    }

    /**
     * Load settings page content
     * @return void
     */
    public function settings_page()
    {
        // Build page HTML output
        // If you don't need tabbed navigation just strip out everything between the <!-- Tab navigation --> tags.
?>
        <div class="wrap" id="<?php echo $this->plugin_slug; ?>">
            <h2><?php _e('ATR WooCommerce Order Notifier Settings', $this->textdomain); ?></h2>
            <p><?php _e('Settings.', $this->textdomain); ?></p>

            <!-- Tab navigation starts -->
            <h2 class="nav-tab-wrapper settings-tabs hide-if-no-js">
                <?php
                foreach ($this->settings as $section => $data) {
                    echo '<a href="#' . $section . '" class="nav-tab">' . $data['title'] . '</a>';
                }
                ?>
            </h2>
            <?php $this->do_script_for_tabbed_nav(); ?>
            <!-- Tab navigation ends -->

            <form action="options.php" method="POST">
                <?php settings_fields($this->plugin_slug); ?>
                <div class="settings-container">
                    <?php do_settings_sections($this->plugin_slug); ?>
                </div>
                <?php submit_button(); ?>
            </form>
        </div>
    <?php
    }

    /**
     * Print jQuery script for tabbed navigation
     * @return void
     */
    private function do_script_for_tabbed_nav()
    {
        // Very simple jQuery logic for the tabbed navigation.
        // Delete this function if you don't need it.
        // If you have other JS assets you may merge this there.
    ?>
        <script>
            jQuery(document).ready(function($) {
                var headings = jQuery('.settings-container > h2, .settings-container > h3');
                var paragraphs = jQuery('.settings-container > p');
                var tables = jQuery('.settings-container > table');
                var triggers = jQuery('.settings-tabs a');

                triggers.each(function(i) {
                    triggers.eq(i).on('click', function(e) {
                        e.preventDefault();
                        triggers.removeClass('nav-tab-active');
                        headings.hide();
                        paragraphs.hide();
                        tables.hide();

                        triggers.eq(i).addClass('nav-tab-active');
                        headings.eq(i).show();
                        paragraphs.eq(i).show();
                        tables.eq(i).show();
                    });
                })

                triggers.eq(0).click();
            });
        </script>
<?php
    }

    public function get_post_types()
    {
        $args = array(
            'public'   => true,
            '_builtin' => false,
        );

        $output = 'names'; // names or objects, note names is the default
        $operator = 'and'; // 'and' or 'or'

        $post_types = get_post_types($args, $output, $operator);

        if (!empty($post_types)) {
            $post_types_arr = [];
            //$post_types_arr[$item] = "";
            foreach ($post_types as $item) {
                $post_types_arr[$item] = "$item";
            }
        }
        return $post_types_arr;
    }
}
/**
 * Explain the atr_wc_notifier_encryption_key field in your plugin's settings.
 */
/*
The atr_wc_notifier_encryption_key field in your plugin's settings is a crucial security feature designed to ensure that the Telegram bot token is stored securely within your WordPress database.

Purpose of the atr_wc_notifier_encryption_key Field:
Encryption of Sensitive Data:

The atr_wc_notifier_encryption_key field allows the admin to input a custom encryption key. This key is used to encrypt the Telegram bot token before it is saved to the database.
Encryption ensures that even if someone gains unauthorized access to the database, the bot token remains protected and unreadable without the encryption key.
Decryption for Plugin Use:

When the plugin needs to use the Telegram bot token (e.g., to send a message via Telegram), the stored encrypted token is decrypted using the atr_wc_notifier_encryption_key.
This decryption process is done on-the-fly within the plugin, ensuring that the sensitive token is never exposed in plain text outside the plugin’s internal logic.
Key Features:
One-Time Setup:
The encryption key is intended to be set once. After the key is set, it cannot be changed from the plugin settings interface. This prevents potential security risks associated with changing the key.
Admin Responsibility:
The admin is responsible for providing a strong, unique encryption key. This key is critical to the security of the stored Telegram bot token.
Data Security:
The key must be securely stored and should be known only to the admin. The plugin relies on this key to ensure that encrypted data remains secure and accessible only by the plugin.
Practical Example:
Setting Up:

The admin enters the bot token and a strong encryption key in the plugin settings.
When the settings are saved, the plugin uses the encryption key to encrypt the bot token before storing it in the database.
During Operation:

When the plugin needs to send a Telegram message, it retrieves the encrypted token from the database, decrypts it using the stored encryption key, and then uses the decrypted token to communicate with the Telegram API.
By using the atr_wc_notifier_encryption_key, the plugin provides an additional layer of security, ensuring that sensitive information like the Telegram bot token is protected at rest and is only accessible by the plugin with the correct key.
*/