<?php

/*
Plugin Name: RSS Feed to WP Post
Plugin URI: https://codewp.ai
Description: This plugin automatically creates and publishes posts on WordPress from an RSS feed.
Version: 1.0
Author: Sekou Perry
Author URI: https://codewp.ai
Text Domain: codewp
*/

define('RSS_FEED_URL', 'http://example.com/rss'); // Replace with your RSS feed URL
define('POST_TYPE', 'post'); // Set the post type
define('POST_STATUS', 'publish'); // Set the post status
define('IMPORT_LIMIT', 10); // Maximum number of posts to import. Set to 0 for unlimited.

function codewp_fetch_rss_feed() {
    $feed = fetch_feed(RSS_FEED_URL);

    if (!is_wp_error($feed)) {
        $items = $feed->get_items();

        $import_count = 0;
        foreach ($items as $item) {
            if (IMPORT_LIMIT > 0 && $import_count >= IMPORT_LIMIT) {
                break;
            }

            $post_id = post_exists($item->get_title());

            if (!$post_id) {
                $post_data = array(
                    'post_title'    => wp_strip_all_tags($item->get_title()),
                    'post_content'  => $item->get_content(),
                    'post_status'   => POST_STATUS,
                    'post_type'     => POST_TYPE,
                );

                wp_insert_post($post_data);
                $import_count++;
            }
        }
    }
}

if (!wp_next_scheduled('codewp_fetch_rss_feed')) {
    wp_schedule_event(time(), 'hourly', 'codewp_fetch_rss_feed');
}

add_action('codewp_fetch_rss_feed', 'codewp_fetch_rss_feed');
