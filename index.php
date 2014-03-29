<?php
session_cache_limiter(false);
session_start();
require_once 'vendor/autoload.php'; 

$config = json_decode(file_get_contents('config/config.json'));

$app = new \Slim\Slim(array(
    'view' => new \Slim\Views\Twig()
));

$view = $app->view();
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

$app->post('/admin/login',function() use($app, $config) {
  $post = (object) $app->request()->post();
  if(isset($post->username) && isset($post->password)) {
    foreach($config->users as $user) {
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
  if(isset($post['parameters'])) $parameters = $post['parameters'];
  else $parameters = file_get_contents('site/parameters.json');

  $data = array(
    'meta'=>json_decode($post['meta']),
    'content'=>json_decode($post['content']),
    'parameters'=>json_decode($parameters)
  );
  
  // Create temp file on disk to render the preview and then delete
  $filename = '__preview_'.microtime(true).'.twig';
  file_put_contents('templates/'.$filename,json_decode($post['template'])->template);
  $app->render($filename,$data);
  unlink('templates/'.$filename);
});

$app->get('/admin', $authenticate, function() use($app) {
  $pages = scandir('site/pages/');
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
  // Get template data
  $data = array(
    'meta'=>json_decode(file_get_contents('site/pages/'.$page.'/meta.json')),
    'metaschema'=>json_decode(file_get_contents('config/meta.schema.json')),
    'content'=>json_decode(file_get_contents('site/pages/'.$page.'/content.json')),
    'contentschema'=>json_decode(file_get_contents('site/pages/'.$page.'/content.schema.json')),
    'template'=>file_get_contents('templates/pages/'.$page.'.twig')
  );
  
  $res = $app->response;
  $res->setBody(json_encode($data));
});
$app->post('/admin/api/page/:page', $authenticate, function($page) use($app) {
  // TODO: save page data
});

$displayPage = function($page) use($app) {
  // Get template data
  $data = array(
    'meta'=>json_decode(file_get_contents('site/pages/'.$page.'/meta.json')),
    'content'=>json_decode(file_get_contents('site/pages/'.$page.'/content.json')),
    'parameters'=>json_decode(file_get_contents('site/parameters.json'))
  );
  
  $app->render('pages/'.$page.'.twig',$data);
};

$app->get('/',function() use($displayPage) { $displayPage('home'); })->name('home');

$app->get('/:page', $displayPage); 

$app->run(); 
