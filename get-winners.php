<?php

echo "No Need to run this as the data already exists in this repo.";
exit;

require "vendor/autoload.php";
use PHPHtmlParser\Dom;
$dom = new Dom;
$beerfest = new BeerFest;

for($year = 1983; $year <= date('Y'); $year++){
  $fields = ['action' => 'search-winners',
    '_medal' => 0,
    '_state' => 0,
    '_style' => 0,
    '_year' => $year,
    '_fulltext' => ''
  ];
  $winners = [];

  $result = $beerfest->DownloadContent('https://www.greatamericanbeerfestival.com/wp-admin/admin-ajax.php', $fields);
  
  // write html
  file_put_contents(__DIR__."/gabf/html/$year.html", $result);

  // convert to json
  $dom->load($result);
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
  }
  file_put_contents(__DIR__."/gabf/json/$year.json", json_encode($winners, JSON_NUMERIC_CHECK));
}

for($year = 1996; $year <= date('Y'); $year++){
  if($year % 2 !== 0){
    echo "World Beer Cup Runs every 2 years on even years";
    continue;
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
  file_put_contents(__DIR__."/wbc/json/$year.json", json_encode($winners, JSON_NUMERIC_CHECK));
}
?>