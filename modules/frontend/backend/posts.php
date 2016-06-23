<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Posts_Mvc_Controller extends Mvc_Controller {

	public function index() {

		$table = new G2_ImprovedDataTable();
		$table->add_exec_query('SELECT DISTINCT post_type FROM postmeta');

		$table->set_fields([
			['name' => 'post_type', 'label' => 'Post Types']
		]);

		$table->add_function(PACKAGE_URL . 'posts/view/[post_type]', 'View');

		echo $table->render();
	}

	public function view($args) {

		$posttype = array_shift($args);
		if (empty($posttype)) {
			$this->redirect(PACKAGE_URL . 'posts');
		}
		echo '<h1>'.  ucfirst($posttype).'</h1>';
		$model = $this->loadModel('post_model'); /* @var $model Post_Model */
		$posts = $model->get_posts($posttype);

		if (!empty($posts)) {


			$table = new G2_ImprovedDataTable();
			$table->set_data($posts, 5);


			$fields = $model->get_fields($posttype);

			$table->set_fields($model->get_fields($posttype, true));

			$table->add_function(PACKAGE_URL . "posts/crud/$posttype/[id]", 'Edit');
			$table->add_function(PACKAGE_URL . "posts/delete/$posttype/[id]", 'Delete');

			echo $table->render();
		} else {
			echo '<p>No Posts Found</p>';
		}
		
		echo '<a href="'.PACKAGE_URL."posts/crud/$posttype".'" class="btn btn-default">Add Post</a>';
	}
	
	public function crud($args) {
		$posttype = array_shift($args);
		$id = array_shift($args);
		$post = current(R::findOrDispense('post','id = :id', ['id' => $id]));
		$model = $this->loadModel('post_model');
		
		if($model->crud($posttype, $post)){
			Theme_Loader::get_instance()->get_cache_object()->clean('all');
			$this->redirect(PACKAGE_URL."posts/view/$posttype");
		}
	}
	
	public function delete($args) {
		$posttype = array_shift($args);
		$id = array_shift($args);
		$post = current(R::findOrDispense('post','id = :id', ['id' => $id]));
		if($post->getID()) {
			R::trash($post);
		}
		$this->redirect(PACKAGE_URL."posts/view/$posttype");
	}

}
