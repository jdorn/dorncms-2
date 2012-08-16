<?php
namespace DornCMS\Twig;

/**
 * Represents a Twig section
 * 
 * @author  Jeremy Dorn <jeremy@jeremydorn.com>
 */
class BlockSection extends Section
{		
	public function getTitle() {
		return ucwords(trim($this->getName(),'"')).' Block';
	}
	
	public function canRename() {
		return true;
	}
	
	protected function generateSource() {
		return '{% block '.$this->getName().' %}'."\n".$this->getBody()."\n".'{% endblock '.$this->getName().' %}';
	}
}
