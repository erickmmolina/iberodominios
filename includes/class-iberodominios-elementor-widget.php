<?php
/**
 * Clase para crear un widget de Elementor que permita:
 * - Ingresar un nombre de dominio
 * - Mostrar disponibilidad y precio
 * - (Opcional) Añadir un enlace para comprar (este punto requiere más lógica, aquí solo se muestra ejemplo)
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Usamos el namespace de Elementor si es necesario (en caso contrario solo extendemos la clase)
use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Iberodominios_Elementor_Widget extends Widget_Base
{
    /**
     * Nombre del widget
     */
    public function get_name()
    {
        return 'iberodominios_widget';
    }

    /**
     * Título del widget
     */
    public function get_title()
    {
        return __('Buscador de Dominios (Iberodominios)', 'iberodominios');
    }

    /**
     * Icono del widget
     */
    public function get_icon()
    {
        return 'eicon-search';
    }

    /**
     * Categorías del widget
     */
    public function get_categories()
    {
        return ['general'];
    }

    /**
     * Controles del widget (en este caso no muchos, solo para estilizar)
     */
    protected function _register_controls()
    {
        parent::_register_controls(); // Ya existe el placeholder

        $this->start_controls_section(
            'section_filters',
            [
                'label' => __('Opciones de Filtrado/Orden', 'iberodominios'),
            ]
        );

        $this->add_control(
            'show_hide_unavailable',
            [
                'label' => __('Mostrar filtro "Hide unavailable"', 'iberodominios'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Sí', 'iberodominios'),
                'label_off' => __('No', 'iberodominios'),
                'default' => 'yes',
            ]
        );

        // Podríamos añadir más controles similares para Top Level Domains, etc.
        // De momento lo hacemos estático y luego los haremos dinámicos.

        $this->end_controls_section();
    }

    /**
     * Renderizar el widget en el frontend
     */
    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $placeholder = $settings['placeholder'];

        // Obtenemos valores por defecto del panel de administración
        $default_currency = get_option('iberodominios_default_currency', 'EUR');
        $show_promo = get_option('iberodominios_show_promo', 0);

        // Opciones de filtrado que el usuario podría cambiar en el frontend
        // Para simplificar, lo haremos con formularios GET
        $hide_unavailable = isset($_GET['hide_unavailable']) ? 1 : 0;
        $sort_by = isset($_GET['sort_by']) ? sanitize_text_field($_GET['sort_by']) : 'relevance';
        $search_domain = isset($_GET['iberodominio']) ? sanitize_text_field($_GET['iberodominio']) : '';

        // HTML del formulario de búsqueda y filtros
        ?>
        <div class="iberodominios-search-container" style="border:1px solid #ccc; padding:15px;">
            <h2><?php _e('Domain Registration', 'iberodominios'); ?></h2>
            <form method="GET" style="margin-bottom:10px;">
                <input type="text" name="iberodominio" placeholder="<?php echo esc_attr($placeholder); ?>"
                    value="<?php echo esc_attr($search_domain); ?>" style="width:300px; padding:5px;" />

                <button type="submit"
                    style="padding:6px 12px; margin-left:5px;"><?php _e('Search', 'iberodominios'); ?></button>

                <div style="margin-top:10px;">
                    <?php if ($settings['show_hide_unavailable'] === 'yes'): ?>
                        <label style="margin-right:10px;">
                            <input type="checkbox" name="hide_unavailable" value="1" <?php checked($hide_unavailable, 1); ?> />
                            <?php _e('Hide unavailable', 'iberodominios'); ?>
                        </label>
                    <?php endif; ?>

                    <!-- Podríamos añadir más filtros aquí -->

                    <label style="margin-right:10px;">
                        <?php _e('Sort By:', 'iberodominios'); ?>
                        <select name="sort_by">
                            <option value="relevance" <?php selected($sort_by, 'relevance'); ?>>
                                <?php _e('Relevance', 'iberodominios'); ?></option>
                            <option value="price_low" <?php selected($sort_by, 'price_low'); ?>>
                                <?php _e('Price (Low to High)', 'iberodominios'); ?></option>
                            <option value="price_high" <?php selected($sort_by, 'price_high'); ?>>
                                <?php _e('Price (High to Low)', 'iberodominios'); ?></option>
                        </select>
                    </label>

                    <button type="submit" style="padding:6px 12px;"><?php _e('Apply Filters', 'iberodominios'); ?></button>
                </div>
            </form>
            <?php

            // Si se ha introducido un dominio, consultamos la API
            if (!empty($search_domain)) {
                $api = new Iberodominios_API();
                // Supongamos que añadimos una función check_multiple_tlds
                // que devuelva varios TLDs relacionados con el dominio base
                // Por simplicidad, aquí lo haremos con un array de TLDs fijos:
                $tlds = array('com', 'tv', 'eu', 'shop', 'design', 'it');

                $results = array();
                foreach ($tlds as $tld) {
                    $full_domain = $search_domain . '.' . $tld;
                    $res = $api->check_domain_availability($full_domain);
                    if ($res) {
                        $results[] = $res;
                    }
                }

                // Filtrar resultados: por ejemplo, si $hide_unavailable está activo
                if ($hide_unavailable) {
                    $results = array_filter($results, function ($r) {
                        return isset($r['status']) && $r['status'] === 'free';
                    });
                }

                // Ordenar resultados según $sort_by
                if ($sort_by === 'price_low') {
                    usort($results, function ($a, $b) {
                        return ($a['price']['reseller']['price'] ?? 9999) <=> ($b['price']['reseller']['price'] ?? 9999);
                    });
                } elseif ($sort_by === 'price_high') {
                    usort($results, function ($a, $b) {
                        return ($b['price']['reseller']['price'] ?? 0) <=> ($a['price']['reseller']['price'] ?? 0);
                    });
                }

                // Mostrar resultados en una tabla similar a la imagen
                if (!empty($results)) {
                    echo '<table style="width:100%; border-collapse:collapse;">';
                    echo '<thead><tr style="border-bottom:1px solid #ccc;">';
                    echo '<th style="text-align:left;padding:5px;">' . __('Domain', 'iberodominios') . '</th>';
                    echo '<th style="text-align:left;padding:5px;">' . __('Status', 'iberodominios') . '</th>';
                    echo '<th style="text-align:left;padding:5px;">' . __('Price', 'iberodominios') . '</th>';
                    echo '<th style="text-align:left;padding:5px;">' . __('Action', 'iberodominios') . '</th>';
                    echo '</tr></thead><tbody>';

                    foreach ($results as $r) {
                        $domain_name = $r['domain'];
                        $status = $r['status'];
                        $price = isset($r['price']['reseller']['price']) ? $r['price']['reseller']['price'] : '-';
                        $currency = isset($r['price']['reseller']['currency']) ? $r['price']['reseller']['currency'] : $default_currency;

                        // Determinamos si es promocional
                        $is_promo = (isset($r['is_premium']) && $r['is_premium']) || ($show_promo && rand(0, 1)); // Ejemplo aleatorio, luego lógica real
                        $promo_label = $is_promo ? '<span style="background:red;color:#fff;padding:2px 4px;border-radius:3px;font-size:12px;">' . __('Hot Price', 'iberodominios') . '</span>' : '';

                        echo '<tr style="border-bottom:1px solid #eee;">';
                        echo '<td style="padding:5px;">' . esc_html($domain_name) . '</td>';
                        echo '<td style="padding:5px;">' . esc_html($status) . '</td>';
                        echo '<td style="padding:5px;">' . $promo_label . ' ' . esc_html($price . ' ' . $currency) . '</td>';
                        echo '<td style="padding:5px;"><button style="padding:5px 10px; background:#333; color:#fff; border:none; cursor:pointer;">' . __('Add to cart', 'iberodominios') . '</button></td>';
                        echo '</tr>';
                    }

                    echo '</tbody></table>';
                } else {
                    echo '<p>' . __('No results found.', 'iberodominios') . '</p>';
                }
            }

            echo '</div>';
    }


}
