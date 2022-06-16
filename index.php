<?php
/**
 * Plugin Name: TinyPNG2
 * Plugin URI: https://github.com/mina998/tinypng2
 * Description: TinyPNG 图片压缩
 * Version: 0.1
 * Author: Soroy
 * Author URI: https://www.skiss.cc
 **/

defined('ABSPATH') || exit();
define( 'PLUGIN_ROOT', dirname(__FILE__ ) . '/');
$plugin_file = plugin_basename(__FILE__);

require_once 'TinyPNG.php';
spl_autoload_register('TinyPNG::loader');
$image = new Image($plugin_file);

# 添加插件设置菜单
add_action('admin_menu', [$image, 'admin_menu']);
// 在插件列表页添加设置按钮
add_filter('plugin_action_links', [$image, 'plugin_action_links'], 10, 2);
// Ajax 操作key
add_action( 'wp_ajax_ask_key', [$image, 'ajax_key_ask'] );

add_filter('wp_generate_attachment_metadata', [$image, 'image_optimization']);
// 后台提示
add_action( 'admin_notices',  [$image, 'key_admin_notice'] );
