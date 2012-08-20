<?php
namespace DornCMS\Admin;

use Symfony\Component\HttpFoundation\Response;
use DornCMS\Controller;
use DornCMS\Twig\Parser;

class AdminController extends Controller {	
	public function loginAction() {
		//if the form was submitted
		if($this->kernel->request->request->get('username',false)) {
			$user = $this->kernel->getUser($this->kernel->request->request->get('username'));
			
			//if the login was successful
			if($user->authenticate($request->request->get('password'))) {
				//get page to redirect to
				$path = $this->kernel->request->getSession()->get('login_redirect',$this->getUrl('cms_home'));
				
				return $this->redirect($path);
			}
			
		}
		
		return $this->render('admin/login.html',array());
	}
}
