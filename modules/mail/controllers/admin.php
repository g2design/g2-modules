<?php
class Admin_Mvc_Controller extends G2_TwigController{
	function index(){
		//Get all Submission Types
		$view = new G2_TwigView('pages/index');
		// Mail Lists
		$lists = R::findAll(Package_Mail::MAIL_LIST);
		$view->set('maillist',$lists);
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
		if(!empty($id) && is_numeric($id)){
			$list = R::load(Package_Mail::MAIL_LIST, $id);
			if($list->isEmpty()){
				$this->redirect(PACKAGE_URL);
			}
		} else {
			$list = R::dispense(Package_Mail::MAIL_LIST);
		}
		
		$form_view = new G2_TwigView('forms/maillist');
		$form_view->set('addresses',$list->sharedMailaddress); 
		$form_view->set('list',$list);
		$form = new G2_FormMagic($form_view->get_render());
		
		if($form->is_posted()){
			var_dump($_POST);exit;
		}
		
		$view = new G2_TwigView('pages/crud');
		$view->set('form',$form->parse());
		
		$view->render();
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