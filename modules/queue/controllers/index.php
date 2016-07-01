<?php
namespace Queue;

use G2;

class indexController extends G2\Controller {
	public function index() {
		print('Starting cli Call');
	}
	
	public function run() {
		\Queuer::execute(50);
	}
}