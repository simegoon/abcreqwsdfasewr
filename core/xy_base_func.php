<?php

function file_get_contents_utf8($fn) { 
	$ctx = stream_context_create(array( 
			'https' => array(
				'proxy' => 'tcp://128.0.0.1:8080', ) 
			) 
		);

    $result = @file_get_contents($fn,false,$ctx); 
    $encode_arr = array('UTF-8','ASCII','GBK','GB2312','BIG5');  
    $encoded = mb_detect_encoding($result, $encode_arr);  
    return mb_convert_encoding($result, "utf-8",$encoded); 
} 


?>
