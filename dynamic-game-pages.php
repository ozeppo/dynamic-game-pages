<?php
/**
 * Plugin Name: Dynamic Game Pages
 * Description: Generates dynamic game pages with Steam API for better SEO performacne on your Website.
 * Plugin URI: https://github.com/ozeppo/dynamic-games-pages
 * Version: 1.1
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

function dgp_render_game_template($game) {
    // Steam API structure
    $release_date = !empty($game['release_date']['date']) ? esc_html($game['release_date']['date']) : __('No data', 'dynamic-game-pages');
    $requirements_min = __('No data', 'dynamic-game-pages');
    $requirements_rec = __('No data', 'dynamic-game-pages');
    if (!empty($game['pc_requirements'])) {
        if (!empty($game['pc_requirements']['minimum'])) {
            $requirements_min = $game['pc_requirements']['minimum'];
        }
        if (!empty($game['pc_requirements']['recommended'])) {
            $requirements_rec = $game['pc_requirements']['recommended'];
        }
    }

    // Get FAQ and color settings
    $accent_color = get_option('dgp_accent_color', '#0073aa');
    $faq_q1 = get_option('dgp_faq_q1', 'When is the release date of %title?');
    $faq_a1_past = get_option('dgp_faq_a1_past', '%title was released on %release');
    $faq_a1_future = get_option('dgp_faq_a1_future', 'The release of %title is planned for %release');
    $faq_q2 = get_option('dgp_faq_q2', 'What are the system requirements for %title?');
    $faq_a2 = get_option('dgp_faq_a2', '%title has the following requirements: %requirements');

    $game_name = $game['name'];
    $is_past_release = false;
    if ($release_date && strtotime($release_date) !== false) {
        $is_past_release = strtotime($release_date) <= time();
    }

    $faq_items = [
        [
            '@type' => 'Question',
            'name' => str_replace('%title', $game_name, $faq_q1),
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => $is_past_release
                    ? str_replace(['%title', '%release'], [$game_name, $release_date], $faq_a1_past)
                    : str_replace(['%title', '%release'], [$game_name, $release_date], $faq_a1_future),
            ],
        ],
        [
            '@type' => 'Question',
            'name' => str_replace('%title', $game_name, $faq_q2),
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => str_replace(['%title', '%requirements'], [$game_name, wp_strip_all_tags($requirements_min)], $faq_a2),
            ],
        ],
    ];
    $faq_json_ld = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $faq_items,
    ];

    // Prepare genres, publishers, developers
    $genres = !empty($game['genres']) ? implode(', ', array_map(fn($g) => esc_html($g['description'] ?? $g['name']), $game['genres'])) : '';
    $publishers = !empty($game['publishers']) ? implode(', ', array_map('esc_html', $game['publishers'])) : '';
    $developers = !empty($game['developers']) ? implode(', ', array_map('esc_html', $game['developers'])) : '';

    $price = '';
    if (!empty($game['price_overview']['final_formatted'])) {
        $price = $game['price_overview']['final_formatted'];
    } elseif (!empty($game['price_overview']['final'])) {
        $price = number_format($game['price_overview']['final'] / 100, 2) . ' ' . ($game['price_overview']['currency'] ?? '');
    }

    ob_start();
    ?>
    <main>
        <style>
            .dgp-content-wrapper {
                max-width: 1200px;
                margin: 0 auto;
                padding: 20px;
            }
            /* Remove body styling for bg/text color */
            .section-1 {
                display: flex;
                gap: 20px;
                margin-bottom: 40px;
            }
            .section-1 .info {
                flex: 0 0 65%;
            }
            .section-1 .cover {
                flex: 0 0 35%;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .section-1 .cover img {
                max-width: 100%;
                height: auto;
                border-radius: 8px;
            }
            h1 {
                margin-top: 0;
            }
            h2 {
                margin-top: 40px;
                margin-bottom: 15px;
                border-bottom: 2px solid <?php echo esc_attr($accent_color); ?>;
                padding-bottom: 5px;
            }
            .requirements {
                display: flex;
                gap: 20px;
                margin-bottom: 40px;
            }
            .requirements > div {
                flex: 1;
            }
            .faq-item {
                margin-bottom: 20px;
            }
            .description {
                font-size: 16px;
            }
            .game-info-list {
                margin: 10px 0 0 0;
                padding: 0;
                list-style: none;
            }
            .game-info-list li {
                margin-bottom: 3px;
            }
            /* Accent color for banner button (if needed in template) */
            .dgp-accent-btn {
                background: <?php echo esc_attr($accent_color); ?> !important;
                color: #fff !important;
            }
        </style>
        <script type="application/ld+json">
        <?php echo json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'VideoGame',
            'name' => $game['name'],
            'image' => $game['header_image'] ?? '',
            'description' => wp_trim_words($game['short_description'], 30),
            'datePublished' => $release_date,
            'gamePlatform' => ['PC'], // Steam API gives PC games
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
        </script>
        <script type="application/ld+json">
        <?php echo json_encode($faq_json_ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
        </script>
        <div class="dgp-content-wrapper">
            <section class="section-1">
                <div class="info">
                    <h1><?php echo esc_html($game['name']); ?></h1>
                    <p><strong><?php _e('Release date:', 'dynamic-game-pages'); ?></strong> <?php echo $release_date; ?></p>
                    <ul class="game-info-list">
                        <?php if ($genres): ?>
                            <li><strong><?php _e('Genre:', 'dynamic-game-pages'); ?></strong> <?php echo $genres; ?></li>
                        <?php endif; ?>
                        <?php if ($publishers): ?>
                            <li><strong><?php _e('Publisher:', 'dynamic-game-pages'); ?></strong> <?php echo $publishers; ?></li>
                        <?php endif; ?>
                        <?php if ($developers): ?>
                            <li><strong><?php _e('Developer:', 'dynamic-game-pages'); ?></strong> <?php echo $developers; ?></li>
                        <?php endif; ?>
                        <?php if ($price): ?>
                            <li><strong><?php _e('Price:', 'dynamic-game-pages'); ?></strong> <?php echo esc_html($price); ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="cover">
                    <?php if ($game['header_image']): ?>
                        <img src="<?php echo esc_url($game['header_image']); ?>" alt="<?php echo esc_attr($game['name']); ?>">
                    <?php endif; ?>
                </div>
            </section>
            <section class="section-2">
                <h2><?php _e('About the game', 'dynamic-game-pages'); ?></h2>
                <p class="description"><?php echo nl2br(esc_html(wp_trim_words($game['short_description'], 100))); ?></p>
            </section>
            <section class="section-3">
                <h2><?php _e('System Requirements', 'dynamic-game-pages'); ?></h2>
                <div class="requirements">
                    <div class="minimum">
                        <h3><?php _e('Minimum', 'dynamic-game-pages'); ?></h3>
                        <div><?php echo $requirements_min; ?></div>
                    </div>
                    <div class="recommended">
                        <h3><?php _e('Recommended', 'dynamic-game-pages'); ?></h3>
                        <div><?php echo $requirements_rec; ?></div>
                    </div>
                </div>
            </section>
            <section class="section-4">
                <h2><?php _e('FAQ', 'dynamic-game-pages'); ?></h2>
                <div class="faq-item">
                    <strong><?php echo esc_html(str_replace('%title', $game_name, $faq_q1)); ?></strong>
                    <p><?php echo esc_html($is_past_release
                        ? str_replace(['%title', '%release'], [$game_name, $release_date], $faq_a1_past)
                        : str_replace(['%title', '%release'], [$game_name, $release_date], $faq_a1_future)); ?></p>
                </div>
                <div class="faq-item">
                    <strong><?php echo esc_html(str_replace('%title', $game_name, $faq_q2)); ?></strong>
                    <p><?php echo esc_html(str_replace(['%title', '%requirements'], [$game_name, wp_strip_all_tags($requirements_min)], $faq_a2)); ?></p>
                </div>
            </section>
        </div>
    </main>
    <?php
    $main_content = ob_get_clean();
    ob_start();
    get_header();
    echo $main_content;
    get_footer();
    return ob_get_clean();
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
