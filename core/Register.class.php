<?php
/**
 * Yf框架
 *
 * @package 注册变量
 * @author tony.yang <tongyyang@pptv.com>
 */

class Register {

	protected static $_config;

	/**
	 * get
	 * @param  [string] $name 
	 * @return value
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
	 * set
	 * @param  [string] $name 
	 * @param  [strinfg $value
 	 * @return value
	 */
	public static function set($name, $value) {
		if (empty($name)) {
			Error::write('set config error');
		}
		self::$_config[$name] = $value;
		return self::$_config[$name];
	}


	/**
	 * isSet
	 * @param  [string]  $name [description]
	 * @return 
	 */
	public static function isRegister($name) {
		return isset(self::$_config[$name]);
	}

}