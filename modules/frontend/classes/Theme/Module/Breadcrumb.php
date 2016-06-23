<?php

class Theme_Module_Breadcrumb extends Mvc_Base implements Theme_Module_Interface {

	private $page;
	static $remove = "";

	/**
	 * 
	 * 
	 * @param type $id
	 * @param type $page_id
	 */
	public function __construct($id, $page_id = false) {
		$this->page = R::load('page', $page_id);
	}

	static function remove($string) {
		self::$remove[] = $string;
	}

	public function render() {
		// Create the path titles with urls

		$parents = explode('/', $this->page->slug);
		$current = current(array_reverse($parents));
		debug("Current" . $current);

		$urls = [
		];
		foreach ($parents as $slug) {
			if ($slug == $current)
				break;
			$parentp = R::findOne('page', 'slug = :slug', ['slug' => $slug]);

			$title = $parentp->title;
			$title = current(explode(' | ',$title));
			foreach (self::$remove as $string) {
				$title = str_replace($string, '', $title);
				
			}
			
			$exploded = explode(' | ', $title);
			foreach ($exploded as $rem) self::$remove[] = $rem;
			$title = str_replace('|', '', $title);
			$title = trim($title);
			$u = ['label' => $title, 'href' => $parentp->slug];
			$urls[] = $u;
		}
		
		$title = $this->page->title;
		$title = current(explode(' | ',$title));
		foreach (self::$remove as $string) {
			$title = str_replace($string, '', $title);
		}
//		$title = trim(str_replace('|', '', $title));
		
		$urls[] = ['label' => $title, 'href' => $this->page->slug];
		

		return Theme_Loader::get_instance()->render_file('modules/breadcrumb/breadcrumb.twig', ['urls' => $urls]);
	}

}
