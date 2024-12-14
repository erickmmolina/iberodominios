<?php
/**
 * Clase principal del plugin Iberodominios
 * 
 * Esta clase inicializa el plugin, carga los widgets de Elementor, 
 * la clase para la API, y la página de ajustes.
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

require_once IBERODOMINIOS_PLUGIN_DIR . 'admin/class-iberodominios-settings-page.php';
require_once IBERODOMINIOS_PLUGIN_DIR . 'includes/class-iberodominios-api.php';
require_once IBERODOMINIOS_PLUGIN_DIR . 'includes/class-iberodominios-elementor-widget.php';

class Iberodominios_Plugin
{
    /**
     * Instancia singleton
     */
    private static $instance = null;

    /**
     * Constructor privado para patrón singleton
     */
    private function __construct()
    {
        // Cargar texto de internacionalización
        load_plugin_textdomain('iberodominios', false, dirname(plugin_basename(__FILE__)) . '/languages/');

        // Inicializar ajustes del plugin
        add_action('admin_init', array($this, 'register_plugin_settings'));

        // Agregar página de ajustes al menú de administración
        add_action('admin_menu', array($this, 'add_settings_page'));

        // Inicializar integración con Elementor
        add_action('elementor/widgets/register', array($this, 'register_elementor_widgets'));
    }

    /**
     * Método para obtener la instancia única de la clase
     */
    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Registrar ajustes del plugin en la base de datos, para almacenar credenciales de API
     */
    public function register_plugin_settings()
    {
        // Registramos opciones para guardar usuario, password y token de la API
        register_setting('iberodominios_settings_group', 'iberodominios_api_username');
        register_setting('iberodominios_settings_group', 'iberodominios_api_password');
        register_setting('iberodominios_settings_group', 'iberodominios_api_token');
        register_setting('iberodominios_settings_group', 'iberodominios_default_currency');
        register_setting('iberodominios_settings_group', 'iberodominios_show_promo');

        // El token puede ser generado y guardado tras el login. Aquí sólo se define el setting.
    }

    /**
     * Agregar página de ajustes al menú
     */
    public function add_settings_page()
    {
        add_menu_page(
            __('Iberodominios', 'iberodominios'),
            __('Iberodominios', 'iberodominios'),
            'manage_options',
            'iberodominios-settings',
            array($this, 'render_settings_page'),
            'dashicons-admin-network'
        );
    }

    /**
     * Renderizar la página de ajustes
     */
    public function render_settings_page()
    {
        // Utilizamos la clase separada para que sea más limpio
        $settings_page = new Iberodominios_Settings_Page();
        $settings_page->render_page();
    }

    /**
     * Registrar el widget de Elementor
     */
    public function register_elementor_widgets($widgets_manager)
    {
        // Registramos el widget definido en la clase Iberodominios_Elementor_Widget
        $widgets_manager->register_widget_type(new Iberodominios_Elementor_Widget());
    }
}
