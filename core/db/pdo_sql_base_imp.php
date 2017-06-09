<?php
/****
本地数据库操作类
****/

class CPdosqlBase
{
	public $dbh;
	function __construct()
	{
		$this->dbh = new PDO('mysql:host='.MYSQL_SERVER_NAME.';dbname='.MYSQL_DATABASE.'', MYSQL_USERNAME, MYSQL_PASSWORD);
		//$this->dbh = new PDO('sqlite:foo.db');
		$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->dbh->exec('set names utf8');
		$this->dbh->exec('SET CHARACTER SET utf8');
		$this->dbh->exec('SET CHARACTER_SET_CONNECTION=utf8');
	}
	function conn()
	{
		return $this->dbh;
	}
	function query($sql)
	{
		// 执行sql查询
		$stmt = $this->dbh->prepare($sql);
		try{
			$result = $stmt->execute();
			// 获取查询结果
			$ret_list=array();
			$ret_list["result"] = $result;
			$ret_list["rowCount"] = $stmt->rowCount();
			if($result == false)
				$ret_list["errorInfo"] = $stmt->errorInfo();
			if ($stmt->columnCount()>0)
				$ret_list["recode"]=$stmt->fetchAll();
		}
		catch(PDOException $e){
			$ret_list["result"] = false;
			$ret_list["exception"]=$e->getMessage();
		}
		$stmt->closeCursor();
		return $ret_list;
	}
	function __destruct(){
    // ...
	}
};

?>
