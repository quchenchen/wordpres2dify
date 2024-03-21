<?php
// Dify API configuration
$dify_api_key = 'your_dify_api_key_here';
$dify_dataset_id = 'your_dify_dataset_id_here';
$dify_api_base_url = 'http://gpt.dify.ai/v1';

// WordPress API configuration
$wordpress_api_base_url = 'https://www.yourweb.com/wp-json/wp/v2';

// Number of posts to process per batch
$posts_per_batch = 100;

// Custom post types to import (leave empty to import all post types)
$custom_post_types = [];

// Categories to import (leave empty to import all categories)
$categories_to_import = [];

// Tags to import (leave empty to import all tags)
$tags_to_import = [];

// Custom fields to include in the imported content (leave empty to exclude custom fields)
$custom_fields_to_include = [];

// Attachment import settings
$import_attachments = true;
$attachment_download_timeout = 60; // in seconds

// Log settings
$enable_logging = true;
$log_file_path = plugin_dir_path(__FILE__) . 'dify-integration.log';

// Debug mode
$debug_mode = false;
