<?php
/*
Plugin Name: Dify Knowledge Base Pusher
Plugin URI: http://example.com/
Description: A plugin to push WordPress posts to Dify Knowledge Base
Version: 1.0
Author: quchenchen
Author URI: http://example.com/
*/

// 定义插件常量
define('DIFY_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('DIFY_PLUGIN_URL', plugin_dir_url(__FILE__));

// 引入必要的文件
require_once(DIFY_PLUGIN_PATH . 'includes/settings.php');
require_once(DIFY_PLUGIN_PATH . 'includes/pusher.php');
require_once(DIFY_PLUGIN_PATH . 'includes/record.php');

// 注册插件激活和停用的回调函数
register_activation_hook(__FILE__, 'dify_plugin_activate');
register_deactivation_hook(__FILE__, 'dify_plugin_deactivate');

// 插件激活函数
function dify_plugin_activate() {
    Dify_Record::create_table();
}

// 插件停用函数
function dify_plugin_deactivate() {
    // 可以在这里进行一些清理工作
}

// 注册插件的设置页面
add_action('admin_menu', 'dify_plugin_settings_page');

function dify_plugin_settings_page() {
    add_options_page(
        'Dify Knowledge Base Pusher',
        'Dify Pusher',
        'manage_options',
        'dify-pusher',
        'dify_plugin_settings_page_callback'
    );
}

// 插件设置页面的回调函数
function dify_plugin_settings_page_callback() {
    // 处理表单提交
    if (isset($_POST['dify_submit'])) {
        Dify_Settings::save_settings($_POST);
    }

    // 显示设置页面的表单
    Dify_Settings::render_settings_form();
}

// 注册推送文章的管理员菜单
add_action('admin_menu', 'dify_plugin_push_menu');

function dify_plugin_push_menu() {
    add_menu_page(
        'Push Posts to Dify',
        'Push to Dify',
        'manage_options',
        'push-to-dify',
        'dify_plugin_push_page'
    );
}

// 推送文章页面的回调函数
function dify_plugin_push_page() {
    // 处理表单提交
    if (isset($_POST['dify_push'])) {
        $post_ids = isset($_POST['post_ids']) ? $_POST['post_ids'] : array();
        Dify_Pusher::push_posts($post_ids);
    }

    // 显示文章筛选和推送的表单
    Dify_Pusher::render_push_form();
}

// 注册推送记录的管理员菜单
add_action('admin_menu', 'dify_plugin_record_menu');

function dify_plugin_record_menu() {
    add_submenu_page(
        'push-to-dify',
        'Push Records',
        'Push Records',
        'manage_options',
        'push-records',
        'dify_plugin_record_page'
    );
}

// 推送记录页面的回调函数
function dify_plugin_record_page() {
    // 显示推送记录的表格
    Dify_Record::render_record_table();
}
