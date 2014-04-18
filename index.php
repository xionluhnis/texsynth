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

// check the value of $path
if (!preg_match('![0-9]{4}/.+!', $path) || !is_dir($path)){
  // wrong path! /!\ could be a malicious user!
  $target = 'index.html';
  $path = '';
}

// set environment variables
$env = array(
  'path' => $path,
  'params' => $params
);

include_once 'libs/index.inc.php';
include_once 'libs/page.inc.php';

switch($target) {
  case 'index.html':
    $env['months'] = get_months();
    break;
  case 'page.html':
    // the general event data
    $env['text']  = get_event_text($path);
    $env['title'] = get_event_title($path, 'Synthesis: ' . $path);

    // load the event structure data
    parse_str(get_file_content($path), $data);
    $env['data']  = $data;
    $type = array_key_exists($data, 'type') ? $data['type'] : 'single';

    switch($type) {
      case 'single':
        $env['images'] = get_event_images($path);
        break;
    }
    break;
}

$twig->addFilter(new Twig_SimpleFilter('filename', function($file){
  return pathinfo($file, PATHINFO_FILENAME);
}));

// render!
$twig->display($target, $env);

?>

<!-- with twig, by Alexandre Kaspar -->
