<?php

require "vendor/autoload.php";
use PHPHtmlParser\Dom;
$dom = new Dom;
$beerfest = new BeerFest;

$winners = [];
if ($handle = opendir(__DIR__."/gabf/html")) {
  while (false !== ($file = readdir($handle))) {
    
    if(is_dir(__DIR__."/gabf/html/$file")) continue;
    
    $winners = [];
    $year = '';
    $contents = file_get_contents(__DIR__."/gabf/html/$file");
    $dom->load($contents);
    $tr = $dom->find('.winners tbody tr');
    foreach($tr as $row){
      $tds = $row->find('td');
      
      $name = trim($tds[2]->innerHtml);
      $city = trim($tds[3]->innerHtml);
      $state = strtoupper(trim($tds[4]->innerHtml));
      $location = $beerfest->GetLocation($name, $city, $state);
      
      $winners[] = [
        'medal' => trim($tds[0]->find('span')->innerHtml),
        'beer' => trim($tds[1]->innerHtml),
        'brewery' => $name,
        'city' => $city,
        'state' => $state,
        'style' => trim($tds[5]->innerHtml),
        'year' => trim($tds[6]->innerHtml),
        'coords' => !empty($location['lat']) ? [$location['lng'],$location['lat']] : null,
        'lat' => !empty($location['lat']) ? $location['lat'] : null,
        'lng' => !empty($location['lng']) ? $location['lng'] : null,
        'comp' => 'GABF'
      ];
      $year = $tds[6]->innerHtml;
    }
    if(!empty($winners))
      file_put_contents(__DIR__."/gabf/json/$year.json", json_encode($winners, JSON_NUMERIC_CHECK));
  }
  closedir($handle);
}

if ($handle = opendir(__DIR__."/wbc/html")) {
  while (false !== ($file = readdir($handle))) {
    
    if(is_dir(__DIR__."/wbc/html/$file")) continue;
    
    $winners = [];
    $year = '';
    $contents = file_get_contents(__DIR__."/wbc/html/$file");
    $dom->load($contents);
    $tr = $dom->find('.winners tbody tr');
    foreach($tr as $row){
      $tds = $row->find('td');
      // lat/lng lookup
      $winners[] = [
        'medal' => trim($tds[0]->find('span')->innerHtml),
        'beer' => trim($tds[1]->innerHtml),
        'brewery' => trim($tds[2]->innerHtml),
        'style' => trim($tds[3]->innerHtml),
        'year' => trim($tds[4]->innerHtml),
        'comp' => 'WBC'
      ];
      $year = $tds[4]->innerHtml;
    }
    if(!empty($winners))
      file_put_contents(__DIR__."/wbc/json/$year.json", json_encode($winners, JSON_NUMERIC_CHECK));
  }
  closedir($handle);
}
?>