<?php
/****
本地数据库操作类
****/

if (!defined("DB_TYPE")) {
 	define("DB_TYPE","MYSQL");
}

if (DB_TYPE == 'PDO') {
print("line:".__FILE__."line:".__LINE__."</br>");
	include_once("pdo_sql_base_imp.php");
print("line:".__FILE__."line:".__LINE__."</br>");
}
else if (DB_TYPE == 'SQLITE') {
print("line:".__FILE__."line:".__LINE__."</br>");
	include_once("pdo_sql_base_sqlite_imp.php");
print("line:".__FILE__."line:".__LINE__."</br>");
}
else {
print("line:".__FILE__."line:".__LINE__."</br>");
	include_once("pdo_sql_base_mysqli_imp.php");
print("line:".__FILE__."line:".__LINE__."</br>");
}

?>
