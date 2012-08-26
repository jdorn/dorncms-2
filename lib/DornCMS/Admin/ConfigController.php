<?php
namespace DornCMS\Admin;

use Symfony\Component\HttpFoundation\Response;
use DornCMS\Twig;

class ConfigController extends AdminController {
	public function editAction($request) {
		$file = $request->query->get('file');
		
		//user must be an admin to edit a config file
		if($response = $this->authorize(User::ROLE_ADMIN)) {
			return $response;
		}
		
		$contents = file_get_contents(__DIR__.'/../../../config/'.$file.'.yml');
		
		//if this contains blocks, use the Ace editor, otherwise, use WYSIWYG.
		$editor = new Twig\AceEditor(preg_replace('/[^a-zA-Z0-9\-_]/','',$file),$contents);
		$editor->setLanguage("yaml");
		
		return $this->render('admin/edit_config.html',array(
			'file'=>array(
				'editor'=>$editor,
				'name'=>$file
			),
			'javascripts'=>$editor->getJs(),
			'stylesheets'=>$editor->getCss(),
		));
	}
}
