<?php

use Wa72\HtmlPageDom\HtmlPageCrawler;

class Theme_Process extends Mvc_Base {

	var $page = null, $logged_in = true;

	function __construct(Theme_Page $page) {
		$this->page = $page->data();
	}

	function process($content) {
		$beans = $this->get_areas($content);
		return $this->replace_content($beans, $content);
	}

	function replace_content($beans, $content) {
		//Load content into DOM

		$c = HtmlPageCrawler::create($content);


		foreach ($beans as $area) {
			$cp = new Theme_Area_Processor($area);
			$content = $cp->render();
			//Find the nodes matching this areas ids
			$node = $c->filter('[mvc-edit="' . $area->area_name . '"]');
			
			if($cp->replace_content()) {
				$node->replaceWith($content);
			} else {
				$node->setInnerHtml($content);
			}
			if ($this->logged_in) {
				$node->setAttribute('data-contenteditable', 'true');
				$node->setAttribute('data-area', $area->getID());
				$node->setAttribute('data-type', $area->type);
			} else {
				$node->removeAttribute('mvc-type');
			}
		}

		return $c->saveHTML();
	}

	function get_areas($html) {
		//Load the html into a dom document
//		$dom = new DOMDocument("4.0", 'UTF-8');
//		$dom->loadHTML($html);
		$dom = HtmlPageCrawler::create($html);

		//Look for html nodes that has the mvc:edit attribute
//		$nodes = $this->find_editable($dom);
		$nodes = $dom->filter('[mvc-edit]');
		$instance = &$this;
		$area_beans = $nodes->each(function($node) use ($nodes_c, $instance) {
			$field_name = $node->getAttribute('mvc-edit'); /* @var $node HtmlPageCrawler */
			$field_type = $node->getAttribute('mvc-type');
			$starting_html = $node->saveHTML();

			return $instance->load_area($instance->page->id, $field_name, $field_type, $starting_html);
		});

		// Convert these nodes to area beens
//		$area_beans = $this->nodes_to_beans($nodes);
		return $area_beans;
	}

	private function find_editable(DomDocument $dom) {
		$xpath = new DOMXPath($dom);

		$nodes = $xpath->query('// *[@mvc-edit]');
		return $nodes;
	}

	private function nodes_to_beans(DOMNodeList $nodes) {
		$beans = [];
		foreach ($nodes as $node) {
			/* @var $node DOMElement */
			// Retrieve the value of the mvc-edit attribute
			$field_name = $node->getAttribute('mvc-edit');
			$field_type = $node->getAttribute('mvc-type');
			$starting_html = $this->innerHTML($node);

			//Create a unique id according to the page and node position we are on
			$bean = $this->load_area($this->page->id, $field_name, $field_type, $starting_html);
			$beans[] = $bean;
		}

		return $beans;
	}

	function node_to_string(DOMElement $node) {
		$newdoc = new DOMDocument();
		$cloned = $node->cloneNode(TRUE);
		$newdoc->appendChild($newdoc->importNode($cloned, TRUE));
		return $newdoc->saveHTML();
	}

	function innerHTML(DOMElement $element) {
		$innerHTML = "";
		$children = $element->childNodes;

		foreach ($children as $child) {
			$innerHTML .= $element->ownerDocument->saveHTML($child);
		}

		return $innerHTML;
	}

	function load_area($page_id, $area_name, $field_type, $starting_html) {
		$area = R::dispense('area');
		$area->area_name = $area_name;
		$area->html = $starting_html;
		$area->type = $field_type;
		$area->page = R::load('page', $page_id);
		$ap = new Theme_Area_Processor($area);
		return $ap->load();
	}

}
