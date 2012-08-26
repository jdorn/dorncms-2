<?php
namespace DornCMS;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use DornCMS\Twig\Parser;

class Controller {
	protected $kernel;
	
	public function __construct($kernel) {
		$this->kernel = $kernel;
	}
	
	public function redirectAction($route, $attributes, $permanent=false) {
        if (!$route) {
            return new Response(null, 410);
        }
        
        unset($attributes['route'], $attributes['permanent'], $attributes['_route']);

		return $this->redirect($this->kernel->getUrl($route,$attributes),$permanent);
	}
	
	
	
	
	protected function getUrl($name, $params) {
		return $this->kernel->getUrl($name,$params);
	}
	protected function getPath($name, $params) {
		return $this->kernel->getPath($name,$params);
	}
	protected function render($template,$params=array()) {		
		return new Response($this->kernel->twig->render($template.'.twig',$params));
	}
	protected function redirect($location,$permanent=false) {
		return new RedirectResponse($location,$permanent ? 301 : 302);
	}
}
