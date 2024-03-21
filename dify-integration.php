<?php
/*
Plugin Name: Dify Integration
Plugin URI: https://www.example.com/
Description: A plugin to import WordPress posts into Dify using the WordPress REST API.
Version: 1.0
Author: quchenchen
Author URI: https://www.example.com/
*/

// Ensure the plugin is running in a WordPress environment
if (!defined('ABSPATH')) {
    exit;
}

class Dify_Integration {
    private $dify_api_key;
    private $dify_dataset_id;
    private $dify_api_base_url;
    private $wordpress_api_base_url;

    public function __construct() {
        $this->dify_api_key = 'YOUR_DIFY_API_KEY';
        $this->dify_dataset_id = 'YOUR_DIFY_DATASET_ID';
        $this->dify_api_base_url = 'https://api.dify.ai/v1';
        $this->wordpress_api_base_url = 'https://your-wordpress-site.com/wp-json/wp/v2';

        add_action('admin_menu', [$this, 'add_admin_menu']);
    }

    public function activate() {
        $this->import_wordpress_posts_to_dify();
    }

    public function add_admin_menu() {
        add_menu_page(
            'Dify Integration',
            'Dify Integration',
            'manage_options',
            'dify-integration',
            [$this, 'render_admin_page']
        );
    }

    public function render_admin_page() {
        if (isset($_POST['import'])) {
            $this->import_wordpress_posts_to_dify();
        }
        echo '<form method="post">';
        echo '<input type="submit" name="import" value="Import Posts to Dify">';
        echo '</form>';
    }

    private function get_wordpress_posts() {
        $api_url = $this->wordpress_api_base_url . '/posts?_embed';
        $response = wp_remote_get($api_url);

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            echo "Error: $error_message";
            return [];
        } else {
            $posts = json_decode(wp_remote_retrieve_body($response), true);
            return $posts;
        }
    }

    private function upload_post_to_dify($post) {
        $url = "{$this->dify_api_base_url}/datasets/{$this->dify_dataset_id}/document/create_by_text";

        $headers = [
            'Authorization' => 'Bearer ' . $this->dify_api_key,
            'Content-Type' => 'application/json',
        ];

        $author = $post['_embedded']['author'][0]['name'];
        $published_date = date('Y-m-d', strtotime($post['date']));

        $body = [
            'name' => $post['title']['rendered'],
            'text' => "Author: {$author}\nPublished Date: {$published_date}\n\n" . strip_tags($post['content']['rendered']),
            'indexing_technique' => 'high_quality',
            'process_rule' => [
                'mode' => 'automatic',
            ],
        ];

        $response = wp_remote_post($url, [
            'headers' => $headers,
            'body' => json_encode($body),
        ]);

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            echo "Error: $error_message";
            return false;
        } else {
            $response_body = json_decode(wp_remote_retrieve_body($response), true);
            return $response_body;
        }
    }

    private function import_wordpress_posts_to_dify() {
        $posts = $this->get_wordpress_posts();

        foreach ($posts as $post) {
            $result = $this->upload_post_to_dify($post);

            if ($result) {
                echo "Post '{$post['title']['rendered']}' uploaded to Dify successfully.<br>";
            } else {
                echo "Failed to upload post '{$post['title']['rendered']}' to Dify.<br>";
            }
        }
    }
}

// Initialize the plugin when plugins are loaded
function dify_integration_init() {
    $dify_integration = new Dify_Integration();
    register_activation_hook(__FILE__, [$dify_integration, 'activate']);
}
add_action('plugins_loaded', 'dify_integration_init');
