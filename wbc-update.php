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
  '_comp' => 'wbc',
  '_fulltext' => ''
];
$winners = [];

// $result = $beerfest->DownloadContent('https://www.worldbeercup.org/wp-admin/admin-ajax.php', $fields);
// if(empty($result)){
//   echo "Could not download WBC content";
//   exit;
// }

// if(file_exists(__DIR__."/wbc/html/$year.html")){
//   unlink(__DIR__."/wbc/html/$year.html");
// }
   
// // write html
// file_put_contents(__DIR__."/wbc/html/$year.html", $result);

$result = file_get_contents(__DIR__."/wbc/html/$year.html");

// convert to json
$dom->load($result);
$tr = $dom->find('.gridjs-tbody tr');
foreach($tr as $row){
  $tds = $row->find('td');
  $name = trim($tds[2]->innerHtml);
  $city = trim($tds[4]->innerHtml);
  $state = strtoupper(trim($tds[5]->innerHtml));
  $location = $beerfest->GetLocation($name, $city, $state);
  $winners[] = [
    'medal' => trim($tds[0]->innerHtml),
    'beer' => trim($tds[1]->innerHtml),
    'brewery' => trim($tds[2]->innerHtml),
    'style' => trim($tds[3]->innerHtml),
    'city' => trim($tds[4]->innerHtml),
    'state' => trim($tds[5]->innerHtml),
    'country' => trim($tds[6]->innerHtml),
    'medalSort' => trim($tds[7]->innerHtml),
    'year' => $year,
    'coords' => !empty($location['lat']) ? [$location['lng'],$location['lat']] : null,
    'lat' => !empty($location['lat']) ? $location['lat'] : null,
    'lng' => !empty($location['lng']) ? $location['lng'] : null,
    'comp' => 'WBC'
  ];  
}
if(!empty($winners)){
  if(file_exists(__DIR__."/wbc/json/$year.json")){
    unlink(__DIR__."/wbc/json/$year.json");
  }

  file_put_contents(__DIR__."/wbc/json/$year.json", json_encode($winners, JSON_NUMERIC_CHECK));
  echo "Found ".count($winners)." winners";
} else {
  echo "Something went wrong, no winners foound";
}
?>