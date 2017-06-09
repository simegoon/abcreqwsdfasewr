<?php
print("line:".__FILE__."line:".__LINE__."</br>");
set_time_limit(0);
ini_set("display_errors", "On");
error_reporting(E_ALL | E_STRICT);
print("line:".__FILE__."line:".__LINE__."</br>");
date_default_timezone_set('PRC');
print("line:".__FILE__."line:".__LINE__."</br>");
header("Content-type:text/html;charset=utf-8");

include("config.php");
print("line:".__FILE__."line:".__LINE__."</br>");
include(COREPATH."db/pdo_sql_api.php");
print("line:".__FILE__."line:".__LINE__."</br>");
include(COREPATH."db/firebase.php");
print("line:".__FILE__."line:".__LINE__."</br>");
include(COREPATH."smtp.php");
print("line:".__FILE__."line:".__LINE__."</br>");
include(COREPATH."sematext.log.php");
print("line:".__FILE__."line:".__LINE__."</br>");

$log = new SematextLog("831a6914-98a7-4915-87a5-25b12cedaa9f");
print("line:".__FILE__."line:".__LINE__."</br>");
$db = new FirebaseHigh("https://mygood-ea08e.firebaseio.com/",true);
print("line:".__FILE__."line:".__LINE__."</br>");

$dbApi = new CPdosqlApi();
print("line:".__FILE__."line:".__LINE__."</br>");
$dbApi->debug = true;

print("line:".__FILE__."line:".__LINE__."</br>");
// 每个账户一小时发一次，防止进入垃圾名单
$send = new SendfTable();

print("line:".__FILE__."line:".__LINE__."</br>");
if(isset($_GET["to"])){
	if(!$db->getOne($send,false,1))
	{
		die("No sender!");
	}
}
else{
	if(!$db->getOne($send))
	{
		die("No sender!");
	}
}
print("line:".__FILE__."line:".__LINE__."</br>");
var_dump($send);

print("line:".__FILE__."line:".__LINE__."</br>");
$to = new SendtTable();
if(!$db->getOne($to))
{
	die("No receiver!");
}
if(!isset($_GET["to"])){
	$db->LockRecode($to);
}
var_dump($to);


print("------------------------------------------<br>");
$send_contact = $dbApi->query("SELECT * FROM send_contact ORDER BY RANDOM() limit 1")["recode"][0];

//新建SMTP实例
$smtp = new SMTP("127.0.0.1",true);
$smtp->host($send->get("host"),$send->get("ssl")=="1"?"ssl":"");
$smtp->auth($send->get("from"),$send->get("passwd"));
$smtp->from($send->get("from"),$send->get("from_name"));
// 调试模式
if(isset($_GET["to"]))
	$smtp->to($_GET["to"],$to->get("to_name"));
else {
	$smtp->to($to->get("to"),$to->get("to_name"));
}
$smtp->subject("感悟分享：".$send_contact["title"]);

$contents = '<html><style type="text/css">
p{text-indent:2em}
.big{font-weight:bold}
</style><p></p><p class="big">本˙公˙司˙有˙增˙值˙税、普˙通˙正˙规˙发˙票˙向˙外˙代˙开。可˙国˙税˙局˙验˙证˙后˙付˙款。电˙话:139-1597-8767</p>
<p></br></p>'.$send_contact["content"].'</html>';

$smtp->body($contents);

$result = $smtp->send();

var_dump($result);

function a2s($array){
    $string = [];
    foreach ($array as $key=> $value){
    	if (is_array($value))
        	$string[] = '"'.$key.'":"'.a2s($value).'"';
    	else
        	$string[] = '"'.$key.'":"'.$value.'"';
    }
    return "{".implode(',',$string)."}";
}
if($result){
	echo "</br>邮件已经发送</br>";
	$log->trace("sender","send email success!sender:".a2s($send->data()).";receiver:".a2s($to->data()).";");
}
else{
	echo "</br>邮件发送失败</br>";
	$log->error("sender","send email error!sender:".a2s($send->data()).";receiver:".a2s($to->data()).";");
}
// 非调试模式
if(!isset($_GET["to"])){
	$db->UpdateTimes($send);
	$db->ComitRecode($to);
	$dbApi->commitRecode("send_contact",$send_contact);
}
	
die("OK");

	
