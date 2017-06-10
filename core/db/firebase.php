<?php
class Firebase {
    // connection
    protected $root_url;
    protected $curl;
    protected $debug;
    private $id;
    private $isLock;
    public function __construct($url,$debug=false)
    {
        $this->root_url = $url;
        $this->debug = $debug;
        $this->id = time()%10000*1000+rand(1,1000);
        $this->isLock = false;
    }
    public function init(){
        $this->curl=curl_init();
	    curl_setopt($this->curl,CURLOPT_RETURNTRANSFER,true); 
		curl_setopt($this->curl, CURLOPT_HEADER, 0 );
		curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false); 
    }
    public function close(){
		curl_close($this->curl);
	}
	function __destruct(){
	}
	// 全局分布式互斥锁
	/*******************************
      "lock":{
        ".validate": "(data.child('owner').val() === newData.child('owner').val()
        || (data.child('time').val() + data.child('outtime').val()<now)
        || (data.child('isLock').val() === 0 && newData.child('isLock').val() === 1))
        && newData.hasChildren(['owner', 'time' , 'outtime','isLock'])
        && newData.child('time').isNumber()
        && newData.child('outtime').isNumber()
        && newData.child('isLock').isNumber()
        && (newData.child('isLock').val() === 0 || newData.child('isLock').val() === 1 )"
      }
    *******************************/
	public function lock()
	{
		$data = array("outtime"=>5*60*1000,
			"time"=>array(".sv"=> "timestamp"),
			"owner"=>$this->id,
			"isLock"=>1
		);
		$debug = $this->debug;
		$this->debug = false;
		while(true){
			$ret = $this->overwrite("test",$data,"lock");
			if($ret["code"] == 200 ){
				if($debug){
					var_dump($ret["html"]);
				}
				break;
				
			}
			sleep(1);
		}
		$this->debug = $debug;
        $this->isLock = true;
	}
	public function unlock()
	{
		if(!$this->isLock)
		return true;
		$data = array("outtime"=>5*60*1000,
			"time"=>array(".sv"=> "timestamp"),
			"owner"=>$this->id,
			"isLock"=>0
		);
		$ret = $this->overwrite("test",$data,"lock");
		if($ret["code"] == 200 )
        	$this->isLock = false;
			return true;
		return false;
	}
	public function updaterule()
	{
		// TODO 更新数据库规则
	}
	// 插入一条新记录，不会覆盖或修改原先存在的记录
	// $key随机产生
	public function insert($table,$recode)
	{
		$url = $this->root_url . $table . ".json";
		$req_type = "POST";
		return $this->post($req_type,$url,$recode);
	}
	// 数据库中不存在，会新建，存在会覆盖
	public function overwrite($table,$recode,$key){
		$url = $this->root_url . $table . "/" . $key . ".json";
		$ret = $this->post("PUT",$url,$recode);
		return $ret;
	}
	public function update($table,$recode,$key=""){
		if ($key != ""){
			$key = "/".$key;
		}
		$url = $this->root_url . $table . $key . ".json";
		$ret = $this->post("PATCH",$url,$recode);
		return $ret;
	}
	public function select($table,$where=""){
		$url = $this->root_url . $table . ".json?".urldecode($where);
		$ret = $this->get($url);
		return $ret;
	}
	public function del($table,$key){
		$url = $this->root_url . $table . "/" . $key . ".json";
		$ret = $this->post("DELETE",$url,array());
		return $ret;
	}
	private function post($method,$url,$data){
		if ($this->debug) {
			print("/*******************************************/</br>");
		 	var_dump(str_replace($this->root_url,"",$url));
		 	var_dump($data);
		}
		$ret = array();
		$this->init();
		curl_setopt($this->curl, CURLOPT_HTTPHEADER,array("X-HTTP-Method-Override: $method"));//设置HTTP头信息
		curl_setopt($this->curl, CURLOPT_URL,$url);
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($data));
		curl_setopt($this->curl, CURLOPT_POST, 1 );
	    $ret["html"] = json_decode(curl_exec($this->curl));
	    $ret["code"] = curl_getinfo($this->curl,CURLINFO_HTTP_CODE); 
		if ($this->debug) {
		 	var_dump($ret);
			print("/*******************************************/</br>");
		}
		$this->close();
	    return $ret;
	} 
	private function get($url){
		if ($this->debug) {
		 	var_dump(str_replace($this->root_url,"",$url));
		}
		$ret = array();
		$this->init();
		curl_setopt($this->curl, CURLOPT_URL,$url);
		curl_setopt($this->curl, CURLOPT_POST, 0 );
	    $ret["html"] = json_decode(curl_exec($this->curl),true);
	    $ret["code"] = curl_getinfo($this->curl,CURLINFO_HTTP_CODE); 
		if ($this->debug) {
		 	var_dump($ret);
		}
		$this->close();
	    return $ret;
	} 
}



