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
                    'id'             => 'wp_wc_telegram_bot_token',
                    'label'            => __('Bot Token', $this->textdomain),
                    'description'    => __('tekegram Bot Token', $this->textdomain),
                    'type' => 'text',
                    'default' => '',
                    'placeholder' => 'Bot Token',
                ),           
                array(
                    'id'             => 'wp_wc_telegram_chat_id',
                    'label'            => __('Chat ID', $this->textdomain),
                    'description'    => __('tekegram Chat ID', $this->textdomain),
                    'type' => 'text',
                    'default' => '',
                    'placeholder' => 'Chat ID',
                ),                     
                array(
                    'id'             => 'wp_wc_telegram_enabled',
                    'label'            => __('Enable Telegram', $this->textdomain),
                    'description'    => __('Enable Telegram', $this->textdomain),
                    'type' => 'checkbox',
                    'default' => 'off',
                ),


            )
        );

        $settings = apply_filters('plugin_settings_fields', $settings);

        return $settings;
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
                $html .= '<input id="' . esc_attr($field['id']) . '" type="' . $field['type'] . '" name="' . esc_attr($option_name) . '" placeholder="' . esc_attr($field['placeholder']) . '" value="' . $data . '"/>' . "\n";
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
                    $html .= '<label for="' . esc_attr($field['id'] . '_' . $k) . '"><input type="checkbox" ' . checked($checked, true, false) . ' name="' . esc_attr($option_name) . '[]" value="' . esc_attr($k) . '" id="' . esc_attr($field['id'] . '_' . $k) . '" /> ' . $v . '</label> ';
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
            case 'categories_checkbox_multi_select':
                if ($this->is_woocommerce_activated()) {
                    foreach ($field['options'] as $k => $v) {
                        $html .= '<input type="text" id="atrCatSearchInput"  placeholder="' . __('Search for categories...', $this->textdomain) . '" title="Type in a category name"><a href="javascript:void(0);" class="atr-cats-select-actions atr-expand-all-cats" title="Expand all categories">Expand all</a><a href="javascript:void(0);" class="atr-cats-select-actions atr-close_all-cats" title="Close all categories">Close all</a><a href="javascript:void(0);" class="atr-cats-select-actions atr-check-all-cats" title="Check all categories">Check all</a><a href="javascript:void(0);" class="atr-cats-select-actions atr-uncheck-cats" title="Uncheck all categories">Uncheck all</a>';
                        $html .= '<ul class="atr-cat-list">';
                        if ($v) {
                            foreach ($v as $term_obj => $term_prop) {
                                $checked = false;
                                if (is_array($data) && in_array($term_prop->term_id, $data)) {
                                    $checked = true;
                                }
                                $html .= '<li parent-id="' . $term_prop->parent . '" li-id="' . $term_prop->term_id . '"><label for="' . esc_attr($field['id'] . '_' . $term_prop->name) . '">';
                                $html .= '<input class="categories-select-chkbox" type="checkbox" ' . checked($checked, true, false) . ' name="' . esc_attr($option_name) . '[]" value="' . esc_attr($term_prop->term_id) . '" id="' . esc_attr($field['id'] . '_' . $term_prop->term_id) . '" /> ';
                                $html .= $term_prop->name . '</label></li>';
                            }
                        }

                        $html .= '</ul>';
                    }
                } else {
                    $html .= __('Please activate Woocommerce...', $this->textdomain);
                }

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
        // $data array contains values to be saved:
        // either sanitize/modify $data or return false
        // to prevent the new options to be saved

        // Sanitize fields, eg. cast number field to integer
        // $data['number_field'] = (int) $data['number_field'];

        // Validate fields, eg. don't save options if the password field is empty
        // if ( $data['password_field'] == '' ) {
        // 	add_settings_error( $this->plugin_slug, 'no-password', __('A password is required.', $this->textdomain), 'error' );
        // 	return false;
        // }

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
