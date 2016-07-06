<?php

class Theme_Area_Processor extends Mvc_Base {

	private $area = null;

	function __construct(&$area) {
		$this->area = $area;
	}

	public function __get($name) {
		return $this->area->{$name};
	}

	function __set($name, $value) {
		$this->area->$name = $value;
	}

	function render() {
		//Check if a class is defined for a type
		$area = $this->get_area_instance();

		//Remove all funny tags from file
		$content = $area->render();
		$c = \Wa72\HtmlPageDom\HtmlPageCrawler::create($content);
		$c->removeAttr('mvcl-type');
		$c->removeAttr('mvc-type');
		$c->removeAttr('mvc-type');
		$c->removeAttr('mvc-size');
		if ($c->getNode(0)->hasAttributes()) {

			foreach ($c->getNode(0)->attributes as $attr) {
				$name = $attr->nodeName;
				if(Mvc_Functions::startsWith($name, 'mvc')) {
					$c->removeAttr($name);
				}
			}
		}
		return $c->saveHTML();
	}

	function replace_content() {
		$area = $this->get_area_instance();
		return $area->replace_content();
	}

	/**
	 * 
	 * @return \Theme_Area_Default
	 */
	private function get_area_instance() {
		//Check if a class is defined for a type
		$class = "Theme_Area_" . ucfirst($this->area->type);
		debug($class);
		if (class_exists($class)) {
			$area = new $class($this->area); /* $area Theme_Area_Default */
		} else {
			$area = new Theme_Area_Default($this->area);
		}

		return $area;
	}

	function save() {
		$area = $this->get_area_instance();

		$area->save();
	}

	function load() {
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

	function validate() {
		$instance = $this->get_area_instance();
		return $instance->validate();
	}

}
