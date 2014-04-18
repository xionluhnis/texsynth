<?php

include_once 'parsedown/Parsedown.php';

function get_event_text($path) {
  $md = new Parsedown();
  $text = get_file_content($path . '/text.dat');
  return $md->text($text);
}

function get_event_images($path) {
  static $image_extensions = array('png', 'jpg');
  $files = array_filter(glob($path . '/images/*'), function($file){
    return is_file($file) || in_array(pathinfo($file, PATHINFO_EXTENSION), $image_extensions);
  });
  return $files;
}

?>
