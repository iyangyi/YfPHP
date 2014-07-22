<?php
/**
 * Yf框架
 *
 * @package index.php
 * @author tony.yang <tongyyang@pptv.com>
 * @version  $ID 2014-03-27 $
 */

// error_reporting(E_ERROR | E_WARNING | E_NOTICE | E_PARSE);
//error_reporting(E_ERROR | E_PARSE);

header("content-Type: text/html; charset=UTF-8");
//1. 必须定义web路径 
if (!defined('APP_PATH')) {
	echo '请先定义APP_PATH';die;
}

//2.定义框架路径
define('DS', DIRECTORY_SEPARATOR);//目录分割符
define('FRAME_PATH', (dirname(__FILE__)));
define('FRAME_CONFIG_PATH', FRAME_PATH . DS. 'config'. DS);
define('FRAME_COMMON_fUNCTION_PATH', FRAME_PATH . DS. 'common'. DS);
define('FRAME_CORE_PATH', FRAME_PATH . DS. 'core'. DS);
define('APP_COMMON_fUNCTION_PATH', APP_PATH . DS. 'common'. DS);
define('APP_CONFIG_PATH', APP_PATH . DS. 'config'. DS);
define('APP_RUNTIME_PATH', APP_PATH. DS. 'runtime'. DS);
define('APP_CONTROLLER_PATH', APP_PATH . DS. 'controller'. DS);
define('APP_MODEL_PATH', APP_PATH . DS. 'model'. DS);
define('APP_MODULE_PATH', APP_PATH . DS. 'module'. DS);
define('APP_PUBLIC_PATH', APP_PATH . DS. 'public'. DS);
define('APP_TEMPLATE_PATH', APP_PATH . DS. 'view'. DS);
define('APP_COMPILE_PATH', APP_PATH . DS. 'runtime'. DS . 'tpl' . DS);

//3.加载Init初始化文件和核心文件
require_once FRAME_CORE_PATH. 'Init.class.php';
require_once FRAME_CORE_PATH. 'Controller.class.php';
require_once FRAME_CORE_PATH. 'Model.class.php';
require_once FRAME_CORE_PATH. 'Module.class.php';
require_once FRAME_CORE_PATH. 'Error.class.php';

//3.读取配置看是否自动生成目录
if (config('auto_create_dir')) {
	autoCreateDir();
}

//4.加载common功能函数
require_once FRAME_COMMON_fUNCTION_PATH. 'common.php';
if (is_file(APP_COMMON_fUNCTION_PATH. 'common.php')) {
	require_once APP_COMMON_fUNCTION_PATH. 'common.php';
}

//5.show
show();


