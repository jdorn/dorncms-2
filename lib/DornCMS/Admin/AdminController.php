<?php
namespace DornCMS\Admin;

use Symfony\Component\HttpFoundation\Response;
use DornCMS\Controller;
use DornCMS\Twig\Parser;

class AdminController extends Controller {	
	public function indexAction() {
		return $this->render('admin/home.html');
	}
	
	public function loginAction() {
		//template params
		$params = array();
		
		//if the form was submitted
		if($this->kernel->request->request->get('username',false)) {
			try {
				$user = $this->kernel->getUser($this->kernel->request->request->get('username'));
				
				//if the login was successful
				if($user->authenticate($this->kernel->request->request->get('password'))) {
					//store username in session
					$this->kernel->request->getSession()->set('dorncms_username',$this->kernel->request->request->get('username'));
					
					//get page to redirect to
					$path = $this->kernel->request->getSession()->get('login_redirect',$this->kernel->getUrl('cms_home'));
					
					return $this->redirect($path);
				}
				else {
					throw new \Exception("Incorrect username or password");
				}
			}
			catch(\Exception $e) {
				$params['error'] = $e;
			}
		}
		
		return $this->render('admin/login.html',$params);
	}
	
	public function logoutAction() {
		$this->kernel->request->getSession()->set('dorncms_username',null);
		
		return $this->redirect($this->kernel->getUrl('home'));
	}
}
