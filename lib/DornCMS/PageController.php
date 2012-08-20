<?php
namespace DornCMS;

class PageController extends Controller {
	public function viewAction($template, $attributes) {
		unset($attributes['template'],$attributes['_controller']);
		
		return $this->render($template,$attributes);
	}
}
