<?php
/*
Files in /usopen/$year.json are hand copied and pasted from the website
Files in /usopen/json/$year.json are parsed from the above files and formatted
*/
require "vendor/autoload.php";

if (ob_get_level() == 0) ob_start();

$beerfest = new BeerFest;

for($year=2009; $year <= 2021; $year++){
  
  if(!file_exists(__DIR__."/usopen/$year.json")) 
    continue;
  
  $contents = file_get_contents(__DIR__."/usopen/$year.json");
  if(!empty($contents)){
    $winners = json_decode($contents, true);
    $formatted = [];
    // format winners
    foreach($winners as $winner){
      $style = '';
      foreach($winner as $key => $value){
        if($key == 'style') {
          $style = $value;
          continue;
        }
        $medal = '';
        if(stristr($key, 'gold'))
          $medal = 'Gold';
        if(stristr($key, 'silver'))
          $medal = 'Silver';
        if(stristr($key, 'bronze'))
          $medal = 'Bronze';
          
        if($year == 2009)
          $parsedName = explode(',', $value);
        else
          $parsedName = explode('–', $value);
        
        $formatted[] = [
          "medal" => $medal,
          "beer" => isset($parsedName[0]) ? trim($parsedName[0]) : "",
          "brewery" => isset($parsedName[1]) ? trim($parsedName[1]) : "",
          "state" => isset($parsedName[2]) ? trim($parsedName[2]) : "",
          "style" => $style,
          "year" => $year,
          "comp" => "USOpen"
        ];
      }
    }
    
    if(!empty($formatted)){
      $beerfest->SaveWinners('USOpen', (int) $year, $formatted);
      
      if(file_exists(__DIR__."/usopen/json/{$year}.json"))
          unlink(__DIR__."/usopen/json/{$year}.json");
        file_put_contents(__DIR__."/usopen/json/{$year}.json", json_encode($formatted, JSON_NUMERIC_CHECK));
        
      echo count($formatted)." - $year.json\n";
      ob_flush();
      flush();
    }
  }
}