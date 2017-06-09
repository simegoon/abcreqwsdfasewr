<?php
/****
程序使用的数据操作类，屏蔽本地数据库和远程数据库操作类的差异，仅需指定数据库所在url即可
同时支持一些具化的接口，如insert、update、delete，这些接口会自动去拼接sql语句，并调用query方法
****/

include_once("pdo_sql_base.php");

class CPdosqlApi
{
	// 仅仅指域名部分
	private $url;
	// path 
	private $path = "";
	private $pdo_sql;
	public $debug = false;
	/*
	参 数:  url, 如果是本地数据库，不需要设置。远程数据库设置为服务器的url地址
			path, 但文件没有放在根目录时，设置为子目录路径
	*/
	function __construct($url=False,$path="")
	{
		if($url) // 远程数据库
			$this->url = $url;
		else{ // 本地数据库
			$this->url = self::get_http_host();
			$this->pdo_sql = new CPdosqlBase();
		}
		if($path != "" && strpos($path, "/") != 0)
			$this->path = "/".$path;
		else
			$this->path = $path;
	}
	/****
	核心基础方法，判断远程调用还是本次执行
	****/
	function query($sql)
	{
		if($this->debug)
			var_dump($sql."\r\n");
	    // 本地数据库
	    if( self::get_http_host() == $this->url){
			$ret = $this->pdo_sql->query($sql);
	    }
	    else // 远程数据库，通过get请求的方式，返回结果
			$ret = json_decode(file_get_contents(rawurlencode("http://".$this->url.$this->path."/pdo_sql.php?sql=".$sql)));
		if($this->debug)
			var_dump($ret);
		return $ret;
	}
	function select($table,$where = false)
	{
		if ($where) {
		 	$ret = $this->query("SELECT * FROM $table where $where;");
		}else {
		 	$ret = $this->query("SELECT * FROM $table;");
		}
		if($ret["result"] && count($ret["recode"])>0){
			return $ret["recode"];
		}
		return false;
	}
	/****
	获取一条记录
	****/
	function selectOne($table,$query_times=false,$where = "",$before=86400)
	{
		if ($where== "") {
			$where = "1=1";
		}
		$after_time = date('Y-m-d H:i:s',time()-$before);
		if ($query_times){
			$where .= " and (QUERY_TIMES=$query_times or (QUERY_TIMES=-$query_times and TIMES<=datetime('$after_time')))";
		}
		else{
			$where .= " and TIMES<=datetime('$after_time')";
		}
		$ret = $this->query("SELECT * FROM $table where $where limit 1;");
		
		if($ret["result"] && count($ret["recode"])>0){
			$this->lockRecode($table,$ret["recode"][0],$query_times);
			return $ret["recode"][0];
		}
		return false;
	}
	function getPollingQueryTimes($table,&$query_times,$where,$before)
	{
		$ret = $this->query("SELECT max(QUERY_TIMES) as qmax, min(QUERY_TIMES) as qmin FROM $table where $where and QUERY_TIMES >0 limit 1;");
		$query_times = min((int)$ret["recode"][0]["qmin"],(int)$ret["recode"][0]["qmax"]);
	}
	function selectPollingOne($table,&$query_times,$where = "",$before=86400)
	{
		if ($where== "") {
			$where = "1=1";
		}
		if ($query_times<=0) {
			$this->getPollingQueryTimes($table,$query_times,$where,$before);
		}
		$ret = $this->_selectPollingOne($table,$query_times,$where,$before);
		if ($ret == false) {
		 	$this->getPollingQueryTimes($table,$query_times,$where,$before);
		 	return $this->_selectPollingOne($table,$query_times,$where,$before);
		}
		return $ret;
	}
	function _selectPollingOne($table,$query_times,$where,$before)
	{
		$after_time = date('Y-m-d H:i:s',time()-$before);
		$where .= " and (QUERY_TIMES=$query_times or (QUERY_TIMES<0 and QUERY_TIMES>=-$query_times and TIMES<=datetime('$after_time')))";

		$ret = $this->query("SELECT * FROM $table where $where limit 1;");
		
		if($ret["result"] && count($ret["recode"])>0){
			$this->lockRecode($table,$ret["recode"][0],$query_times);
			return $ret["recode"][0];
		}
		return false;
	}
	function lockRecode($table,$recode,$query_times)
	{
		if($query_times)
		$recode['QUERY_TIMES'] = -abs((int)$recode['QUERY_TIMES']);
		$recode['TIMES'] = date('Y-m-d H:i:s');
		$this->update($table,$recode,array("ID"),array("QUERY_TIMES","TIMES"));
	}
	function commitRecode($table,$recode)
	{
		$recode['QUERY_TIMES'] = 1+abs((int)$recode['QUERY_TIMES']);
		$recode['TIMES'] = date('Y-m-d H:i:s');
		$this->update($table,$recode,array("ID"),array("QUERY_TIMES","TIMES"));
	}
	/****
	Param : $columus 用来约束插入哪些值，可以不传
	****/
	function insert($table,$data,$columus=false)
	{
		$sql = "INSERT OR IGNORE INTO {{{table_name}}} ( {{{columns_name}}} ) VALUES ({{{columns_value}}});";
		
		if($columus){
			foreach($columus as $key)
			{
				if(isset($data[$key]))
				{
					$columns_name[]="[".$key."]";
					$columns_value[]="'".$this->quote($data[$key])."'";
				}
			}
		}
		else{
			foreach($data as $key => $value)
			{
				$columns_name[]="[".$key."]";
				$columns_value[]="'".$this->quote($value)."'";
			}
		}
		$sql = str_replace("{{{columns_name}}}", implode(", ",$columns_name), $sql);
		$sql = str_replace("{{{columns_value}}}", implode(", ",$columns_value), $sql);
		$sql = str_replace("{{{table_name}}}", $table, $sql);
		return $this->query($sql);
	}
	/****
	Param : $columus 用来约束插入哪些值，可以不传
	****/
	function update($table,$data,$condition_columus=array("ID"),$columus=false)
	{
		$sql = "UPDATE {{{table_name}}} SET {{{columns_value}}} where {{{where_value}}};";
		$where_value=array();
		foreach($condition_columus as $key)
		{
			if(isset($data[$key]))
			{
				$where_value[]="[$key] = '".$this->quote($data[$key])."'";
				unset($data[$key]);
				if($columus)
					$this->array_remove_by_value($columus,$key);
			}
		}
		if($columus){
			foreach($columus as $key)
			{
				if(isset($data[$key]))
				{
						$columns_value[]="[$key] = '".$this->quote($data[$key])."'";
				}
			}
		}
		else{
			foreach($data as $key => $value)
			{
					$columns_value[]="[$key] = '".$this->quote($value)."'";
			}
		}
		$sql = str_replace("{{{where_value}}}", implode(", ",$where_value), $sql);
		$sql = str_replace("{{{columns_value}}}", implode(", ",$columns_value), $sql);
		$sql = str_replace("{{{table_name}}}", $table, $sql);
		
		return $this->query($sql);
	}
	/****
	Param : $columus 用来约束插入哪些值，可以不传
	****/
	function del($table,$data,$condition_columus=false)
	{
		$sql = "DELETE {{{table_name}}} where {{{where_value}}};";

		if($condition_columus){
			foreach($condition_columus as $key)
			{
				if(isset($data[$key]))
				{
					$where_value[]=$key." = ".$this->quote($data[$key]);
				}
			}
		}
		else{
			foreach($data as $key => $value)
			{
				$where_value[]=$key." = ".$this->quote($value);
			}
		}
		$sql = str_replace("{{{where_value}}}", implode(", ",$where_value), $sql);
		$sql = str_replace("{{{table_name}}}", $table, $sql);
		
		return $this->query($sql);
	}
	static function get_http_host()
	{
		$t_host = "";
		if ( isset( $_SERVER['HTTP_X_FORWARDED_HOST'] ) ) { // Support ProxyPass
	        $t_hosts = explode( ',', $_SERVER['HTTP_X_FORWARDED_HOST'] );
	        $t_host = $t_hosts[0];
	    } else if ( isset( $_SERVER['HTTP_HOST'] ) ) {
	        $t_host = $_SERVER['HTTP_HOST'];
	    } else if ( isset( $_SERVER['SERVER_NAME'] ) ) {
	        $t_host = $_SERVER['SERVER_NAME'] . $t_port;
	    } else if ( isset( $_SERVER['SERVER_ADDR'] ) ) {
	        $t_host = $_SERVER['SERVER_ADDR'] . $t_port;
	    }
	    return $t_host;
	}
	static function array_remove_by_value(&$arr, $var)
	{
		foreach ($arr as $key => $value) {
			$value = trim($value);
			if ($value == $var) {
				unset($arr[$key]);
			}
		}
	}
	static function quote($var)
	{
		return SQLite3::escapeString($var);
	}
};

?>
