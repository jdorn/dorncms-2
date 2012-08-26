<?php
namespace FrontEnd;

use DornCMS\Controller;

class ContactController extends Controller {
	public function submitAction($request) {
		$from = $request->request->get('from');
		$message = $request->request->get('message');
		
		$error = false;
		
		if(strlen($message) < 10) {
			$error = true;
			$this->kernel->getSession()->setFlash('error','Your message must be at least 10 characters long');
		}
		
		if(!$error) {
			$this->kernel->getSession()->setFlash('notice','Form submitted successfully.  We will get back to you shortly.');
		}
		
		return $this->redirect($this->getUrl('contact'));
	}
}
