<?php
require "vendor/autoload.php";

$mongo_url = 'mongodb://localhost:27017/beer-fest';

if (ob_get_level() == 0) ob_start();

try {
  
  $link = new MongoDB\Client($mongo_url);
  $collection = $link->selectDatabase('beef-fest')->winners;
  echo "Great American Beer Fest\n---------------------\n";
  if ($handle = opendir(__DIR__."/gabf/json")) {
    while (false !== ($file = readdir($handle))) {
        $contents = file_get_contents(__DIR__."/gabf/json/$file");
        if(!empty($contents)){
          $winners = json_decode($contents, true);
          if(!empty($winners)){
            $collection->deleteMany(['comp' => 'GABF', 'year' => (int) $winners[0]['year']]);
            $collection->insertMany($winners);
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
            $collection->deleteMany(['comp' => 'WBC', 'year' => (int) $winners[0]['year']]);
            $collection->insertMany($winners);
            echo count($winners)." $file\n";
            ob_flush();
            flush();
          }
        }
      }
    closedir($handle);
  }

} catch(Exception $ex){
  echo $ex->getMessage();
  exit; 
} 
ob_end_flush();
?>