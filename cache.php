<?php

// check data
$target = $_GET['img'];
if(!is_file($target) || strrpos($target, '..') !== FALSE || substr($target, -4) !== '.png'){
  header('Status: 410 Gone', false, 410);
  header('Location: style/missingfile.png');
  exit;
}
$tokens = explode('/', $target, 4);
$tokens[2] = 'cache/' . $tokens[2];
$cached = implode('/', $tokens);
$cached = substr($cached, 0, -3) . 'jpg';

// create cache directory if needed
$cache_dir = dirname($cached);
if(!is_dir($cache_dir)){
  mkdir($cache_dir, 0770, true);
}

// generate the image when not done yet
if(!is_file($cached)){
  $im = new Imagick();
  $im->readImage($target);
  $im->writeImage($cached);
}

header('Status: 301 Moved Permanently', false, 301);
header("Location: $cached");
exit;
?>
