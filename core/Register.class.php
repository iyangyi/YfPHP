<?php
/**
 * Yf框架
 * 注册变量
 *
 * @package Register.class.php
 * @author tony.yang <yangyiphper@sina.cn>
 * @version $Id: Module.class.php 2014-07-16 $
 */

class Register {

	protected static $_config;

	/**
	 * 获取变量值
	 * 
	 * @param  string $name 
	 * @return string
	 */
	public static function get($name) {
		if (self::isRegister($name)) {
			return self::$_config[$name];
		}
		else {
			return NULL;
		}
	}

	/**
	 * 设置变量值
	 * 
	 * @param  string $name 
	 * @param  string $value
 	 * @return string
	 */
	public static function set($name, $value) {
		if (!isset($name) || !isset($value)) {
			Error::write('register name or value not null');
		}
		self::$_config[$name] = $value;
		return self::$_config[$name];
	}


	/**
	 * 判断变量是否注册
	 * 
	 * @param  string $name
	 * @return bool
	 */
	public static function isRegister($name) {
		return isset(self::$_config[$name]);
	}
}