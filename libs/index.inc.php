<?php

// functions for the index environment
function get_month_str($i) {
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
  return array_key_exists($i, $month_str_list) ? $month_str_list[$i] : "$i";
}

function reverse_order($k1, $k2){
  if($k1 < $k2) return 1;
  if($k1 > $k2) return -1;
  return 0;
}

function get_month_directories($basedir = '.') {
  // read the directories
  $dirs = glob($basedir . '/*');
  $months = array();
  foreach($dirs as $dir){
    $dir = basename($dir);
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
  uksort($months, "reverse_order");
  return $months;
}
function get_event_directories($basedir) {
  // read all the directories
  return array_filter( glob($basedir . '/*'), function($value) {
    return is_dir($value);
  });
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
function get_event_title($event_dir, $def_title) {
  $title = get_file_content($event_dir . '/title.dat');
  return empty($title) ? $def_title : $title;
}

function get_months(){
  $months = get_month_directories();
  return array_map(function($dates, $month){
    rsort($dates);
    // months[i] : {name, dates}
    return array(
      'name'  => get_month_str($month),
      'dates' => array_map(function($date) {
        // months[i].dates[d] : {day, events}
        $events = get_event_directories($date);
        sort($events);
        return array(
          'day'     => substr($date, 2),
          'events'  => array_map(function($event) {
            // months[i].dates[d].events : {link, title, text}
          // Seems not working: $event_name = basename("$event");
            $event_name = basename($event);
            return array(
              'link'  => "$event/",
              'title' => get_event_title($event, $event_name),
              'text'  => $event_name
            );
          }, $events)
        );
      }, $dates)
    );
  }, $months, array_keys($months));
}
