<?php
/**
 * USER : Soroy
 * DATE : 2022-06-13 013
 */

if ( ! defined( 'ABSPATH' ) && ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	// 如果 uninstall 不是从 WordPress 调用，则退出
	exit();
}
$option_name = dirname( __FILE__ );
$option_name = basename($option_name) . '_plugin';
// 从 options 表删除选项
delete_option( $option_name );