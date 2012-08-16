<?php
namespace DornCMS\Twig;

/**
 * Represents a Twig section
 * 
 * @author  Jeremy Dorn <jeremy@jeremydorn.com>
 */
interface SectionInterface
{	
	public function getSource();
	public function getBody();
	public function getName();
	public function getTitle();
	
	public function setBody($body);
	public function setName($name);
	
	public function canRename();
	public function getEditor();
}
