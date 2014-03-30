<?php
session_cache_limiter(false);
session_start();
require_once 'vendor/autoload.php'; 

class DornCMSApp extends \Slim\Slim {
  protected $string_view;
  protected $site_view;
  public $config;
  public $meta_schema;
  
  public function __construct($args) {
    parent::__construct($args);
    
    $this->config = json_decode(file_get_contents('config/config.json'));
    
    $site_loader = new \Twig_Loader_Filesystem($this->config->sitedir.'templates');
    $this->site_view = new \Twig_Environment($site_loader);
    $this->site_view->addExtension(new \Slim\Views\TwigExtension());
    
    $this->meta_schema = json_decode(file_get_contents('config/meta.schema.json'));
  }
  public function renderSitePage($page) {
    // Get template data
    $data = array(
      'meta'=>json_decode(file_get_contents($this->config->sitedir.'pages/'.$page.'/meta.json')),
      'content'=>json_decode(file_get_contents($this->config->sitedir.'pages/'.$page.'/content.json')),
      'parameters'=>json_decode(file_get_contents($this->config->sitedir.'parameters.json'))
    );
    
    $this->response->setBody($this->site_view->render('pages/'.$page.'.twig',$data));
    $this->stop();
  }
  public function previewPage($template, $data) {
    // Create temp file on disk to render the preview and then delete
    $filename = '__preview_'.microtime(true).'.twig';
    file_put_contents($this->config->sitedir.'/templates/'.$filename,$template);
    $this->response->setBody($this->site_view->render($filename,$data));
    unlink($this->config->sitedir.'/templates/'.$filename);
    $this->stop();
  }
  public function getPageData($page) {
    $data = array(
      'meta'=>json_decode(file_get_contents($this->config->sitedir.'pages/'.$page.'/meta.json')),
      'content'=>json_decode(file_get_contents($this->config->sitedir.'pages/'.$page.'/content.json')),
      'contentschema'=>json_decode(file_get_contents($this->config->sitedir.'pages/'.$page.'/content.schema.json')),
      'template'=>file_get_contents($this->config->sitedir.'templates/pages/'.$page.'.twig')
    );
    
    return $data;
  }
  public function getSiteParameters() {
    return json_decode(file_get_contents($this->config->sitedir.'parameters.json'));
  }
}

$app = new DornCMSApp(array(
    'view' => new \Slim\Views\Twig()
));

$view = $app->view();
$view->setTemplatesDirectory('admin_templates');
$view->parserExtensions = array(
    new \Slim\Views\TwigExtension(),
);

$authenticate = function() use($app) {
  if(!isset($_SESSION['authenticated'])) {
    $app->redirect($app->urlFor('login'));
  }
};

$app->get('/admin/login',function() use($app) {
  $app->render('admin/login.twig',array());
})->name('login');

$app->post('/admin/login',function() use($app) {
  $post = (object) $app->request()->post();
  if(isset($post->username) && isset($post->password)) {
    foreach($app->config->users as $user) {
      if($user->username === $post->username && $user->password === $post->password) {        
        $_SESSION['authenticated'] = $post->username;
        $app->redirect($app->urlFor('admin'));
      }
    }
    $app->render('admin/login.twig',array(
      'error'=> 'Incorrect Username or Password'
    ));
  }
  else {
    $app->render('admin/login.twig',array(
      'error'=> 'Please enter your Username and Password'
    ));
  }
})->name('login_post');

$app->get('/admin/logout',function() use($app) {
  unset($_SESSION['authenticated']);
  $app->redirect($app->urlFor('home'));
})->name('logout');

$app->post('/admin/preview', $authenticate, function() use($app) {
  $post = $app->request()->post();
  
  $parameters = '';
  if(isset($post['parameters'])) $parameters = json_decode($post['parameters']);
  else $parameters = $app->getSiteParameters();

  $data = array(
    'meta'=>json_decode($post['meta']),
    'content'=>json_decode($post['content']),
    'parameters'=>$parameters
  );
  
  $app->previewPage($post['template'],$data);
})->name('admin_preview');

$app->get('/admin', $authenticate, function() use($app) {
  $pages = scandir($app->config->sitedir.'pages/');
  $pages = array_filter($pages,function($page) {
    if($page[0]==='.') return false;
    return true;
  });
  
  $app->render('admin/home.twig',array(
    'pages'=>$pages
  ));
})->name('admin');

$app->get('/admin/page/:page', $authenticate, function($page) use($app) {
  $app->render('admin/edit_page.twig',array(
    'page'=>$page
  ));
})->name('edit_page');

$app->get('/admin/api/page/:page', $authenticate, function($page) use($app) {
  $data = $app->getPageData($page);
  $data['metaschema'] = json_decode(file_get_contents('config/meta.schema.json'));
  
  $res = $app->response;
  $res->setBody(json_encode($data));
})->name('admin_api_page');

$app->post('/admin/api/page/:page', $authenticate, function($page) use($app) {
  $post = $app->request()->post();
  
  // TODO: revision history
  // TODO: error handling
  file_put_contents($app->config->sitedir.'pages/'.$page.'/meta.json',$post['meta']);
  file_put_contents($app->config->sitedir.'pages/'.$page.'/content.json',$post['content']);
  file_put_contents($app->config->sitedir.'templates/pages/'.$page.'.twig',$post['template']);
  
  $app->response->setBody(json_encode(array('status'=>1)));
})->name('admin_api_page_post');

// Custom routes
if(file_exists($app->config->sitedir.'routes.php')) {
  require($app->config->sitedir.'routes.php');
}

$app->get('/',function() use($app) { 
  $app->renderSitePage($app->config->home);
})->name('home');

$app->get('/:page', function($page) use($app) {
  $app->rednerSitePage($page);
})->name('page');

$app->run(); 
