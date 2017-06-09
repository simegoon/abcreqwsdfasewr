<?php
/****
本地数据库操作类
****/

if (!defined("DB_TYPE")) {
 	define("DB_TYPE","MYSQL");
}

if (DB_TYPE == 'PDO') {
	include_once("pdo_sql_base_imp.php");
}
else if (DB_TYPE == 'SQLITE') {
	include_once("pdo_sql_base_sqlite_imp.php");
}
else {
	include_once("pdo_sql_base_mysqli_imp.php");
}

?>
