<?php

abstract class Theme_Type extends Mvc_Base {
	var $theme = null;
	
	public function __construct($theme) {
		$this->theme = $theme;
	}
	
	abstract function create($filename, App_Config_Ini $config, $template, $mtime) ;
}