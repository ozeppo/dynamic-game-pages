<?php
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
    require dirname(__DIR__) . '/assets/styler.php';
    ?>
    <main>
        
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