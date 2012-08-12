<?php

namespace DornCMS;

use Symfony\Component\ClassLoader\UniversalClassLoader;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Kernel {
	public $config;
	public $request;
	
	public function __construct() {
		require_once __DIR__."/../Symfony/Component/ClassLoader/UniversalClassLoader.php";
		
		$loader = new UniversalClassLoader();
		$loader->register();
		
		//Library autoloaders
		$loader->registerNamespace('Symfony', __DIR__.'/..');
		$loader->registerNamespace('DornCMS',__DIR__.'/..');
		$loader->registerPrefix('Twig_',__DIR__.'/../Twig/lib');
		
		//SessionHandlerInterface
		if (!interface_exists('SessionHandlerInterface')) {
			$loader->registerPrefixFallback(__DIR__.'/../Symfony/Component/HttpFoundation/Resources/stubs');
		}
		
		$this->config = $this->getConfig();
		$this->request = $this->getRequest();
	}
	
	public function run() {
		//determine the controller to use for the route
		$route = $this->route();
		
		$controller_parts = explode(':',$route['_controller']);
		
		$action = array_pop($controller_parts);
		
		$class = implode('\\',$controller_parts);
		
		if(class_exists($class)) {
			$controller = new $class();
			
			if(method_exists($controller,$action)) {
				$response = $controller->{$action}($route);
			}
			else {
				throw new \Exception("Unknown method $action of class $controller");
			}
		}
		else {
			throw new \Exception("Unknown class $controller");
		}
		
		$response->send();
	}
	
	protected function getConfig() {
		//get the main config settings
		$config = Yaml::parse(__DIR__.'/../../config/config.yml');
		
		//load the routing settings
		$config['routing'] = Yaml::parse(__DIR__.'/../../config/routing.yml');
		if(!$config['routing']) $config['routes'] = array();
		
		//load the security settings
		$config['security'] = Yaml::parse(__DIR__.'/../../config/security.yml');
		if(!$config['security']) $config['security'] = array();
		
		//load app parameters
		$config['parameters'] = Yaml::parse(__DIR__.'/../../config/parameters.yml');
		if(!$config['parameters']) $config['parameters'] = array();
		
		//load admin routing rules
		$admin_routes = Yaml::parse(__DIR__.'/../../config/admin_routing.yml');
		if(!$admin_routes) $admin_routes = array();
		$config['routing'] = array_merge($config['routing'],$admin_routes);
		
		return $config;
	}
	public function route() {
		$routes = new RouteCollection();
		
		//add routes from config
		foreach($this->config['routing'] as $name=>$route) {
			if(!isset($route['defaults'])) $route['defaults'] = array();
			if(!isset($route['requirements'])) $route['requirements'] = array();
			if(!isset($route['options'])) $route['options'] = array();
			
			$routes->add($name, new Route($route['pattern'], $route['defaults'],$route['requirements'],$route['options']));
		}
		
		$context = new RequestContext();
		$context->fromRequest($this->request);

		$matcher = new UrlMatcher($routes, $context);

		$path = array_shift(explode('?',$this->request->getRequestUri()));

		$parameters = $matcher->match($this->request->getPathInfo());
		
		return $parameters;
	}
	protected function getRequest() {
		return Request::createFromGlobals();
	}
	public function getResponse($response='',$code=200, $headers = array()) {
		return new Response($response,$code,$headers);
	}
}
