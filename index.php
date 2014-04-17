<?php
// use Twig
require_once '/usr/share/pear/Twig/Autoloader.php';
Twig_Autoloader::register();

// create twig loader and environment
$loader = new Twig_Loader_Filesystem('./tpl');
$twig = new Twig_Environment($loader, array(
    'cache' => getcwd() . '/cache',
    'debug' => true
  )
);

// set general environment variables
$target = 'index.html';
$path = '';
$params = array();
if (isset($_GET['path'])){
  $path = $_GET['path'];
  $target = 'page.html';

  if (isset($_GET['params'])) {
    parse_str($_GET['params'], $params);
  }
}

// set environment variables
$env = array(
  'path' => $path,
  'params' => $params
);

include_once 'libs/index.inc.php';

switch($target) {
  case 'index.html':
    $env['months'] = get_months();
    break;
  case 'page.html':
    
    break;
}

// render!
$twig->render($target, $env);

?>

<!-- hello -->
