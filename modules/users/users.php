<?php

class Package_Users extends Mvc_Package {
	const MAIL_LIST = 'maillist';
	const ADDRESS = 'mailaddress';
	const MAIL = 'mail';
	const MAIL_ATTACHMENT = 'mailattachment';
	public function __construct() {
		MVC_Router::getInstance()->add_library($this->get_package_dir() . 'classes');
	}

	public function get_label() {

		return str_replace('_', ' ', strtolower(__CLASS__));
	}

	public function get_action() {
		return strtolower(__CLASS__);
	}

	public function add($lists,$subject,$body,$attachments = array()){
		$list_obs = [];
		foreach($lists as $list){
			$list_obs[] = $this->init_list($list);
		}
		$attachments = $this->create_attachments($attachments);
		$mail = $this->create_mail($list_obs,$subject,$body,$attachments);
		$this->send($mail);
	}

	public function send($mail){
		//Retrieve the list of email addresses connected to this mail
		$maillists = $mail->sharedMaillist;
		$addresses = [];
		foreach($maillists as $list){
			$addresses = array_merge($addresses,$list->sharedMailaddress);
		}
		$mailer = new Zend_Mail();
		foreach($addresses as $address){
			$mailer->addTo($address->email, $address->name ? sharedMailaddress : 'Web Admin' );
		}
		$mailer->setBodyHtml($mail->body);
		$mailer->setSubject($mail->subject);
		$mailer = $this->add_attachments($mailer, $mail->sharedMailattachment);
		$mailer->send(); // @TODO Add Transport From Config

		// Add code to log that the mail was sent
	}

	public function add_attachments($mailer,$attachments){
		foreach($attachments as $att){
			if(file_exists($att->uri)){
				$at = $mailer->createAttachment(file_get_contents($att->uri));
				$at->filename = basename($att->uri);
				$at->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
				$at->encoding = Zend_Mime::ENCODING_BASE64;

			}
		}

		return $mailer;
	}

	public function create_address($email,$name = false){
		if(filter_var($email, FILTER_VALIDATE_EMAIL)){

			$address = Mvc_Db::dispense(self::ADDRESS);

			$address->email = $email;
			$address->name = $name;
			Mvc_Db::store($address);
			return $address;
		}
	}

	public function init_list($list){
		$list_ob = R::findOne(self::MAIL_LIST,'name = :list',['list' => $list]);
		if(empty($list_ob)){
			$list_ob = Mvc_Db::dispense(self::MAIL_LIST);
			$list_ob->name = $list;
			$list_ob->sharedMailaddress = array($this->create_address('stephan@g2design.co.za'));
			Mvc_Db::store($list_ob);
		}

		return $list_ob;
	}
	function create_mail($lists,$subject,$body,$attachments){
		$mail = R::dispense(self::MAIL);
		$mail->subject = $subject;
		$mail->body = $body;
		$mail->start_time = date('Y-m-d H:i:s');
		$mail->sharedMaillist = $lists;
		$mail->sharedMailattachment = $attachments;
		Mvc_Db::store($mail);

		return $mail;
	}
	function create_attachments($attachments){
		$attachments_objs = [];
		$timestamp = time();
		foreach($attachments as $att){
			$filename = $att[G2_FormMagic::FILE_NAME];
			$location = $att[G2_FormMagic::FILE_URI];
			if(file_exists($location)){
				//Determine File name
//				$filename = basename($file);
				$new_folder = $this->get_package_dir(true)."mail_attachments/attached-$timestamp/$filename";
				//Create this new folder
				mkdir(dirname($new_folder), 0777, TRUE);
				//Move file to folder that will be attached
				rename($location, $new_folder);
				//Create the mail attachment db file
				$attachment = Mvc_Db::dispense(self::MAIL_ATTACHMENT);
				$attachment->filename = $filename;
				$attachment->uri = $new_folder;
				Mvc_Db::store($attachment);
				$attachments_objs[] = $attachment;
			}
		}

		return $attachments_objs;
	}

	public function get_admin_controller(){
		return 'admin';
	}

	public function get_permission(){

		return 'developer';
	}
//
//	public function get_dashboard_widget($package_url){
//		$widget = new G2_TwigView('widget/dashboard');
//		//Retrieve all submission Types
//		$submissions = R::findAll('submission_type');
//		foreach($submissions as $key => $sub_type){
//			$submissions[$key]->count = R::getCell('SELECT COUNT(id) FROM :table WHERE ownSubmission_type_id = :id',array('table' => $sub_type->table,'id' => $sub_type->id));
//		}
//		$widget->set('submissions',$submissions);
//		$widget->set('package_url',$package_url);
//		return $widget->get_render();
//	}
}
