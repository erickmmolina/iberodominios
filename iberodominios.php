<?php
/**
 * Plugin Name:       Iberodominios
 * Plugin URI:        https://ibero.capital
 * Description:       Permite conectarse a la API de Openprovider, listar y comprar dominios usando AJAX. Integra TLDs locales, sincronización y configuraciones avanzadas.
 * Version:           2.0.0
 * Author:            Iberocapital
 * Author URI:        https://ibero.capital
 * License:           GPL-2.0-or-later
 * Text Domain:       iberodominios
 */

if (!defined('ABSPATH')) {
    exit;
}

define('IBERODOMINIOS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('IBERODOMINIOS_PLUGIN_URL', plugin_dir_url(__FILE__));

add_action('init', function () {
    load_plugin_textdomain('iberodominios', false, dirname(plugin_basename(__FILE__)) . '/languages/');
});

require_once IBERODOMINIOS_PLUGIN_DIR . 'includes/class-iberodominios-plugin.php';

function iberodominios_activate_plugin()
{
    Iberodominios_Plugin::create_tables();
}
register_activation_hook(__FILE__, 'iberodominios_activate_plugin');

function iberodominios_deactivate_plugin()
{
    // Limpieza si se requiere
}
register_deactivation_hook(__FILE__, 'iberodominios_deactivate_plugin');

add_action('plugins_loaded', function () {
    $plugin_instance = Iberodominios_Plugin::instance();
});
