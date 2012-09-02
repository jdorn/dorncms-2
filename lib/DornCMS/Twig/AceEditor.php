<?php
namespace DornCMS\Twig;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface for a template editor
 * 
 * @author  Jeremy Dorn <jeremy@jeremydorn.com>
 */
class AceEditor implements EditorInterface
{
	protected $source;
	protected $options;
	protected $id;
	
	public function __construct($id, $source, $options=array()) {
		$this->id = 'ace_editor_'.$id;
		$this->source = $source;
		$this->options = $options;
	}
	
	public function getEditorHtml() {
		return '
		<div id="'.$this->id.'" style="position:relative; width: 100%; height: 100%;">'.htmlentities($this->source).'</div>
		<script type="text/javascript">
			var '.$this->id.' = ace.edit("'.$this->id.'");
			'.$this->id.'.setTheme("ace/theme/github");
			'.$this->id.'.getSession().setMode("ace/mode/'.$this->getLanguage().'");
		</script>';
	}
	
	public function setLanguage($language) {
		$this->options['language'] = $language;
	}
	public function getLanguage() {
		if(isset($this->options['language'])) return $this->options['language'];
		else return 'text';
	}
	
	public function getCss() {
		return array();
	}
	
	public function getJs() {
		return array(
			'ace/ace.js'
		);
	}
	
	public function getSource(Request $request) {
		return $request->request->get($this->id);
	}
}
