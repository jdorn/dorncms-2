<?php

namespace DornCMS;

use Symfony\Component\ClassLoader\UniversalClassLoader;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Session;

class Kernel {
	public $config;
	public $request;
	
	public $routeCollection;
	public $requestContext;
	
	public function __construct() {
		require_once __DIR__."/../Symfony/Component/ClassLoader/UniversalClassLoader.php";
		
		$loader = new UniversalClassLoader();
		$loader->register();
		
		//Library autoloaders
		$loader->registerNamespace('Symfony', __DIR__.'/..');
		$loader->registerNamespace('DornCMS',__DIR__.'/..');
		$loader->registerNamespace('FrontEnd',__DIR__.'/../../src');
		$loader->registerPrefix('Twig_',__DIR__.'/../Twig/lib');
		
		//SessionHandlerInterface
		if (!interface_exists('SessionHandlerInterface')) {
			$loader->registerPrefixFallback(__DIR__.'/../Symfony/Component/HttpFoundation/Resources/stubs');
		}
		
		$this->init();
	}
	
	public function run() {
		//determine the controller to use for the route
		$route = $this->urlMatcher->match($this->request->getPathInfo());
		
		//example route: Namespace:Controller:action
		$controller_parts = explode(':',$route['_controller']);
		
		$action = array_pop($controller_parts).'Action';
		
		$class = implode('\\',$controller_parts);
		
		if(class_exists($class)) {
			$controller = new $class($this);
			
			if(method_exists($controller,$action)) {
				//determine the parameters that should be passed into the method
				$reflectionMethod = new \ReflectionMethod($controller,$action);
				$parameters = $reflectionMethod->getParameters();
				$call_array = array();
				foreach($parameters as $parameter) {
					$name = $parameter->getName();
					
					if($name === 'attributes') {
						$call_array[] = $route;
					}
					elseif($name === 'request') {
						$call_array[] = $this->request;
					}
					elseif(isset($route[$name])) {
						$call_array[] = $route[$name];
					}
					elseif($parameter->isDefaultValueAvailable()) {
						$call_array[] = $parameter->getDefaultValue();
					}
					else {
						throw new \Exception("Unknown parameter '$name' in '$class::$action'");
					}
				}
				
				$response = call_user_func_array(array($controller,$action),$call_array);
			}
			else {
				throw new \Exception("Unknown method '$action' of class '$class'");
			}
		}
		else {
			throw new \Exception("Unknown class $class");
		}
		
		$response->send();
	}
	
	
	protected function init() {
		$this->getConfig();
		$this->getRequest();
		
		//instantiate the Twig environment and load the DornCMS extensions
		$loader = new \Twig_Loader_Filesystem(__DIR__.'/../../templates');
		$this->twig = new \Twig_Environment($loader, array(
			//'cache' => '/tmp/twig_cache',
		));
		$dorncms_twig_extensions = new Twig\Extensions($this);
		$this->twig->addExtension($dorncms_twig_extensions);
		$this->twig->addGlobal('app', $this);
		
		//initialize routes
		$routes = new RouteCollection();
		$admin_routes = new RouteCollection();
		
		//add front-end routes from config
		foreach($this->config['routing'] as $name=>$route) {
			if(!isset($route['defaults'])) $route['defaults'] = array();
			if(!isset($route['requirements'])) $route['requirements'] = array();
			if(!isset($route['options'])) $route['options'] = array();
			
			$routes->add($name, new Route($route['pattern'], $route['defaults'],$route['requirements'],$route['options']));
		}
		
		//add back-end routes from config
		foreach($this->config['admin_routing'] as $name=>$route) {
			if(!isset($route['defaults'])) $route['defaults'] = array();
			if(!isset($route['requirements'])) $route['requirements'] = array();
			if(!isset($route['options'])) $route['options'] = array();
			
			$admin_routes->add($name, new Route($route['pattern'], $route['defaults'],$route['requirements'],$route['options']));
		}
		
		//make all admin routes have the same prefix
		$routes->addCollection($admin_routes,'/admin');
		
		$context = new RequestContext();
		$context->fromRequest($this->request);

		$this->urlGenerator = new UrlGenerator($routes, $context);
		$this->urlMatcher = new UrlMatcher($routes, $context);
	}
	public function getUrl($name, $params=array()) {
		return $this->request->getUriForPath($this->getPath($name, $params));
	}
	public function getPath($name, $params=array()) {
		return $this->urlGenerator->generate($name, $params);
	}
	public function getAssetVersion() {
		return isset($this->config['asset_version'])? $this->config['asset_version'] : '1';
	}
	
	public function getSession() {
		return $this->request->getSession();
	}
	
	public function getRequest() {
		if($this->request) return $this->request;
		
		$request = Request::createFromGlobals();
		$request->setSession(new Session());
		
		$this->request = $request;
		
		return $request;
	}
	public function getConfig() {
		if($this->config) return $this->config;
		
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
		$config['admin_routing'] = Yaml::parse(__DIR__.'/../../config/admin_routing.yml');
		if(!$config['admin_routing']) $config['admin_routing'] = array();
		
		$this->config = $config;
		
		return $config;
	}
}
