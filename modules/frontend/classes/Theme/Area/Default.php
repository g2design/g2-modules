<?php

class Theme_Area_Default extends Mvc_Base {

	protected $area = null;
	protected $replace_content_only = false;
	public $editable = false;
	protected $c = null;

	function __construct($area) {
		$this->area = $area;
	}

	/**
	 * 
	 * @return Wa72\HtmlPageDom\HtmlPageCrawler
	 */
	function &c() {
		if($this->c) {
			return $this->c;
		} else {
			return $this->c = Wa72\HtmlPageDom\HtmlPageCrawler::create($this->area->html);
		}
	}

	function enable_edit() {
		debug('GOT HERE');
		
		$this->c()->setAttribute('data-contenteditable', 'true');
		$this->c()->setAttribute('data-area', $this->area->getID());
		$this->c()->setAttribute('data-type', $this->area->type);

		$this->area->html = $this->c()->saveHTML();
	}

	function render() {
		if ($this->editable) {
			$this->enable_edit();
		}

		return $this->c()->saveHTML();
	}

	function save() {
		R::store($this->area);
	}

	function replace_content() {
		if ($this->replace_content_only) { // When only inner html will be replaced
			return false;
		} else { //When the whole element needs to be replaced
			return true;
		}
	}

	function load() {
		$c = $this->c();

		if ($this->replace_content_only) {
			$starting_html = $c->html();
		} else {
			$starting_html = $c->saveHTML();
		}

		$page_id = $this->area->page->id;
		$area_name = $this->area->area_name;
		$field_type = $this->c()->getAttribute('mvc-type') ? $this->c()->getAttribute('mvc-type') : 'default';
		//$starting_html = $this->area->html;
		$this->area = current(R::findOrDispense('area', 'page_id = :page AND area_name = :field AND type = :type', ['page' => $page_id, 'field' => $area_name, 'type' => $field_type]));
		if (!$this->area->getID()) {
			$this->area->area_name = $area_name;
			$this->area->html = $starting_html;
			$this->area->type = $field_type;
			$this->area->page = R::load('page', $page_id);

			$this->save();
		}

		return $this->area;
	}

	function set_value($value) {
		$this->area->html = $value;
	}

	function validate() {
		return true;
	}

	function get_form_field() {
		return "{$this->get_label()}<input class=\"form-control\" name=\"{$this->area->id}\" type=\"text\">";
	}

	function get_label() {
		return "<label for=\"\" class=\"\">{$this->area->area_name}</label>";
	}

}
