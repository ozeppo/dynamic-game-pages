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
        font-size: 18px;
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