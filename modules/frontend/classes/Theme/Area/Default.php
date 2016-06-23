<?php

class Theme_Area_Default extends Mvc_Base {

	protected $area = null;
	protected $replace_content_only = true;

	function __construct($area) {
		$this->area = $area;
	}
	
	function render() {
		return $this->area->html;
	}
	
	function save(){
		R::store($this->area);
	}
	
	function replace_content() {
		if($this->replace_content_only) { // When only inner html will be replaced
			return false;
		} else { //When the whole element needs to be replaced
			return true;
		}
	}
	
	function load() {
		$c = Wa72\HtmlPageDom\HtmlPageCrawler::create($this->area->html);
		
		if($this->replace_content_only) {
			$starting_html = $c->html();
		} else {
			$starting_html = $c->saveHTML();
		}
		
		$page_id = $this->area->page->id;
		$area_name = $this->area->area_name;
		$field_type = $this->area->type;
		//$starting_html = $this->area->html;
		$this->area = current(R::findOrDispense('area', 'page_id = :page AND area_name = :field', ['page' => $page_id, 'field' => $area_name]));
		if (!$this->area->getID()) {
			$this->area->area_name = $area_name;
			$this->area->html = $starting_html;
			$this->area->type = $field_type;
			$this->area->page = R::load('page', $page_id);

			$this->save();
			
		}
		
		return $this->area;
	}
	
	function set_value($value){
		$this->area->html = $value;
	}
	
	function validate(){
		return true;
	}
	
	function get_form_field(){
		return "{$this->get_label()}<input class=\"form-control\" name=\"{$this->area->id}\" type=\"text\">";
	}
	
	function get_label() {
		return "<label for=\"\" class=\"\">{$this->area->area_name}</label>";
	}
}
