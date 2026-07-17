<?php
/**
 * Plugin Name: Chatra Live Chat + ChatBot + Cart Saver
 * Plugin URI: https://chatra.com/help/cms/wordpress/
 * Description: Chatra allows you to chat with your visitors, view the list of visitors who are currently online on your website and start a conversation manually or via configurable automatic targeted messages.
 * Author: Chatra
 * Author URI: https://chatra.com
 * Version: 1.0.13
 * Requires at least: 4.5
 * Requires PHP: 5.6
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: chatra-live-chat
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

// Add multilingual support
add_action('init', 'chatra_plugin_init');
function chatra_plugin_init()
{
    load_plugin_textdomain('chatra-live-chat', false, dirname(plugin_basename(__FILE__)) . '/languages');
}

// Add settings page and register settings with WordPress
add_action('admin_menu', 'chatra_setup');
function chatra_setup()
{
    add_submenu_page('options-general.php', __('Chatra widget', 'chatra-live-chat'), __('Chatra widget', 'chatra-live-chat'), 'manage_options', 'options-chatra', 'chatra_settings');
    register_setting('chatra', 'chatra-code', 'chatra_sanitize_id');
}

// Keep only the alphanumeric Chatra widget ID from the pasted code (or a bare ID)
function chatra_sanitize_id($value)
{
    $id_pattern = '[A-Za-z0-9]{4,64}';
    if (!is_string($value)) {
        return '';
    }
    $value = trim($value);
    if (preg_match('/^' . $id_pattern . '$/', $value)) {
        return $value;
    }
    if (preg_match('/ChatraID\s*[:=]\s*[\'"](' . $id_pattern . ')[\'"]/', $value, $m)) {
        return $m[1];
    }
    return '';
}

// Display settings page
function chatra_settings()
{
    $allowed_html = array(
        'a'  => array('href' => array(), 'target' => array()),
        'br' => array(),
    );
    echo "<h2>" . esc_html__('Chat widget setup', 'chatra-live-chat') . "</h2>";
    if (get_option('chatra-code')) {
        echo "<p>";
        /* translators: 1: site home URL, 2: Chatra dashboard URL */
        printf(wp_kses(__('Seems like everything is OK! <br>Check your <a href="%1$s" target="_blank">website</a> to see if the live chat widget is present.<br>Log in to your <a href="%2$s" target="_blank">Chatra dashboard</a> to chat with your website visitors and manage preferences.', 'chatra-live-chat'), $allowed_html), esc_url(home_url()), esc_url('https://app.chatra.io/?utm_source=WP&utm_campaign=WP'));
        echo "</p>";
    } else {
        echo "<p>";
        /* translators: 1: Chatra sign-up URL, 2: Chatra widget-code settings URL */
        printf(wp_kses(__('Signup for a free Chatra account at <a href="%1$s" target="_blank">app.chatra.io</a>,<br> then copy and paste <a href="%2$s" target="_blank">Widget code</a> from Chatra dashboard settings into the form below:', 'chatra-live-chat'), $allowed_html), esc_url('https://app.chatra.io/?utm_source=WP&utm_campaign=WP'), esc_url('https://app.chatra.io/settings/integrations/widget?utm_source=WP&utm_campaign=WP'));
        echo "</p>";
    }
    echo "<form action=\"options.php\" method=\"POST\">";
    settings_fields('chatra');
    do_settings_sections('chatra');
    echo "<textarea cols=\"80\" rows=\"14\" name=\"chatra-code\">" . esc_textarea(get_option('chatra-code')) . "</textarea>";
    submit_button();
    echo "</form>";
}

add_action('wp_enqueue_scripts', 'chatra_add_code');
function chatra_add_code()
{
    $id = chatra_sanitize_id(get_option('chatra-code'));
    if (!$id) {
        return;
    }
    wp_enqueue_script('chatra-widget', 'https://call.chatra.io/chatra.js', array(), '1.0.13', true);
    wp_add_inline_script(
        'chatra-widget',
        "window.ChatraID = '" . esc_js($id) . "';\n"
            . "window.Chatra = window.Chatra || function() { (window.Chatra.q = window.Chatra.q || []).push(arguments); };",
        'before'
    );
}
