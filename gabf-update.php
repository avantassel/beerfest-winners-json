<?php
require "vendor/autoload.php";
use PHPHtmlParser\Dom;
$dom = new Dom;
$beerfest = new BeerFest;

$year = date('Y');
$fields = ['action' => 'search-winners',
  '_medal' => 0,
  '_state' => 0,
  '_style' => 0,
  '_year' => $year,
  '_comp' => 'gabf',
  '_fulltext' => ''
];
$winners = [];

$result = $beerfest->DownloadContent('https://www.greatamericanbeerfestival.com/wp-admin/admin-ajax.php', $fields);
if(empty($result)){
  echo "Could not download GABF content";
  exit;
}

if(file_exists(__DIR__."/gabf/html/$year.html"))
  unlink(__DIR__."/gabf/html/$year.html");
  
// write html
file_put_contents(__DIR__."/gabf/html/$year.html", $result);

// 2024 GABF
// $result = file_get_contents(__DIR__."/gabf/html/$year.html");

// convert to json
$dom->load($result);
$tr = $dom->find('.winners tbody tr');
foreach($tr as $row){
  $tds = $row->find('td');
  
  $name = trim($tds[2]->innerHtml);
  $city = trim($tds[3]->innerHtml);
  $state = strtoupper(trim($tds[4]->innerHtml));
  $location = $beerfest->GetLocation($name, $city, $state);
  $style = trim($tds[5]->innerHtml);
  if(strstr($style, '<br')){
    $style = substr($style, 0, strpos($style, '<br'));
    $style =  strip_tags($style);
  }
  $winners[] = [
    'medal' => trim($tds[0]->find('span')->innerHtml),
    'beer' => trim($tds[1]->innerHtml),
    'brewery' => $name,
    'city' => $city,
    'state' => $state,
    'style' => $style,
    'year' => trim($tds[6]->innerHtml),
    'coords' => !empty($location['lat']) ? [$location['lng'],$location['lat']] : null,
    'lat' => !empty($location['lat']) ? $location['lat'] : null,
    'lng' => !empty($location['lng']) ? $location['lng'] : null,
    'comp' => 'GABF'
  ];
}
if(!empty($winners)){
  if(file_exists(__DIR__."/gabf/json/$year.json"))
    unlink(__DIR__."/gabf/json/$year.json");
  file_put_contents(__DIR__."/gabf/json/$year.json", json_encode($winners, JSON_NUMERIC_CHECK));
  echo "Found ".count($winners)." winners";
} else {
  echo "Something went wrong, no winners foound";
}
?>