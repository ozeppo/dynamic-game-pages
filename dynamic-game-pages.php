<?php
/**
 * Plugin Name: Dynamic Game Pages
 * Description: Generates dynamic game pages with Steam API for better SEO performacne on your Website.
 * Plugin URI: https://github.com/ozeppo/dynamic-games-pages
 * Version: 1.1.1
 * Author: Filip Chmielecki
 * Author URI: https://filipchmielecki.pl/
 * Text Domain: dynamic-game-pages
 * Domain Path: /languages
 */

add_action('plugins_loaded', function() {
    load_plugin_textdomain('dynamic-game-pages', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

require_once plugin_dir_path(__FILE__) . 'includes/settings-page.php';
require_once plugin_dir_path(__FILE__) . 'includes/shortcode-banner.php';
require_once plugin_dir_path(__FILE__) . 'includes/render-game-template.php';

// Register Elementor element
add_action('elementor/widgets/widgets_registered', function($widgets_manager) {
    require_once __DIR__ . '/includes/class-dgp-elementor-widget.php';
    $widgets_manager->register(new \DGP_Elementor_Game_Banner());
});

// Register Gutenberg block
add_action('init', function () {
    $block_js = plugin_dir_path(__FILE__) . 'build/index.js';
    $block_asset = plugin_dir_path(__FILE__) . 'build/index.asset.php';

    if (!file_exists($block_js) || !file_exists($block_asset)) {
        return;
    }

    $asset = include $block_asset;

    wp_register_script(
        'dgp-block-banner',
        plugins_url('build/index.js', __FILE__),
        $asset['dependencies'],
        $asset['version']
    );

    register_block_type('dgp/game-banner', [
        'editor_script'   => 'dgp-block-banner',
        'render_callback' => 'dgp_render_game_banner_block',
        'attributes'      => [
            'appid' => ['type' => 'string'],
            'button' => ['type' => 'string'],
        ],
    ]);
});

function dgp_render_game_banner_block($attributes) {
    $appid = isset($attributes['appid']) ? esc_attr($attributes['appid']) : '';
    $button = isset($attributes['button']) ? esc_html($attributes['button']) : '';
    return do_shortcode("[game_banner appid=\"$appid\" button=\"$button\"]");
}

// Add rewrite rules for dynamic game pages

add_action('init', 'dgp_add_rewrite');
function dgp_add_rewrite() {
    $slug = get_option('dgp_game_slug', 'game');
    add_rewrite_rule('^' . preg_quote($slug, '/') . '/([^/]+)/?$', 'index.php?dgp_game_slug=$matches[1]', 'top');
    add_rewrite_tag('%dgp_game_slug%', '([^&]+)');
}

add_action('template_redirect', 'dgp_handle_dynamic_game_page');
function dgp_handle_dynamic_game_page() {
    $base_slug = get_option('dgp_game_slug', 'game');
    $appid = get_query_var('dgp_game_slug');
    if (!$appid) return;

    $game = dgp_get_game_data($appid);
    if (!$game) {
        status_header(404);
        echo '<h1>' . esc_html(__('The game was not found', 'dynamic-game-pages')) . '</h1>';
        exit;
    }

    // SEO
    add_filter('pre_get_document_title', fn() => $game['name']);
    echo dgp_render_game_template($game);
    exit;
}

function dgp_get_game_data($appid) {
    // Determine language and country code automatically using determine_locale if available
    $lang = get_option('dgp_steam_language', 'english');
    $cc = $lang;

    $cache_key = 'dgp_steam_' . sanitize_title($appid) . "_{$cc}_{$lang}";
    $cached = get_transient($cache_key);
    if ($cached) return $cached;

    $api_url = 'https://store.steampowered.com/api/appdetails?appids=' . urlencode($appid) . '&cc=' . $cc . '&l=' . $lang;
    $response = wp_remote_get($api_url);
    if (is_wp_error($response)) return false;

    $body = wp_remote_retrieve_body($response);
    $json = json_decode($body, true);
    if (empty($json) || empty($json[$appid]['success']) || empty($json[$appid]['data'])) return false;
    $data = $json[$appid]['data'];
    // Normalize structure a bit for template
    $game = [
        'appid' => $appid,
        'name' => $data['name'] ?? '',
        'type' => $data['type'] ?? '',
        'genres' => $data['genres'] ?? [],
        'release_date' => $data['release_date'] ?? [],
        'price_overview' => $data['price_overview'] ?? [],
        'header_image' => $data['header_image'] ?? '',
        'short_description' => $data['short_description'] ?? '',
        'pc_requirements' => $data['pc_requirements'] ?? [],
        'developers' => $data['developers'] ?? [],
        'publishers' => $data['publishers'] ?? [],
        'steam_url' => 'https://store.steampowered.com/app/' . $appid,
    ];
    set_transient($cache_key, $game, DAY_IN_SECONDS);
    return $game;
}

add_action('admin_menu', 'dgp_add_settings_page');
function dgp_add_settings_page() {
    add_options_page(
        'Dynamic Game Pages',
        'Dynamic Game Pages',
        'manage_options',
        'dgp-settings',
        'dgp_render_settings_page'
    );
}

add_action('admin_init', 'dgp_register_settings');
function dgp_register_settings() {
    register_setting('dgp_settings_group', 'dgp_game_slug', [
        'sanitize_callback' => 'sanitize_title',
        'default' => 'game',
        'show_in_rest' => false,
        'type' => 'string',
        'description' => __('Slug for dynamic game pages base URL', 'dynamic-game-pages'),
        'args' => [],
    ]);
    register_setting('dgp_settings_group', 'dgp_accent_color', [
        'sanitize_callback' => 'sanitize_hex_color',
        'default' => '#0073aa',
    ]);
    register_setting('dgp_settings_group', 'dgp_faq_q1', [
        'sanitize_callback' => 'sanitize_text_field',
        'default' => 'When is the release date of %title?',
    ]);
    register_setting('dgp_settings_group', 'dgp_faq_a1_past', [
        'sanitize_callback' => 'sanitize_textarea_field',
        'default' => '%title was released on %release',
    ]);
    register_setting('dgp_settings_group', 'dgp_faq_a1_future', [
        'sanitize_callback' => 'sanitize_textarea_field',
        'default' => 'The release of %title is planned for %release',
    ]);
    register_setting('dgp_settings_group', 'dgp_faq_q2', [
        'sanitize_callback' => 'sanitize_text_field',
        'default' => 'What are the system requirements for %title?',
    ]);
    register_setting('dgp_settings_group', 'dgp_faq_a2', [
        'sanitize_callback' => 'sanitize_textarea_field',
        'default' => '%title has the following requirements: %requirements',
    ]);
    register_setting('dgp_settings_group', 'dgp_steam_language', [
        'sanitize_callback' => 'sanitize_text_field',
        'default' => 'english',
    ]);
    add_settings_error(
        'dgp_game_slug',
        'dgp_game_slug_perm_link',
        __('After changing the slug, remember to go to Settings > Permalinks and click "Save Changes" to refresh rewrite rules.', 'dynamic-game-pages'),
        'notice'
    );
}
