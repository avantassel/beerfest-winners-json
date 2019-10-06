<?php
require "vendor/autoload.php";
use PHPHtmlParser\Dom;
$dom = new Dom;
$beerfest = new BeerFest;

$year = date('Y');

if($year % 2 !== 0){
  echo "World Beer Cup Runs every 2 years on even years";
  exit;
}

$fields = ['action' => 'search-winners',
  '_medal' => 0,
  '_state' => 0,
  '_style' => 0,
  '_year' => $year,
  '_fulltext' => ''
];
$winners = [];

$result = $beerfest->DownloadContent('https://www.worldbeercup.org/wp-admin/admin-ajax.php', $fields);
if(empty($result)){
  echo "Could not download WBC content";
  exit;
}

// write html
file_put_contents(__DIR__."/wbc/html/$year.html", $result);

// convert to json
$dom->load($result);
$tr = $dom->find('.winners tbody tr');
foreach($tr as $row){
  $tds = $row->find('td');
  $winners[] = [
    'medal' => trim($tds[0]->find('span')->innerHtml),
    'beer' => trim($tds[1]->innerHtml),
    'brewery' => trim($tds[2]->innerHtml),
    'style' => trim($tds[3]->innerHtml),
    'year' => trim($tds[4]->innerHtml),
    'comp' => 'WBC'
  ];  
}
if(!empty($winners))
  file_put_contents(__DIR__."/wbc/json/$year.json", json_encode($winners, JSON_NUMERIC_CHECK));
?>