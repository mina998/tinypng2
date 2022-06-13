<?php
/**
 * USER : Soroy
 * DATE : 2022-06-11 011
 */

class Image {
	private $plugin_file;
	private $full_path;
	private $option_name;
	private $used_key;
	private $mode;
	/**
	 * 构造方法
	 * @param $plugin_file :插件文件
	 */
	public function __construct($plugin_file){
		$this->plugin_file = $plugin_file;
		$this->option_name = str_replace('.', '_', dirname($this->plugin_file)).'_plugin';
		$this->full_path   = wp_get_upload_dir()['path'].'/';
		$this->mode = $this->ty_option_action('mode') ?: 0;
	}

	/**
	 * 创建 TinyPNG对象
	 * @param $key : API key
	 * @param $image_path  : 图片路径
	 * @return TinyPNG
	 */
	private function tp($key, $image_path = null): TinyPNG {
		return new TinyPNG($key, $image_path);
	}

	/**
	 * 压缩图片
	 * @param $image : 图片路径
	 * @return void
	 */
	private function compress($image){
		if($this->is_uesd_key()){
			$used = $this->tp($this->used_key['key'], $image)->get_compression_count();
			$this->used_key['used'] = $used;
		}
	}

	/**
	 * 判断并设置当前操作Key
	 * @return bool
	 */
	private function is_uesd_key(): bool {

		if(is_array($this->used_key) && $this->used_key['used'] < $this->used_key['total']){
			return true;
		}
		$keys = $this->ty_option_action('keys');
		foreach($keys as $key => $item) {
			if($item['used'] < $item['total']){
				$this->used_key = array_merge(['key'=>$key], $item);
				return true;
			}
		}
		return false;
	}

	/**
	 * 处理KEYs 数据
	 * @param $string
	 * @return array
	 */
	private function handler_keys($string): array {
		$temp = explode("\n", $string);
		$temp = array_filter($temp, function($item){
			return trim($item);
		});
		$keys = $this->ty_option_action('keys');
		foreach ($temp as $id => $item){
			$temp[$id] = $item = sanitize_text_field($item);
			if(strpos($item,'#') > 10 && !preg_match('/[\x{4e00}-\x{9fa5}]+/u', $item) ){
				$row = explode('#', $item);
				$key = trim($row[0]);
				$val = trim($row[1]);
				if(!array_key_exists($key,$keys)){
					$keys[$key] = ['total'=>$val, 'used'=>0];
					unset($temp[$id]);
				}
			}
		}
		$this->ty_option_action('keys', $keys);
		return $temp;
	}

	/**
	 * 查询和更新插件参数
	 * @param $name : 插件参数对象中的属性名
	 * @param string|array $data : 需要保存的参数数据
	 * @return array|bool
	 */
	private function ty_option_action($name, $data=false){
		$options = get_option($this->option_name) ?: [];
		if(is_array($data) || is_string($data) || is_int($data) || $data){
			$options[$name] = $data;
			update_option($this->option_name, $options);
			return true;
		}
		if(isset($options[$name]) && $data === false){
			return $options[$name];
		}
		return [];
	}

	/**
	 * Ajax 更新 KEYs
	 * @return void
	 */
	public function tinypng_key_ask(){
		$key  = sanitize_text_field($_POST['key']);
		$keys = $this->ty_option_action('keys');
		if (array_key_exists($key, $keys) && $_POST['q'] == 'test'){
			echo $this->tp($key)->validate();
		}
		if (array_key_exists($key, $keys) && $_POST['q'] == 'del'){
			unset($keys[$key]);
			$this->ty_option_action('keys', $keys);
			echo $key;
		}
		die();
	}

	public function __destruct(){
		if (is_array($this->used_key)){
			$keys = $this->ty_option_action('keys');
			extract($this->used_key);
			$keys[$key]['used'] = $used;
			$this->ty_option_action('keys', $keys);
		}
	}

	public function image_optimization($meta) {
		if (isset($meta['file'])) {
			$flie_path = $this->full_path . basename($meta['file']);
			$this->compress($flie_path);
		}
		if (isset($meta['sizes']) && count($meta['sizes']) > 0) {
			foreach ($meta['sizes'] as $item) {
				$flie_path = $this->full_path . basename($item['file']);
				$this->compress($flie_path);
			}
		}
		return $meta;
	}

	public function plugin_action_links($links, $file){
		if ($this->plugin_file == $file) {
			$links[] = '<a href="admin.php?page=' . $file . '">设置</a>';
		}
		return $links;
	}

	public function view() {
		if ( isset($_POST['_wpnonce']) ){
			$fails= $this->handler_keys($_POST['keys']);
			$this->mode = sanitize_text_field($_POST['mode']);
			$this->ty_option_action('mode', $this->mode);
		}
		$keys = $this->ty_option_action('keys');
		$fails= $fails ?? [];
		require_once plugin_dir_path(__FILE__) . 'view.php';
	}

	/**
	 * 在导航栏“设置”中添加条目
	 * @return void
	 */
	public function admin_menu(): void {
		add_options_page('TinyPNG图片压缩', '图片压缩', 'manage_options', $this->plugin_file, [$this, 'view']);
	}
}