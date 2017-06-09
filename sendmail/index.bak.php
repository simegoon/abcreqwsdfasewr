<?php
die("maintainace");
set_time_limit(0);
date_default_timezone_set('PRC');
header("Content-type:text/html;charset=utf-8");


include("config.php");

include(COREPATH."db/pdo_sql_api.php");
include(COREPATH."lock.php");
include(COREPATH."smtp.php");
include(COREPATH."sematext.log.php");

$log = new SematextLog("831a6914-98a7-4915-87a5-25b12cedaa9f");

$dbApi = new CPdosqlApi();
$dbApi->debug = true;

// 每个账户一小时发一次，防止进入垃圾名单
// 调试模式,使用不带提交的纯select
if(isset($_GET["to"])){
	if (isset($_GET["from"])) {
		$sendf = $dbApi->select("sendf","[from] = '".$_GET["from"]."' limit 1");
	}
	else{
		$sendf = $dbApi->select("sendf","1=1 limit 1");
	}
	$sendf = $sendf[0];
}
else{
	$sendf = $dbApi->selectOne("sendf",false,"",3600);
}
if(!$sendf){
	die("No sender!");
}


$sendt = $dbApi->selectOne("sendt",1,"",86400);
if(!$sendt){
	die("No receiver!");
}
print_r($sendt);




print("------------------------------------------<br>");
$send_contact_count = 0;
$send_contact = $dbApi->selectPollingOne("send_contact",$send_content_count);

//新建SMTP实例
$smtp = new SMTP("127.0.0.1",true);
$smtp->host($sendf["host"],$sendf["ssl"]=="1"?"ssl":"");
$smtp->auth($sendf["from"],$sendf["passwd"]);
$smtp->from($sendf["from"],$sendf["from_name"]);
// 调试模式
if(isset($_GET["to"]))
	$smtp->to($_GET["to"],$sendt["to_name"]);
else {
	$smtp->to($sendt["to"],$sendt["to_name"]);
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
	$log->trace("sender","send email success!sender:".a2s($sendf).";receiver:".a2s($sendt).";");
}
else{
	echo "</br>邮件发送失败</br>";
	$log->error("sender","send email error!sender:".a2s($sendf).";receiver:".a2s($sendt).";");
}
// 非调试模式
if(!isset($_GET["to"])){
	$dbApi->commitRecode("sendt",$sendt);
	$dbApi->commitRecode("send_contact",$send_contact);
}
	
die("OK");

	
