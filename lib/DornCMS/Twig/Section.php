<?php
namespace DornCMS\Twig;

/**
 * Represents a Twig section
 * 
 * @author  Jeremy Dorn <jeremy@jeremydorn.com>
 */
class Section implements SectionInterface
{
	protected $source;
	protected $body;
	protected $name;
	
	public function __construct($source, $body, $name) {
		$this->source = $source;
		$this->body = $body;
		$this->name = $name;
	}
	
	public function getSource() {
		return $this->source;
	}
	public function getBody() {
		return $this->body;
	}
	public function getName() {
		return $this->name;
	}
	public function getTitle() {
		return $this->name;
	}
	
	public function setBody($body) {
		$this->body = $body;
		$this->source = $this->generateSource();
	}
	public function setName($name) {
		$this->name = $name;
		$this->source = $this->generateSource();
	}
	
	public function canRename() {
		return false;
	}
	public function getEditor() {
		return new AceEditor(preg_replace('/[^a-zA-Z0-9_\-]*/','',$this->getTitle()), $this->getBody());
	}
	
	protected function generateSource() {
		return $this->body;
	}
}
