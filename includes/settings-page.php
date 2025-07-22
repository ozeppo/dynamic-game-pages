<?php
function dgp_render_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('Dynamic Game Pages - Settings', 'dynamic-game-pages'); ?></h1>
        <form method="post" action="options.php">
            <?php settings_fields('dgp_settings_group'); ?>
            <?php do_settings_sections('dgp_settings_group'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="dgp_game_slug"><?php _e('Game Page Slug', 'dynamic-game-pages'); ?></label></th>
                    <td>
                        <input name="dgp_game_slug" type="text" id="dgp_game_slug" value="<?php echo esc_attr(get_option('dgp_game_slug', 'game')); ?>" class="regular-text" />
                        <p class="description"><?php _e('Enter a custom base slug for game pages (e.g., "games"). After changing the slug, go to Settings > Permalinks and click "Save Changes".', 'dynamic-game-pages'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="dgp_accent_color"><?php _e('Accent Color', 'dynamic-game-pages'); ?></label></th>
                    <td>
                        <input name="dgp_accent_color" type="text" id="dgp_accent_color" value="<?php echo esc_attr(get_option('dgp_accent_color', '#0073aa')); ?>" class="regular-text" />
                        <p class="description"><?php _e('Accent color for borders and buttons (e.g., #0073aa).', 'dynamic-game-pages'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="dgp_steam_language"><?php _e('Steam API Language', 'dynamic-game-pages'); ?></label></th>
                    <td>
                        <input name="dgp_steam_language" type="text" id="dgp_steam_language" value="<?php echo esc_attr(get_option('dgp_steam_language', 'en')); ?>" class="regular-text" />
                        <p class="description"><?php _e('Language for Steam API requests (e.g., "english", "spanish", "polish").', 'dynamic-game-pages'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th colspan="2"><h2><?php _e('FAQ Configurator', 'dynamic-game-pages'); ?></h2></th>
                </tr>
                <tr>
                    <th scope="row"><label for="dgp_faq_q1"><?php _e('Question 1', 'dynamic-game-pages'); ?></label></th>
                    <td>
                        <input name="dgp_faq_q1" type="text" id="dgp_faq_q1" value="<?php echo esc_attr(get_option('dgp_faq_q1', 'When is the release date of %title?')); ?>" class="regular-text" />
                        <p class="description"><?php _e('First FAQ question. You can use %title as a placeholder for the game name.', 'dynamic-game-pages'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="dgp_faq_a1_past"><?php _e('Answer 1 (release in the past)', 'dynamic-game-pages'); ?></label></th>
                    <td>
                        <textarea name="dgp_faq_a1_past" id="dgp_faq_a1_past" rows="3" class="large-text"><?php echo esc_textarea(get_option('dgp_faq_a1_past', '%title was released on %date')); ?></textarea>
                        <p class="description"><?php _e('Answer for question 1 when the release has already taken place. Use %title and %date as placeholders.', 'dynamic-game-pages'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="dgp_faq_a1_future"><?php _e('Answer 1 (release in the future)', 'dynamic-game-pages'); ?></label></th>
                    <td>
                        <textarea name="dgp_faq_a1_future" id="dgp_faq_a1_future" rows="3" class="large-text"><?php echo esc_textarea(get_option('dgp_faq_a1_future', 'The release of %title is planned for %date')); ?></textarea>
                        <p class="description"><?php _e('Answer for question 1 when the release is in the future. Use %title and %date as placeholders.', 'dynamic-game-pages'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="dgp_faq_q2"><?php _e('Question 2', 'dynamic-game-pages'); ?></label></th>
                    <td>
                        <input name="dgp_faq_q2" type="text" id="dgp_faq_q2" value="<?php echo esc_attr(get_option('dgp_faq_q2', 'What are the system requirements for %title?')); ?>" class="regular-text" />
                        <p class="description"><?php _e('Second FAQ question. You can use %title as a placeholder for the game name.', 'dynamic-game-pages'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="dgp_faq_a2"><?php _e('Answer 2', 'dynamic-game-pages'); ?></label></th>
                    <td>
                        <textarea name="dgp_faq_a2" id="dgp_faq_a2" rows="3" class="large-text"><?php echo esc_textarea(get_option('dgp_faq_a2', '%title has the following requirements: %requirements')); ?></textarea>
                        <p class="description"><?php _e('Answer for question 2. Use %title and %requirements as placeholders.', 'dynamic-game-pages'); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}