<?php

class Theme_Area_Wysiwig extends Theme_Area_Default {
	function render() {
		return $this->area->html;
	}
	
	public function get_form_field() {
		return "{$this->get_label()}<textarea name=\"{$this->area->id}\" class=\"editor\"></textarea>";
	}
}