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
     * The slug of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_slug    The current version of this plugin.
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
        $links[] = '<a href="' . esc_url(get_admin_url(null, 'admin.php?page=' . $this->plugin_name)) . '">' . __('Settings', 'atr-wc-order-notifier') . '</a>';
        $links[] = '<a href="http://atarimtr.com" target="_blank">More plugins by Yehuda Tiram (English)</a>';
        $links[] = '<a href="http://atarimtr.co.il" target="_blank">More plugins by Yehuda Tiram (Hebrew)</a>';
        return $links;
    }



    /**
     * Build settings fields
     * @return array Fields to be displayed on settings page
     */
    private function settings_fields()
    {
        $security_notice = '<div class="security-notice">';
        $security_notice .= '<h2 style="font-weight:bold;color:red;">' . esc_html__('🔐 Important Security Notice before you save the settings', 'atr-wc-order-notifier') . '</h2>';
        $security_notice .= '<p>' . esc_html__('Your bot token will be securely encrypted and stored in the database. Please take note of the following:', 'atr-wc-order-notifier') . '</p>';
        $security_notice .= '<ul>';
        /* translators: %1$s and %2$s: HTML tags for strong emphasis */
        $security_notice .= '<li>' . sprintf(__('This is your %1$sonly opportunity%2$s to copy the token if you did not do so yet.', 'atr-wc-order-notifier'), '<strong>', '</strong>') . '</li>';
        $security_notice .= '<li>' . esc_html__('It cannot be retrieved or displayed again through this interface. However, you can retrieve it from Telegram, look at the guide', 'atr-wc-order-notifier') . ' <a target="_blank" href="https://github.com/yehudaTiram/atr-wc-order-notifier">' . esc_html__('here in my Github', 'atr-wc-order-notifier') . '</a></li>';
        $security_notice .= '<li>' . esc_html__('Treat this token as you would any sensitive credential.', 'atr-wc-order-notifier') . '</li>';
        $security_notice .= '</ul>';
        $security_notice .= '<div class="action-steps">';
        $security_notice .= '<h3>' . esc_html__('Recommended Actions:', 'atr-wc-order-notifier') . '</h3>';
        $security_notice .= '<ol>';
        $security_notice .= '<li>' . esc_html__('Store it in a secure, private location.', 'atr-wc-order-notifier') . '</li>';
        $security_notice .= '<li>' . esc_html__('Consider using a password manager or encrypted note for safekeeping.', 'atr-wc-order-notifier') . '</li>';
        $security_notice .= '</ol>';
        $security_notice .= '</div>';
        $security_notice .= '<p class="warning"><strong>' . esc_html__('Note:', 'atr-wc-order-notifier') . '</strong> ' . esc_html__('If you lose this token, you can retrieve it from Telegram or you\'ll need to generate a new one.', 'atr-wc-order-notifier') . '</p>';
        $security_notice .= '</div>';

        $settings['easy'] = array(
            'title'                    => __('General', 'atr-wc-order-notifier'),
            'description'            => __('General settings', 'atr-wc-order-notifier'),
            'fields'                => array(
                array(
                    'id'             => 'atr_wc_notifier_telegram_bot_token',
                    'label'            => __('Bot Token', 'atr-wc-order-notifier'),
                    'description'    => $security_notice,
                    'type' => 'text',
                    'default' => '',
                    'placeholder' => 'Bot Token',
                ),
                array(
                    'id'             => 'atr_wc_notifier_telegram_chat_id',
                    'label'            => __('Chat ID', 'atr-wc-order-notifier'),
                    'description'    => __('telegram Chat ID', 'atr-wc-order-notifier'),
                    'type' => 'text',
                    'default' => '',
                    'placeholder' => 'Chat ID',
                ),
                array(
                    'id'             => 'atr_wc_notifier_telegram_enabled',
                    'label'            => __('Enable Telegram', 'atr-wc-order-notifier'),
                    'description'    => __('Enable Telegram', 'atr-wc-order-notifier'),
                    'type' => 'checkbox',
                    'default' => 'off',
                ),
                array(
                    'id' => 'atr_wc_notifier_statuses',
                    'label' => __('Select statuses', 'atr-wc-order-notifier'),
                    'description' => __('Select statuses to notify about.', 'atr-wc-order-notifier'),
                    'type' => 'checkbox_multi',
                    'options' => array(
                        'pending' => __('Pending payment', 'atr-wc-order-notifier'),
                        'on-hold' => __('On hold', 'atr-wc-order-notifier'),
                        'processing' => __('Processing', 'atr-wc-order-notifier'),
                        'cancelled' => __('Cancelled', 'atr-wc-order-notifier'),
                        'completed' => __('Completed', 'atr-wc-order-notifier'),
                        'failed' => __('Failed', 'atr-wc-order-notifier'),
                        'refunded' => __('Refunded', 'atr-wc-order-notifier'),
                        'checkout-draft' => __('Checkout draft', 'atr-wc-order-notifier'),
                    ),
                    'default' => '',
                ),
            )
        );
        $settings['sec_tab'] = array(
            'title'                    => __('Message details', 'atr-wc-order-notifier'),
            'description'            => __('The message details', 'atr-wc-order-notifier'),
            'fields' => array(
                array(
                    'id'          => 'order_id_in_message',
                    'label'       => __('Order ID', 'atr-wc-order-notifier'),
                    'description' => __('Include Order ID in the message', 'atr-wc-order-notifier'),
                    'type'        => 'checkbox',
                    'default'     => 'off',
                ),
                array(
                    'id'          => 'customer_name_in_message',
                    'label'       => __('Customer Name', 'atr-wc-order-notifier'),
                    'description' => __('Include Customer Name in the message', 'atr-wc-order-notifier'),
                    'type'        => 'checkbox',
                    'default'     => 'off',
                ),
                array(
                    'id'          => 'email_in_message',
                    'label'       => __('Email', 'atr-wc-order-notifier'),
                    'description' => __('Include Customer Email in the message', 'atr-wc-order-notifier'),
                    'type'        => 'checkbox',
                    'default'     => 'off',
                ),
                array(
                    'id'          => 'phone_in_message',
                    'label'       => __('Phone', 'atr-wc-order-notifier'),
                    'description' => __('Include Customer Phone in the message', 'atr-wc-order-notifier'),
                    'type'        => 'checkbox',
                    'default'     => 'off',
                ),
                array(
                    'id'          => 'billing_address_in_message',
                    'label'       => __('Billing Address', 'atr-wc-order-notifier'),
                    'description' => __('Include Billing Address in the message', 'atr-wc-order-notifier'),
                    'type'        => 'checkbox',
                    'default'     => 'off',
                ),
                array(
                    'id'          => 'shipping_address_in_message',
                    'label'       => __('Shipping Address', 'atr-wc-order-notifier'),
                    'description' => __('Include Shipping Address in the message', 'atr-wc-order-notifier'),
                    'type'        => 'checkbox',
                    'default'     => 'off',
                ),
                array(
                    'id'          => 'total_amount_in_message',
                    'label'       => __('Total Amount', 'atr-wc-order-notifier'),
                    'description' => __('Include Total Order Amount in the message', 'atr-wc-order-notifier'),
                    'type'        => 'checkbox',
                    'default'     => 'off',
                ),
                array(
                    'id'          => 'order_status_in_message',
                    'label'       => __('Order Status', 'atr-wc-order-notifier'),
                    'description' => __('Include Order Status in the message', 'atr-wc-order-notifier'),
                    'type'        => 'checkbox',
                    'default'     => 'off',
                ),
                array(
                    'id'          => 'date_created_in_message',
                    'label'       => __('Date Created', 'atr-wc-order-notifier'),
                    'description' => __('Include Order Creation Date in the message', 'atr-wc-order-notifier'),
                    'type'        => 'checkbox',
                    'default'     => 'off',
                ),
                array(
                    'id'          => 'date_modified_in_message',
                    'label'       => __('Date Modified', 'atr-wc-order-notifier'),
                    'description' => __('Include Order Modification Date in the message', 'atr-wc-order-notifier'),
                    'type'        => 'checkbox',
                    'default'     => 'off',
                ),
                array(
                    'id'          => 'product_name_in_message',
                    'label'       => __('Product Name', 'atr-wc-order-notifier'),
                    'description' => __('Include Product Name(s) in the message', 'atr-wc-order-notifier'),
                    'type'        => 'checkbox',
                    'default'     => 'off',
                ),
                array(
                    'id'          => 'product_quantity_in_message',
                    'label'       => __('Product Quantity', 'atr-wc-order-notifier'),
                    'description' => __('Include Product Quantity in the message', 'atr-wc-order-notifier'),
                    'type'        => 'checkbox',
                    'default'     => 'off',
                ),
                array(
                    'id'          => 'payment_method_in_message',
                    'label'       => __('Payment Method', 'atr-wc-order-notifier'),
                    'description' => __('Include Payment Method in the message', 'atr-wc-order-notifier'),
                    'type'        => 'checkbox',
                    'default'     => 'off',
                ),
                array(
                    'id'          => 'customer_note_in_message',
                    'label'       => __('Customer Note', 'atr-wc-order-notifier'),
                    'description' => __('Include Customer Note in the message', 'atr-wc-order-notifier'),
                    'type'        => 'checkbox',
                    'default'     => 'off',
                ),
                array(
                    'id'          => 'order_link_in_message',
                    'label'       => __('Order Link', 'atr-wc-order-notifier'),
                    'description' => __('Include a clickable link to the order details in the message', 'atr-wc-order-notifier'),
                    'type'        => 'checkbox',
                    'default'     => 'off',
                )

            ),

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
                    $html .= '<p class="description">' . __('Enter your Telegram Bot Token. If left empty, the current token will be retained.', 'atr-wc-order-notifier') . '</p>';
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
     * Handles the bot token field separately from other fields.
     * If the bot token field is empty, it either uses the existing token or removes the field if no token exists.
     * If a new token is provided, it encrypts it.
     * Merges the updated data with the current options, ensuring all other field changes are preserved.
     * This approach ensures that:
     * Changes to other fields are always saved, regardless of the bot token field's state.
     * The bot token is handled correctly (preserved when empty, encrypted when new).
     * All existing options are maintained, even if not present in the submitted data.
     * @param  array $data Inputted value
     * @return array       Validated value
     */
    public function validate_fields($data)
    {
        $utils = new Atr_Wc_Order_Notifier_Admin_utils($this->plugin_name, $this->version);
        $encryption_key = $utils->get_encryption_key();

        // Get the current options using the specified format
        $current_options = get_option($this->plugin_name, $this->version, array());

        // Handle the bot token field
        if (!empty($data['atr_wc_notifier_telegram_bot_token'])) {
            // If a new token is provided, encrypt it
            $data['atr_wc_notifier_telegram_bot_token'] = $this->encrypt_telegram_token($data['atr_wc_notifier_telegram_bot_token'], $encryption_key);
        } else {
            // If the token field is empty, use the existing encrypted token (if any)
            if (isset($current_options['atr_wc_notifier_telegram_bot_token'])) {
                $data['atr_wc_notifier_telegram_bot_token'] = $current_options['atr_wc_notifier_telegram_bot_token'];
            }
        }

        // Merge the updated data with current options to ensure all fields are preserved
        $updated_options = array_merge($current_options, $data);

        return $updated_options;
    }

    private function encrypt_telegram_token($token, $key)
    {
        $iv_length = openssl_cipher_iv_length('aes-256-cbc');
        $iv = openssl_random_pseudo_bytes($iv_length);
        $encrypted_token = openssl_encrypt($token, 'aes-256-cbc', $key, 0, $iv);
        return base64_encode($encrypted_token . '::' . $iv);
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

    /**
     * Load settings page content
     * @return void
     */
    public function settings_page()
    {
        // Build page HTML output
        // If you don't need tabbed navigation just strip out everything between the <!-- Tab navigation --> tags.
?>
        <div class="wrap" id="<?php echo esc_html($this->plugin_slug); ?>">
            <h2><?php esc_attr_e('ATR WooCommerce Order Notifier Settings', 'atr-wc-order-notifier'); ?></h2>
            <p><?php esc_attr_e('Settings.', 'atr-wc-order-notifier'); ?></p>

            <!-- Tab navigation starts -->
            <h2 class="nav-tab-wrapper settings-tabs hide-if-no-js">
                <?php
                foreach ($this->settings as $section => $data) {
                    echo '<a href="#' . esc_html($section) . '" class="nav-tab">' . esc_html($data['title']) . '</a>';
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
