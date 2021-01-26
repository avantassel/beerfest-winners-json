<?php

require "vendor/autoload.php";
  
if ($handle = opendir(__DIR__."/gabf/json")) {
  $fcsv = fopen(__DIR__."/gabf/csv/all-winners.csv", "w");
  
  // write headers
  fwrite($fcsv, "Medal,Beer,Brewery,City,State,Style,Year,Coords,Lat,Lng,Comp\n");
  
  while (false !== ($file = readdir($handle))) {
    if(is_dir($file)) continue;
    
    $winners = [];
    $year = '';
    $contents = file_get_contents(__DIR__."/gabf/json/$file");    
    $contents_json = json_decode($contents);
    foreach($contents_json as $winner){
      $line = [];
      foreach($winner as $k => $v){
        if(is_array($v))
          array_push($line, $v[1]." ".$v[0]);
        else
          array_push($line, $v);
      }      
      fputcsv($fcsv, $line);
    }
  }
}

fclose($fcsv);