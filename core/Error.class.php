<?php
/**
 * Yf框架
 * 错误记录打印相关
 *
 * @package Error.class.php
 * @author tony.yang <yangyiphper@sina.cn>
 * @version $Id: Module.class.php 2014-07-16 $
 * 
 */

class Error {

	/**
	 * 屏幕上输出错误信息
	 * @param string $msg
	 * @return void
	 */
	public  static function write($msg) {
		trigger_error($msg, E_USER_ERROR);
	}
}