<?php
namespace DornCMS\Twig;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface for a template editor
 * 
 * @author  Jeremy Dorn <jeremy@jeremydorn.com>
 */
interface EditorInterface
{	
	public function getEditorHtml();
	
	public function getSource(Request $request);
	
	public function getCss();
	
	public function getJs();
}
