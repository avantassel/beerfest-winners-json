<?php
require "vendor/autoload.php";

$beerfest = new BeerFest;

if (ob_get_level() == 0) ob_start();

try {
  
  echo "Great American Beer Fest\n---------------------\n";
  if ($handle = opendir(__DIR__."/gabf/json")) {
    while (false !== ($file = readdir($handle))) {
        $contents = file_get_contents(__DIR__."/gabf/json/$file");
        if(!empty($contents)){
          $winners = json_decode($contents, true);
          if(!empty($winners)){
            $beerfest->SaveWinners('GABF', (int) $winners[0]['year'], $winners);
            echo count($winners)." $file\n";
            ob_flush();
            flush();
          }
        }
      }
    closedir($handle);
  }
  echo "\nWorld Beer Fest\n---------------------\n";
  if ($handle = opendir(__DIR__."/wbc/json")) {
    while (false !== ($file = readdir($handle))) {
        $contents = file_get_contents(__DIR__."/wbc/json/$file");
        if(!empty($contents)){
          $winners = json_decode($contents, true);
          if(!empty($winners)){
            $beerfest->SaveWinners('WBC', (int) $winners[0]['year'], $winners);
            echo count($winners)." $file\n";
            ob_flush();
            flush();
          }
        }
      }
    closedir($handle);
  }
  
  // count collections
  $data = $beerfest->GetWinnersByCity();
  file_put_contents(__DIR__."/map/data/by_city.json", json_encode($data, JSON_NUMERIC_CHECK));
  
  for($year = 1983; $year <= date('Y'); $year++){
    $data = $beerfest->GetWinnersByCity($year);
    if(!empty($data))
      file_put_contents(__DIR__."/map/data/{$year}_by_city.json", json_encode($data, JSON_NUMERIC_CHECK));
  }
  
  $beerfest->SetIndexes();
  
} catch(Exception $ex){
  echo $ex->getMessage();
  exit; 
} 
ob_end_flush();
?>