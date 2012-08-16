<?php
namespace DornCMS;

use Symfony\Component\HttpFoundation\Response;
use DornCMS\Twig\Parser;

class PageController {
	public function view($template, $route) {
		$loader = new \Twig_Loader_Filesystem(__DIR__.'/../../templates');
		$twig = new \Twig_Environment($loader, array(
			//'cache' => '/tmp/twig_cache',
		));

		$response = new Response($twig->render($template.'.twig', $route),200);
		
		return $response;
	}
	
	public function edit($route) {
		$loader = new \Twig_Loader_Filesystem(__DIR__.'/../../templates');
		$twig = new \Twig_Environment($loader, array(
			//'cache' => '/tmp/twig_cache',
		));
		
		$contents = file_get_contents(__DIR__.'/../../templates/'.$route['template'].'.twig');
		
		//if this contains blocks, use the Ace editor, otherwise, use WYSIWYG.
		if(preg_match('/\{\%\s*block/',$contents)) {
			$editor = new Twig\AceEditor(preg_replace('/[^a-zA-Z0-9\-_]/','',$route['template']),$contents);
		}
		else {
			$editor = new Twig\CleEditor(preg_replace('/[^a-zA-Z0-9\-_]/','',$route['template']),$contents);
		}
		
		$response = new Response($twig->render('admin/edit_template.html.twig',array(
			'template'=>array(
				'editor'=>$editor,
				'name'=>$route['template'],
			),
				
			'javascripts'=>$editor->getJs(),
			'stylesheets'=>$editor->getCss()
		)));
		
		
		return $response;
	}
}
