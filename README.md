# wordpres2dify
A plugin to import WordPress posts into Dify using the WordPress REST API.

# Dify Knowledge Base Pusher

A WordPress plugin to push posts to Dify Knowledge Base using the Dify API.

## Features

- Push WordPress posts to Dify Knowledge Base
- Filter posts by category, status, and date range
- Bulk push selected posts
- Record and display push status and results
- Retry failed pushes and handle API errors

## Installation

1. Download the plugin zip file and extract it.
2. Upload the `dify-knowledge-base-pusher` directory to the `/wp-content/plugins/` directory of your WordPress installation.
3. Activate the plugin through the 'Plugins' menu in WordPress.

## Configuration

1. Go to the 'Dify Pusher' settings page under the 'Settings' menu in your WordPress admin panel.
2. Enter your Dify API Key and Dataset ID. You can find this information in your Dify developer console.
3. Select the desired indexing technique (high_quality or economy).
4. Click 'Save Settings' to store your configuration.

## Usage

1. Go to the 'Push to Dify' page under the 'Dify Pusher' menu in your WordPress admin panel.
2. Use the filters to select the desired posts by category, status, and date range.
3. Click 'Filter Posts' to display the matching posts.
4. Select the posts you want to push to Dify Knowledge Base by checking the checkboxes next to each post.
5. Click 'Push to Dify' to start the push process.
6. The plugin will push the selected posts to Dify Knowledge Base using the Dify API, retrying failed pushes and handling API errors.
7. You can view the push status and results on the 'Push Records' page under the 'Dify Pusher' menu.

## Push Records

The 'Push Records' page displays a table with the following information for each pushed post:

- Post ID: The ID of the pushed post.
- Title: The title of the post, linking to the post edit page in WordPress.
- Status: The status of the push (success or failed).
- Message: Additional information about the push result or error.
- Date: The date and time of the push.

The push records are paginated, with 20 records per page. You can navigate between pages using the pagination links below the table.

## Troubleshooting

If you encounter any issues while using the plugin, please check the following:

- Make sure your Dify API Key and Dataset ID are correct and properly configured in the plugin settings.
- Check the push records for any error messages or additional information.
- If posts are not being pushed, make sure they meet the selected filter criteria and are not already processed or currently being indexed by Dify.
- If you continue to experience issues, please contact Dify support or open an issue on the plugin's GitHub repository.

## Contributing

If you would like to contribute to the development of this plugin, please fork the repository and submit a pull request with your changes. We welcome bug reports, feature requests, and code improvements.

## Dify
https://github.com/langgenius/dify/releases

## License

This plugin is released under the [GPL-2.0+ License](https://www.gnu.org/licenses/gpl-2.0.html).
