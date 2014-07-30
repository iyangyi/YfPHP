<?php
/**
 * Yf框架
 * controller基类
 *
 * @package Controller.class.php
 * @author tony.yang <yangyiphper@sina.cn>
 * @version $Id: Module.class.php 2014-07-16 $
 */
class Controller {

	public $smarty;
	private $template_mode; //默认是1，1是smarty, 2是原生的
	private $out;

	public function __construct() {
		//默认使用smarty模板引擎
		$this->template_mode = 1;
		if (!config('user_smarty')) {
			$this->template_mode = 2;
		}
	}

	/**
	 * 设置marty模板
	 *
	 * @return smarty object
	 */
	public function setSmarty() {
		$name = 'set_smarty';
		if (!Register::isRegister($name)) {
			require_once FRAME_CORE_PATH . 'smarty/Smarty.class.php';
			$smarty = new Smarty();
			$smarty->setTemplateDir(APP_TEMPLATE_PATH);  
			$smarty->setCompileDir(APP_COMPILE_PATH);
			$smarty->left_delimiter  = '<{';  
			$smarty->right_delimiter = '}>';
			echo 34;die;  
			Register::set($name, $smarty);
		}
		return Register::get($name);
	}

	/**
	 * 设置变量
	 * 
	 * @param  string $name
	 * @param  string $value 
	 * @return void
	 */
	public function assign($name, $value) {
		if ($this->template_mode == 2) {
			$this->out[$name] = $value;
		} else {
			$this->smarty = $this->setSmarty();
			$this->smarty->assign($name, $value);
		}
		
	}

	/**
	 * 显示模板
	 * 
	 * @param  string $html
	 * @return void
	 */
	public function display($html) {
		if ($this->template_mode == 2) {
			//分解变量
			if (is_array($this->out) && $this->out) {
				extract($this->out);
			}
			require_once APP_TEMPLATE_PATH . $html;
		} else {
			if (!$this->smarty) {
				$this->smarty = $this->setSmarty();
			}
			$this->smarty->display($html);
		}
	}

	/**
	 * 渲染模板
	 * 
	 * @param  string $html 
	 * @return html
	 */
	public function fetch($html) {
		if ($this->template_mode == 2) {
			//分解变量
			if (is_array($this->out) && $this->out) {
				extract($this->out);
			}
			return file_get_contents(APP_TEMPLATE_PATH . $html . 'html');
		} else {
			if (!$this->smarty) {
				$this->smarty = $this->setSmarty();
			}
			return $this->smarty->fetch($html);
		}
	}

	/**
	 *  ajax|jsonp返回数据
	 * 
	 * @param  array  $data             
	 * @param  int $status           
	 * @param  string  $return_data_type 
	 * @return json
	 */
	public function ajaxReturn($data, $status = 1, $return_data_type = 'json') {
		header("Content-Type:text/html; charset=utf-8");
		$return_data = json_encode(array('data' => $data, 'status' => $status));
		if (isset($_REQUEST['jsoncallback'])) {
			$return_data = htmlentities($_REQUEST['jsoncallback']) . '(' . json_encode(array('data' => $data, 'status' => $status)) . ')';
		}
		echo $return_data;
	}
}