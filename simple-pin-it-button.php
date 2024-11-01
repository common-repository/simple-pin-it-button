<?php
/**
 * Plugin Name: Simple Pin It Button
 * Plugin URI: https://wordpress.org/plugins/simple-pin-it-button/
 * Description: Simple Pin It Button is a WordPress plugin that adds a customizable "Pin it" button over images on hover with various options. It's designed to make it easy for your website visitors to share images on Pinterest, potentially increasing your site's social media engagement.
 * Version: 2.3.2
 * Author: Rank Rivet
 * Author URI: http://rankrivet.com
 * License: GPL2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: simple-pin-it-button
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * Tested up to: 6.6.2
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

class SimplePinItButtonForWP {
    private $options;

    public function __construct() {
        $this->options = get_option('simple_pin_it_button_options');
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function enqueue_scripts() {
    if ($this->should_load_assets()) {
        wp_enqueue_style('simple-pin-it-button', plugins_url('css/style.css', __FILE__), array(), '2.3.2', 'all');
        wp_style_add_data('simple-pin-it-button', 'priority', 100);
            wp_enqueue_script('simple-pin-it-button', plugins_url('js/script.js', __FILE__), array('jquery'), '2.3.2', true);
            wp_script_add_data('simple-pin-it-button', 'defer', true);
            wp_add_inline_script('simple-pin-it-button', 'var pinItOptions = ' . wp_json_encode($this->options) . ';', 'before');
        }
    }

    private function should_load_assets() {
        $load = false;
        if (!empty($this->options['show_on_posts']) && is_single()) {
            $load = true;
        }
        if (!empty($this->options['show_on_pages']) && is_page()) {
            $load = true;
        }
        if (!empty($this->options['show_on_archives']) && (is_archive() || is_home())) {
            $load = true;
        }
        return apply_filters('simple_pin_it_button_load_assets', $load);
    }

    public function add_admin_menu() {
        add_options_page(
            esc_html__('Simple Pin It Button Settings', 'simple-pin-it-button'),
            esc_html__('Simple Pin It Button', 'simple-pin-it-button'),
            'manage_options',
            'simple-pin-it-button',
            array($this, 'options_page')
        );
    }

    public function register_settings() {
        register_setting('simple_pin_it_button_options', 'simple_pin_it_button_options', array($this, 'validate_options'));
        
        add_settings_section('general_settings', esc_html__('General Settings', 'simple-pin-it-button'), null, 'simple-pin-it-button');
        
        $this->add_settings_field('button_color', esc_html__('Button Color', 'simple-pin-it-button'), 'color');
        $this->add_settings_field('button_size', esc_html__('Button Size', 'simple-pin-it-button'), 'select', array('small' => esc_html__('Small', 'simple-pin-it-button'), 'medium' => esc_html__('Medium', 'simple-pin-it-button'), 'large' => esc_html__('Large', 'simple-pin-it-button')));
        $this->add_settings_field('button_shape', esc_html__('Button Shape', 'simple-pin-it-button'), 'select', array('rectangle' => esc_html__('Rectangle', 'simple-pin-it-button'), 'round' => esc_html__('Round', 'simple-pin-it-button')));
        $this->add_settings_field('button_position', esc_html__('Button Position', 'simple-pin-it-button'), 'select', array('top-left' => esc_html__('Top Left', 'simple-pin-it-button'), 'top-right' => esc_html__('Top Right', 'simple-pin-it-button')));
        $this->add_settings_field('button_text', esc_html__('Button Text', 'simple-pin-it-button'), 'select', array('pin-it' => esc_html__('Pin it', 'simple-pin-it-button'), 'save' => esc_html__('Save', 'simple-pin-it-button')));
        $this->add_settings_field('show_on_mobile', esc_html__('Show on Mobile', 'simple-pin-it-button'), 'checkbox');
        $this->add_settings_field('show_on_posts', esc_html__('Show on Posts', 'simple-pin-it-button'), 'checkbox');
        $this->add_settings_field('show_on_pages', esc_html__('Show on Pages', 'simple-pin-it-button'), 'checkbox');
        $this->add_settings_field('show_on_archives', esc_html__('Show on Post Archives', 'simple-pin-it-button'), 'checkbox');
    }

    private function add_settings_field($id, $title, $type, $options = array()) {
        add_settings_field(
            $id,
            $title,
            array($this, 'settings_field_callback'),
            'simple-pin-it-button',
            'general_settings',
            array('id' => $id, 'type' => $type, 'options' => $options)
        );
    }

    public function settings_field_callback($args) {
        $id = $args['id'];
        $type = $args['type'];
        $options = $args['options'];
        $value = isset($this->options[$id]) ? $this->options[$id] : '';

        switch ($type) {
            case 'color':
                echo '<input type="color" id="' . esc_attr($id) . '" name="simple_pin_it_button_options[' . esc_attr($id) . ']" value="' . esc_attr($value) . '" />';
                break;
            case 'select':
                echo '<select id="' . esc_attr($id) . '" name="simple_pin_it_button_options[' . esc_attr($id) . ']">';
                foreach ($options as $key => $label) {
                    echo '<option value="' . esc_attr($key) . '" ' . selected($value, $key, false) . '>' . esc_html($label) . '</option>';
                }
                echo '</select>';
                break;
            case 'checkbox':
                echo '<input type="checkbox" id="' . esc_attr($id) . '" name="simple_pin_it_button_options[' . esc_attr($id) . ']" value="1" ' . checked($value, true, false) . ' />';
                break;
        }
    }

    public function options_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Simple Pin It Button Settings', 'simple-pin-it-button'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('simple_pin_it_button_options');
                do_settings_sections('simple-pin-it-button');
                wp_nonce_field('simple_pin_it_button_nonce_action', 'simple_pin_it_button_nonce');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function validate_options($input) {
        // Verify the nonce
        $nonce_value = isset($_POST['simple_pin_it_button_nonce']) ? sanitize_text_field(wp_unslash($_POST['simple_pin_it_button_nonce'])) : '';
        if (empty($nonce_value) || !wp_verify_nonce($nonce_value, 'simple_pin_it_button_nonce_action')) {
            add_settings_error('simple_pin_it_button_options', 'nonce_error', esc_html__('Security check failed. Please try again.', 'simple-pin-it-button'), 'error');
            return $this->options; // Return old options if nonce verification fails
        }

        $new_input = array();
        $new_input['button_color'] = sanitize_hex_color($input['button_color']);
        $new_input['button_size'] = sanitize_text_field($input['button_size']);
        $new_input['button_shape'] = sanitize_text_field($input['button_shape']);
        $new_input['button_position'] = sanitize_text_field($input['button_position']);
        $new_input['button_text'] = sanitize_text_field($input['button_text']);
        $new_input['show_on_mobile'] = isset($input['show_on_mobile']) ? true : false;
        $new_input['show_on_posts'] = isset($input['show_on_posts']) ? true : false;
        $new_input['show_on_pages'] = isset($input['show_on_pages']) ? true : false;
        $new_input['show_on_archives'] = isset($input['show_on_archives']) ? true : false;
        return $new_input;
    }
}

// Initialize the plugin
$simple_pin_it_button = new SimplePinItButtonForWP();