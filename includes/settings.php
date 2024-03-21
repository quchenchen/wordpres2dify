<?php

class Dify_Settings {
    // 保存插件设置
    public static function save_settings($settings) {
        update_option('dify_api_key', sanitize_text_field($settings['api_key']));
        update_option('dify_dataset_id', sanitize_text_field($settings['dataset_id']));
        update_option('dify_indexing_technique', sanitize_text_field($settings['indexing_technique']));
    }

    // 渲染插件设置表单
    public static function render_settings_form() {
        $api_key = get_option('dify_api_key');
        $dataset_id = get_option('dify_dataset_id');
        $indexing_technique = get_option('dify_indexing_technique', 'high_quality');
        ?>
        <div class="wrap">
            <h1>Dify Knowledge Base Pusher Settings</h1>
            <form method="post" action="">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="api_key">API Key</label></th>
                        <td><input name="api_key" type="text" id="api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="dataset_id">Dataset ID</label></th>
                        <td><input name="dataset_id" type="text" id="dataset_id" value="<?php echo esc_attr($dataset_id); ?>" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="indexing_technique">Indexing Technique</label></th>
                        <td>
                            <select name="indexing_technique" id="indexing_technique">
                                <option value="high_quality" <?php selected($indexing_technique, 'high_quality'); ?>>High Quality</option>
                                <option value="economy" <?php selected($indexing_technique, 'economy'); ?>>Economy</option>
                            </select>
                        </td>
                    </tr>
                </table>
                <?php submit_button('Save Settings', 'primary', 'dify_submit'); ?>
            </form>
        </div>
        <?php
    }
}
