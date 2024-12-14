<?php
/**
 * Clase para generar la página de ajustes de Iberodominios en el panel de administración de WordPress
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class Iberodominios_Settings_Page
{
    /**
     * Renderizamos el formulario de ajustes donde el usuario ingresa credenciales API
     */
    public function render_page()
    {
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
                                style="width: 300px;" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('Moneda por defecto', 'iberodominios'); ?></th>
                        <td>
                            <select name="iberodominios_default_currency">
                                <?php
                                $current_currency = get_option('iberodominios_default_currency', 'EUR');
                                $currencies = array('EUR', 'USD', 'GBP');
                                foreach ($currencies as $cur) {
                                    echo '<option value="' . esc_attr($cur) . '" ' . selected($current_currency, $cur, false) . '>' . esc_html($cur) . '</option>';
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('Mostrar dominios promocionales por defecto', 'iberodominios'); ?></th>
                        <td>
                            <input type="checkbox" name="iberodominios_show_promo" value="1" <?php checked(get_option('iberodominios_show_promo'), 1); ?> />
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
            // Si el usuario presiona el botón para generar token:
            if (isset($_POST['iberodominios_generate_token_action']) && check_admin_referer('iberodominios_generate_token', 'iberodominios_generate_token_nonce')) {
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
            ?>

            <h3><?php _e('Token Actual', 'iberodominios'); ?></h3>
            <p><strong><?php echo esc_html(get_option('iberodominios_api_token')); ?></strong></p>
        </div>
        <?php
    }
}
