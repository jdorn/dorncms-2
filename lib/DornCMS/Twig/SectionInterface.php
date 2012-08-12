<?php
namespace DornCMS\Twig;

/**
 * Represents a Twig section
 * 
 * @author  Jeremy Dorn <jeremy@jeremydorn.com>
 */
interface SectionInterface
{
	const TYPE_TEXT_INPUT = 'text';
	const TYPE_TEXTAREA = 'textarea';
	const TYPE_WYSIWYG = 'wysiwyg';
	
	public function getSource();
	
	public function getName();
	
	public function setName($name);
	
	public function getBody();
	
	public function setBody($body);
	
	public function getTitle();
	
	public function canRename();
	
	public function getEditorType();
}
