<?php
/****
���������ݿ�������װ��Զ�����ݿ⣬��Զ�̳������
ԭ����ͨ��url��ȡsql��䲢ͨ���������ݿ�������ȡ��ѯ�����ͨ��json����󷵻ظ�Զ��
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
