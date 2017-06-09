<?php
/****
本地数据库操作类
****/

class CPdosqlBase
{
	private $conn;
	function __construct($sqlite_name=SQLITE_NAME)
	{
print("line:".__FILE__."line:".__LINE__."</br>");
		$this->conn = new SQLite3($sqlite_name);
print("line:".__FILE__."line:".__LINE__."</br>");
		/*
		mysqli_query($this->conn,"SET NAMES 'utf8'");
		mysqli_query($this->conn,"SET CHARACTER SET utf8");
		mysqli_query($this->conn,"SET CHARACTER_SET_CONNECTION=utf8");
		mysqli_query($this->conn,"SET SQL_MODE = ''");
		*/
	}
	function conn()
	{
		return $this->conn;
	}
	function exec($sql)
	{
		$this->conn->query("select * from adf;");
		print("+++".$this->conn->lastErrorCode()."---");
		print("+++".$this->conn->lastErrorCode()."---");
		print("+++".$this->conn->lastErrorCode()."---");
		print("+++".$this->conn->lastErrorCode()."---");
		// 执行sql查询
		$result = $this->conn->exec($sql);
		// 获取查询结果
		$ret_list=array();
		$ret_list["recode"]=array();
		$ret_list["result"] = true;
		if(True===$result){
			$ret_list["rowCount"] = $this->conn->changes();
		}
		else if(False === $result)
		{
			$ret_list=$result;
			$ret_list["result"] = false;
		}
		return $ret_list;
	}
	function query($sql)
	{
		// 执行sql查询
		$result = $this->conn->query($sql);
		// 获取查询结果
		$ret_list=array();
		$ret_list["recode"]=array();
		$ret_list["result"] = true;
		
		if(True===$result){
			$ret_list["rowCount"] = $this->conn->changes();
		}
		else if(False === $result)
		{
			$ret_list=$result;
			$ret_list["result"] = false;
		}
		else{
			$ret_list["rowCount"] = $this->conn->changes();
			while($row=$result->fetchArray(SQLITE3_ASSOC)){
				//echo $row->ID.'</br>';
				//echo $row->user_login.'</br>';
				foreach($row as $key => $value) {
				    $ret[$key]=$value;
				}
				$ret_list["recode"][]=$ret;
				unset($ret);
			}
		}
		
		return $ret_list;
	}
};

?>
