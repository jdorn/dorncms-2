<?php
require_once 'vendor/autoload.php'; 

$app = new \Slim\Slim(array(
    'view' => new \Slim\Views\Twig()
));

$view = $app->view();
$view->parserExtensions = array(
    new \Slim\Views\TwigExtension(),
);

$app->get('/home', function() use($app) { 
  // Get template data
  $data = array(
    'meta'=>json_decode(file_get_contents('site/pages/home/meta.json')),
    'content'=>json_decode(file_get_contents('site/pages/home/content.json')),
    'parameters'=>json_decode(file_get_contents('site/parameters.json'))
  );
  
  $app->render('pages/home.twig',$data);
}); 

$app->post('/admin/preview', function() use($app) {
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

$app->get('/admin/edit/page/home', function() use($app) {
  $app->render('admin/edit_page.twig',array(
    'page'=>'home'
  ));
});

$app->get('/admin/api/page/home', function() use($app) {
  // Get template data
  $data = array(
    'meta'=>json_decode(file_get_contents('site/pages/home/meta.json')),
    'metaschema'=>json_decode(file_get_contents('config/meta.schema.json')),
    'content'=>json_decode(file_get_contents('site/pages/home/content.json')),
    'contentschema'=>json_decode(file_get_contents('site/pages/home/content.schema.json')),
    'template'=>file_get_contents('templates/pages/home.twig')
  );
  
  $res = $app->response;
  $res->setBody(json_encode($data));
});
$app->post('/admin/api/page/home', function() use($app) {
  // TODO: save page data
});

$app->run(); 
