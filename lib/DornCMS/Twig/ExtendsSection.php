<?php
namespace DornCMS\Twig;

/**
 * Represents a Twig section
 * 
 * @author  Jeremy Dorn <jeremy@jeremydorn.com>
 */
class ExtendsSection extends Section
{	
	public function __construct($source, $body) {
		parent::__construct($source, $body, '');
	}
	
	public function canRename() {
		return false;
	}
	
	public function getTitle() {
		return 'Parent Template';
	}
	
	public function getEditor() {
		return new TextInputEditor(preg_replace('/[^a-zA-Z0-9_\-]*/','',$this->getTitle()), $this->getBody());
	}
	
	protected function generateSource() {
		return '{% extends '.$this->body.' %}';
	}
}
