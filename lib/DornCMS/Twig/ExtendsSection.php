<?php
namespace DornCMS\Twig;

/**
 * Represents a Twig section
 * 
 * @author  Jeremy Dorn <jeremy@jeremydorn.com>
 */
class ExtendsSection implements SectionInterface
{
	protected $source;
	protected $body;
	protected $name;
	
	public function __construct($source, $body) {
		$this->source = $source;
		$this->name = trim($body,'"\' ');
		$this->body = $body;
	}
	
	public function getSource() {
		return $this->source;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function setName($name) {
		
	}
	
	public function getBody() {
		return $this->body;
	}
	
	public function setBody($body) {
		$this->body = $body;
		$this->name = trim($body,'"\' ');
		$this->source = $this->generateSource();
	}
	
	public function getTitle() {
		return "Parent Template";
	}
	
	public function canRename() {
		return false;
	}
	
	public function getEditorType() {
		return self::TYPE_TEXT_INPUT;
	}
	
	protected function generateSource() {
		return '{% extends '.$this->body.' %}';
	}
}
