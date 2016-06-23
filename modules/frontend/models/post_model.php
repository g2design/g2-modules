<?php

class Post_Model extends Mvc_Model {

	var $post = null;

	function create_field($post_type, $field_name, $field_type, $show_on_front = false) {

		$post_meta = current(R::findOrDispense('postmeta', 'post_type = :posttype AND fieldname = :field', ['posttype' => $post_type, 'field' => $field_name]));

		if (!$post_meta->getID()) {
			$post_meta->post_type = $post_type;
			$post_meta->fieldname = $field_name;
		}

		$post_meta->type = $field_type;
		$post_meta->show_on_front = $show_on_front;

		R::store($post_meta);
	}

	function get_fields($posttype, $show = false) {
		$postmeta = R::findAll('postmeta', 'post_type = :posttype', ['posttype' => $posttype]);

		$fields = [];
		foreach ($postmeta as $field) {
			if ($show && $field->show_on_front == false) {
				continue;
			}
			$fields[] = ['name' => $field->fieldname, 'label' => ucfirst($field->fieldname), 'type' => $field->type];
		}

		return $fields;
	}

	function get_posts($post_type, $id = false) {
		$posts = R::findAll('post', 'post_type = :post ORDER BY id DESC ', ['post' => $post_type]);

		$posts_populated = [];
		foreach ($posts as $post) {
			$fields = $post->ownPostdata;

			$object = R::dispense('postobject');
			foreach ($fields as $fielddata) {
				$object->{$fielddata->postmeta->fieldname} = $fielddata->value;
			}
			$object->id = $post->id;
			$posts_populated[] = $object;
		}

		return $posts_populated;
	}

	function crud($posttype, RedBeanPHP\OODBBean $post_object) {

		$fields = $this->get_fields($posttype, false);
		$view = new G2_TwigView('post_form');
		$view->fields = $fields;

		$form = new G2_FormMagic($view->get_render());
		
		if(!$form->is_posted() && $post_object->getID()){
			$data = [];
			foreach($post_object->ownPostdata as $postdata){
				$data[$postdata->postmeta->fieldname] = $postdata->value;
			}
			
			$form->set_data($data);
		}
		
		if ($form->is_posted()) {

			$old = clone $post_object;

			//Save fields to correct db locations
			$postdatas = [];
			foreach ($form->data() as $field => $value) {
				// First Match the field with the correct postdata record from the current post
				unset($postdata);
				foreach ($post_object->ownPostdata as &$data) {
					if ($data->postmeta->fieldname == $field) {
						$postdata = $data;
					}
				}


				if (!isset($postdata)) {
					
					$postdata = R::dispense('postdata');
					$postdata->postmeta = R::findOne('postmeta', 'fieldname = :field AND post_type = :type', ['type' => $posttype, 'field' => $field]);
				}
				
				$postdata->value = $value;
				R::store($postdata);
				$postdatas[] = $postdata;
			}
			
			
			
			$post_object->ownPostdata = $postdatas;
			$post_object->post_type = $posttype;
			if(!$post_object->getID()) {
				$post_object->date_created = date('Y-m-d H:i:s');
			}
			$post_object->date_modified = date('Y-m-d H:i:s');
			
			$files = $form->get_uploaded_files();
			R::store($post_object);
			//Save files to 
			Audit::create($old, $post_object, 'An Post was saved');
			
			return true;
		}

		echo $form->parse();
	}

}
