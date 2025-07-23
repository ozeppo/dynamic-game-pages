<?php
// Shortcode: [game_banner appid="123456"]
function dgp_game_banner_shortcode($atts) {
    $atts = shortcode_atts([
        'appid' => '',
        'button' => __('Buy on Steam', 'dynamic-game-pages'),
    ], $atts, 'game_banner');

    if (empty($atts['appid'])) return '';
    $game = dgp_get_game_data($atts['appid']);
    if (!$game) return '';
    $accent_color = get_option('dgp_accent_color', '#0073aa');

    $genres = !empty($game['genres']) ? implode(', ', array_map(fn($g) => esc_html($g['description'] ?? $g['name']), $game['genres'])) : '';
    $game_url = site_url('/' . get_option('dgp_game_slug', 'game') . '/' . urlencode($game['appid']));
    $cover = !empty($game['header_image']) ? esc_url($game['header_image']) : '';
    $price = '';
    if (!empty($game['price_overview']['final_formatted'])) {
        $price = $game['price_overview']['final_formatted'];
    } elseif (!empty($game['price_overview']['final'])) {
        $price = number_format($game['price_overview']['final'] / 100, 2) . ' ' . ($game['price_overview']['currency'] ?? '');
    }
    $store_link = $game['steam_url'];
    ob_start();
    ?>
    <style>
    .dgp-game-banner {
        display: flex;
        align-items: stretch;
        gap: 20px;
        border: 2px solid <?php echo esc_attr($accent_color); ?>;
        padding: 15px;
        background: none;
        box-sizing: border-box;
        height: auto;
        flex-wrap: wrap;
    }
    .dgp-game-banner-cover {
        display: inline-block;
        flex: 0 0 30%;
        min-width: 120px;
        max-width: 200px;
    }
    .dgp-game-banner-cover img {
        width: 100%;
        display: block;
        object-fit: cover;
        border-radius: 4px;
    }
    .dgp-game-banner-info {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-width: 180px;
    }
    .dgp-game-banner-details {
        display: flex;
        gap: 10px;
        font-size: 0.75em;
        color: #666;
        flex-wrap: wrap;
    }
    .dgp-game-banner-desc {
        font-size: 0.75em;
        margin-top: auto;
    }
    .dgp-game-banner-actions {
        flex: 0 0 auto;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        min-width: 120px;
    }
    .dgp-game-banner-price {
        font-size: 1em;
        font-weight: bold;
        margin-bottom: 8px;
    }
    .dgp-game-banner-btn {
        background: <?php echo esc_attr($accent_color); ?>;
        color: #fff;
        padding: 10px 20px;
        text-decoration: none;
        font-weight: bold;
        font-size: 1em;
        display: inline-block;
        margin-top: 4px;
    }
    @media (max-width: 700px) {
        .dgp-game-banner {
            flex-direction: column;
            gap: 10px;
            padding: 10px;
        }
        .dgp-game-banner-cover,
        .dgp-game-banner-info,
        .dgp-game-banner-actions {
            min-width: 0;
            max-width: 100%;
            flex: unset;
        }
        .dgp-game-banner-cover {
            margin-bottom: 10px;
            max-width: 100%;
        }
        .dgp-game-banner-actions {
            margin-top: 10px;
        }
        .dgp-game-banner-btn {
            width: 100%;
            text-align: center;
        }
    }
    </style>
    <div class="dgp-game-banner">
        <?php if ($cover): ?>
            <a href="<?php echo esc_url($game_url); ?>" class="dgp-game-banner-cover">
                <img src="<?php echo $cover; ?>" alt="<?php echo esc_attr($game['name']); ?>">
            </a>
        <?php endif; ?>
        <div class="dgp-game-banner-info">
            <div>
                <a href="<?php echo esc_url($game_url); ?>" style="text-decoration:none;color:inherit;">
                    <strong style="font-size:1.5em;"><?php echo esc_html($game['name']); ?></strong>
                </a>
                <div class="dgp-game-banner-details">
                    <span><?php echo esc_html($game['release_date']['date'] ?? ''); ?></span>
                    <?php if ($genres): ?>
                        <span><?php echo $genres; ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <?php if (!empty($game['short_description'])): ?>
                <div class="dgp-game-banner-desc"><?php echo esc_html(implode(' ', array_slice(explode(' ', strip_tags($game['short_description'])), 0, 15))) . '...'; ?></div>
            <?php endif; ?>
        </div>
        <div class="dgp-game-banner-actions">
            <?php if (!empty($price)): ?>
                <div class="dgp-game-banner-price"><?php echo esc_html($price); ?></div>
            <?php endif; ?>
            <a href="<?php echo esc_url($store_link); ?>" target="_blank" rel="noopener" class="dgp-game-banner-btn">
                <?php echo esc_html($atts['button']); ?>
            </a>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('game_banner', 'dgp_game_banner_shortcode');