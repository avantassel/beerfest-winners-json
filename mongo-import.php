<?php
require "vendor/autoload.php";

$beerfest = new BeerFest;
$import_year = 2020; // set to null to import all

if (ob_get_level() == 0) ob_start();

try {
  
  echo "Great American Beer Fest\n---------------------\n";
  if($import_year){
    if(file_exists(__DIR__."/gabf/json/$import_year.json")){
      $contents = file_get_contents(__DIR__."/gabf/json/$import_year.json");
      if(!empty($contents)){
        $winners = json_decode($contents, true);
        if(!empty($winners)){
          $beerfest->SaveWinners('GABF', (int) $winners[0]['year'], $winners);
          echo count($winners)." $import_year.json\n";
          ob_flush();
          flush();
        }
      }
    } else {
      echo "$import_year.json not found\n";
    }
  } else if ($handle = opendir(__DIR__."/gabf/json")) {
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
  if($import_year){
    if(file_exists(__DIR__."/wbc/json/$import_year.json")){
      $contents = file_get_contents(__DIR__."/wbc/json/$import_year.json");
      if(!empty($contents)){
        $winners = json_decode($contents, true);
        if(!empty($winners)){
          $beerfest->SaveWinners('WBC', (int) $winners[0]['year'], $winners);
          echo count($winners)." $import_year.json\n";
          ob_flush();
          flush();
        }
      }      
    } else {
      echo "$import_year.json not found\n";
    }
  } else if ($handle = opendir(__DIR__."/wbc/json")) {
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
  
  // all winners
  $data = $beerfest->GetAllWinnersWithCoords();
  
  if(file_exists(__DIR__."/map/data/winners.json"))
    unlink(__DIR__."/map/data/winners.json");
  if($data)
    file_put_contents(__DIR__."/map/data/winners.json", json_encode($data, JSON_NUMERIC_CHECK));
  
  // count collections
  $data = $beerfest->GetWinnersByCity();
  
  if(file_exists(__DIR__."/map/data/by_city.json"))
    unlink(__DIR__."/map/data/by_city.json");
  if($data)
    file_put_contents(__DIR__."/map/data/by_city.json", json_encode($data, JSON_NUMERIC_CHECK));
  
  // create by_city json for map
  if($import_year){
    $data = $beerfest->GetWinnersByCity($import_year);
    if(!empty($data))
      file_put_contents(__DIR__."/map/data/{$import_year}_by_city.json", json_encode($data, JSON_NUMERIC_CHECK));
  } else {
    for($year = 1983; $year <= date('Y'); $year++){
      $data = $beerfest->GetWinnersByCity($year);
      if(!empty($data))
        file_put_contents(__DIR__."/map/data/{$year}_by_city.json", json_encode($data, JSON_NUMERIC_CHECK));
    }    
  }
  
  $beerfest->SetIndexes();
  
} catch(Exception $ex){
  echo $ex->getMessage();
  exit; 
} 
ob_end_flush();
?>