<?php
/****
将本地数据库操作类封装成远程数据库，供远程程序调用
原来：通过url获取sql语句并通过本地数据库操作类获取查询结果，通过json编码后返回给远端
****/
if(!isset($_GET["sql"])){
	echo json_encode(false);
	exit(1);
}

include_once("pdo_sql_api.php");
if(!isset($_GET["json"])){
	echo json_encode(CPdosqlApi::$pdo_sql->query($_GET["sql"]));
}
else
	var_dump($pdo_sql->query($_GET["sql"]));
?>
