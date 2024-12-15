<?php
// if uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Verificar la opción deep_cleanup
$deep_cleanup = get_option('iberodominios_deep_cleanup', 0);

if ($deep_cleanup) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'iberodominios_tlds';

    // Borrar tabla
    $wpdb->query("DROP TABLE IF EXISTS $table_name");

    // Borrar opciones
    $options = [
        'iberodominios_api_username',
        'iberodominios_api_password',
        'iberodominios_api_token',
        'iberodominios_default_currency',
        'iberodominios_show_promo',
        'iberodominios_popular_tlds',
        'iberodominios_initial_results_count',
        'iberodominios_deep_cleanup',
    ];

    foreach ($options as $opt) {
        delete_option($opt);
    }
}
