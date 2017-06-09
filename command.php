<?php

set_time_limit(0);
date_default_timezone_set('PRC');

$lock = "lock.lock";
if(isset($_GET["unlock"])){
	if (file_exists($lock))
        unlink($lock);
}
if(isset($_GET["lock"])){
	if (!file_exists($lock))
        touch($lock);
}
if(file_exists($lock) ){
	//维护状态；
	echo "maintainace";
	exit();
}

if(isset($_GET["install"])){
	if($_GET["install"] == "")
		$_GET["install"] = "install.sql";
	echo "begin install system...</br>";
	install_sql($_GET["install"]);
	echo "install system finshed.</br>";
	exit();
}

if(isset($_GET["install_demo"])){
	if($_GET["install_demo"] == "")
		$_GET["install_demo"] = "install_demo.sql";
	echo "begin install system demo data...</br>";
	install_sql($_GET["install_demo"]);
	echo "install system demo data finshed.</br>";
	exit();
}

function install_sql($filename)
{
	$lines = file($filename);
	if (is_file("../core/db/pdo_sql_api.php")) {
	 	include("../core/db/pdo_sql_api.php");
	}
	else if (is_file("core/db/pdo_sql_api.php")) {
	 	include("core/db/pdo_sql_api.php");
	}
	
	$dbApi = new CPdosqlApi();
	$dbApi->debug = true;
	if ($lines) {
		$sql = '';

		foreach($lines as $line) {
			if ($line && (substr($line, 0, 2) != '--') && (substr($line, 0, 1) != '#')) {
				$sql .= $line;
				if (preg_match('/;\s*$/', $line)) {
					if( $dbApi->query($sql))
					{
						echo "执行sql【成功】！</br>\r\n$sql</br>\r\n";
					}
					else{
						echo "执行sql【失败】！</br>\r\n$sql</br>\r\n";
					}
					$sql = '';
				}
			}
		}
	}
	exit();
}
?>
