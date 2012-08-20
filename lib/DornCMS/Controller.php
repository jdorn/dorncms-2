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
	
	
	
	
	
	protected function render($template,$params) {
		$loader = new \Twig_Loader_Filesystem(__DIR__.'/../../templates');
		$twig = new \Twig_Environment($loader, array(
			//'cache' => '/tmp/twig_cache',
		));
		
		return new Response($twig->render($template.'.twig',$params));
	}
	protected function redirect($location,$permanent=false) {
		return new RedirectResponse($location,$permanent ? 301 : 302);
	}
	protected function authorize($role) {
		//if user isn't authenticated, redirect to login page
		if(!$this->kernel->request->getSession()->get('dorncms_username',false)) {
			//store this path in the session, so we know what to go back to after loggin in
			$this->kernel->request->getSession()->set('login_redirect',$this->kernel->request->getPathInfo());
			
			//redirect to login page
			return $this->redirect($this->kernel->getUrl('login'));
		}
		
		//if user isn't authorized to view the page, return a 403
		$user = $this->kernel->getUser($this->kernel->request->getSession()->get('dorncms_username'));
		if(!$user->authorize($role)) {
			return new Response('You are not authorized to view this page',403);
		}
	}
}
