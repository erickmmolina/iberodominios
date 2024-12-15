<?php
if (!defined('ABSPATH')) {
    exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

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
        $this->start_controls_section(
            'section_content',
            [
                'label' => __('Contenido', 'iberodominios'),
            ]
        );

        $this->add_control(
            'placeholder',
            [
                'label' => __('Placeholder', 'iberodominios'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Ingresa tu dominio...', 'iberodominios'),
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $placeholder = $settings['placeholder'];
        ?>
        <div class="iberodominios-search-container" style="border:1px solid #ccc; padding:15px;">
            <h2><?php _e('Domain Registration', 'iberodominios'); ?></h2>
            <form class="iberodominios-search-form" style="margin-bottom:10px;">
                <input type="text" name="domain" placeholder="<?php echo esc_attr($placeholder); ?>"
                    style="width:300px; padding:5px;" />
                <button type="submit"
                    style="padding:6px 12px; margin-left:5px;"><?php _e('Search', 'iberodominios'); ?></button>
            </form>
            <div class="iberodominios-results"></div>
        </div>
        <?php
    }
}
