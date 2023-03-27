<?php
/**
 * Plugin Name: WP Index URL
 * Plugin URI: https://github.com/italomsr/wp-index/
 * Description: Um plugin para indexar várias URLs no Google via API do Search Console.
 * Version: 1.0
 * Author: Italo Mariano
 * Author URI: https://www.linkedin.com/in/italomsr/
 * License: GPL2
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'index-admin-page.php';

function index_url_activation() {
    add_option('index_google_json_key', '');
}
register_activation_hook(__FILE__, 'index_url_activation');

function index_urls_deactivation() {
    delete_option('index_google_json_key');
}
register_deactivation_hook(__FILE__, 'index_urls_deactivation');
