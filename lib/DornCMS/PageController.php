<?php
namespace DornCMS;

use Symfony\Component\HttpFoundation\Response;
use DornCMS\Twig\Parser;

class PageController {
	public function view($route) {
		$loader = new \Twig_Loader_Filesystem(__DIR__.'/../../templates');
		$twig = new \Twig_Environment($loader, array(
			//'cache' => '/tmp/twig_cache',
		));

		$response = new Response($twig->render($route['template'].'.twig', $route),200);
		
		return $response;
	}
	
	public function edit($route) {
		$loader = new \Twig_Loader_Filesystem(__DIR__.'/../../templates');
		$twig = new \Twig_Environment($loader, array(
			//'cache' => '/tmp/twig_cache',
		));
		
		$template = $loader->getSource($route['template'].'.twig');
		
		$stream = $twig->tokenize($template);
		
		$twig->setParser(new Parser());
		
		$return = $twig->parse($stream);
		
		$response = '';
		
		foreach($return as $section) {
			if($section instanceof Twig\SectionInterface) {
				$response .= "<h2>".$section->getTitle().
					($section->canRename()? "<a href='#' style='font-size: .6em; margin-left: 20px; font-weight:normal;'>change name</a>" : "").
					"</h2>";
				
				if($section->getEditorType() === Twig\SectionInterface::TYPE_TEXT_INPUT) {
					$response .= "<input type='text' style='width: 80%;' value='".htmlentities($section->getBody())."' />";
				}
				elseif($section->getEditorType() === Twig\SectionInterface::TYPE_TEXTAREA) {
					$response .= "<textarea style='width: 80%; height: 200px;'>".htmlentities($section->getBody())."</textarea>";
				}
				elseif($section->getEditorType() === Twig\SectionInterface::TYPE_WYSIWYG) {
					$response .= "<textarea style='width: 80%; height: 200px;'>".htmlentities($section->getBody())."</textarea>";
				}
			}
			else {
				$response .= "<pre>".print_r($section,true)."</pre>";
			}
		}
		
		return new Response($response);
	}
}
