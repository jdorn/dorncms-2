<?php
namespace DornCMS\Twig;

/**
 * Represents a Twig section
 * 
 * @author  Jeremy Dorn <jeremy@jeremydorn.com>
 */
class BlockSection implements SectionInterface
{
	protected $source;
	protected $body;
	protected $name;
	
	public function __construct($name, $source, $body) {
		$this->source = $source;
		$this->name = trim($name);
		$this->body = $body;
	}
	
	public function getSource() {
		return $this->source;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function setName($name) {
		$this->name = trim($name);
		$this->source = $this->generateSource();
	}
	
	public function getBody() {
		return $this->body;
	}
	
	public function setBody($body) {
		$this->body = $body;
		$this->source = $this->generateSource();
	}
	
	public function getTitle() {
		return 'Block "'.$this->getName().'"';
	}
	
	public function canRename() {
		return true;
	}
	
	protected function generateSource() {
		return '{% block '.$this->name.' %}'."\n".$this->body."\n".'{% endblock '.$this->name.' %}';
	}
	
	public function getEditorType() {
		return self::TYPE_WYSIWYG;
	}
}
