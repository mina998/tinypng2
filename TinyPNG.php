<?php
use Tinify\Tinify;
use function Tinify\fromFile, Tinify\validate;

class TinyPNG{
	//自身实例
	private static $instance;
	//当前操作KEY
	private $key;
	/**
	 * Option 类的实例
	 * @var Option
	 */
	private $option;

	public function __construct($option) {
		$this->option = $option;
		$this->is_valid_key();
	}

	/**
	 * 每月1号重置所有KEYs 使用次数
	 * @return void
	 */
	private function is_valid_key() {
		if(time() > $this->option->reset){
			$keys = $this->option->keys;
			$keys = array_filter($keys, function($item) {
				$item['used'] = 0;
				return $item;
			});
			$reset_init = $this->option->reset_init();
			$this->option->update(['reset'=> $reset_init, 'keys'=>$keys, 'status'=>1]);
		}
	}

	/**
	 * 判断(设置)当前操作KEY
	 * @return bool
	 */
	private function uesd_key_exist(): bool {
		$keys = $this->option->keys;
		foreach($keys as $key => $item) {
			if($item['used'] < $item['total']){
				$this->key  = $key;
				$this->option->status = 1;
				return true;
			}
		}
		//设置状态为没有可用的KEYs
		$this->option->status = -1;
		return false;
	}

	/**
	 * 压缩图片 成功返回压缩后图片文件大小 失败返回false
	 * @param $image
	 *
	 * @return false|int
	 */
	public function compress($image){
		try {
			if( $this->uesd_key_exist() ){
				Tinify::setKey($this->key);
				$source = fromFile($image);
				$size   = $source->toFile($image);
				$used   = Tinify::getCompressionCount();
				$this->option->key_del_save($this->key, $used);
				return $size;
			}
		} catch(Exception $e) {
			error_log(print_r($e->getMessage(), true));
		}
		return false;
	}

	/**
	 * 单例模式
	 * @param $option
	 *
	 * @return TinyPNG
	 */
	public static function init($option): TinyPNG {
		if(!self::$instance instanceof self){
			self::$instance = new self($option);
		}
		return self::$instance;
	}

	/**
	 * 验证KEY 获取使用次数
	 * @param $key
	 *
	 * @return int|null
	 */
	public static function validate($key): ?int {
		try {
			Tinify::setKey($key);
			validate();
		} catch ( Exception $e ) {
			error_log($e->getMessage());
			return -1;
		}
		return Tinify::getCompressionCount() ?: 0;
	}

	/**
	 * 自动加载类
	 * @param $class
	 * @return void
	 */
	public static function loader($class){
		$class = str_replace('\\', '/', $class);
		if (in_array($class, ['Image', 'Option'])){
			$class = PLUGIN_ROOT . $class . '.php';
		}else if(basename($class) == 'Tinify'){
			$class = PLUGIN_ROOT . 'tinylib/Tinify.php';
		}else{
			$class = PLUGIN_ROOT . 'tinylib/' . $class . '.php';
		}
		if(file_exists($class)){
			require_once $class;
		}
	}
}