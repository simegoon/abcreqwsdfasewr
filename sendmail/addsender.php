<?php

set_time_limit(0);
date_default_timezone_set('PRC');
header("Content-type:text/html;charset=utf-8");


include("config.php");

include(COREPATH."db/pdo_sql_api.php");
include(COREPATH."lock.php");

$dbApi = new CPdosqlApi();
$dbApi->debug = true;


if (!isset($_GET["from"])) {
 	die("Param error!");
}
if(!isset($_GET["from_name"]))
	$_GET["from_name"]= "周静媛";
if(!isset($_GET["ssl"]))
	$_GET["ssl"]= "1";
if(!isset($_GET["passwd"]))
	$_GET["passwd"]= "19840922xy";
if(!isset($_GET["host"]))
	$_GET["host"]= preg_replace("/^.*?@/","smtp.",$_GET["from"]);
$recode = array( "from" => $_GET["from"],
       "from_name" => $_GET["from_name"],
       "host" => $_GET["host"],
       "ssl" => $_GET["ssl"],
       "passwd" => $_GET["passwd"],
      );
$sendt = $dbApi->insert("sendf",$recode);
var_dump($sendt );
