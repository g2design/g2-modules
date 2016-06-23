<?php

class Ajax_Mvc_Controller extends Mvc_Controller {

	function index() {
		
	}

	function save_area() {
		G2_User::init();
		if (G()->logged_in() && !empty($_POST)) {
			$content = $_POST['content'];
			$area_id = $_POST['area_id'];

			$area = R::findOne('area', 'id = :area', ['area' => $area_id]);
			$old = clone $area;
			$area->html = $content;

			R::store($area);
			Audit::create($old, $area, 'An Area was updated with new content');
			echo json_encode(['success' => true, 'message' => 'Content Saved Successfully']);
		} else {
			echo json_encode(['success' => false, 'message' => 'Not Logged in']);
		}
		die();
	}

	function nav_save() {
		G2_User::init();
		if (G()->logged_in() && !empty($_POST)) {
			if (!empty($_POST['items']) && !empty($_POST['identity'])) {
				// Delete all current navigation details
				$navitems = R::findAll('navitem', 'identity=:id', ['id' => $_POST['identity']]);

				R::trashAll($navitems);

				foreach ($_POST['items'] as $new_item) {
					$nav = R::dispense('navitem');
					$nav->identity = $_POST['identity'];
					$nav->label = $new_item['label'];
					$nav->href = !$new_item['href'] ? null : $new_item['href'];
					$nav->order = $new_item['order'];
					
					//@todo parent node support
					R::store($nav);
				}

				echo json_encode(['success' => true, 'message' => 'Content Saved Successfully']);
			} else {
				echo json_encode(['success' => false, 'message' => 'Data sent not correct']);
			}
		} else {
			echo json_encode(['success' => false, 'message' => 'Not Logged in']);
		}
		die();
	}

}
