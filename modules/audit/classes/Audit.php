<?php

class Audit extends Mvc_Base {


	private function __construct() {
		;
	}

	static function get_instance() {
		// check if object exists
		if (empty($_ENV['instances']['audit'])) {
			// no object yet, create an object
			$_ENV['instances']['audit'] = new self;
		}
		// return reference to object
		$ref = &$_ENV['instances']['audit'];
		return $ref;
	}

	/**
	 *
	 * @param \RedBeanPHP\OODBBean $old_object
	 * @param \RedBeanPHP\OODBBean $new_object
	 * @param type $message
	 */
	public static function create($old_object, $new_object, $message, $entity = false) {

//Determine the current user
		$audit = R::dispense('audit');
		$audit->message = $message;
		$audit->date_logged = date('Y-m-d H:i:s');
		$audit->table = $new_object ? $new_object->getMeta('type') : null;
		$audit->entity = $entity ? $entity : $audit->table;
		$aidit->tableId = $new_object ? $new_object->id : $old_object->id;
		$audit->old_obj = serialize($old_object);
		$audit->new_obj = serialize($new_object);
		$audit->user = G()->get_user();
		R::store($audit);
//		$user->ownAudit[] = $audit;
//		R::store($user);

		Admin_Alert::add_message($message.' <a href="'.ADMIN_URL."package/audit/changes/$audit->id".'">View Change</a>');
	}

	public static function deserialize($audits){
		$audits_d = [];
		foreach($audits as $ad){
			$ad->old_obj = unserialize($ad->old_obj);
			$ad->new_obj = unserialize($ad->new_obj);

			foreach($ad as $field => $value){
				if(Mvc_Functions::endsWith($field, '_id')){
					$bean = substr($field, 0,  strpos($field, '_id') );
					$ad->$bean;
				}
			}
			$audits_d[] = $ad;
		}



		return $audits_d;
	}

	public function save(){
		$current_user = G()->get_user();;

	}

}
