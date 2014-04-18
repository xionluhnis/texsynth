<?php

include_once 'parsedown/Parsedown.php';

function get_event_text($path) {
  $md = new Parsedown();
  $text = get_file_content($path . '/text.dat');
  return $md->text($text);
}

function get_pathinfo($path) {
  $info = pathinfo($path);
  $info['file'] = $path;
  return $info;
}

function get_imageinfo($file) {
  $info = get_pathinfo($file);
  list($width, $height, $tail) = getimagesize($file);
  $info['width'] = $width;
  $info['height'] = $height;
  return $info;
}

function get_event_images($path, $suffix = '') {
  static $image_extensions = array('png', 'jpg');
  $files = array_filter(glob($path . '/images/*'), function($file) use(&$suffix) {
    if (strlen($suffix) > 0) {
      $name = pathinfo($file, PATHINFO_FILENAME);
      if (substr($name, -strlen($suffix)) !== $suffix) return false;
    }
    return is_file($file) || in_array(pathinfo($file, PATHINFO_EXTENSION), $image_extensions);
  });
  return $files;
}

function get_image_exemplar($file) {
  static $images_extensions = array('png', 'jpg');
  $info = pathinfo($file);
  $name = $info['filename'];
  $ex_base = $info['dirname'] . '/' . str_replace('-im', '-ex.', $name);
  foreach($images_extensions as $fext){
    if(file_exists($ex_base . $fext)){
      return $ex_base . $fext;
    }
  }
  return '';
}

?>
