<?php

/**
 * USER : Soroy
 * DATE : 2022-06-14 014
 */
class Image {

	private $plugin_file;
	private $full_path;
	private $option;

	public function __construct($plugin_file) {
		$this->plugin_file = $plugin_file;
		$this->full_path   = wp_get_upload_dir()['path'].'/';
		$this->option      = new Option(str_replace('.', '_', dirname($this->plugin_file)).'_plugin');
	}

	/**
	 * 处理上传图片
	 * @param $meta
	 *
	 * @return mixed
	 */
	public function image_optimization($meta) {
		if($this->option->status == -1) return $meta;
		$tinify = TinyPNG::init($this->option);
		if (isset($meta['file'])) {
			$flie_path = $this->full_path . basename($meta['file']);
			$meta['filesize'] = $tinify->compress($flie_path) ?: $meta['filesize'];
		}
		if (isset($meta['sizes']) && count($meta['sizes']) > 0) {
			foreach ($meta['sizes'] as $id => $item) {
				$flie_path = $this->full_path . basename($item['file']);
				$meta['sizes'][$id]['filesize'] = $tinify->compress($flie_path) ?: $item['filesize'];
			}
		}
		return $meta;
	}

	/**
	 * 插件后台提醒
	 * @return void
	 */
	public function key_admin_notice() {
        $template = '<div class="notice notice-error is-dismissible"> <p>%s</p> </div>';
		if($this->option->status == -1) {
            printf($template, '请检查TinyPNG密钥压缩次数是否已用完, 插件压缩图片功能将被停止.');
		}elseif(!is_ssl()) {
			printf($template, 'TinyPNG2插件 需要开启HTTPS安装链接才可以使用.');
		}
	}

	/**
	 * 处理前端AJAX请求
	 * @return void
	 */
	public function ajax_key_ask(){
		$key  = sanitize_text_field($_POST['key']);
		if ($_POST['q'] == 'test'){
			$total= (int) $_POST['total'];
			$used = TinyPNG::validate($key);
			if($this->option->key_del_save($key, $used) && $used < $total){
				$this->option->status = 1;
            }
			echo $used;
		}
		if ( $_POST['q'] == 'del') {
			$this->option->key_del_save($key);
			echo $key;
		}
		die();
	}

	/**
	 * 加载设置页面HTML
	 * @return void
	 */
	public function view_load(){
		$option = $this->option->select();
		if ( isset($_POST['_wpnonce'])){
			$temp = $this-> option->validate($_POST['keys']);
			$option->keys = $temp['keys'] + $option->keys;
			$option->fail = $temp['fail'];
			$option->mode = sanitize_text_field($_POST['mode']);
			$this->option-> update(['mode'=>$option->mode, 'keys'=>$option->keys]);
		}
		require_once plugin_dir_path(__FILE__) . 'view.php';
	}

	/**
	 * 在插件列表添加设置按钮
	 * @param $links
	 * @param $file
	 *
	 * @return mixed
	 */
	public function plugin_action_links($links, $file){
		if ($this->plugin_file == $file) {
			$links[] = '<a href="admin.php?page=' . $file . '">设置</a>';
		}
		return $links;
	}
	/**
	 * 在导航栏“设置”中添加条目
	 * @return void
	 */
	public function admin_menu(): void {
		add_options_page('TinyPNG图片压缩', '图片压缩', 'manage_options', $this->plugin_file, [$this, 'view_load']);
	}
}