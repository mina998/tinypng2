<?php
use Tinify\Tinify;
use function Tinify\fromFile, Tinify\validate;

class TinyPNG{
	public function __construct($key, $image_path) {
		Tinify::setKey($key);
		if ($image_path){
			$source = fromFile($image_path);
			$source->toFile($image_path);
		}
	}

	/**
	 * 验证 KEY 是否有效
	 * @return int|null
	 */
	public function validate(): ?int {
		try {
			validate();
		} catch ( Exception $e ) {
			error_log($e->getMessage());
			return -1;
		}
		return $this-> get_compression_count();
	}

	/**
	 * 获取使用压缩次数
	 * @return null|int
	 */
	public function get_compression_count(): ?int {
		return Tinify::getCompressionCount();
	}

	/**
	 * 自动加载类
	 * @param $class
	 * @return void
	 */
	public static function loader($class){
		$class = str_replace('\\', '/', $class);
		if ($class == 'Image'){
			$class = PLUGIN_ROOT . 'Image.php';
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