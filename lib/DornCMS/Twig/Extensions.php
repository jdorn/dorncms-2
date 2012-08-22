<?php
namespace DornCMS\Twig;

/**
 * Interface for a template editor
 * 
 * @author  Jeremy Dorn <jeremy@jeremydorn.com>
 */
class Extensions extends \Twig_Extension
{
	protected static $kernel;
	
	public function __construct($kernel) {
		self::$kernel = $kernel;
	}
	
	public function getName() {
		return 'dorncms_extensions';
	}
	public function getFunctions() {
		return array(
			'url'=>new \Twig_Function_Function('DornCMS\Twig\Extensions::url'),
			'path'=>new \Twig_Function_Function('DornCMS\Twig\Extensions::path'),
			'asset'=>new \Twig_Function_Function('DornCMS\Twig\Extensions::asset'),
		);
	}
	public static function url($name,$params=array()) {
		return self::$kernel->getUrl($name,$params);
	}
	public static function path($name,$params=array()) {		
		return self::$kernel->getPath($name,$params);
	}
	public static function asset($url) {
		//add cache busting query string
		if(strpos($url,'?')!==false) $url .='&';
		else $url .= '?';
		$url .= 'v='.self::$kernel->getAssetVersion();
		
		return self::$kernel->request->getBasePath().'/'.$url;
	}
}