class TableRecode
{
	private $_table;
	private $_data;
	private $_unique;
    public function __construct($table,$unique)
    {
    	$this->_table = $table;
    	$this->_unique = $unique;
    }
	public function set($key,$value)
	{
		$this->_data[$key] = $value;
	}
	public function setArray($data)
	{
		foreach ($data as $key=>$value) {
			$this->_data[$key] = $value; 
		}
	}
	public function get($key)
	{
		return $this->_data[$key];
	}
	public function table()
	{
		return $this->_table;
	}
	public function data()
	{
		if(!in_array("querys",array_keys($this->_data))){
			$this->_data["querys"] = 1;
		}
		if(!in_array("times",array_keys($this->_data))){
			$this->_data["times"] = 0;
		}
		$this->_data["query_times"] = $this->_data["querys"]."_".$this->_data["times"];
		return $this->_data;
	}
	public function unique()
	{
		return md5($this->_data[$this->_unique]);
	}
}

class SendfTable extends TableRecode
{
    public function __construct()
    {
    	parent::__construct("test/sendf","from");
    }
}
class SendtTable extends TableRecode
{
    public function __construct()
    {
    	parent::__construct("test/sendt","to");
    }
}

class FirebaseHigh extends Firebase
{
    public function __construct($url,$debug=false)
    {
    	parent::__construct($url,$debug);
    }
    private function byQuerys($table,$querys){
		$param = array( "equalTo" => $querys,
	       "limitToFirst"=>1,
	       "orderBy"=>'"querys"');
		return $this->select($table,http_build_query($param));
    }
    private function byTimes($table,$times){
		$param = array("orderBy"=>'"times"',
		 "endAt" => $times,
	       "limitToFirst"=>1,
	       );
		return $this->select($table,http_build_query($param));
    }
    private function byQueryTimes($table,$querys,$times){
		$param = array( 
			"endAt" => json_encode($querys."_".$times),
			"startAt" => json_encode($querys."_0"),
	       "limitToFirst"=>1,
	       "orderBy"=>'"query_times"');
		return $this->select($table,http_build_query($param));
    }
    // 如果是指定querytimes的，time_out指的是超时时间，否则是指一条记录的操作间隔
	public function getOne($data,$query_times=false,$time_out=3600)
	{
		// 设置查询参数
		// 1、查询次数
		if($query_times){
			$ret = $this->byQuerys($data->table(),$query_times);
			// 无正常数据时，查询异常数据
			if($ret["code"] != 200 || sizeof($ret["html"])==0){
				$ret = $this->byQueryTimes($data->table(),-$query_times,time()-$time_out);
			}
		}else{
			$ret = $this->byTimes($data->table(),time()-$time_out);
		}
		if($ret["code"] == 200 && sizeof($ret["html"])>0){
			reset($ret["html"]);
			$data->setArray(current($ret["html"]));
			return true;
		}
		return false;
	}
	// 仅更新操作时间
	public function UpdateTimes($data){
		$data->set("times",time());
		return $this->update($data->table(),$data->data(),$data->unique());
	}
	// 仅更新操作时间
	public function LockUpdateTimes($data,$timeout=300,$timespan=3600){
		$data->set("times",time()-$timespan+$timeout);
		return $this->update($data->table(),$data->data(),$data->unique());
	}
	// 更新操作时间并设置querys
	public function LockRecode($data){
		var_dump($data);
		$data->set("times",time());
		$data->set("querys",-abs($data->get("querys")));
		var_dump($data);
		return $this->update($data->table(),$data->data(),$data->unique());
	}
	// 更新操作时间并设置querys
	public function ComitRecode($data){
		$data->set("times",time());
		$data->set("querys",1+abs($data->get("querys")));
		return $this->update($data->table(),$data->data(),$data->unique());
	}
	public function Add($data)
	{
		return $this->overwrite($data->table(),$data->data(),$data->unique());
	}
}
