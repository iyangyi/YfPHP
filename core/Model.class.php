<?php
/**
 * Yf框架
 * model基类
 *
 * @package index.php
 * @author tony.yang <tongyyang@pptv.com>
 */
class Model {

	private $child_class;
	private $mysql;
	private $stmt;
	private $band_values;
	private $table;
	private $alias_table_name;
	private $where_keys;
	private $limit_keys;
	private $group_keys;
	private $having_keys;
	private $order_keys;
	private $join_on;
	private $join;


	/**
	 * 构造方法
	 */
	public function __construct() {
		if (!$this->mysql) {
			$this->mysql = $this->connectDb();
		}
	}

	/**
	 * 获取子model方法
	 * @return object
	 */
	private function getChildClassName() {
		$this->child_class = get_class($this);
		$this->child_class = trim(str_replace('Model', '', $this->child_class));
		if (!$this->child_class) {
			$this->child_class = __CLASS__;
		}
		return $this->child_class;
	}

	/**
	 * 连接数据库
	 * @return object
	 */
	private function connectDb() {
		$name = 'connect_mysql';
		if (!Register::isRegister($name)) {
			$mysql_config = config('mysql');
			if (!$mysql_config) {
				Error::write('请在配置文件中添加mysql配置');
			}
			if (!isset($mysql_config['host']) || !isset($mysql_config['db_name']) || !isset($mysql_config['user_name']) || !isset($mysql_config['password'])) {
				Error::write('mysql配置文件缺失参数,请检查');
			}
			$dsn = 'mysql:host='. $mysql_config['host'] .';dbname='. $mysql_config['db_name'];
			$options = array(PDO::ATTR_PERSISTENT => true, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8");
			try {
				$this->mysql  = new PDO ($dsn, $mysql_config['user_name'], $mysql_config['password'], $options);
			} catch (PDOException $e){
				Error::write('数据库连接失败: '  .  $e -> getMessage ());
			}
			Register::set($name, $this->mysql);
		}
		return Register::get($name);
	}

	/**
	 * 单纯的执行sql|一般的增删改
	 * 
	 * @param  string $sql
	 * @return array
	 */
	public function exec($sql) {
		return $this->prepare($sql)->execute();
	}

	/**
	 * 单纯的执行sql|一般的查询
	 * 
	 * @param  string $sql
	 * @return array
	 */
	public function query($sql, $row = 0) {
		if(empty($sql)) {
			return false;
		}
		$this->prepare($sql)->execute();
		$this->stmt->setFetchMode(PDO::FETCH_ASSOC);
		if ($row) {
			return $this->stmt->fetch();
		} 
		else {
			return $this->stmt->fetchAll();
		}
	}

	/**
	 * 指定表名
	 * 
	 * @param  string $able_name
	 * @param  string alias_table_name
	 */
	public function table($table_name, $alias_table_name = ''){
		$this->table = $table_name;
		$this->alise_table = '';
		//别名
		if ($alias_table_name) {
			$this->alise_table = ' AS ' . $alias_table_name;
		}
		$this->where_keys = '';
		$this->band_values = '';
		$this->limit_keys = '';
		$this->group_keys = '';
		$this->having_keys = '';
		$this->order_keys = '';
		$this->join = '';
		$this->join_on = '';
		return $this;
	}

	/**
	 * 增加`符号转义
	 * @param string $value
	 * @param string
	 */
	private function addEscape($value){
		if (is_array($value)) {
			return implode(',', $value);
		} else {
			return $value;
		}
	}

	
	/**
	 * prepare准备
	 * @return [type] [description]
	 */
	private function prepare($sql){
		$this->stmt = $this->mysql->prepare($sql);
		return $this;
	}

	/**
	 * 产生占位符
	 * @param  int $len 
	 * @return string
	 */
	private function createPlaceHolder(array $data) {
		$holder = '';
		foreach ($data as $value) {
			$holder .= ':'.$value.', ';
		}
		return rtrim(trim($holder), ',');
	}

	/**
	 * 产生绑定值
	 * @param  int $len 
	 * @return string
	 */
	private function createBandValue(array $data) {
		foreach ($data as $key => $value) {
			if (is_int($value)) {
				$this->band_values[":".$key] = array($value, PDO::PARAM_INT);
			} else {
				$this->band_values[":".$key] = array($value, PDO::PARAM_STR);
			}
		}
		return $this;
	}

	/**
	 * 数据绑定
	 *
	 * @return $this
	 */
	private function bind(){
		//循环绑定
		if ($this->band_values) {
			foreach ($this->band_values as $key => $value) {
				$this->stmt->bindValue($key, $value[0], $value[1]);
			}
		}
		return $this;
	}

	/**
	 * 执行stmt
	 * @return int 受影响的行数
	 */
	private function execute(){
		$this->stmt->execute();
		//打出错误信息
		if ($this->stmt->errorCode()!= '00000') {
			return $this->stmt->errorInfo();
		} else {
			return $this->stmt->rowCount();
		}
	}

	/**
	 * 插入数据/增加
	 * 
	 * @param $data
	 * @return int
	 */
	public function insert($data) {
		if(empty($data)) {
			return false;
		}
		$data_keys = array_keys($data);
		$sql = "INSERT INTO " . $this->addEscape($this->table) . '(' . $this->addEscape($data_keys) . ') VALUES (' . $this->createPlaceHolder($data_keys) . ')';
		$state = $this->prepare($sql)->createBandValue($data)->bind()->execute();
		$last_insert_id = $this->mysql->lastInsertId();
		if ($last_insert_id) {
			return $last_insert_id;
		} else {
			return $state;
		}
	}

	/**
	 * 删除
	 * @return int
	 */
	public function delete() {
		$sql = "DELETE FROM " . $this->addEscape($this->table) . $this->where_keys;
		return $this->prepare($sql)->bind()->execute();
	}

	/**
	 * 修改
	 * @return int
	 */
	public function update($data) {
		$update_keys = '';
		if (is_array($data)) {
			foreach ($data as $key => $value) {
				$update_keys[] = $this->addEscape($key) . " = :" . $key;
				if (is_int($value)) {
					$this->band_values[":".$key] = array($value, PDO::PARAM_INT);
				} else {
					$this->band_values[":".$key] = array($value, PDO::PARAM_STR);
				}
			}
			$update_keys = implode(", ", $update_keys);
		} else {
			$update_keys = $data;
		}

		$sql = "UPDATE " . $this->addEscape($this->table) . " SET " . $update_keys . $this->where_keys;
		return $this->prepare($sql)->bind()->execute();
	}

	/**
	 * where 条件
	 * @param  array|string $data 
	 * @return $this
	 */
	public function where($data = '', $releation = 'AND') {
		if (empty($data)) {
			return $this;
		}
		if (is_array($data)) {
			foreach ($data as $key => $value) {
				if (is_array($value)) {
					//去除多表查询中的. 不知道是不是PDO的BUG
					$key_new = str_replace('.', '', $key);
					$where_keys[] = $key . " " . $value[0] . " :" . $key_new;
					if (is_int($value[1])) {
						$this->band_values[":".$key_new] = array($value[1], PDO::PARAM_INT);
					} else {
						$this->band_values[":".$key_new] = array($value[1], PDO::PARAM_STR);
					}
				} else {
					//去除多表查询中的. 不知道是不是PDO的BUG
					$key_new = str_replace('.', '', $key);
					$where_keys[] = $key . " = :" . $key_new;
					if (is_int($value)) {
						$this->band_values[":".$key_new] = array($value, PDO::PARAM_INT);
					} else {
						$this->band_values[":".$key_new] = array($value, PDO::PARAM_STR);
					}
				}
			}
			$this->where_keys = ' WHERE ' . implode(' ' . strtoupper($releation) . ' ', $where_keys);
		} else {
			$this->where_keys = ' WHERE ' . $data;
		}
		return $this;
	}

	/**
	 * 查询/多条
	 * @param  string $field 
	 * @return array
	 */
	public function select($field = '*') {
		$sql = "SELECT " . $field . " FROM " . $this->addEscape($this->table) . $this->alise_table . $this->join_on . $this->where_keys . $this->group_keys . $this->having_keys . $this->order_keys . $this->limit_keys;
		$row_count =  $this->prepare($sql)->bind()->execute();
		if ($row_count) {
			if (is_array($row_count) && $row_count[2]) {
				Error::write($row_count[2]);
			} else {
				//单个查询直接给出组合好的一维数组
				if (strpos($field, ',') === false && $field != '*') {
					return $this->stmt->fetchAll(PDO::FETCH_COLUMN);
				} else {
					return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
				}
			}
		} else{
			return array();
		}
	}

	/**
	 * 对查询的结果按照第一个值分组，不是group的sql语句。
	 *
	 * @param string $field
	 * @return array
	 */
	public function groupSelect($field){
		//必须查询条件为2个以及2个以上。
		if (strpos($field, ',') === false || $field == '*') {
			Error::write('查询结果分组显示，查询条件必须要2个以上，且不能有*');
		}
		$sql = "SELECT " . $field . " FROM " . $this->addEscape($this->table). $this->alise_table . $this->join_on . $this->where_keys . $this->group_keys . $this->having_keys . $this->order_keys . $this->limit_keys;
		$row_count =  $this->prepare($sql)->bind()->execute();
		if ($row_count) {
			if (is_array($row_count) && $row_count[2]) {
				Error::write($row_count[2]);
			} else {
				preg_match_all('/\,/', $field, $match);
				if (count($match[0]) == 1) {
					return $this->stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
				} else {
					return $this->stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);
				}
			}
		} else {
			return array();
		}
	}

