<?php

class Crud_Mvc_Controller extends Mvc_Controller {
	
	function __construct() {
		//Determine the current theme location
		$config = ROOT_DIR . 'config/site.ini';
		if (file_exists($config)) {
			$config = new Zend_Config_Ini($config, APP_DEPLOY);
			$theme_location = $config->site->theme ? $config->site->theme : 'themes/default';
		} else {
			$theme_location = 'themes/default';
		}
		define("THEME", $theme_location);
	}
	
	public function __before() {
		G2_User::init();
		
		if(!G()->logged_in()){
			$this->redirect(BASE_URL);
		}
	}
	
	function generate_pages(){
		//Get all page saved in the pages folder
		echo THEME.'/pages <br>';
		$location = THEME.'/pages';
		$pages = Mvc_Functions::directoryToArray ( $location , true);
		foreach($pages as $page){
			if(Mvc_Functions::get_extension($page) != 'twig'){
				continue;
			}
			
			//Determine page slug
			$new_loc = str_replace( $location.'/' , THEME.'/templates/', $page);
			$file = str_replace($location.'/', '', $page);
			$slug = str_replace('.twig', '', $file);
			$template = "templates/$file";
			
			
			// Determine the page Title
			$title = str_replace('/', ' | ', $slug);
			$title = ucwords(str_replace('-', ' ', $title));
			
			echo "NEW LOCATION: $new_loc | FILE: $file | TEMPLATE: $template | SLUG: $slug | TITLE: $title <br>";
			
			//Copy the file to the layouts location
			if(!is_dir(dirname($new_loc))){
				mkdir(dirname($new_loc), 0777, true);
			}
			copy($page, $new_loc);
			
			//Create Page Bean
			$page = current(R::findOrDispense('page','slug = :slug',['slug' => $slug]));
			$page->slug = $slug;
			$page->title = "$title | Durbanville Hills";
			$page->template = $template;
			$page->layout = "layouts/default.twig";
			
			R::store($page);
		}
	}
	
	function page_crud(){
		if(!empty($_POST)){
			//Load an existing page if it exists
			$page = current(R::findOrDispense('page', 'id = :id', ['id' => $_POST['page_id']]));
			if(!$page->getID()){
				$page->slug = $_POST['slug'];
			} else {
				$old = clone $page;
			}
			
			$page->description = $_POST['description'];
			$page->title = $_POST['title'];
			$page->template = $_POST['template'];
			$page->layout = $_POST['layout'];
			
			R::store($page);
			Audit::create($old, $page, 'Page details Saved');
			
			$this->redirect(BASE_URL.$page->slug);
		}
	}
	
	/**
	 * Update titles of pages in site
	 * 
	 */
	function update_titles() {
		echo 'Load Config <br>';
		$config = new Zend_Config_Ini('meta.ini', APP_DEPLOY);
		
		$title = $config->meta->title->change;
		
		$pages_slugs = [];
		$this->filter($title,$pages_slugs);
		
		$pages = R::findAll('page');
		$meta = new Meta_Generator($config);
		
		foreach($pages as $page){
			$old = clone $page;
			echo "$page->title ==== ".$meta->get_title($page->slug) .'<br>';
			echo "$page->description ==== ".$meta->get_description($page->slug) .'<br>';
			$page->title = $meta->get_title($page->slug);
			$page->description = $meta->get_description($page->slug);
			Audit::create($old, $page, "Script updated Meta Data");
			R::store($page);
		}
		
		
//		foreach($pages_slugs as $slug => $title){
//			$page = R::findOne('page','slug = :slug',['slug' => $slug]);
//			if($page){
//				echo "$page->title => $title ====   $page->slug<br>";
//			} else {
//				echo "$slug Page not Found <br>";
//			}
//		}
	}
	
	private function filter($config , &$data = [], $slug = false){
		foreach($config as $key => $value){
			if (is_string($value)) {
				if(!empty($slug) && $key == 'index'){
					$slugkey = $slug;
				} else {
					$slugkey = $slug.'/'.$key;
				}
				
				$slugkey = trim($slugkey, '/');
				$slugkey = str_replace('_', '-', $slugkey);
				
				$data[$slugkey] = $value;
			} else {
				echo "$key ==== $slug <br>";
				$slug .= "/$key";
				$this->filter($value,$data, $slug);
			}
		}
	}
}

