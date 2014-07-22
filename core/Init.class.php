<?php
/**
 * Yf框架
 * 启动加载
 *
 * @package index.php
 * @author tony.yang <tongyyang@pptv.com>
 */

/**
 * 自动加载类
 *
 * @param $class 需要自动加载的类
 * @return resource
 */
function __autoload($class_name) {
	if (empty($class_name)) {
		Error::write('未找到加载的类');
	}
	//Frame基类
	$file_name = FRAME_CORE_PATH. $class_name. '.class.php';
	if (is_file($file_name)) {
		require_once $file_name;
	}
	else {
		//app Controller
		$is_controller = strpos($class_name, 'Controller');
		if ($is_controller !== false) {
			$app_controller_name = APP_CONTROLLER_PATH. $class_name. '.class.php';
			if (is_file($app_controller_name)) {
				require_once $app_controller_name;
			}
			else {
				Error::write($class_name . '类不存在!');
			}
		}else {
			//app Model
			$is_model = strpos($class_name, 'Model');
			if ($is_model !== false) {
				$app_model_name = APP_MODEL_PATH. $class_name. '.class.php';
				if (is_file($app_model_name)) {
					require_once $app_model_name;
				}
				else {
					Error::write($class_name . '类不存在!');
				}
			}
			else {
				//app Module
				$is_module = strpos($class_name, 'Module');
				if ($is_module !== false) {
					$app_module_name = APP_MODULE_PATH. $class_name. '.class.php';
					if (is_file($app_module_name)) {
						require_once $app_module_name;
					}
					else {
						Error::write($class_name . '类不存在!');
					}
				} else {
				 	Error::write($class_name . '类不存在!');
				}
			}
		}
	}
}

/**
 * 读取配置
 * @param  [string] $name
 * @return  value
 */
function config($name) {
	if (Register::isRegister($name)) {
		return Register::get($name);
	}
	else {
		$app_runtime_config = APP_RUNTIME_PATH . 'config'. DS. 'config.php';
		//缓存时间为1天
		if (is_file($app_runtime_config) && (time() - filemtime($app_runtime_config) < 86400)) {
			$config =  unserialize((file_get_contents(APP_RUNTIME_PATH . 'config'. DS . 'config.php')));
			if ($name && isset($config[$name])) {
				Register::set($name, $config[$name]);
				return $config[$name];
			}
			else {
				return '';
			}
		}
		else {
			$config_frame = require FRAME_CONFIG_PATH . 'config.php';
			if (is_file(APP_CONFIG_PATH . 'config.php')) {
				$config_app = require_once APP_CONFIG_PATH . 'config.php';
			}
			$config = $config_frame;
			if (isset($config_app) && is_array($config_app)) {
				$config = array_merge($config_frame, $config_app);
			}
			//生成缓存文件
			if (!is_dir(APP_RUNTIME_PATH)) {
				mkdir(APP_RUNTIME_PATH);
			}
			if (!is_dir(APP_RUNTIME_PATH . DS . 'config')) {
				mkdir(APP_RUNTIME_PATH . DS . 'config');
			}
			@unlink($app_runtime_config);
			@touch($app_runtime_config);
			@file_put_contents($app_runtime_config, serialize($config));
			if (isset($config[$name])) {
				Register::set($name, $config[$name]);
				return $config[$name];
			}
			else {
				return '';
			}
		}
	}
}

/**
 * 自动创建目录
 */
