<?php

namespace Route;

use SuperClosure\Serializer;
use R;

class Router {

	function create_route($slug, $callable, $allow_params = false) {
		// Create the route in the database
		//Serialize the callable object
		$serializer = new Serializer();

		$s_closure = $serializer->serialize($callable);
		$hash = md5($s_closure);
		
		if(R::findOne('route','hash = :hash',['hash' => $hash])) {
			return;
		}

		$route = current(R::findOrDispense('route', 'slug = :slug', ['slug' => $slug]));
		
		$route->hash = $hash;
		$route->slug = $slug;
		$route->closure = $s_closure;
		$route->allow_params = $allow_params;

		R::store($route);
		// Store
	}

	function routable($slug) {
		//Check if a route is stored for given slug
		$route = $this->load_route($slug);

		if ($route) {
			return true;
		}

		return false;
	}
	
	function load_route($slug) {
		
		$route = R::findOne('route', "slug = :s", ['s' => $slug]);
		if(!$route) {
			$parts = explode('/', $slug);
			while(count($parts) > 0) {
				array_pop($parts);
				$n_slug = implode('/', $parts);
				$route = R::findOne('route', "slug = :s", ['s' => $n_slug]);
				
				if($route) break;
			}
		}
		return $route;
	}

	function route($slug) {
		//Load calleble object via slug
		$route = $this->load_route($slug);

		if ($route) {
			$serializer = new Serializer();

			$closure = $serializer->unserialize($route->closure);
			$params = explode('/', $slug);
			array_shift($params);
			$closure($slug , $params);
		}

		//Run calleble object render function
	}

	

}
