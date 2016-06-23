<?php

class Theme_Module_Navigation extends Mvc_Base implements Theme_Module_Interface {

	var $nav_items = null , $id = null, $title = null;
	

	public function __construct($id, $page_id = false) {
		//Load items belonging to this $id and page is set;
		$identity = md5($id);
		$this->title = $id;
		$this->id = $identity;
		$navs = R::findAll('navitem', 'identity = :identity ORDER by `order` ASC', ['identity' => $identity]);
		if (empty($navs)) {
			$navs = [$this->loadNav($identity, 'Home', '')];
		}

		$this->nav_items = $navs;
		
	}
	
	
	function load_admin(\Wa72\HtmlPageDom\HtmlPageCrawler &$c){
		//Append Button
		$c->filter('.mvc-admin-bar .top-bar')->append('<button class="btn btn-success" data-toggle="navigation-edit-'.$this->id.'">Edit Navigation: '.$this->title.'</button>');
		//Append Edit Area
		$c->filter('.mvc-admin-bar .t-center .v-center')->append(Theme_Loader::get_instance()->render_file('modules/navigation/admin.twig',['navitems' => $this->nav_items, 'page_identity'=> $this->id]));
	}

	function loadNav($identity, $label, $href, $parent_nav = false) {
		$nav = R::dispense('navitem');
		$nav->identity = $identity;
		$nav->label = $label;
		$nav->href = !$href ? null : $href;
		$nav->navitem = $parent_nav;

		R::store($nav);
		return $nav;
	}

	function render() {
		return Theme_Loader::get_instance()->render_file('modules/navigation/navbar.twig', ['navitems' => $this->nav_items]);
	}
	
	function addCss(){
		return Theme_Loader::get_instance()->render_file('modules/navigation/css.twig', ['navitems' => $this->nav_items]);
	}
	
	function addJs(){
		return Theme_Loader::get_instance()->render_file('modules/navigation/js.twig', ['navitems' => $this->nav_items]);
	}
	
	function addAdminHtml(){
		return Theme_Loader::get_instance()->render_file('modules/navigation/admin.twig', ['navitems' => $this->nav_items]);
	}

}