	/**
	 * 查询/一条
	 * @param  string $field 
	 * @return array
	 */
	public function find($field = '*') {
		$sql = "SELECT " . $field . " FROM " . $this->addEscape($this->table). $this->alise_table . $this->join_on. $this->where_keys . $this->group_keys . $this->having_keys . $this->order_keys . $this->limit_keys;
		$row_count =  $this->prepare($sql)->bind()->execute();
		if ($row_count) {
			//出错
			if (is_array($row_count) && $row_count[2]) {
				Error::write($row_count[2]);
			} else {
				$this->stmt->setFetchMode(PDO::FETCH_ASSOC);
				return $this->stmt->fetch();
			}
		} else{
			return array();
		}
	}

	/**
	 * group 分组
	 *
	 * @param  string $field
	 * @return $this
	 */
	public function group($field = '') {
		if (empty($field)) {
			return $this;
		}
		$this->group_keys = ' GROUP BY '. $field;
		return $this;
	}

	/**
	 * having 字句，分组后筛选
	 * @param  string $field 
	 * @return $this
	 */
	public function having($field = '') {
		if (empty($field)) {
			return $this;
		}
		$this->having_keys = ' HAVING '. $field;
		return $this;
	}

	/**
	 * 排序order by
	 * @param  string $field 
	 * @return $this
	 */
	public function order($field = '', $order_desc = 'DESC') {
		if (empty($field)) {
			return $this;
		}
		$this->order_keys = ' ORDER BY '. $field . " " . strtoupper($order_desc);
		return $this;
	}

