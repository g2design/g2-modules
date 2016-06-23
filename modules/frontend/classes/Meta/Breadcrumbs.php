<?php

class Meta_BreadCrumbs extends Mvc_Base {

	var $info = null;

	public function __construct($page) {
		$this->info = $this->collect_information($page);
	}

	/**
	 * Renders the breadcrumb string
	 * @param type $page
	 */
	function render() {

		$view = new G2_TwigView('parts/breadcrumbs');
		$view->crumbs = $this->info;
		return $view->get_render();
	}

	/**
	 * Retrieves the correct page titles from the meta class to create the appropriate links
	 * @param type $page
	 */
	function collect_information($page) {

		//Split the given page url up in sections
		$sections = explode('/', $page);

		//Start looking at the begining of the string and get the correct title from the meta class
		$config = $this->get_package_instance(true)->get_config('meta.ini');
		$meta_gen = new Meta_Generator($config);
		$crumbs = [];
		$link_build = [];
		$check_page = [];
		foreach ($sections as $page) {
			$check_page[] = $page;
			$title = $meta_gen->get_clean_title(implode('/', $check_page));
			
			$link_build[] = $page;
			$link = implode('/', $link_build);
			$crumbs[] = ['label' => $title, 'link' => $link];
		}
		
		return $crumbs;
	}

}
