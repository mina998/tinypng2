<?php
/**
 * Plugin Name: TinyPNG2
 * Plugin URI: https://github.com/mina998
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
$zip = new Image($plugin_file);

# 添加插件设置菜单
add_action('admin_menu', [$zip, 'admin_menu']);
// 在插件列表页添加设置按钮
add_filter('plugin_action_links', [$zip, 'plugin_action_links'], 10, 2);
// Ajax 操作key
add_action( 'wp_ajax_ask_key', [$zip, 'tinypng_key_ask'] );

if(is_ssl()){
	add_filter('wp_generate_attachment_metadata', [$zip, 'image_optimization']);
}
