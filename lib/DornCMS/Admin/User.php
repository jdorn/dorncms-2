<?php
namespace DornCMS\Admin;

class User {
	protected $username;
	protected $password;
	protected $salt;
	protected $roles;
	
	const ROLE_USER = 'ROLE_EDITOR';
	const ROLE_DESIGNER = 'ROLE_DESIGNER';
	const ROLE_ADMIN = 'ROLE_ADMIN';
	
	public function __construct($username, $config) {
		$this->username = $username;
		//passwords are stored in the format salt:hash
		list($this->salt,$this->password) = explode(':',$config['password'],2);
		if(!is_array($config['roles'])) $config['roles'] = array($config['roles']);
		$this->roles = $config['roles'];
	}
	
	public function authenticate($password) {
		$check = self::hash($password,$this->salt);
		
		return ($check === $this->password);
	}
	
	public function authorize($role) {
		if(is_array($role)) {
			foreach($role as $r) {
				if($this->authorize($r)) return true;
			}
			return false;
		}
		
		return in_array($role, $this->roles, true);
	}
	
	public static function hash($password, $salt) {
		//salt the password
		$password = $password.$salt;
		
		//run it through sha512 10 times
		for($i=0;$i<10;$i++) {
			$password = hash('sha512',$password,true);
		}
		
		//base64 encode the result (should make it around 88 characters long)
		$password = base64_encode($password);
		
		return $password;
	}
}
