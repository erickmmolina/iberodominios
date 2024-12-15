<?php
if (!defined('ABSPATH'))
    exit;

require_once IBERODOMINIOS_PLUGIN_DIR . 'admin/class-iberodominios-settings-page.php';
require_once IBERODOMINIOS_PLUGIN_DIR . 'includes/class-iberodominios-api.php';
require_once IBERODOMINIOS_PLUGIN_DIR . 'includes/class-iberodominios-db.php';
require_once IBERODOMINIOS_PLUGIN_DIR . 'includes/class-iberodominios-ajax.php';

class Iberodominios_Plugin
{
    private static $instance = null;

    private function __construct()
    {
        // Registrar ajustes
        add_action('admin_init', array($this, 'register_plugin_settings'));
        add_action('admin_menu', array($this, 'add_settings_page'));

        // Scripts
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));

        // Elementor
        if (did_action('elementor/loaded')) {
            add_action('elementor/widgets/register', array($this, 'register_elementor_widgets'));
        } else {
            add_action('admin_notices', function () {
                echo '<div class="notice notice-warning"><p>' . __('Iberodominios requiere Elementor para funcionar correctamente. Por favor, activa Elementor.', 'iberodominios') . '</p></div>';
            });
        }

        // AJAX principal
        add_action('wp_ajax_iberodominios_check_domain', array('Iberodominios_AJAX', 'check_domain'));
        add_action('wp_ajax_nopriv_iberodominios_check_domain', array('Iberodominios_AJAX', 'check_domain'));

        // Acción AJAX para batch
        add_action('wp_ajax_iberodominios_check_batch', array('Iberodominios_AJAX', 'check_batch'));
        add_action('wp_ajax_nopriv_iberodominios_check_batch', array('Iberodominios_AJAX', 'check_batch'));

        // Acción AJAX para búsqueda de TLDs en backend
        add_action('wp_ajax_iberodominios_search_tlds', array($this, 'iberodominios_search_tlds_callback'));

        // Acción AJAX para chequeo individual
        add_action('wp_ajax_iberodominios_check_single', array('Iberodominios_AJAX', 'check_single'));
        add_action('wp_ajax_nopriv_iberodominios_check_single', array('Iberodominios_AJAX', 'check_single'));
    }

    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function register_plugin_settings()
    {
        register_setting('iberodominios_settings_group', 'iberodominios_api_username');
        register_setting('iberodominios_settings_group', 'iberodominios_api_password');
        register_setting('iberodominios_settings_group', 'iberodominios_api_token');
        register_setting('iberodominios_settings_group', 'iberodominios_default_currency');
        register_setting('iberodominios_settings_group', 'iberodominios_show_promo');
        register_setting('iberodominios_settings_group', 'iberodominios_popular_tlds', array('default' => []));
        register_setting('iberodominios_settings_group', 'iberodominios_initial_results_count', array('default' => 10));
        register_setting('iberodominios_settings_group', 'iberodominios_deep_cleanup');
    }

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

    public function render_settings_page()
    {
        $settings_page = new Iberodominios_Settings_Page();
        $settings_page->render_page();
    }

    public function register_elementor_widgets($widgets_manager)
    {
        require_once IBERODOMINIOS_PLUGIN_DIR . 'includes/class-iberodominios-elementor-widget.php';
        $widgets_manager->register_widget_type(new Iberodominios_Elementor_Widget());
    }

    public function enqueue_frontend_assets()
    {
        wp_enqueue_style('iberodominios-frontend', IBERODOMINIOS_PLUGIN_URL . 'assets/css/frontend.css', array(), '1.0.0');
        wp_enqueue_script('iberodominios-frontend', IBERODOMINIOS_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), '1.0.0', true);

        wp_localize_script('iberodominios-frontend', 'IberodominiosAjax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('iberodominios_ajax_nonce'),
            'plugin_url' => IBERODOMINIOS_PLUGIN_URL
        ));
    }

    public function enqueue_admin_assets($hook)
    {
        if (strpos($hook, 'iberodominios-settings') !== false) {
            wp_enqueue_script('jquery-ui-sortable');
            // Encolar Select2
            wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', array(), '4.1.0');
            wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), '4.1.0', true);

            wp_enqueue_script('iberodominios-admin', IBERODOMINIOS_PLUGIN_URL . 'assets/js/admin.js', array('jquery', 'select2-js'), '1.0.0', true);
            wp_localize_script('iberodominios-admin', 'IberodominiosAdmin', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('iberodominios_admin_ajax_nonce')
            ]);
        }
    }

    public static function create_tables()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'iberodominios_tlds';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            tld_name VARCHAR(100) NOT NULL,
            status VARCHAR(20) DEFAULT 'ACT',
            PRIMARY KEY (id),
            KEY tld_name (tld_name)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function iberodominios_search_tlds_callback()
    {
        check_ajax_referer('iberodominios_admin_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('No tienes permisos para realizar esta acción.', 'iberodominios')]);
        }

        $q = isset($_POST['q']) ? sanitize_text_field($_POST['q']) : '';
        if (strlen($q) < 2) {
            // Devolver array vacío si la longitud es menor a 2
            wp_send_json_success([]);
        }

        $results = Iberodominios_DB::search_tlds($q, 50);
        // Devolvemos directamente el array
        wp_send_json_success($results);
    }

}
