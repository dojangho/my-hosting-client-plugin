<?php
/*
Plugin Name: My Hosting Client Plugin
Description: 고객용 관리 플러그인 (SEO Meta / Open Graph + GitHub 업데이트)
Version: 1.0.1
Author: Your Company
Update URI: https://github.com/dojangho/my-hosting-client-plugin
*/

if (!defined('ABSPATH')) {
    exit;
}

/*
 * plugin-update-checker 라이브러리를 plugin-update-checker 폴더에 넣으면
 * GitHub 자동 업데이트가 작동합니다.
 */

if (file_exists(__DIR__ . '/plugin-update-checker/plugin-update-checker.php')) {
    require_once __DIR__ . '/plugin-update-checker/plugin-update-checker.php';

    if (class_exists('YahnisElsts\\PluginUpdateChecker\\v5\\PucFactory')) {
        $updateChecker = YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
            'https://github.com/djangho/my-hosting-client-plugin/',
            __FILE__,
            'my-hosting-client-plugin'
        );

        $updateChecker->setBranch('main');
    }
}

add_action('admin_menu', function () {
    add_menu_page(
        'SEO 설정',
        'SEO 설정',
        'manage_options',
        'my-hosting-seo',
        'my_hosting_seo_page',
        'dashicons-chart-area',
        30
    );
});

add_action('admin_init', function () {
    register_setting('my_hosting_seo_group', 'my_hosting_seo_options');

    add_settings_section(
        'my_hosting_seo_section',
        'SEO 기본 설정',
        '__return_false',
        'my-hosting-seo'
    );

    $fields = [
        'meta_description' => 'Meta Description',
        'meta_keywords'    => 'Meta Keywords',
        'robots'           => 'Robots',
        'canonical_url'    => 'Canonical URL',
        'og_title'         => 'Open Graph Title',
        'og_description'   => 'Open Graph Description',
        'og_image'         => 'Open Graph Image URL',
        'twitter_card'     => 'Twitter Card',
        'twitter_image'    => 'Twitter Image URL',
    ];

    foreach ($fields as $key => $label) {
        add_settings_field(
            $key,
            $label,
            function () use ($key) {
                $options = get_option('my_hosting_seo_options', []);
                $value = isset($options[$key]) ? $options[$key] : '';

                if ($key === 'meta_description' || $key === 'og_description') {
                    echo '<textarea name="my_hosting_seo_options[' . esc_attr($key) . ']" rows="4" style="width:100%;">' . esc_textarea($value) . '</textarea>';
                } else {
                    echo '<input type="text" name="my_hosting_seo_options[' . esc_attr($key) . ']" value="' . esc_attr($value) . '" style="width:100%;" />';
                }
            },
            'my-hosting-seo',
            'my_hosting_seo_section'
        );
    }
});

function my_hosting_seo_page() {
    ?>
    <div class="wrap">
        <h1>SEO 설정</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('my_hosting_seo_group');
            do_settings_sections('my-hosting-seo');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

add_action('wp_head', function () {
    if (is_admin()) {
        return;
    }

    $options = get_option('my_hosting_seo_options', []);

    if (empty($options) || !is_array($options)) {
        return;
    }

    if (!empty($options['meta_description'])) {
        echo '<meta name="description" content="' . esc_attr($options['meta_description']) . '">' . "\n";
    }

    if (!empty($options['meta_keywords'])) {
        echo '<meta name="keywords" content="' . esc_attr($options['meta_keywords']) . '">' . "\n";
    }

    if (!empty($options['robots'])) {
        echo '<meta name="robots" content="' . esc_attr($options['robots']) . '">' . "\n";
    }

    if (!empty($options['canonical_url'])) {
        echo '<link rel="canonical" href="' . esc_url($options['canonical_url']) . '">' . "\n";
    }

    if (!empty($options['og_title'])) {
        echo '<meta property="og:title" content="' . esc_attr($options['og_title']) . '">' . "\n";
    }

    if (!empty($options['og_description'])) {
        echo '<meta property="og:description" content="' . esc_attr($options['og_description']) . '">' . "\n";
    }

    if (!empty($options['og_image'])) {
        echo '<meta property="og:image" content="' . esc_url($options['og_image']) . '">' . "\n";
    }

    echo '<meta property="og:type" content="website">' . "\n";
    echo '<meta property="og:url" content="' . esc_url(home_url()) . '">' . "\n";
    echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '">' . "\n";

    if (!empty($options['twitter_card'])) {
        echo '<meta name="twitter:card" content="' . esc_attr($options['twitter_card']) . '">' . "\n";
    }

    if (!empty($options['twitter_image'])) {
        echo '<meta name="twitter:image" content="' . esc_url($options['twitter_image']) . '">' . "\n";
    }
}, 5);
