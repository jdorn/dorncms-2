<?php
namespace DornCMS\Admin;

use Symfony\Component\HttpFoundation\Response;
use DornCMS\Controller;
use DornCMS\Twig\Parser;

class AdminController extends Controller {
	public function indexAction() {
		//user must be an admin
		if($response = $this->authorize(User::ROLE_ADMIN)) {
			return $response;
		}
		
		return $this->render('admin/home.html');
	}
	
	public function loginAction() {
		//template params
		$params = array();
		
		//if the form was submitted
		if($this->kernel->request->request->get('username',false)) {
			try {
				$user = $this->getUser($this->kernel->request->request->get('username'));
				
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
	
	protected function authorize($role) {
		//if user isn't authenticated, redirect to login page
		if(!$this->kernel->request->getSession()->get('dorncms_username',false)) {
			//store this path in the session, so we know what to go back to after loggin in
			$this->kernel->request->getSession()->set('login_redirect',$this->kernel->request->getUri());
			
			//redirect to login page
			return $this->redirect($this->kernel->getUrl('login'));
		}
		
		//if user isn't authorized to view the page, return a 403
		$user = $this->getUser($this->kernel->request->getSession()->get('dorncms_username'));
		if(!$user->authorize($role)) {
			return new Response('You are not authorized to view this page',403);
		}
	}
	protected function getUser($username) {
		//look up user in config
		if(isset($this->kernel->config['security']['users'][$username])) {
			return new User($username, $this->kernel->config['security']['users'][$username]);
		}
		//user not found in config (should almost never happen)
		else {
			throw new \Exception("User '$username' Not Found");
		}
	}
}
