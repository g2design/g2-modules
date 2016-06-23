<?php
class Admin_Mvc_Controller extends G2_TwigController{

	public function __before() {
		parent::__before();

		if(!Permission::has_permission('auditing')){
			$this->redirect(ADMIN_URL);
		}
	}

	function index() {
		$view = new G2_TwigView('pages/index');
		$test_a = R::findOne('audit');
		$args = [];
		if(!empty($_GET)){
			$wheres = [];
			foreach($_GET as $field => $value){
				if( isset($test_a->$field) && trim($value)){
					$wheres[] = "$field LIKE :$field";

					$args[$field] = $value;
				}
			}
			$where = implode(' AND ', $wheres);
		} else {
			$where = '';
		}


		$audits = Audit::deserialize(Mvc_Db::paginate_findAll('audit', 10, "$where ORDER BY id DESC", $args));

		// Sort into entities
		$sorted = [];
		foreach($audits as $audit){ // Limit to 10 entries per entity
			if(count($sorted[$audit->entity]) <= 10){
				$sorted[$audit->entity][] = $audit;
			}
		}
		$view->set('sorted',  $sorted);
		$view->entities = R::getCol('SELECT DISTINCT entity FROM audit');
		$view->set_entity = $_GET['entity'];
		$view->page_count = Mvc_Db::get_last_total_pages();
		$view->current = Mvc_Db::get_current_page();
		unset($_GET['p']);
		$view->current_url = PACKAGE_URL.'?'.http_build_query($_GET);
		$view->render();
	}


	function __construct() {
		parent::__construct();
		$this->template = new G2_TwigTemplate('templates/template.twig');
		$this->template->user = G()->get_user();
		$this->template->package_url = PACKAGE_URL;
	}

	function changes($args){
		$id = array_shift($args);
		if(!is_numeric($id) || !($audit = R::load('audit', $id))){
			$this->redirect(PACKAGE_URL);
		}

		$update_form = new G2_TwigView('forms/audit');

		$update_form = new G2_FormMagic($update_form->get_render());

		if($update_form->is_posted() && $update_form->validate()){
			foreach($update_form->data() as $field => $data){
				$audit->{$field} = $data;
			}
			R::store($audit);

		}
		$update_form->set_data($audit->export());

		$view = new G2_TwigView('pages/changes');
		$view->audit = current(Audit::deserialize([$audit]));
		$view->update_form = $update_form->parse();
		$view->render();
	}

	function restore($args){
		$what = array_shift($args);
		$id = array_shift($args);
		if(!is_numeric($id) || !($audit = R::load('audit', $id))){
			$this->redirect(PACKAGE_URL);
		}
		$audit = current(Audit::deserialize([$audit]));

		if($what == 'new'){
			$bean = $audit->new_obj;
		} else {
			$bean = $audit->old_obj;
		}

		//Retrieve the current record
		$current = R::findOne($bean->getMeta('type'), 'id = :id', array('id' => $bean->id) );
		
		if(!$current){ // Record was removed. Thus record needs to be recreated
			//$bean = R::dup($bean);
		}
		
		$old = $current != null ? clone $current : null;
		
		if($current == null) {
			$current = R::dispense($bean->getMeta('type'));
		}
		
		foreach($bean as $field => $value){
			$current->$field = $value;
		}

		R::store($current);

		Audit::create($old, $current, "restored to previous version changed on $audit->date_logged");

		$this->redirect(PACKAGE_URL);
	}



	private function filter_out($objects,$keys){

		foreach($objects as  $key => $object){
			foreach($keys as $key_r){
				unset($objects[$key]->$key_r);
			}
		}
		return $objects;
	}
}