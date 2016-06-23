<?php

use Wa72\HtmlPageDom\HtmlPageCrawler;

class Theme_Module_Posts extends Mvc_Base implements Theme_Module_Interface {

	var $html = null;
	var $posts = [];

	public function __construct($id, $page_id = false) {
		;
	}

	public function render() {
		$c = HtmlPageCrawler::create($this->html);

		$this->process_html();
		$c = HtmlPageCrawler::create($this->html);
		$table = $c->filter('attribute[key="post-type"]')->text();
		$look_dir = 'modules/posts/';
		$template_name = $table.'-page.twig';
		
		if(Theme_Loader::get_instance()->getTwigLoader()->exists($look_dir.$template_name)){
			$template = $look_dir.$template_name;
		} else {
			$template = $look_dir.'post-page.twig';
		}
		return Theme_Loader::get_instance()->render_file($template, ['posts' => $this->posts]);
	}

	function process_html() {
		//Find the post table name
		$c = HtmlPageCrawler::create($this->html);

		$model = $this->loadModel('post_model'); /* @var $model Post_Model */

		$table = $c->filter('attribute[key="post-type"]')->text();
		$this->posts = $model->get_posts($table);


		$fields = $c->filter('field');
		foreach ($fields as $field) {
			$fieldname = $field->getAttribute('name');
			$type = $field->getAttribute('type') ? $field->getAttribute('type') : 'text';
			$show = $field->getAttribute('show') == 'true' ? true : false;
			$model->create_field($table, $fieldname, $type, $show);
		}
	}

	public function set_module_xml($html) {
		$this->html = $html;
	}

}
