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

$app->run(); 
