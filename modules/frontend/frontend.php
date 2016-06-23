<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class Package_Frontend extends Mvc_Package {
	public function __construct() {
		MVC_Router::getInstance()->add_library($this->get_package_dir().'classes');
	}
	
	public function get_admin_control_dir() {
		return 'backend';
	}
	
	public function get_admin_controller(){
		return 'admin';
	}

	public function get_permission(){
		return 'Edit Front End';
	}
	
	public function get_dashboard_widget($package_url){
		$widget = new G2_TwigView('widgets/dashboard');
		$widget->package_url = $package_url;
		
		
		return $widget->get_render();
	}
}
