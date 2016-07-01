<?php

class Execute extends Thread {
	var $function = null;
	
	public function __construct($function) {
		$this->function = $function;
	}
	
	public function run() {
		if(!is_null($this->function)) {
			$this->function();
		}
	}
}

