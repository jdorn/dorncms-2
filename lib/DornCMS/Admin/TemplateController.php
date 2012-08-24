<?php
namespace DornCMS\Admin;

use Symfony\Component\HttpFoundation\Response;
use DornCMS\Controller;
use DornCMS\Twig;
use DornCMS\User;

class TemplateController extends Controller {
	public function editAction($request) {
		$template = $request->query->get('file');
		
		//user must be an admin to edit a template
		if($response = $this->authorize(User::ROLE_ADMIN)) {
			return $response;
		}
		
		$contents = file_get_contents(__DIR__.'/../../../templates/'.$template.'.twig');
		
		//if this contains blocks, use the Ace editor, otherwise, use WYSIWYG.
		if(preg_match('/\{\%\s*block/',$contents)) {
			$editor = new Twig\AceEditor(preg_replace('/[^a-zA-Z0-9\-_]/','',$template),$contents);
			$editor->setLanguage('html');
		}
		else {
			$editor = new Twig\CleEditor(preg_replace('/[^a-zA-Z0-9\-_]/','',$template),$contents);
		}
		
		return $this->render('admin/edit_template.html',array(
			'template'=>array(
				'editor'=>$editor,
				'name'=>$template
			),
			'javascripts'=>$editor->getJs(),
			'stylesheets'=>$editor->getCss(),
		));
	}
	
	
	
	
	protected function getFileList($directory) {
		$_POST['dir'] = urldecode($_POST['dir']);

		$root = __DIR__.'/../../web/';
		
		if (false !== strpos($name, "\0")) {
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

		if( file_exists($root . $_POST['dir']) ) {
			$files = scandir($root . $_POST['dir']);
			natcasesort($files);
			if( count($files) > 2 ) { /* The 2 accounts for . and .. */
				echo "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
				// All dirs
				foreach( $files as $file ) {
					if( file_exists($root . $_POST['dir'] . $file) && $file != '.' && $file != '..' && is_dir($root . $_POST['dir'] . $file) ) {
						echo "<li class=\"directory collapsed\"><a href=\"#\" rel=\"" . htmlentities($_POST['dir'] . $file) . "/\">" . htmlentities($file) . "</a></li>";
					}
				}
				// All files
				foreach( $files as $file ) {
					if( file_exists($root . $_POST['dir'] . $file) && $file != '.' && $file != '..' && !is_dir($root . $_POST['dir'] . $file) ) {
						$ext = preg_replace('/^.*\./', '', $file);
						if($ext === 'php') continue;
						
						echo "<li class=\"file ext_$ext\"><a href=\"#\" rel=\"" . htmlentities($_POST['dir'] . $file) . "\">" . htmlentities($file) . "</a></li>";
					}
				}
				echo "</ul>";	
			}
		}
		else {
			throw new \Exception("Directory doesn't exist");
		}
	}
}
