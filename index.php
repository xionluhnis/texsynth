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
    // manually parse
    $tokens = explode('/', $_GET['params']);
    foreach($tokens as $token){
      $token_parts = explode(':', $token, 2);
      switch(count($token_parts)){
      case 2:
        $params[$token_parts[0]] = $token_parts[1];
        break;
      case 1:
        $params[$token_parts[0]] = '';
        break;
      }
    }
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
  'params' => $params,
  'options' => array('big', 'small')
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

    // load the display parameters
    static $display_modes = array('big', 'small', 'list', 'scales', 'video');
    $env['class'] = 'small';
    foreach($params as $pname => $pval){
      if(in_array($pname, $display_modes)){
        $env['class'] = $pname;
        break;
      }
    }

    // load the event structure data
    parse_str(get_file_content($path . '/data.dat'), $data);
    $env['data']  = $data;
    $type = array_key_exists('type', $data) ? trim($data['type']) : 'single';

    // masks
    $env['has_masks'] = is_dir($path . '/masks');

    // use of scales
    if($scales = get_scales($path, $type == 'params')) {
      $env['options'][]  = 'scales';
      $env['has_scales'] = true;
      rsort($scales);
      $env['scales'] = $scales;
      // list scales
      rsort($env['scales']);
    } else {
      $env['has_scales'] = false;
    }

    // synthesis parameters
    $env['has_params'] = false; // not by default

    // prefix of image paths
    $path_prefix = '';

    switch($type) {
      case 'single':
        $env['images'] = get_event_images($path);
        break;

      case 'params':
        $env['options'][] = 'params';
        // build parameter list
        $env['explore_values']  = get_param_values($path); // the values range per parameter
        $env['explore_names']   = array(); // the exploration parameter names
        $env['has_params'] = true;
        // set names and the first path prefix for the images
        $path_prefix = array();
        foreach($env['explore_values'] as $name => $values) {
          $env['explore_names'][] = $name;
          $path_prefix[] = $name . $values[0];
        }
        $path_prefix = implode('/', $path_prefix) . '/';

        // fall-through
        //
      case 'default':
        $env['images'] = get_event_images($path, '-im', $path_prefix); // only select the ones with filename ending in -im
        $env['options'][] = 'list';

        // class-dependent data
        switch($env['class']){
          case 'scales':
          case 'list':
            if(!empty($env['images'])){
              // add the current picture
              if(array_key_exists('image_base', $params)){
                $base = $params['image_base'];
                // find the image
                foreach($env['images'] as $img){
                  $file = pathinfo($img);
                  if($file['filename'] === $base . '-im') {
                    $env['image'] = get_imageinfo($img);
                    $env['image_base'] = $base;
                    break;
                  }
                }
              } else {
                $file = get_pathinfo(reset($env['images']));
                $env['image'] = $file;
                $name = $file['filename'];
                $env['image_base'] = substr($name, 0, strlen($name) - 3); // remove -im
              }
              // Check for exemplar
              $ex = get_image_exemplar($env['image']['file']);
              if(!empty($ex)){
                $env['exemplar'] = get_imageinfo($ex);
              }
            } else {
              $env['class'] = 'small'; // revert else illconditioned
            }
            break;
          default:
        }
        break;
    }
    break;
}

$twig->addFilter(new Twig_SimpleFilter('filename', function($file, $args){
  $name = pathinfo($file, PATHINFO_FILENAME);
  if(isset($args) && substr($name, -strlen($args)) === $args){
    $name = substr($name, 0, -strlen($args));
  }
  return $name;
}));

$twig->addFilter(new Twig_SimpleFilter('scalepath', function($file, $scale){
  /* if(strlen($scale) < 2 || substr($scale, 0, 1) != 's') return $scale; */
  $info = pathinfo($file);
  return $info['dirname'] . "/$scale/" . $info['basename'];
}));

// render!
$twig->display($target, $env);

?>

<!-- with twig, by Alexandre Kaspar -->
