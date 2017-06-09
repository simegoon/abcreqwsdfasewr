<?php
class SematextLog {
    // connection
    protected $token;
    protected $curl;
    protected $debug;
    public function __construct($token="",$debug=false)
    {
        $this->token = $token;
        $this->debug = $debug;
        $this->curl=curl_init();
	    curl_setopt($this->curl,CURLOPT_RETURNTRANSFER,true); 
		curl_setopt($this->curl,CURLOPT_USERAGENT,"Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322; .NET CLR 2.0.50727)");
		curl_setopt($this->curl, CURLOPT_POST, 1 );
		curl_setopt($this->curl, CURLOPT_HEADER, 0 );
    }
	function __destruct(){
		curl_close($this->curl);
	}
    public function logging($type,$key,$value)
    {
		$data= array(
            $key => $value
        );
		curl_setopt($this->curl, CURLOPT_URL,"http://logsene-search.sematext.com/".$this->token."/".$type."/");
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($data));
	    $ret = json_decode(curl_exec($this->curl),true);
        if($this->debug){
        	var_dump("http://logsene-search.sematext.com/".$this->token."/".$type."/");
        	var_dump(json_encode($data));
        	var_dump($ret);
        }
		if (isset($ret["error"]))
			return false;
		return true;
    }
    public function trace($key,$value)
    {
    	return $this->logging(__FUNCTION__,$key,$value);
    }
    public function info($key,$value)
    {
    	return $this->logging(__FUNCTION__,$key,$value);
    }
    public function debug($key,$value)
    {
    	return $this->logging(__FUNCTION__,$key,$value);
    }
    public function error($key,$value)
    {
    	return $this->logging(__FUNCTION__,$key,$value);
    }
    public function warning($key,$value)
    {
    	return $this->logging(__FUNCTION__,$key,$value);
    }

}