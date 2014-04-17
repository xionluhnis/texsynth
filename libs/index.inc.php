<?php

// functions for the index environment
function month_str($i) {
  static $month_str_list = array(
    '01' => 'January',
    '02' => 'February',
    '03' => 'March',
    '04' => 'April',
    '05' => 'May',
    '06' => 'June',
    '07' => 'July',
    '08' => 'August',
    '09' => 'September',
    '10' => 'October',
    '11' => 'November',
    '12' => 'December'
  );
  return $month_str_list[$i];
}

function get_month_directories($basedir = '.') {
  // read the directories
  $dirs = glob($basedir . '/*');
  $months = array();
  foreach($dirs as $dir){
    if(   is_dir($dir) 
      &&  is_numeric($dir) 
      &&  strlen($dir) == 4) {
      $month = substr($dir, 0, 2);
      if (array_key_exists($month, $months))
        $months[$month][] = $dir;
      else
        $months[$month] = array($dir);
    }
  }
  // sort in reverse order
  rsort($months);
  return $months;
}
function get_file_content($file) {
  if(file_exists($file)) {
    $fh = fopen($file, 'r');
    $data = array();
    while ($line = fgets($fh))
      $data[] = $line;
    fclose($fh);
    return join("\n", $data);
  }
  return '';
}
function get_event_title($event_dir) {
  $title = get_file_content($event_dir . '/title.dat');
  return empty($title) ? substr($event, 5, -1) : $title;
}

include_once 'parsedown/Parsedown.php';

function get_event_text($event_dir) {
  $md = new Parsedown();
  $text = get_file_content($event_dir . '/text.dat');
  return $md->text($text);
}

function get_months(){
  $months = get_month_directories();
  return array_map(function($month){
    return array(
      'name'  => month_str($month),
      'dates' => array()
    );
  }, $months);
}
