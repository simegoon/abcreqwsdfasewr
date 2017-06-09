<?php
/****
本地数据库操作类
****/

class CPdosqlBase
{
	private $conn;
	function __construct()
	{
		$this->conn = new mysqli(MYSQL_SERVER_NAME, MYSQL_USERNAME,MYSQL_PASSWORD);
		$this->conn->select_db(MYSQL_DATABASE);
		mysqli_query($this->conn,"SET NAMES 'utf8'");
		mysqli_query($this->conn,"SET CHARACTER SET utf8");
		mysqli_query($this->conn,"SET CHARACTER_SET_CONNECTION=utf8");
		mysqli_query($this->conn,"SET SQL_MODE = ''");
	}
	function conn()
	{
		return $this->conn;
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
			$ret_list["rowCount"] = mysqli_affected_rows ( $this->conn );
		}
		else if(False === $result)
		{
			$ret_list=$result;
			$ret_list["result"] = false;
		}
		else{
			while($row=$result->fetch_object()){
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
