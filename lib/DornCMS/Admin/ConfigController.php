<?php
namespace DornCMS\Admin;

use Symfony\Component\HttpFoundation\Response;
use DornCMS\Twig;

class ConfigController extends FileSystemController {
	public function editAction($request) {
		$file = $request->query->get('file');
		
		//user must be an admin to edit a config file
		if($response = $this->authorize(User::ROLE_ADMIN)) {
			return $response;
		}
		
		$editor = $this->getEditor($file,'config/');
		
		return $this->render('admin/config/edit.html',array(
			'file'=>array(
				'editor'=>$editor,
				'name'=>$file
			),
			'javascripts'=>$editor->getJs(),
			'stylesheets'=>$editor->getCss(),
		));
	}
}
