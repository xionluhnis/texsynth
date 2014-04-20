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

function get_event_images($path, $suffix = '', $prefix = '') {
  static $image_extensions = array('png', 'jpg');
  $files = array_filter(glob("$path/images/$prefix*"), function($file) use(&$suffix) {
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

function has_images($path) {
  return glob("$path/*.png") || glob("$path/*.jpg");
}

function get_param_values($path, &$values = array(), $prefix = '') {
  $dirs = array_filter(glob("$path/images/$prefix*"), function($file) {
    if(!is_dir($file)) return false;
    return preg_match("/^[a-zA-Z]+/", basename($file));
  });
  // check if next level has images => this level has texture names
  if(has_images("$path/images/$prefix/{$dirs[0]}")) return $values;
  foreach($dirs as $dir) {
    $base = basename($dir);
    $res = preg_match("/^[a-zA-Z]+/", $base, $match);
    assert($res == 1, "Invalid parameter base $base"); // from before, we expect this
    // set value
    $name = $match[0];
    $value = substr($base, strlen($name));
    if($values[$name]){
      $values[$name][] = $value;
    } else {
      $values[$name] = array($value);
    }
  }
  $new_prefix=basename($dirs[0]);
  return get_param_values($path, $values, "{$prefix}$new_prefix/");
}

function get_first_directory($path) {
  foreach(glob("$path/*") as $file){
    if(is_dir($file)) return $file;
  }
  return '';
}

function get_scales($path, $recursive) {
  $scale_prefix = "$path/images";
  if(!is_dir("$scale_prefix/s1")){
    if(!$recursive) return array();
    while($scale_prefix = get_first_directory($scale_prefix)) {
      if(is_dir("$scale_prefix/s1")) break;
    }
    if(!$scale_prefix) return array();
  }
  // populate from $scale_prefix
  $scale_dirs = glob("$scale_prefix/s*");
  return array_map(function($dir){
    return basename($dir);
  }, $scale_dirs);
}

?>
