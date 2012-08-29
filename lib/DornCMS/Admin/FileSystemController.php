<?php
namespace DornCMS\Admin;

use Symfony\Component\HttpFoundation\Response;
use DornCMS\Twig;

class FileSystemController extends AdminController {
	protected function getFileContents($file, $folder='templates/') {
		$this->validateFileName($file);
		
		$filePath = $this->getFilePath($file, $folder);
		
		if(!file_exists($filePath)) throw new \Exception("Unknown file '$filePath'");
		
		return file_get_contents($filePath);
	}
	protected function putFileContents($file, $folder='templates/', $contents='') {
		$this->validateFileName($file);
		
		$filePath = $this->getFilePath($file, $folder);
		
		//make the directory if it doesn't exist
		if(!file_exists(dirname($filePath))) {
			mkdir(dirname($filePath,0777,true));
		}
		
		file_put_contents($filePath, $contents);
	}
	protected function getFilePath($file, $folder='templates/') {
		return __DIR__.'/../../../'.$folder.$file;
	}
	
	protected function getEditor($file, $folder='templates/') {
		$contents = $this->getFileContents($file, $folder);
		
		//if this is a Yaml file, use Ace
		$ext = array_pop(explode('.',$file));
		if($ext === 'yml') {
			$editor = new Twig\AceEditor(preg_replace('/[^a-zA-Z0-9\-_]/','',$file),$contents);
			$editor->setLanguage('yaml');
		}
		//javascript
		elseif($ext === 'js') {
			$editor = new Twig\AceEditor(preg_replace('/[^a-zA-Z0-9\-_]/','',$file),$contents);
			$editor->setLanguage('javascript');
		}
		//css
		elseif($ext === 'css') {
			$editor = new Twig\AceEditor(preg_replace('/[^a-zA-Z0-9\-_]/','',$file),$contents);
			$editor->setLanguage('css');
		}
		//if this contains blocks, use the Ace editor
		elseif(preg_match('/\{\%\s*block/',$contents)) {
			$editor = new Twig\AceEditor(preg_replace('/[^a-zA-Z0-9\-_]/','',$file),$contents);
			$editor->setLanguage('html');
		}
		//use a WYSIWYG editor.  Simple HTML
		else {
			$editor = new Twig\CleEditor(preg_replace('/[^a-zA-Z0-9\-_]/','',$file),$contents);
		}
		
		return $editor;
	}
	protected function validateFileName($file) {
		//standardize directory separator
        $file = preg_replace('#/{2,}#', '/', strtr($file, '\\', '/'));
		
		//make sure the file doesn't go up a level
		$level = 0;
		$parts = explode('/',$file);
		foreach($parts as $part) {
			if($part === '.') {
				continue;
			}
			elseif($part === '..') {
				$level --;
			}
			else {
				$level ++;
			}
			
			if($level < 0) throw new \Exception("Tried to access file '$file' outside of configured directories.");
		}
	}
	
	protected function getFileList($root, $directory, $skip = array()) {
		$root = __DIR__.'/../../..' . $root;
		
		if (false !== strpos($directory, "\0")) {
			throw new \Exception('A file path cannot contain NUL bytes.');
		}
		
        $directory = preg_replace('#/{2,}#', '/', strtr($directory, '\\', '/'));
		$parts = explode('/', $directory);
		$level = 0;
		foreach ($parts as $part) {
			if ('..' === $part) {
				--$level;
			} elseif ('.' !== $part) {
				++$level;
			}

			if ($level < 0) {
				throw new \Exception(sprintf('Cannot load path outside configured directories (%s).', $directory));
			}
		}

		$params = array(
			'files'=>array()
		);
		
		if( file_exists($root . $directory) ) {
			$files = scandir($root . $directory);
			natcasesort($files);
			
			foreach($files as $file) {
				if(in_array($file,$skip)) continue;
				if(in_array($file,array('.','..','.svn','.git'),true)) continue;
				
				if(is_dir($root.$directory.$file)) {
					$params['files'][] = array(
						'type'=>'directory',
						'name'=>$file,
						'path'=>$directory.$file.'/'
					);
				}
				else {
					$params['files'][] = array(
						'type'=>'file',
						'extension'=>array_pop(explode('.',str_replace('.twig','',$file))),
						'name'=>$file,
						'path'=>$directory.$file
					);
				}
			}
		}
		
		return $this->render('admin/filesystem/list.html',$params);
	}
}
