<?php
namespace DornCMS\Twig;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface for a template editor
 * 
 * @author  Jeremy Dorn <jeremy@jeremydorn.com>
 */
class TextInputEditor implements EditorInterface
{
	protected $source;
	protected $options;
	protected $id;
	
	public function __construct($id, $source, $options=array()) {
		$this->id = 'text_input_'.$id;
		$this->source = $source;
		$this->options = $options;
	}
	
	public function getEditorHtml() {
		return '<input type="text" id="'.$this->id.'" name="'.$this->id.'" value="'.htmlentities($this->source).'" />';
	}
	
	public function getCss() {
		return array();
	}
	
	public function getJs() {
		return array();
	}
	
	public function getSource(Request $request) {
		return $request->request->get($this->id);
	}
}
