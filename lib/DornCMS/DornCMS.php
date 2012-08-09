<?php
class DornCMS {
	public $config;
	public $request;
	
	public function __construct() {
		require_once __DIR__."/../Symfony/Component/ClassLoader/UniversalClassLoader.php";
		
		$loader = new Symfony\Component\ClassLoader\UniversalClassLoader();
		$loader->register();
		
		//Register all Symfony Components
		$loader->registerNamespace('Symfony', __DIR__.'/..');
		
		//SessionHandlerInterface
		if (!interface_exists('SessionHandlerInterface')) {
			$loader->registerPrefixFallback(__DIR__.'/../Symfony/Component/HttpFoundation/Resources/stubs');
		}
		
		$this->config = $this->getConfig();
		$this->request = $this->getRequest();
	}
	
	public function run() {
		//determine the controller to use for the route
		$controller = $this->route();
		
		//prepare a response
		$response = $this->getResponse("This is a test");
		
		$response->send();
	}
	
	protected function getConfig() {
		//get the main config settings
		$config = Symfony\Component\Yaml\Yaml::parse(__DIR__.'/../../config/config.yml');
		
		//load the routing settings
		$config['routing'] = Symfony\Component\Yaml\Yaml::parse(__DIR__.'/../../config/routing.yml');
		if(!$config['routing']) $config['routes'] = array();
		
		//load the security settings
		$config['security'] = Symfony\Component\Yaml\Yaml::parse(__DIR__.'/../../config/security.yml');
		if(!$config['security']) $config['security'] = array();
		
		//load app parameters
		$config['parameters'] = Symfony\Component\Yaml\Yaml::parse(__DIR__.'/../../config/parameters.yml');
		if(!$config['parameters']) $config['parameters'] = array();
		
		//load admin routing rules
		$admin_routes = Symfony\Component\Yaml\Yaml::parse(__DIR__.'/../../config/admin_routing.yml');
		if(!$admin_routes) $admin_routes = array();
		$config['routing'] = array_merge($config['routing'],$admin_routes);
		
		return $config;
	}
	public function route() {
		$routes = new Symfony\Component\Routing\RouteCollection();
		
		//add routes from config
		foreach($this->config['routing'] as $name=>$route) {
			if(!isset($route['defaults'])) $route['defaults'] = array();
			if(!isset($route['requirements'])) $route['requirements'] = array();
			if(!isset($route['options'])) $route['options'] = array();
			
			$routes->add($name, new Symfony\Component\Routing\Route($route['pattern'], $route['defaults'],$route['requirements'],$route['options']));
		}
		
		$context = new Symfony\Component\Routing\RequestContext();
		$context->fromRequest($this->request);

		$matcher = new Symfony\Component\Routing\Matcher\UrlMatcher($routes, $context);

		$path = array_shift(explode('?',$this->request->getRequestUri()));

		$parameters = $matcher->match($this->request->getPathInfo());
		
		return $parameters;
	}
	protected function getRequest() {
		return Symfony\Component\HttpFoundation\Request::createFromGlobals();
	}
	public function getResponse($response='',$code=200, $headers = array()) {
		return new Symfony\Component\HttpFoundation\Response($response,$code,$headers);
	}
}
