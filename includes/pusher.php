<?php

class Dify_Pusher {
    // 推送文章到Dify知识库
    public static function push_posts($post_ids) {
        $api_key = get_option('dify_api_key');
        $dataset_id = get_option('dify_dataset_id');
        $indexing_technique = get_option('dify_indexing_technique', 'high_quality');

        $max_retries = 3;
        $retry_delay = 5;

        foreach ($post_ids as $post_id) {
            $post = get_post($post_id);

            // 准备文章数据
            $data = array(
                'name' => $post->post_title,
                'text' => $post->post_content,
                'indexing_technique' => $indexing_technique,
                'process_rule' => array(
                    'mode' => 'automatic',
                ),
            );

            // 调用Dify API创建文档
            for ($retry = 0; $retry < $max_retries; $retry++) {
                $response = wp_remote_post(
                    "https://gpt.nengbank.com/v1/datasets/{$dataset_id}/document/create_by_text",
                    array(
                        'headers' => array(
                            'Authorization' => "Bearer {$api_key}",
                            'Content-Type' => 'application/json',
                        ),
                        'body' => json_encode($data),
                    )
                );

                if (!is_wp_error($response)) {
                    $status_code = wp_remote_retrieve_response_code($response);
                    if ($status_code === 200) {
                        break;
                    } elseif ($status_code === 400) {
                        $body = wp_remote_retrieve_body($response);
                        $error = json_decode($body, true);
                        if (isset($error['code'])) {
                            switch ($error['code']) {
                                case 'dataset_not_initialized':
                                    sleep($retry_delay);
                                    continue 2;
                                case 'document_already_finished':
                                case 'document_indexing':
                                    break 2;
                                default:
                                    Dify_Record::log_push_record($post_id, 'failed', $error['message']);
                                    continue 2;
                            }
                        }
                    }
                }

                sleep($retry_delay);
            }

            // 处理API响应,记录推送结果
            if (is_wp_error($response)) {
                Dify_Record::log_push_record($post_id, 'failed', $response->get_error_message());
            } else {
                $body = wp_remote_retrieve_body($response);
                $status_code = wp_remote_retrieve_response_code($response);
                $response_body = json_decode($body, true);

                if ($status_code === 200 && isset($response_body['document'])) {
                    Dify_Record::log_push_record($post_id, 'success');
                } else {
                    $error_message = isset($response_body['message']) ? $response_body['message'] : 'API returned an error';
                    Dify_Record::log_push_record($post_id, 'failed', $error_message);
                }
            }
        }
    }

    // 渲染文章筛选和推送的表单
    public static function render_push_form() {
        // 获取所有文章类别
        $categories = get_categories(array(
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => false,
        ));
        ?>
        <div class="wrap">
            <h1>Push Posts to Dify</h1>
            <form method="post" action="">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="post_category">Post Category</label></th>
                        <td>
                            <select name="post_category" id="post_category">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $category) : ?>
                                    <option value="<?php echo esc_attr($category->term_id); ?>"><?php echo esc_html($category->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="post_status">Post Status</label></th>
                        <td>
                            <select name="post_status" id="post_status">
                                <option value="publish">Published</option>
                                <option value="draft">Draft</option>
                                <option value="pending">Pending</option>
                                <option value="future">Future</option>
                                <option value="private">Private</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="post_date_from">Post Date From</label></th>
                        <td><input name="post_date_from" type="date" id="post_date_from" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="post_date_to">Post Date To</label></th>
                        <td><input name="post_date_to" type="date" id="post_date_to" class="regular-text"></td>
                    </tr>
                </table>
                <?php submit_button('Filter Posts', 'secondary', 'dify_filter'); ?>
            </form>
            <?php
            if (isset($_POST['dify_filter'])) {
                // 根据筛选条件查询文章
                $args = array(
                    'post_type' => 'post',
                    'posts_per_page' => -1,
                    'category' => isset($_POST['post_category']) ? intval($_POST['post_category']) : '',
                    'post_status' => isset($_POST['post_status']) ? sanitize_text_field($_POST['post_status']) : 'publish',
                    'date_query' => array(
                        'after' => isset($_POST['post_date_from']) ? sanitize_text_field($_POST['post_date_from']) : '',
                        'before' => isset($_POST['post_date_to']) ? sanitize_text_field($_POST['post_date_to']) : '',
                        'inclusive' => true,
                    ),
                );
                $posts = get_posts($args);
                if (!empty($posts)) {
                    ?>
                    <form method="post" action="">
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th scope="col" class="manage-column column-cb check-column"><input type="checkbox" /></th>
                                    <th scope="col" class="manage-column">Title</th>
                                    <th scope="col" class="manage-column">Author</th>
                                    <th scope="col" class="manage-column">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($posts as $post) : ?>
                                    <tr>
                                        <th scope="row" class="check-column"><input type="checkbox" name="post_ids[]" value="<?php echo esc_attr($post->ID); ?>" /></th>
                                        <td><?php echo esc_html($post->post_title); ?></td>
                                        <td><?php echo esc_html(get_the_author_meta('display_name', $post->post_author)); ?></td>
                                        <td><?php echo esc_html(get_the_date('', $post->ID)); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php submit_button('Push to Dify', 'primary', 'dify_push'); ?>
                    </form>
                    <?php
                } else {
                    echo '<p>No posts found.</p>';
                }
            }
            ?>
        </div>
        <?php
    }
}
