<?php
namespace DornCMS;

use Symfony\Component\HttpFoundation\Response;

class PageController {
	public function view($route) {
		$loader = new \Twig_Loader_Filesystem(__DIR__.'/../../templates');
		$twig = new \Twig_Environment($loader, array(
			//'cache' => '/tmp/twig_cache',
		));

		$response = new Response($twig->render($route['template'].'.twig', $route),200);
		
		return $response;
	}
}
