<?php
if (!defined('ABSPATH')) {
    exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_CSS_Filter;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;

class Iberodominios_Elementor_Widget extends Widget_Base
{
    public function get_name()
    {
        return 'iberodominios_widget';
    }

    public function get_title()
    {
        return __('Buscador de Dominios (Iberodominios)', 'iberodominios');
    }

    public function get_icon()
    {
        return 'eicon-search';
    }

    public function get_categories()
    {
        return ['general'];
    }

    protected function _register_controls()
    {
        // SECCIÓN CONTENIDO
        $this->start_controls_section(
            'section_content',
            [
                'label' => __('Contenido', 'iberodominios'),
            ]
        );

        $this->add_control(
            'placeholder',
            [
                'label' => __('Placeholder del Campo', 'iberodominios'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Ingresa tu dominio...', 'iberodominios'),
            ]
        );

        $this->add_control(
            'button_text',
            [
                'label' => __('Texto del Botón', 'iberodominios'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Search', 'iberodominios'),
            ]
        );

        $this->add_control(
            'button_icon',
            [
                'label' => __('Icono del Botón', 'iberodominios'),
                'type' => Controls_Manager::ICONS,
                'fa4compatibility' => 'icon',
                'default' => [
                    'value' => '',
                    'library' => ''
                ]
            ]
        );

        $this->add_control(
            'loading_svg_url',
            [
                'label' => __('URL del SVG de Carga', 'iberodominios'),
                'type' => Controls_Manager::URL,
                'placeholder' => __('http://tu-sitio.com/tu-svg.svg', 'iberodominios'),
                'description' => __('Deja en blanco para usar el SVG por defecto.', 'iberodominios')
            ]
        );

        $this->end_controls_section();

        // SECCIÓN ESTILO CONTENEDOR
        $this->start_controls_section(
            'section_style_container',
            [
                'label' => __('Estilo del Contenedor', 'iberodominios'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'container_bg_color',
            [
                'label' => __('Color de Fondo', 'iberodominios'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .iberodominios-search-container' => 'background-color: {{VALUE}};'
                ],
            ]
        );

        $this->add_control(
            'container_border_color',
            [
                'label' => __('Color Borde', 'iberodominios'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ccc',
                'selectors' => [
                    '{{WRAPPER}} .iberodominios-search-container' => 'border-color: {{VALUE}};'
                ],
            ]
        );

        $this->add_responsive_control(
            'container_border_radius',
            [
                'label' => __('Radio del Borde', 'iberodominios'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'default' => [
                    'unit' => 'px',
                    'size' => 5,
                ],
                'selectors' => [
                    '{{WRAPPER}} .iberodominios-search-container' => 'border-radius: {{SIZE}}{{UNIT}};'
                ],
            ]
        );

        $this->add_responsive_control(
            'container_padding',
            [
                'label' => __('Padding', 'iberodominios'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'default' => [
                    'top' => 15,
                    'right' => 15,
                    'bottom' => 15,
                    'left' => 15,
                    'unit' => 'px'
                ],
                'selectors' => [
                    '{{WRAPPER}} .iberodominios-search-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // ESTILO CAMPO DE BÚSQUEDA
        $this->start_controls_section(
            'section_style_search',
            [
                'label' => __('Campo de Búsqueda', 'iberodominios'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'search_input_width',
            [
                'label' => __('Ancho del Campo', 'iberodominios'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    '%' => ['min' => 10, 'max' => 100],
                    'px' => ['min' => 100, 'max' => 600],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 300,
                ],
                'selectors' => [
                    '{{WRAPPER}} .iberodominios-search-form input[name="domain"]' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'search_input_text_color',
            [
                'label' => __('Color del Texto', 'iberodominios'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .iberodominios-search-form input[name="domain"]' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'search_placeholder_color',
            [
                'label' => __('Color del Placeholder', 'iberodominios'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .iberodominios-search-form input[name="domain"]::placeholder' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // ESTILO BOTÓN
        $this->start_controls_section(
            'section_style_button',
            [
                'label' => __('Botón de Búsqueda', 'iberodominios'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'button_bg_color',
            [
                'label' => __('Color de Fondo del Botón', 'iberodominios'),
                'type' => Controls_Manager::COLOR,
                'default' => '#f06292',
                'selectors' => [
                    '{{WRAPPER}} .iberodominios-search-form button[type="submit"]' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_text_color',
            [
                'label' => __('Color del Texto del Botón', 'iberodominios'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .iberodominios-search-form button[type="submit"]' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_padding',
            [
                'label' => __('Padding del Botón', 'iberodominios'),
                'type' => Controls_Manager::DIMENSIONS,
                'default' => [
                    'top' => 6,
                    'right' => 12,
                    'bottom' => 6,
                    'left' => 12,
                    'unit' => 'px'
                ],
                'selectors' => [
                    '{{WRAPPER}} .iberodominios-search-form button[type="submit"]' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // ESTILO TABLA
        $this->start_controls_section(
            'section_style_table',
            [
                'label' => __('Tabla de Resultados', 'iberodominios'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'table_header_bg',
            [
                'label' => __('Fondo del Encabezado', 'iberodominios'),
                'type' => Controls_Manager::COLOR,
                'default' => '#f0f0f0',
                'selectors' => [
                    '{{WRAPPER}} .iberodominios-results th' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'table_header_text_color',
            [
                'label' => __('Color del Texto del Encabezado', 'iberodominios'),
                'type' => Controls_Manager::COLOR,
                'default' => '#333333',
                'selectors' => [
                    '{{WRAPPER}} .iberodominios-results th' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'table_cell_text_color',
            [
                'label' => __('Color del Texto de Celdas', 'iberodominios'),
                'type' => Controls_Manager::COLOR,
                'default' => '#333333',
                'selectors' => [
                    '{{WRAPPER}} .iberodominios-results td' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'table_border_color',
            [
                'label' => __('Color del Borde de Celdas', 'iberodominios'),
                'type' => Controls_Manager::COLOR,
                'default' => '#eee',
                'selectors' => [
                    '{{WRAPPER}} .iberodominios-results th, {{WRAPPER}} .iberodominios-results td' => 'border-bottom-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'table_padding_cells',
            [
                'label' => __('Padding de las Celdas', 'iberodominios'),
                'type' => Controls_Manager::DIMENSIONS,
                'default' => [
                    'top' => 5,
                    'right' => 5,
                    'bottom' => 5,
                    'left' => 5,
                    'unit' => 'px'
                ],
                'selectors' => [
                    '{{WRAPPER}} .iberodominios-results th, {{WRAPPER}} .iberodominios-results td' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // ESPACIO ENTRE INPUT Y BOTÓN
        $this->start_controls_section(
            'section_style_spacing',
            [
                'label' => __('Espacio entre Campo y Botón', 'iberodominios'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'input_button_spacing',
            [
                'label' => __('Espacio Horizontal', 'iberodominios'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'unit' => 'px',
                    'size' => 5,
                ],
                'selectors' => [
                    '{{WRAPPER}} .iberodominios-search-form button[type="submit"]' => 'margin-left: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // ESTILO ICONO CARGA
        $this->start_controls_section(
            'section_style_loading_icon',
            [
                'label' => __('Icono de Carga (SVG)', 'iberodominios'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'loading_icon_size',
            [
                'label' => __('Tamaño (Anchura)', 'iberodominios'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'unit' => 'px',
                    'size' => 16,
                ],
                'range' => [
                    'px' => ['min' => 8, 'max' => 100]
                ],
                'selectors' => [
                    '{{WRAPPER}} .loading-icon .loading-svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'loading_icon_flip',
            [
                'label' => __('Flip Horizontal', 'iberodominios'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('On', 'iberodominios'),
                'label_off' => __('Off', 'iberodominios'),
                'return_value' => 'yes',
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .loading-icon .loading-svg' => 'transform: scaleX(-1);',
                ],
                'condition' => [
                    'loading_icon_flip' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'loading_icon_rotate',
            [
                'label' => __('Rotar (grados)', 'iberodominios'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'unit' => 'deg',
                    'size' => 0,
                ],
                'range' => [
                    'deg' => ['min' => 0, 'max' => 360]
                ],
                'selectors' => [
                    '{{WRAPPER}} .loading-icon .loading-svg' => 'transform: rotate({{SIZE}}{{UNIT}}) {{loading_icon_flip == "yes" ? " scaleX(-1)" : ""}};',
                ],
            ]
        );

        $this->end_controls_section();

        // ESTILO ICONO DEL BOTÓN
        $this->start_controls_section(
            'section_style_button_icon',
            [
                'label' => __('Icono del Botón', 'iberodominios'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'button_icon[value]!' => '',
                ]
            ]
        );

        $this->add_responsive_control(
            'button_icon_size',
            [
                'label' => __('Tamaño del Icono', 'iberodominios'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'unit' => 'px',
                    'size' => 14,
                ],
                'range' => [
                    'px' => ['min' => 8, 'max' => 100]
                ],
                'selectors' => [
                    '{{WRAPPER}} .iberodominios-button-icon i, {{WRAPPER}} .iberodominios-button-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: auto;',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_icon_spacing',
            [
                'label' => __('Espacio con el Texto', 'iberodominios'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'unit' => 'px',
                    'size' => 5,
                ],
                'range' => [
                    'px' => ['min' => 0, 'max' => 50]
                ],
                'selectors' => [
                    '{{WRAPPER}} .iberodominios-button-icon' => 'margin-right: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_icon_rotate',
            [
                'label' => __('Rotar Icono (grados)', 'iberodominios'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'unit' => 'deg',
                    'size' => 0,
                ],
                'range' => [
                    'deg' => ['min' => 0, 'max' => 360]
                ],
                'selectors' => [
                    '{{WRAPPER}} .iberodominios-button-icon i, {{WRAPPER}} .iberodominios-button-icon svg' => 'transform: rotate({{SIZE}}{{UNIT}});',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $placeholder = $settings['placeholder'];
        $button_text = $settings['button_text'];
        $svg_url = isset($settings['loading_svg_url']['url']) && !empty($settings['loading_svg_url']['url']) ? esc_url($settings['loading_svg_url']['url']) : '';

        // Icono del botón
        $button_icon_html = '';
        if (!empty($settings['button_icon']['value'])) {
            $button_icon_html = '<span class="iberodominios-button-icon">' . \Elementor\Icons_Manager::render_icon($settings['button_icon'], ['aria-hidden' => 'true']) . '</span>';
        }

        ?>
        <div class="iberodominios-search-container" data-custom-svg="<?php echo $svg_url; ?>">
            <h2><?php _e('Domain Registration', 'iberodominios'); ?></h2>
            <form class="iberodominios-search-form" style="margin-bottom:10px;">
                <input type="text" name="domain" placeholder="<?php echo esc_attr($placeholder); ?>" />
                <button type="submit">
                    <?php echo $button_icon_html; ?>
                    <?php echo esc_html($button_text); ?>
                </button>
            </form>
            <div class="iberodominios-results"></div>
        </div>
        <?php
    }
}
