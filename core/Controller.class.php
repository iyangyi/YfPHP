<?php
/**
 * Yf框架
 * controller基类
 *
 * @package index.php
 * @author tony.yang <tongyyang@pptv.com>
 */
class Controller {

	public $smarty;

	public function __construct() {
		
	}

	public function setSmarty() {

		$name = 'set_smarty';
		if (!Register::isRegister($name)) {
			require_once FRAME_CORE_PATH . 'smarty/Smarty.class.php';
			$smarty = new Smarty();
			$smarty->setTemplateDir(APP_TEMPLATE_PATH);  
			$smarty->setCompileDir(APP_COMPILE_PATH);
			$smarty->left_delimiter  = '<{';  
			$smarty->right_delimiter = '}>';  
			Register::set($name, $smarty);
		}
		return Register::get($name);
	}

	public function assign($name, $value) {
		$this->smarty = $this->setSmarty();
		$this->smarty->assign($name, $value);
	}

	public function display($html) {
		if (!$this->smarty) {
			$this->smarty = $this->setSmarty();
		}
		$this->smarty->display($html);
	}

	public function fetch($html) {
		if (!$this->smarty) {
			$this->smarty = $this->setSmarty();
		}
		return $this->smarty->fetch($html);
	}

	/**
	 * [ajaxReturn ajax返回数据]
	 * @param  [type]  $data             
	 * @param  integer $status           
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