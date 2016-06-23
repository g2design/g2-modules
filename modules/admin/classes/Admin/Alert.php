<?php

class Admin_Alert extends Mvc_Base {

	static $instance = null;

	const SUCCESS = 'alert-success';
	const WARNING = 'alert-warning';

	var $rendered = false;
	private $messages = [];

	/**
	 *
	 * @return Admin_Alert
	 */
	static function get_instance() {
		// check if object exsists
		if (empty($_ENV['instances']['admin'])) {
			// no object yet, create an object
			$_ENV['instances']['admin'] = new self;
		}
		// return reference to object
		$ref = &$_ENV['instances']['admin'];
		return $ref;
	}

	private function __construct() {
		if (isset($_SESSION['alerts']['messages'])) {
			$this->messages = $_SESSION['alerts']['messages'];
		}
	}

	public function __destruct() {
//		if (!empty($this->messages) && !$this->rendered) {
			$_SESSION['alerts'] = [
				'messages' => $this->messages
			];
//		} else {
//			$_SESSION['alerts'] = [
//				'messages' => []
//			];
//		}
	}

	static function add_message($message, $code = self::SUCCESS) {
		$instance = self::get_instance();
		$instance->messages[] = ['message' => $message, 'code' => $code];
	}

	function render() {
		$messages  = isset($_SESSION['alerts']['messages']) ? $_SESSION['alerts']['messages'] : [];
		if(empty($messages) && !empty($this->messages)){
			$messages = $this->messages;
		}
		if (!empty($messages)) {
			$view = new G2_TwigView('parts/alert');
			$s = [];
			foreach($messages as $message){
				if(!isset($message['rendered'])){
					$s[] = $message;
				}
			}

			$view->set('messages', $s);

			$alert = $view->get_render();
			$this->messages = [];
			unset($_SESSION['alerts']);
//			$this->messages = [];
//			$this->rendered = true;
			return $alert;
		} else
			return false;
	}

}
