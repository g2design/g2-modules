<?php

class Package_Queue extends Mvc_Package {
	public function __construct() {
		MVC_Router::getInstance()->add_library($this->get_package_dir().'classes');
	}
}