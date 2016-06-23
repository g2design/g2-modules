<?php

class Permission extends Mvc_Base {

	private function __construct() {
		;
	}

	static function get_instance() {
		// check if object exists
		if (empty($_ENV['instances']['permission'])) {
			// no object yet, create an object
			$_ENV['instances']['permission'] = new self;
		}
		// return reference to object
		$ref = &$_ENV['instances']['permission'];
		return $ref;
	}

	public function create_permission($perm, $groups = []) {
		if (R::findOne('permission', 'name = :name', ['name' => $perm])) {
			return false;
		}

		$permission = R::dispense('permission');
		$permission->name = $perm;
		$permission->sharedGroup = $this->get_groups($groups);


		R::store($permission);
	}

	public static function has_permission($perm, $default = 'developer') {
		$user = G()->get_user();
		if (empty($user)) {
			return false;
		}
		$groups = $user->sharedGroup;

		$instance = self::get_instance();
		//Check if the permission that is required is inside the users groups allowed
		$permission = R::findOne('permission', 'name = :name', ['name' => $perm]);

		if (!$permission) {
			$permission = $instance->create_permission($perm, [$default]);
		}
		$permGroups = $permission->sharedGroup;
		if (is_array($permGroups)) {
			foreach (@$permGroups as $must_be) {
				if ($instance->has_group($must_be->name)) {
					return true;
				}
			}
		}

		return false;
	}

	public function has_group($groupname) {
		$user = G()->get_user();
		$groups = $user->sharedGroup;
		foreach ($groups as $group) {
			if ($group->name == $groupname) {
				return true;
			}
		}

		return false;
	}

	public function get_groups($groups) {
		$g_objs = [];
		foreach ($groups as $group) {
			$g = R::findOne('group', 'name = :name', ['name' => $group]);
			if (!$g) {
				$g = $this->create_group($group);
			}
			$g_objs[] = $g;
		}

		return $g_objs;
	}

	public function create_group($name) {
		$group = R::dispense('group');
		$group->name = $name;
		R::store($group);
		return $group;
	}

}
