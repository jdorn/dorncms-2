<?php
namespace DornCMS\Admin;

use Symfony\Component\HttpFoundation\Response;
use DornCMS\Twig;

class TemplateController extends FileSystemController {
	public function editAction($request) {		
		//user must be an admin to edit a template
		if($response = $this->authorize(User::ROLE_ADMIN)) {
			return $response;
		}
		
		$template = $request->query->get('file');
		
		$editor = $this->getEditor($template,'templates/');
		
		return $this->render('admin/template/editor.html',array(
			'editor'=>array(
				'editor'=>$editor,
				'name'=>$template
			),
			'javascripts'=>$editor->getJs(),
			'stylesheets'=>$editor->getCss(),
			'ajax'=>$request->query->get('ajax',false)
		));
	}
	public function createAction($file, $type='layout') {
		
	}
	public function deleteAction($file) {
		
	}
	public function listAction($request) {	
		//user must be an admin
		if($response = $this->authorize(User::ROLE_ADMIN)) {
			return $response;
		}
		
		return $this->getFileList('/templates', $request->request->get('dir'), array('admin'));
	}
	
	public function indexAction($request) {		
		return $this->render('admin/template/index.html',array(
			
		));
	}
	
	public function createRevisionAction($file, $contents) {
		
	}
	public function deleteRevisionAction($file, $revision) {
		
	}
	public function diffRevisionAction($file, $revision) {
		
	}
	public function restoreRevisionAction($file, $revision) {
		
	}
	public function listRevisionsAction($file) {
		
	}
	
	
	
	protected function getDefaultContents($type='layout') {
		
	}
}
