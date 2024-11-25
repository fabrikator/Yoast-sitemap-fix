<?php
/**
 * Plugin Name: Yoast Sitemap Fix
 * Description: Splits Yoast sitemaps by Polylang languages, redirects incorrect language URLs, and excludes post type archives from sitemaps.
 * Version: 0.1
 * Author: Taras Kotvitskiy
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class PolylangYoastSitemapFix {
    public function __construct() {
        add_filter('wpseo_sitemap_url', [$this, 'filter_sitemap_urls_by_language'], 10, 2);
        add_filter('wpseo_sitemap_post_content', [$this, 'filter_sitemap_content_by_language'], 10, 2);
        add_filter('wpseo_sitemap_exclude_post_type_archive', '__return_true'); // Exclude post type archives
        add_action('template_redirect', [$this, 'redirect_invalid_language_urls']);
    }

    // Filter sitemap URLs to split them by language.

    public function filter_sitemap_urls_by_language($url, $type) {
        if (function_exists('pll_get_post_language') && function_exists('pll_languages')) {
            $current_lang = pll_current_language('slug');
            if ($current_lang) {
                $url = home_url("/{$current_lang}/{$type}-sitemap.xml");
            }
        }
        return $url;
    }

    // Register custom post types: review, archive.

    public function register_custom_post_types() {
        register_post_type('review', [
            'labels' => [
                'name' => __('Reviews', 'textdomain'),
                'singular_name' => __('Review', 'textdomain'),
            ],
            'public' => true,
            'has_archive' => true,
        ]);

        register_post_type('archive', [
            'labels' => [
                'name' => __('Archives', 'textdomain'),
                'singular_name' => __('Archive', 'textdomain'),
            ],
            'public' => true,
            'has_archive' => true,
        ]);
    }

    // Filter sitemap content to show only posts/pages of the current language.

    public function filter_sitemap_content_by_language($content, $type) {
        if (!function_exists('pll_get_post_language') || !function_exists('pll_languages')) {
            return $content;
        }

        global $wpdb;
        $current_lang = pll_current_language('slug');
        if (!$current_lang) {
            return $content;
        }

        $post_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} 
                WHERE post_type = %s AND post_status = 'publish'",
                $type
            )
        );

        $filtered_content = '';
        foreach ($post_ids as $post_id) {
            if (pll_get_post_language($post_id) === $current_lang) {
                $filtered_content .= $content; // Adjust to add filtered posts/pages
            }
        }

        return $filtered_content;
    }

    // Redirect invalid language sitemap URLs to the default sitemap index.
     
    public function redirect_invalid_language_urls() {
        $sitemap_base = '/sitemap_index.xml';
        $current_url = $_SERVER['REQUEST_URI'];

        if (preg_match('#^/([a-z]{2})/sitemap_index.xml$#', $current_url, $matches)) {
            $lang = $matches[1];
            $available_languages = pll_languages('slug');

            if (!in_array($lang, $available_languages)) {
                wp_redirect(home_url($sitemap_base));
                exit;
            }
        }
    }
}

new PolylangYoastSitemapFix();

