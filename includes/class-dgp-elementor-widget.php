<?php
class DGP_Elementor_Game_Banner extends \Elementor\Widget_Base {
    public function get_name() {
        return 'dgp_game_banner';
    }
    public function get_title() {
        return __('Game Banner', 'dynamic-game-pages');
    }
    public function get_icon() {
        return 'eicon-posts-ticker';
    }
    public function get_categories() {
        return ['general'];
    }

    protected function _register_controls() {
        $this->start_controls_section('content', ['label' => __('Settings', 'dynamic-game-pages')]);

        $this->add_control('appid', [
            'label' => __('Steam App ID', 'dynamic-game-pages'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => '',
        ]);

        $this->add_control('button', [
            'label' => __('Button Label', 'dynamic-game-pages'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => 'Buy on Steam',
        ]);

        $this->end_controls_section();
    }

    protected function render() {
        $appid = $this->get_settings('appid');
        $button = $this->get_settings('button');
        echo do_shortcode("[game_banner appid=\"$appid\" button=\"$button\"]");
    }
}