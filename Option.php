<?php

/**
 * USER : Soroy
 * DATE : 2022-06-14 014
 */
class Option {
	private $keys   = [];
	/**
	 * 运行状态 -1=没有可用密钥 0=没有开启SSL 1=正常
	 * @var int
	 */
	private $status =-1;
	private $mode   = 0;
	private $option_name;
	private $reset;

	public function __construct($option_name){
		$this->reset = $this->reset_init();
		if( !get_option($option_name) ){
			$table = new stdClass();
			$table-> keys   = $this->keys;
			$table-> mode   = $this->mode;
			$table-> status = $this->status;
			$table-> reset  = $this->reset;
			update_option($option_name, $table);
		}
		$this->option_name = $option_name;
	}

	/**
	 * 生成下次重置日期时间戳
	 * @return false|int
	 */
	public function reset_init(){
		$d = strtotime("+1 month");
		$d = date('Y-m',$d). '-01';
		return strtotime($d);
	}

	/**
	 * 操作KEY 删除和更新
	 * @param $key
	 * @param int|bool $used
	 *
	 * @return bool
	 */
	public function key_del_save($key, $used=false): bool {
		$options = get_option($this->option_name);
		$keys   = $options->keys;
		if (array_key_exists($key, $keys)){
			if($used === false) unset($keys[$key]);
			else  $keys[$key]['used'] = $used;
			count($keys) > 0 || $options->status = -1;
			$options->keys = $keys;
			return update_option($this->option_name, $options);
		}
		return false;
	}

	/**
	 * 更新选项
	 * @param $key
	 * @param $value
	 *
	 * @return bool
	 */
	public function __set( $key, $value ) {
		$option = get_option($this->option_name);
		if(!property_exists($option, $key)) return false;
		$option-> $key = $value;
		return  update_option($this->option_name, $option);
	}

	/**
	 * 更新选项s
	 * @param array $data
	 *
	 * @return bool
	 */
	public function update(array $data): bool {
		$option = get_option($this->option_name);
		foreach ($data as $key => $item) {
			if(!property_exists($option, $key)) continue;
			$option->$key = $item;
		}
		return update_option($this->option_name, $option);
	}

	/**
	 * 查询指定KEY
	 * @param $key
	 *
	 * @return false
	 */
	public function __get($key){
		$option = get_option($this->option_name);
		if(!property_exists($option, $key)) return false;
		return $option->$key;
	}

	/**
	 * 查询所有选项
	 * @return false|mixed|void
	 */
	public function select() {
		return get_option($this->option_name);
	}

	/**
	 * 处理KEYs
	 * @param $keys_string
	 *
	 * @return array
	 */
	public function validate($keys_string): array {
		$temp = explode("\n", $keys_string);
		$temp = array_filter($temp, function($item) {
			$item = str_replace(' ', '', $item);
			return sanitize_text_field($item);
		});
		$keys = [];
		foreach ($temp as $id => $item){
			if (strpos($item,'#') > 10 && substr_count($item,'#') < 2 && !preg_match('/[\x{4e00}-\x{9fa5}]+/u', $item)){
				list($key, $total) = explode('#', $item);
				$keys[$key]   = ['total'=>$total, 'used'=>0];
				unset($temp[$id]);
			}
		}
		return ['keys'=>$keys, 'fail'=>$temp];
	}
}