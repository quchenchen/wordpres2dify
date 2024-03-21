# wordpres2dify
A plugin to import WordPress posts into Dify using the WordPress REST API.

This plugin fully implements the following features:

1. Automatically imports WordPress posts into Dify upon plugin activation.

2. Adds a "Dify Integration" page in the WordPress admin panel, which includes an "Import Posts to Dify" button. Clicking this button also triggers the import process.

3. Includes author and publication date information in the posts uploaded to Dify.

Please ensure that you replace `$this->dify_api_key`, `$this->dify_dataset_id`, `$this->dify_api_base_url`, and `$this->wordpress_api_base_url` with your own values. You can also configure the parameters separately to ensure data security.
