<?php
if (!defined('ABSPATH'))
    exit;

class Iberodominios_Settings_Page
{
    public function render_page()
    {
        // Procesar acciones
        if (isset($_POST['iberodominios_sync_tlds']) && check_admin_referer('iberodominios_sync_tlds_action', 'iberodominios_sync_tlds_nonce')) {
            $this->sync_tlds();
        }

        if (isset($_POST['iberodominios_generate_token_action']) && check_admin_referer('iberodominios_generate_token', 'iberodominios_generate_token_nonce')) {
            $this->generate_token_action();
        }

        if (isset($_POST['iberodominios_create_tables']) && check_admin_referer('iberodominios_create_tables_action', 'iberodominios_create_tables_nonce')) {
            $this->create_tables_action();
        }

        if (isset($_POST['iberodominios_drop_tables']) && check_admin_referer('iberodominios_drop_tables_action', 'iberodominios_drop_tables_nonce')) {
            $this->drop_tables_action();
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'iberodominios_tlds';
        $tlds = array();
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
            $tlds = $wpdb->get_col("SELECT tld_name FROM $table_name ORDER BY tld_name ASC");
        }

        $popular = (array) get_option('iberodominios_popular_tlds', []);
        $initial_results_count = (int) get_option('iberodominios_initial_results_count', 10);
        $current_token = get_option('iberodominios_api_token');
        ?>
        <div class="wrap">
            <h1><?php _e('Ajustes de Iberodominios', 'iberodominios'); ?></h1>

            <form method="post" action="options.php">
                <?php settings_fields('iberodominios_settings_group'); ?>
                <?php do_settings_sections('iberodominios_settings_group'); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php _e('Usuario API', 'iberodominios'); ?></th>
                        <td><input type="text" name="iberodominios_api_username"
                                value="<?php echo esc_attr(get_option('iberodominios_api_username')); ?>"
                                style="width: 300px;" /></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php _e('Contraseña API', 'iberodominios'); ?></th>
                        <td><input type="password" name="iberodominios_api_password"
                                value="<?php echo esc_attr(get_option('iberodominios_api_password')); ?>"
                                style="width: 300px;" />
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php _e('Moneda por defecto', 'iberodominios'); ?></th>
                        <td>
                            <?php
                            $current_currency = get_option('iberodominios_default_currency', 'EUR');
                            $currencies = array('EUR', 'USD', 'GBP');
                            ?>
                            <select name="iberodominios_default_currency">
                                <?php foreach ($currencies as $cur): ?>
                                    <option value="<?php echo esc_attr($cur); ?>" <?php selected($current_currency, $cur); ?>>
                                        <?php echo esc_html($cur); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php _e('Mostrar dominios promocionales por defecto', 'iberodominios'); ?></th>
                        <td>
                            <input type="checkbox" name="iberodominios_show_promo" value="1" <?php checked(get_option('iberodominios_show_promo'), 1); ?> />
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php _e('Resultados iniciales a mostrar', 'iberodominios'); ?></th>
                        <td>
                            <input type="number" name="iberodominios_initial_results_count"
                                value="<?php echo esc_attr($initial_results_count); ?>" style="width:100px;" />
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php _e('TLDs Populares', 'iberodominios'); ?></th>
                        <td>
                            <?php if (!$tlds || count($tlds) === 0): ?>
                                <p><?php _e('No hay TLDs sincronizadas aún.', 'iberodominios'); ?><br><?php _e('Primero sincroniza las TLDs.', 'iberodominios'); ?>
                                </p>
                            <?php else: ?>
                                <p class="description">
                                    <?php _e('Busca y selecciona TLDs usando el campo de abajo. Las TLDs seleccionadas se añadirán a la lista de abajo. Puedes arrastrar y soltar para reordenar.', 'iberodominios'); ?>
                                </p>
                                <!-- Select2 para buscar TLDs -->
                                <select id="tld-select" style="width:300px;"></select>

                                <h4><?php _e('TLDs Populares Seleccionadas', 'iberodominios'); ?></h4>
                                <ul id="popular-tlds-list" style="border:1px solid #ccc; padding:5px; min-height:50px;">
                                    <?php foreach ($popular as $ptld): ?>
                                        <li style="cursor:move;"><?php echo esc_html($ptld); ?><input type="hidden"
                                                name="iberodominios_popular_tlds[]" value="<?php echo esc_attr($ptld); ?>"></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php _e('Limpieza profunda al desinstalar', 'iberodominios'); ?></th>
                        <td>
                            <input type="checkbox" name="iberodominios_deep_cleanup" value="1" <?php checked(get_option('iberodominios_deep_cleanup'), 1); ?> />
                            <p class="description">
                                <?php _e('Si se marca, al eliminar el plugin se borrarán la tabla de TLDs y todas las opciones.', 'iberodominios'); ?>
                            </p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php _e('Stripe Public Key', 'iberodominios'); ?></th>
                        <td>
                            <input type="text" name="iberodominios_stripe_public_key"
                                value="<?php echo esc_attr(get_option('iberodominios_stripe_public_key')); ?>"
                                style="width:300px;">
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php _e('Stripe Secret Key', 'iberodominios'); ?></th>
                        <td>
                            <input type="password" name="iberodominios_stripe_secret_key"
                                value="<?php echo esc_attr(get_option('iberodominios_stripe_secret_key')); ?>"
                                style="width:300px;">
                        </td>
                    </tr>

                </table>
                <?php submit_button(__('Guardar Ajustes', 'iberodominios')); ?>
            </form>

            <hr>

            <h2><?php _e('Generar Token', 'iberodominios'); ?></h2>
            <p><?php _e('Pulsa el botón para obtener un token de acceso a la API de Openprovider usando las credenciales anteriores.', 'iberodominios'); ?>
            </p>
            <form method="post">
                <?php wp_nonce_field('iberodominios_generate_token', 'iberodominios_generate_token_nonce'); ?>
                <input type="submit" class="button button-primary" name="iberodominios_generate_token_action"
                    value="<?php _e('Generar Token', 'iberodominios'); ?>" />
            </form>

            <?php
            if (!empty($current_token)) {
                echo '<h3>' . __('Token Actual', 'iberodominios') . '</h3>';
                echo '<p><strong>' . esc_html($current_token) . '</strong></p>';
            }
            ?>

            <hr>

            <h2><?php _e('Sincronizar TLDs', 'iberodominios'); ?></h2>
            <p><?php _e('Presiona el botón para obtener la lista completa de TLDs desde Openprovider. Esto podría tardar.', 'iberodominios'); ?>
            </p>
            <form method="post">
                <?php wp_nonce_field('iberodominios_sync_tlds_action', 'iberodominios_sync_tlds_nonce'); ?>
                <input type="submit" class="button button-primary" name="iberodominios_sync_tlds"
                    value="<?php _e('Sincronizar TLDs', 'iberodominios'); ?>" />
            </form>

            <hr>

            <h2><?php _e('Herramientas de base de datos', 'iberodominios'); ?></h2>
            <form method="post" style="margin-bottom:10px;">
                <?php wp_nonce_field('iberodominios_create_tables_action', 'iberodominios_create_tables_nonce'); ?>
                <input type="submit" class="button" name="iberodominios_create_tables"
                    value="<?php _e('Crear Tablas', 'iberodominios'); ?>" />
            </form>

            <form method="post">
                <?php wp_nonce_field('iberodominios_drop_tables_action', 'iberodominios_drop_tables_nonce'); ?>
                <input type="submit" class="button" name="iberodominios_drop_tables"
                    value="<?php _e('Eliminar Tablas', 'iberodominios'); ?>" />
            </form>
        </div>
        <?php
    }

