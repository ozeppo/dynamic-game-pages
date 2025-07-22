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
    <?php
    $accent_color = get_option('dgp_accent_color', '#0073aa');
    ?>
    <div class="dgp-game-banner" style="display:flex;align-items:stretch;gap:20px;border:2px solid <?php echo esc_attr($accent_color); ?>;padding:15px;background:none;box-sizing:border-box;height:auto;">
        <?php if ($cover): ?>
            <a href="<?php echo esc_url($game_url); ?>" style="display:inline-block;flex:0 0 30%;">
                <img src="<?php echo $cover; ?>" alt="<?php echo esc_attr($game['name']); ?>" style="width:100%;display:block;object-fit:cover;">
            </a>
        <?php endif; ?>
        <div style="flex:1;display:flex;flex-direction:column;justify-content:space-between;">
            <div>
                <a href="<?php echo esc_url($game_url); ?>" style="text-decoration:none;color:inherit;">
                    <strong style="font-size:1.5em;"><?php echo esc_html($game['name']); ?></strong>
                </a>
                <div style="display:flex;gap:10px;font-size:0.75em;color:#666;">
                    <span><?php echo esc_html($game['release_date']['date'] ?? ''); ?></span>
                    <?php if ($genres): ?>
                        <span><?php echo $genres; ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <?php if (!empty($game['short_description'])): ?>
                <div style="font-size:0.75em;margin-top:auto;"><?php echo esc_html(implode(' ', array_slice(explode(' ', strip_tags($game['short_description'])), 0, 15))) . '...'; ?></div>
            <?php endif; ?>
        </div>
        <div style="flex:0 0 auto;display:flex;flex-direction:column;align-items:flex-center;justify-content:center;text-align:center;">
            <?php if (!empty($price)): ?>
                <div style="font-size:1.0em;font-weight:bold;"><?php echo esc_html($price); ?></div>
            <?php endif; ?>
            <a href="<?php echo esc_url($store_link); ?>" target="_blank" rel="noopener" style="background:<?php echo esc_attr($accent_color); ?>;color:#fff;padding:10px 20px;text-decoration:none;font-weight:bold;font-size:1em;">
                <?php echo esc_html($atts['button']); ?>
            </a>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('game_banner', 'dgp_game_banner_shortcode');