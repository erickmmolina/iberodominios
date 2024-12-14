<?php
/**
 * Plugin Name:       Iberodominios
 * Plugin URI:        https://ibero.capital
 * Description:       Permite conectarse a la API de Openprovider para listar los dominios disponibles y permitir comprarlos desde la interfaz de WordPress, requiere Elementor para funcionar.
 * Version:           1.0.0
 * Author:            Iberocapital
 * Author URI:        https://ibero.capital
 * License:           GPL-2.0-or-later
 * Text Domain:       iberodominios
 * Domain Path:       /languages
 */

// Salimos si se accede directamente
if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

// Definimos constantes importantes del plugin
define( 'IBERODOMINIOS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'IBERODOMINIOS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Cargamos archivos necesarios
require_once IBERODOMINIOS_PLUGIN_DIR . 'includes/class-iberodominios-plugin.php'; 

// Función de activación del plugin
function iberodominios_activate_plugin() {
    // En la activación podemos verificar si Elementor está activo, de lo contrario detenemos la activación.
    if ( ! did_action( 'elementor/loaded' ) ) {
        // Elementor no está activo
        deactivate_plugins( plugin_basename( __FILE__ ) );
        wp_die( __( 'Este plugin requiere que Elementor esté activo. Activa Elementor e intenta de nuevo.', 'iberodominios' ), 'Plugin dependency check', array( 'back_link' => true ) );
    }
}
register_activation_hook( __FILE__, 'iberodominios_activate_plugin' );

// Función de desactivación del plugin
function iberodominios_deactivate_plugin() {
    // Aquí se podrían limpiar caches, transientes o cualquier tarea necesaria al desactivar el plugin.
}
register_deactivation_hook( __FILE__, 'iberodominios_deactivate_plugin' );

// Inicializamos el plugin
function iberodominios_init_plugin() {
    // Cargamos la clase principal del plugin
    $plugin_instance = Iberodominios_Plugin::instance();
}
add_action( 'plugins_loaded', 'iberodominios_init_plugin' );
