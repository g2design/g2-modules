<?php

class Admin_Mvc_Controller extends Mvc_Controller {
	
	public function index() {
		$table = new G2_ImprovedDataTable();
		
		if(isset($_GET['s'])){
			
			$where = 'title LIKE \'%'.  implode('%', str_split(str_replace(' ', '', $_GET['s']))).'%\' ';
		} else $where = '';
		//$table->add_query('page', $where.' ORDER BY id DESC');
		
		$query = "SELECT DISTINCT page.* FROM page INNER JOIN area ON page.id = area.page_id ";
		if($where) {
			$query .= "WHERE " . $where;
		}
		
		$table->add_exec_query($query);
		$table->set_fields([
			['name' => 'title' , 'label' => 'Page Title'],	
//			['name' => 'slug', 'label' => 'Url Slug'],
			['name' => 'description', 'label' => 'Page description'],
		]);
		
		$renderer = new G2_DataTable_Renderer('title');
		$renderer->set_function(function($fieldname, $value, $data){
			return "<strong>$value</strong><br><a href=\"".BASE_URL.$data['slug']."\" target=\"_blank\">View Page</a> | <a href=\"".PACKAGE_URL."page/{$data['id']}\">Edit Page</a>";
		});
		
		$table->add_renderer($renderer);
		
		if(Permission::has_permission('Delete Pages')) {
			$table->add_function(PACKAGE_URL.'delete-page/[id]', 'Delete this page');
		}
		echo '<a href="'.PACKAGE_URL.'posts" class="btn">View Posts</a>';
		echo '<div class="panel"><div class="panel-body"><form action="" method="get"><input name="s" type="text" value="'.$_GET['s'].'"><button>Search</button></form></div></div>';
		echo $table->render();
	}
	
	public function delete_page($args){
		$page_id = array_shift($args);
		if($page = R::load('page', $page_id)) {
			$old = clone $page;
			
			R::trash($page);
			Audit::create($old, NULL, 'Developer deleted page from system');
			
		}
		$this->redirect(PACKAGE_URL);
	}
}
