<?php

class Dify_Record {
    // 创建推送记录数据表
    public static function create_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dify_push_records';

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id INT(11) NOT NULL AUTO_INCREMENT,
            post_id INT(11) NOT NULL,
            status VARCHAR(20) NOT NULL,
            message TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    // 记录推送结果
    public static function log_push_record($post_id, $status, $message = '') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dify_push_records';

        $wpdb->insert(
            $table_name,
            array(
                'post_id' => $post_id,
                'status' => $status,
                'message' => $message,
            )
        );
    }

    // 渲染推送记录的表格
    public static function render_record_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dify_push_records';

        // 获取总记录数
        $total_records = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

        // 分页参数
        $per_page = 20;
        $current_page = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
        $offset = ($current_page - 1) * $per_page;

        // 获取分页的记录
        $records = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC LIMIT $offset, $per_page");

        if (!empty($records)) {
            ?>
            <div class="wrap">
                <h1 class="wp-heading-inline">Push Records</h1>
                <hr class="wp-header-end">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th scope="col" class="manage-column">Post ID</th>
                            <th scope="col" class="manage-column">Title</th>
                            <th scope="col" class="manage-column">Status</th>
                            <th scope="col" class="manage-column">Message</th>
                            <th scope="col" class="manage-column">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $record) : ?>
                            <tr>
                                <td><?php echo esc_html($record->post_id); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(get_edit_post_link($record->post_id)); ?>" target="_blank">
                                        <?php echo esc_html(get_the_title($record->post_id)); ?>
                                    </a>
                                </td>
                                <td><?php echo esc_html(ucfirst($record->status)); ?></td>
                                <td><?php echo esc_html($record->message); ?></td>
                                <td><?php echo esc_html(date_i18n('Y-m-d H:i:s', strtotime($record->created_at))); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php
                // 输出分页导航
                $pagination_args = array(
                    'total' => ceil($total_records / $per_page),
                    'current' => $current_page,
                    'base' => admin_url('admin.php?page=push-records&paged=%#%'),
                    'format' => '?paged=%#%',
                );
                echo '<div class="tablenav bottom">';
                echo '<div class="tablenav-pages">';
                echo paginate_links($pagination_args);
                echo '</div>';
                echo '</div>';
                ?>
            </div>
            <?php
        } else {
            echo '<p>No push records found.</p>';
        }
    }
}
