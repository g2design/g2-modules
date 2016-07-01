<?php


/**
 * Class to quickly render views with a basic structure
 * 
 */
class QuickRender extends Mvc_Base {
	const ALERT_SUCCESS = "alert-success";
	const ALERT_DANGER = "alert-danger";
	const ALERT_INFO = "alert-info";
	const ALERT_WARNING = "alert-warning";
	
	var $title, $content = [], $functions = [], $alerts = [];
	
	public function __construct($title) {
		$this->title = $title;
	}
	
	
	public function render(){
		$view = new G2_TwigView('components/quickrender');
		$view->this = $this;
		
		$view->render();
	}
	
	public function &add_function($label, $action){
		$this->functions[] = ['label' => $label , 'action' => $action ];
		
		return $this;
	}
	
	public function &add_content($content){
		$this->content[] = $content;
		return $this;
	}
	
	public function &add_alert($message, $code = self::ALERT_INFO ) {
		$this->alerts[] = ['class' => $code, 'message' => $message];
	
		return $this;
	}
	
	
}