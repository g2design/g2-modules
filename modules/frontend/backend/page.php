<?php

class Page_Mvc_Controller extends Mvc_Controller {
	
	public function index($args) {
		$page_id = array_shift($args);
		
		if(empty($page_id)){
			$this->redirect(PACKAGE_URL);
		}
		
		$page = R::load('page', $page_id);
		
		
		$model = $this->loadModel('page_model');/* @var $model Page_Model */
		
		if($model->form($page)) {
			$this->redirect(PACKAGE_URL);
		}
	}
}

