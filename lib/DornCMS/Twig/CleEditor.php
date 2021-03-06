<?php
namespace DornCMS\Twig;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface for a template editor
 * 
 * @author  Jeremy Dorn <jeremy@jeremydorn.com>
 */
class CleEditor implements EditorInterface
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
		<div style="height: 100%; width: 100%;">
		<textarea id="'.$this->id.'" name="'.$this->id.'">'.$this->source.'</textarea>
		</div>
		<script type="text/javascript">
			$("#'.$this->id.'").cleditor({width:"100%", height:"100%"});
		</script>';
	}
	
	public function getCss() {
		return array(
			'jquery.cleditor.css',
		);
	}
	
	public function getJs() {
		return array(
			'jquery-1.8.0.min.js',
			'cle/jquery.cleditor.min.js',
		);
	}
	
	public function getSource(Request $request) {
		return $request->request->get($this->id);
	}
}
