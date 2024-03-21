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

// Include the configuration file
require_once plugin_dir_path(__FILE__) . 'dify-config.php';

class Dify_Integration {
    private $dify_api_key;
    private $dify_dataset_id;
    private $dify_api_base_url;
    private $wordpress_api_base_url;
    private $processed_posts_option_name = 'dify_integration_processed_posts';
    private $import_all_posts_option_name = 'dify_integration_import_all_posts';

    public function __construct() {
        global $dify_api_key, $dify_dataset_id, $dify_api_base_url, $wordpress_api_base_url;

        $this->dify_api_key = $dify_api_key;
        $this->dify_dataset_id = $dify_dataset_id;
        $this->dify_api_base_url = $dify_api_base_url;
        $this->wordpress_api_base_url = $wordpress_api_base_url;

        add_action('admin_menu', [$this, 'add_admin_menu']);
        $this->init_processed_posts_option();
        add_action('admin_init', [$this, 'register_import_all_posts_setting']);
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

    public function register_import_all_posts_setting() {
        register_setting('dify_integration_settings', $this->import_all_posts_option_name);
    }

    public function render_admin_page() {
        if (isset($_POST['import'])) {
            $this->import_wordpress_posts_to_dify();
        }

        $import_all_posts = get_option($this->import_all_posts_option_name);
        ?>
        <form method="post">
            <input type="submit" name="import" value="Import Posts to Dify">
        </form>
        <form method="post" action="options.php">
            <?php settings_fields('dify_integration_settings'); ?>
            <input type="checkbox" name="<?php echo $this->import_all_posts_option_name; ?>" value="1" <?php checked(1, $import_all_posts); ?>>
            <label>Import all posts (including previously processed ones)</label>
            <?php submit_button(); ?>
        </form>
        <?php
    }

    private function init_processed_posts_option() {
        if (!get_option($this->processed_posts_option_name)) {
            add_option($this->processed_posts_option_name, []);
        }
    }

    private function get_wordpress_posts($offset = 0) {
        global $posts_per_batch;

        $api_url = $this->wordpress_api_base_url . "/posts?_embed&per_page={$posts_per_batch}&offset={$offset}";
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
        global $posts_per_batch;

        $offset = 0;
        $processed_posts = get_option($this->processed_posts_option_name);
        $import_all_posts = get_option($this->import_all_posts_option_name);

        if ($import_all_posts) {
            $processed_posts = [];
        }

        while (true) {
            $posts = $this->get_wordpress_posts($offset);

            if (empty($posts)) {
                break;
            }

            foreach ($posts as $post) {
                if (!in_array($post['id'], $processed_posts)) {
                    $result = $this->upload_post_to_dify($post);

                    if ($result) {
                        $processed_posts[] = $post['id'];
                        echo "Post '{$post['title']['rendered']}' uploaded to Dify successfully.<br>";
                    } else {
                        echo "Failed to upload post '{$post['title']['rendered']}' to Dify.<br>";
                    }
                }
            }

            $offset += $posts_per_batch;
        }

        update_option($this->processed_posts_option_name, $processed_posts);
    }
}

// Initialize the plugin when plugins are loaded
function dify_integration_init() {
    $dify_integration = new Dify_Integration();
    register_activation_hook(__FILE__, [$dify_integration, 'activate']);
}
add_action('plugins_loaded', 'dify_integration_init');