	/**
	 * limit分页
	 * @param  int $start
	 * @param  int $end
	 * @return $this
	 */
	public function limit($start = '', $end = '') {
		if (empty($start)) {
			return $this;
		}
		if ($end) {
			$limit = " LIMIT {$start}, {$end}";
		} else {
			$limit = " LIMIT {$start}";
		}
		$this->limit_keys = $limit;
		return $this;
	}

		
	/**
	 * [join description]
	 * @param  string $join_table_name
	 * @param  string $join_type
	 * @param  string $alise_join_table_name
	 * @return $this
	 */
	public function join($join_table_name = '', $join_type = 'INNER', $alise_join_table_name = '') {
		if (empty($join_table_name)) {
			return $this;
		}
		$this->join = '';
		$this->join = " " . strtoupper($join_type) . ' JOIN ' . $join_table_name; 
		if ($alise_join_table_name) {
			$this->join .= ' AS ' . $alise_join_table_name;
		}
		return $this;
	}

	/**
	 * join on 的条件
	 * @param  string $r
	 * @return $this
	 */
	public function on($r) {
		if(empty($r)){
			Error::write('join on的条件错误');
		}
		if (!$this->join) {
			Error::write('请先进行join操作');
		}
		$this->join_on .= $this->join . ' ON ' . $r;
		return $this;
	}

	
	
	/**
	 * 返回执行的sql
	 * @return string
	 */
	public function getLastSql() {
		if ($this->band_values) {
			$sql_string = $this->stmt->queryString;
			$sql_value = '';
			foreach ($this->band_values as $key => $value) {
				if ($value[1] == 2) {
					$sql_value = "'" . $value[0] . "'";
				}
				$sql_string = str_replace($key, $sql_value, $sql_string);
			}
			return $sql_string;
		} else {
			return $this->stmt->queryString;
		}
	}

	public function getTable() {
		return $this->table;
	}

	public function __call($method, $arguments) {
		return call_user_func_array(array($this->mysql, $method), $arguments);
	}
}