    private function sync_tlds()
    {
        $username = get_option('iberodominios_api_username');
        $password = get_option('iberodominios_api_password');
        $token = get_option('iberodominios_api_token');

        if (empty($username) || empty($password)) {
            echo '<div class="error"><p>' . __('Por favor configura tus credenciales antes de sincronizar.', 'iberodominios') . '</p></div>';
            return;
        }

        $api = new Iberodominios_API($username, $password, $token);
        if (empty($token)) {
            $new_token = $api->generate_token();
            if ($new_token) {
                update_option('iberodominios_api_token', $new_token);
                $api = new Iberodominios_API($username, $password, $new_token);
            } else {
                echo '<div class="error"><p>' . __('No se pudo generar el token. Revisa las credenciales.', 'iberodominios') . '</p></div>';
                return;
            }
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'iberodominios_tlds';
        // Asegurar que la tabla exista
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            echo '<div class="error"><p>' . __('La tabla de TLDs no existe. Por favor, crea las tablas primero.', 'iberodominios') . '</p></div>';
            return;
        }

        $wpdb->query("TRUNCATE TABLE $table_name");

        $offset = 0;
        $limit = 100;
        $total = null;

        while (true) {
            $endpoint = 'https://api.openprovider.eu/v1beta/tlds?limit=' . $limit . '&offset=' . $offset;
            $response = wp_remote_get($endpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . get_option('iberodominios_api_token')
                ],
                'timeout' => 60,
            ]);