function autoCreateDir() {

	//common && common.php
	if (!is_dir(APP_COMMON_fUNCTION_PATH)) {
		mkdir(APP_COMMON_fUNCTION_PATH) or die('目录不可写,请手动生成目录结构!');
	}
	$common_file = APP_COMMON_fUNCTION_PATH . 'common.php';
	if (!is_file($common_file)) {
		@touch($common_file);
		$common = "<?php\t\n //公共函数，全局可直接调用";
		file_put_contents($common_file, $common);
	}

	//config && config.php
	if (!is_dir(APP_CONFIG_PATH)) {
		@mkdir(APP_CONFIG_PATH);
	}
	$config_file = APP_CONFIG_PATH . 'config.php';
	if (!is_file($config_file)) {
		@touch($config_file);
		$config = "<?php\t\n return array(
			//'配置名' => '配置内容',
		);";
		file_put_contents($config_file, $config);
	}

	//controller && IndexController.class.php
	if (!is_dir(APP_CONTROLLER_PATH)) {
		@mkdir(APP_CONTROLLER_PATH);
	}
	$index_controller_file = APP_CONTROLLER_PATH . 'IndexController.class.php';
	if (!is_file($index_controller_file)) {
		@touch($index_controller_file);
		$index = "<?php\t\n //默认首页
class IndexController extends Controller {
	public function index() {
		echo "."'<div style=\"border: 1px solid #DDDDDD;margin: 50px auto 0;overflow: hidden;padding: 10px;width: 600px;font: 400 14px/25px,Tahoma,sans-serif;color:#174B73;font-weight: bold;\">*^_^* 欢迎使用YfPHP框架!</div>'".";
	}
}";
		file_put_contents($index_controller_file, $index);
	}

	//model
	if (!is_dir(APP_MODEL_PATH)) {
		@mkdir(APP_MODEL_PATH);
	}

	//module
	if (!is_dir(APP_MODULE_PATH)) {
		@mkdir(APP_MODULE_PATH);
	}

	//view
	if (!is_dir(APP_TEMPLATE_PATH)) {
		@mkdir(APP_TEMPLATE_PATH);
	}

	//public
	if (!is_dir(APP_PUBLIC_PATH)) {
		@mkdir(APP_PUBLIC_PATH);
	}

	//runtime
	if (!is_dir(APP_RUNTIME_PATH)) {
		@mkdir(APP_RUNTIME_PATH);
	}
	if (!is_dir(APP_RUNTIME_PATH . DS. 'config')) {
		@mkdir(APP_RUNTIME_PATH . DS. 'config');
	}
	if (!is_dir(APP_RUNTIME_PATH . DS. 'tpl')) {
		@mkdir(APP_RUNTIME_PATH . DS. 'tpl');
	}
	if (!is_dir(APP_RUNTIME_PATH . DS. 'log')) {
		@mkdir(APP_RUNTIME_PATH . DS. 'log');
	}
}

/**
 * show
 */
function show() {
	$php_self_page =  @$_SERVER['PATH_INFO'];
	if (isset($php_self_page)) {
		$path_info_array = explode('/', trim($php_self_page,'/'));
		if (isset($path_info_array[0]) && $path_info_array[0]) {
			$c = $path_info_array[0];
		}
		else {
			$c = config('default_index_controller');
		}
		if (isset($path_info_array[1]) && $path_info_array[1]) {
			$f = $path_info_array[1];
		}
		else {
			$f = config('default_index_action');
		}
	}
	else {
		$c = config('default_index_controller');
		$f = config('default_index_action');
	}
	//伪静态
	if (strpos($f, '.')) {
		$pos = strpos ($f, '.');
   		$f =  substr($f, 0 , $pos);
	}
	$controller_name = ucfirst($c) . 'Controller';
	$controller = new $controller_name();
	if (method_exists($controller, $f)) {
		$controller->$f();
	} else {
		Error::write($controller_name. '类的' . $f . '方法不存在');
	}
}

/**
 * 实例化model/执行方法
 *
 * @param string $model_name
 * @param string $model_function_name
 * @return object
 */
function model($model_name = '', $model_function_name = '') {
	if ($model_name) {
		$model_class_name = ucfirst($model_name) . 'Model';
		//有方法就执行方法
		if ($model_function_name) {
			$model = new $model_class_name();
			if (method_exists($model, $model_function_name)) {
				$model->$model_function_name();
				return $model;
			} else {
				Error::write($model_class_name. '类的' . $model_function_name . '方法不存在');
			}
		} else {
			return new $model_class_name();
		}
	}else {
		return new Model();
	}
}

/**
 * 实例化module/执行方法
 *
 * @param string $module_name
 * @param string $module_function_name
 * @return object
 */
function module($module_name = '', $module_function_name = '') {
	if ($module_name) {
		$module_class_name = ucfirst($module_name) . 'Module';
		//有方法就执行方法
		if ($module_function_name) {
			$module = new $module_class_name();
			if (method_exists($module, $module_function_name)) {
				$module->$module_function_name();
				return $module;
			} else {
				Error::write($module_class_name. '类的' . $module_function_name . '方法不存在');
			}
		} else {
			return new $module_class_name();
		}
	}else {
		return new module();
	}
}

/**
 * 实例化controller/执行方法
 * @param  string $controller_name 
 * @param  string $controller_function_name 
 * @return object
 */
function controller($controller_name = '', $controller_function_name = '') {
	if ($controller_name) {
		$controller_class_name = ucfirst($controller_name) . 'Controller';
		//有方法就执行方法
		if ($controller_function_name) {
			$controller = new $controller_class_name();
			if (method_exists($controller, $controller_function_name)) {
				$controller->$controller_function_name();
				return $controller;
			} else {
				Error::write($controller_class_name. '类的' . $controller_function_name . '方法不存在');
			}
		} else {
			return new $controller_class_name();
		}
	}else {
		return new Controller();
	}
}



