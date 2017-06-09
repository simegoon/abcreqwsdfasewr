<?php

class ExLock {
	private $lockfile;
	private $file = false;
	private $waittime = 30;//目前不支持
	public function __construct ($lockfile = "default",$waittime=30){
 		$this->lockfile = ".lock_file.".$lockfile;
		
 		$this->waittime = $waittime;
	}
 	public function Lock()
 	{
 		$this->file = fopen($this->lockfile,"w+");
 		if(!flock($this->file,LOCK_EX))
 		{
	  		fclose($this->file);
			$this->file = false;
 			return false;
 		}
 		return true;
 	}
 	public function UnLock()
 	{
 		if (!$this->file) {
 		 	echo "no need unlock";
 		 	return;
 		}
  		flock($this->file,LOCK_UN);
  		fclose($this->file);
		$this->file = false;
 	}
}