            if (is_wp_error($response)) {
                echo '<div class="error"><p>' . __('Error al consultar TLDs: ', 'iberodominios') . $response->get_error_message() . '</p></div>';
                return;
            }

            $data = json_decode(wp_remote_retrieve_body($response), true);

            // Depuración
            if (!isset($data['code']) || $data['code'] !== 0) {
                echo '<div class="error"><p>' . __('La API devolvió un error: ', 'iberodominios') . esc_html($data['desc']) . '</p></div>';
                echo '<pre>' . print_r($data, true) . '</pre>';
                return;
            }

            if (!isset($data['data']['results'])) {
                echo '<div class="error"><p>' . __('Respuesta inesperada de la API de TLDs.', 'iberodominios') . '</p></div>';
                echo '<pre>' . print_r($data, true) . '</pre>';
                return;
            }

            $results = $data['data']['results'];
            if ($total === null) {
                $total = $data['data']['total'] ?? 0;
            }

            if (empty($results)) {
                // Sin resultados
                if ($offset === 0) {
                    echo '<div class="error"><p>' . __('No se encontraron TLDs en la API.', 'iberodominios') . '</p></div>';
                }
                break;
            }

            foreach ($results as $tld_info) {
                if (isset($tld_info['name'])) {
                    $tld_name = strtolower($tld_info['name']);
                    $status = isset($tld_info['status']) ? $tld_info['status'] : 'ACT';
                    $wpdb->insert($table_name, [
                        'tld_name' => $tld_name,
                        'status' => $status
                    ]);
                }
            }

            $offset += $limit;
            if ($offset >= $total) {
                break;
            }
        }

        echo '<div class="updated"><p>' . __('Sincronización completa. Se han importado las TLDs.', 'iberodominios') . '</p></div>';
    }

    private function generate_token_action()
    {
        $username = get_option('iberodominios_api_username');
        $password = get_option('iberodominios_api_password');

        if (!empty($username) && !empty($password)) {
            $api = new Iberodominios_API($username, $password);
            $token = $api->generate_token();
            if ($token) {
                update_option('iberodominios_api_token', $token);
                echo '<div class="updated notice"><p>' . __('Token generado correctamente.', 'iberodominios') . '</p></div>';
            } else {
                echo '<div class="error notice"><p>' . __('Error al generar el token. Revisa las credenciales.', 'iberodominios') . '</p></div>';
            }
        } else {
            echo '<div class="error notice"><p>' . __('Por favor, introduce tus credenciales antes de generar el token.', 'iberodominios') . '</p></div>';
        }
    }

    private function create_tables_action()
    {
        check_admin_referer('iberodominios_create_tables_action', 'iberodominios_create_tables_nonce');
        $this->create_tables();
        echo '<div class="updated"><p>' . __('Tablas creadas con éxito.', 'iberodominios') . '</p></div>';
    }

    private function drop_tables_action()
    {
        check_admin_referer('iberodominios_drop_tables_action', 'iberodominios_drop_tables_nonce');
        global $wpdb;
        $table_name = $wpdb->prefix . 'iberodominios_tlds';
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
        echo '<div class="updated"><p>' . __('Tablas eliminadas con éxito.', 'iberodominios') . '</p></div>';
    }

    private function create_tables()
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

}
