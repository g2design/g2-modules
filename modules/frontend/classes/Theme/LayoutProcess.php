<?php
use Wa72\HtmlPageDom\HtmlPageCrawler;

class Theme_LayoutProcess extends Mvc_Base {

	var $layout = null, $logged_in = true, $page = false;

	function __construct($layout) {
		$this->layout = $layout;
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
			$node = $c->filter('[mvcl-edit="' . $area->area_name . '"]');
			
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
				$node->removeAttribute('mvcl-type');
			}
		}
		$this->module_process($c);
		
		return $c->saveHTML();
	}
	
	function module_process(HtmlPageCrawler &$c){
		$modules = $c->filter('module');
		foreach($modules as $mod) {
			$mod_type =  $mod->getAttribute('type');
			//Check if such a module exists
			$class = 'Theme_Module_'.  ucfirst($mod_type);
			if(class_exists($class)){
//				echo $mod->getAttribute('id');
				$mod_ob = new $class($mod->getAttribute('id'), $this->page->id); /* @var $mod_ob Theme_Module_Interface */
				
				if(method_exists($mod_ob, 'set_module_xml')){
					$mod_ob->set_module_xml($c->filter('module[id="'.$mod->getAttribute('id').'"]')->saveHTML());
				}
				
				
				$c->filter('module[id="'.$mod->getAttribute('id').'"]')->replaceWith($mod_ob->render());
				
				
				if(method_exists($mod_ob, 'addCSS')) $c->filter('head')->append($mod_ob->addCSS());
				if(method_exists($mod_ob, 'addJs')) $c->filter('body')->append($mod_ob->addJs());
				if(G()->logged_in() && method_exists($mod_ob, 'load_admin')){
					$mod_ob->load_admin($c);
				}
			}
			//Add css for this module if not exists
			//Add js for this module if not exists
		}
	}

	function get_areas($html) {
		//Load the html into a dom document
//		$dom = new DOMDocument("4.0", 'UTF-8');
//		$dom->loadHTML($html);
		$dom = HtmlPageCrawler::create($html);

		//Look for html nodes that has the mvc:edit attribute
//		$nodes = $this->find_editable($dom);
		$nodes = $dom->filter('[mvcl-edit]');
		$instance = &$this;
		$area_beans = $nodes->each(function($node) use ($nodes_c, $instance) {
			$field_name = $node->getAttribute('mvcl-edit'); /* @var $node HtmlPageCrawler */
			$field_type = $node->getAttribute('mvcl-type');
			$starting_html = $node->saveHTML();

			return $instance->load_area($instance->page->id, $field_name, $field_type, $starting_html);
		});

		// Convert these nodes to area beens
//		$area_beans = $this->nodes_to_beans($nodes);
		return $area_beans;
	}

	private function find_editable(DomDocument $dom) {
		$xpath = new DOMXPath($dom);

		$nodes = $xpath->query('// *[@mvcl-edit]');
		return $nodes;
	}

	private function nodes_to_beans(DOMNodeList $nodes) {
		$beans = [];
		foreach ($nodes as $node) {
			/* @var $node DOMElement */
			// Retrieve the value of the mvcl-edit attribute
			$field_name = $node->getAttribute('mvcl-edit');
			$field_type = $node->getAttribute('mvcl-type');
			$starting_html = $this->innerHTML($node);

			//Create a unique id according to the layout and node position we are on
			$bean = $this->load_area($this->layout->id, $field_name, $field_type, $starting_html);
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

	function load_area_oild($id, $area_name, $field_type, $starting_html) {
		$bean = current(R::findOrDispense('area', 'layout_id = :layout AND area_name = :field', ['layout' => $id, 'field' => $area_name]));
		if (!$bean->getID()) {
			$bean->area_name = $area_name;
			$bean->html = $starting_html;
			$bean->type = $field_type;
			$bean->page_id = $this->page->id;
			$this->layout->ownArea[] = $bean;

			R::store($this->layout);
		}

		return $bean;
	}
	
	function load_area($page_id, $area_name, $field_type, $starting_html) {
		debug($starting_html);
		$area = R::dispense('area');
		$area->area_name = $area_name;
		$area->html = $starting_html;
		$area->type = $field_type;
		$area->page = R::load('page', $this->page->id);
		$ap = new Theme_Area_Processor($area);
		$area = $ap->load();
		
		$this->layout->ownArea[] = $area;
		R::store($this->layout);
		
		return $area;
	}

}
