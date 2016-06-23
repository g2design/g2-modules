<?php

class Index_MVC_Controller extends G2_TwigController{
	/**
	 *
	 * @var Meta_Generator
	 */
	var $meta;
	public function __before($params,$action = false) {
		G2_User::init();
		$page = '';
		$page = implode('/', $params);
		if(!$page){
			$page = 'index';
		}
		if($action != 'index'){
			$page = $action;
		}

		$config = Package_Admin::getInstance()->get_config('meta.ini');

		$this->meta = new Meta_Generator($config);

		$this->template->meta_title = $this->meta->get_title($page);
		$this->template->meta_description = $this->meta->get_description($page);

		if(!G()->logged_in() && $page != 'login'){
			$this->redirect($this->get_package_uri(true).'login');
		}

		parent::__before();
	}

	function __construct() {
		G2_User::init();
		parent::__construct();
		$this->template = new G2_TwigTemplate('templates/default.twig');
		$this->template->user = G()->get_user();
		$this->template->admin_url = $this->get_package_uri(true);
		$packages = MVC_Router::getInstance()->get_packages_loaded();
		foreach($packages as $key => $package){
			if(!method_exists($package, 'get_admin_controller')){
				unset($packages[$key]);
				continue;
			}
			if (method_exists($package, 'get_permission') && !Permission::has_permission($package->get_permission())){
				unset($packages[$key]);
				continue;
			}
			$packages[$key]->link = $this->get_package_uri(true).'package/'.$packages[$key]->name;
		}
		$this->template->packages = $packages;

		$this->template->alert = Admin_Alert::get_instance();
	}

	function index($params){
		$page = 'index';

		$view = new G2_TwigView('pages/'.$page);
//
		$packages = MVC_Router::getInstance()->get_packages_loaded();
		foreach($packages as $key => $package){
			if(!(method_exists($package, 'get_admin_controller') && method_exists($package, 'get_dashboard_widget'))){
				unset($packages[$key]);
				continue;
			}

			if (method_exists($package, 'get_permission') && !Permission::has_permission($package->get_permission())){
				unset($packages[$key]);
				continue;
			}
			$packages[$key]->link = $this->get_package_uri(true).'package/'.$packages[$key]->name;
			$packages[$key]->widget = $package->get_dashboard_widget($this->get_package_uri(true)."package/$package->name/");
		}
		$view->set('packages' , $packages);
		$view->render();
	}

	function package($args){
		$package_name = array_shift($args);
		$package = MVC_Router::getInstance()->get_package_for($package_name);
		if($package){
			if (method_exists($package, 'get_permission') && !Permission::has_permission($package->get_permission())){
				$this->redirect(ADMIN_URL);
			}
			$controller = $package->get_admin_controller();
			define('PACKAGE_URL',$this->get_package_uri(true)."package/$package_name/");
			$package->set_control_dir($package->get_admin_control_dir());
			$package->set_admin_defaults();

			echo $package->auto_route($args);
//			echo call_user_func_array(array($package, $controller), $args);
		}
	}

	function login(){
	    $this->template->set_template_file('templates/signup.twig');
	    $view = new G2_TwigView('pages/login');
		$login_form = new G2_TwigView('forms/login');
		$login_form = new G2_FormMagic($login_form->get_render());
		G()->create_user_if_not_exist('admin', 'g2design123');
		if($login_form->is_posted() && $login_form->validate()){
			$data = $login_form->data();
			$username = $data['username'];
			$password = $data['password'];
			if($user = G()->check_login($username, $password)){
				G()->log_in_user($user);
				$this->redirect($this->get_package_uri().'index');
			} else {
				$login_form->invalidate('username', 'Login Fails. Please check your details and try again');
			}
		}





		$login_form = $login_form->parse();
		$view->set('login_form',$login_form);
	    $view->render();
	}

	public function logout(){
		session_destroy();
		$this->redirect($this->get_package_uri(true));
	}

}