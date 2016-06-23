<?php
class Admin_Mvc_Controller extends G2_TwigController{
	function index(){
		//Get all Submission Types
		$view = new G2_TwigView('pages/index');
		// Mail Lists
		$lists = R::findAll('user');
		$view->set('users',$lists);
		$view->render();
	}
	function __construct() {
		parent::__construct();
		$this->template = new G2_TwigTemplate('templates/submissions.twig');
		$this->template->user = G()->get_user();
		$this->template->package_url = PACKAGE_URL;
	}
	function crud($args){
		$id = array_shift($args);
		//Check if user is loaded
		if(!empty($id) && is_numeric($id)){
			$user = R::load('user', $id);
			if($user->isEmpty()){
				$this->redirect(PACKAGE_URL);
			}
		} else {
			$user = R::dispense('user');
		}
		$old = clone $user;
		$form_view = new G2_TwigView('forms/user');
		$form_view->set('groups',R::findAll('group')); // Sets group information
		$form_view->set('user',$user);
		$form = new G2_FormMagic($form_view->get_render());
		
		
//		var_dump($user->export());
		
		if(empty($_POST)){
			$data = $user->export();
			$groups = [];
			foreach($user->sharedGroup as $bean){
				$groups[] = $bean->name;
			}
			$data['sharedGroup'] = $groups;
			
			$form->set_data($data);
//			var_dump($data);exit;
		}
		
		if($form->is_posted() && $form->validate()){
			
			$data = $form->data();
//			var_dump($data);exit;
			if($data['password'] != $user->password){ //The Users password changed. Need to re encrypt
				$data['password'] = G()->hash_pass($data['password']);
			}
			
			foreach($data as $field => $value){
				if(is_array($user->$field) && Mvc_Functions::startsWith($field, 'shared')){
				
					$bean = strtolower(substr($field, strlen('shared')));
					$bean_ar = [];
					foreach($value as $name){
						$bean_ar[] = G()->load_group($name);
					}
					$user->{"shared".ucfirst($bean)} = $bean_ar;
				} else {
					$user->$field = $value;
				}
			}
			if(empty($user->sharedGroup)){
				$user->sharedGroup = [G()->load_group('default')];
			}
			R::store($user);
			if(empty($old->id)){
				Audit::create($old, $user, 'New user was created');
			} else {
				Audit::create($old, $user, 'User details was updated');
			}
			$this->redirect(PACKAGE_URL.'crud/'.$user->id);
		}
		
		$view = new G2_TwigView('pages/crud');
		$view->set('form',$form->parse());
		
		$view->render();
	}
	
	function remove($args){
		$id = array_shift($args);
		if( is_numeric($id) && $user = R::load('user',$id) ){
			Audit::create($user, null, 'User was removed from the system');
			R::trash($user);
		}
		$this->redirect(PACKAGE_URL);
	}
	
	function view($args){
//		$id = array_shift($args);
//		$sub = R::findOne('submission_type','id = :id', ['id' => $id]);
//		if($sub){
//			$table = $sub->table_name;
//			$submissions = R::findAll($table);
//			$submissions = $this->filter_out($submissions, ['id', 'ownSubmission_type_id']);
//			$view = new G2_TwigView('pages/submissions');
//			$view->set('submissions', $submissions);
//			$view->set('sub_type',$sub);
//			$view->set('sub_head',current($submissions));
//			$view->render();
//		}
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