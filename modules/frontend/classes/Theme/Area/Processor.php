<?php

class Theme_Area_Processor extends Mvc_Base {
	private $area = null;
	private $editable;

	function __construct(&$area) {
		$this->area = $area;
	}
	
	public function __get($name) {
		return $this->area->{$name};
	}
	
	function __set($name, $value) {
		$this->area->$name = $value;
	}
	
	function render(){
		//Check if a class is defined for a type
		$area = $this->get_area_instance();
		if($this->editable) {
			$area->editable = true;
		}
		return $area->render();
	}
	
	function replace_content(){
		$area = $this->get_area_instance();
		return $area->replace_content();
	}
	
	/**
	 * 
	 * @return \Theme_Area_Default
	 */
	private function get_area_instance(){
		//Check if a class is defined for a type
		$class = "Theme_Area_".ucfirst($this->area->type);
		debug($class);
		if( class_exists($class) ){
			$area = new $class($this->area); /* $area Theme_Area_Default */
		} else {
			$area = new Theme_Area_Default($this->area); 
		}
		
		return $area;
	}
	
	function save(){
		$area = $this->get_area_instance();
		
		$area->save();
	}
	
	function load(){
		$area = $this->get_area_instance();
		return $area->load();
	}
	
	function get_form_field() {
		$instance = $this->get_area_instance();
		return $instance->get_form_field();
	}
	
	function set_value($value) {
		$instance = $this->get_area_instance();
		$instance->set_value($value);
	}
	
	function validate(){
		$instance = $this->get_area_instance();
		return $instance->validate();
	}
	
	function editable() {
		$this->editable = true;
	}
}

