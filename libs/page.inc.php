<?php

include_once 'parsedown/Parsedown.php';

function get_event_text($path) {
  $md = new Parsedown();
  $text = get_file_content($path . '/text.dat');
  return $md->text($text);
}

?>
