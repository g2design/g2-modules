<?php
class Admin_Mvc_Controller extends G2_TwigController{


	function __construct() {
		parent::__construct();
		$this->template = new G2_TwigTemplate('templates/template.twig');
		$this->template->user = G()->get_user();
		$this->template->package_url = PACKAGE_URL;
	}


	function index(){
		$permissions = R::findAll('permission');

		$view = new G2_TwigView('pages/permissions');
		$view->permissions =$permissions;
		$view->render();
	}

	function view($args){
		$id = array_shift($args);
		$permission = R::load('permission',$id);
		if(!is_numeric($id) && !$permission->getID()){
			$this->redirect(PACKAGE_URL);
		}

		$allgroups = R::findAll('group');
		foreach($allgroups as $key => $group){
			foreach($permission->sharedGroup as $group_c){
				if($group->id == $group_c->id){
					$allgroups[$key]->checked = true;
				}
			}
		}
//		echo $permission->name;exit;
		$view = new G2_TwigView('pages/view');
		$view->permission = $permission;
		$view->allGroups = $allgroups;
		$form = new G2_FormMagic($view->get_render());

		if($form->is_posted()){
			$groups = R::loadAll('group', array_keys($form->data()['groups']));
			$permission->sharedGroup = $groups;

			R::store($permission);
			Admin_Alert::add_message("\"$permission->name\" permission was updated");
			$this->redirect(PACKAGE_URL);
		}

		echo $form->parse();
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