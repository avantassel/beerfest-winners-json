<?php

echo "No Need to run this as the data already exists in this repo.";
exit;

require "vendor/autoload.php";
use PHPHtmlParser\Dom;
$dom = new Dom;

for($year = 1983; $year <= date('Y'); $year++){
  $fields = ['action' => 'search-winners',
    '_medal' => 0,
    '_state' => 0,
    '_style' => 0,
    '_year' => $year,
    '_fulltext' => ''
  ];
  $winners = [];

  $ch = curl_init();
  curl_setopt($ch,CURLOPT_URL, 'https://www.greatamericanbeerfestival.com/wp-admin/admin-ajax.php');
  curl_setopt($ch,CURLOPT_POST, count($fields));
  curl_setopt($ch,CURLOPT_POSTFIELDS, http_build_query($fields));

  //execute post
  $result = curl_exec($ch);

  //close connection
  curl_close($ch);

  // write html
  file_put_contents(__DIR__."/gabf/html/$year.html", $result);

  // convert to json
  $dom->load($result);
  $tr = $dom->find('.winners tbody tr');
  foreach($tr as $row){
    $tds = $row->find('td');
    $winners[] = [
      'medal' => $tds[0]->find('span')->innerHtml,
      'beer' => $tds[1]->innerHtml,
      'brewery' => $tds[2]->innerHtml,
      'city' => $tds[3]->innerHtml,
      'state' => $tds[4]->innerHtml,
      'style' => $tds[5]->innerHtml,
      'year' => $tds[6]->innerHtml,
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

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, 'https://www.worldbeercup.org/wp-admin/admin-ajax.php');
  curl_setopt($ch, CURLOPT_POST, count($fields));
  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  
  //execute post
  $result = curl_exec($ch);
  
  echo $result;
  exit;

  //close connection
  curl_close($ch);

  // write html
  file_put_contents(__DIR__."/wbc/html/$year.html", $result);

  // convert to json
  $dom->load($result);
  $tr = $dom->find('.winners tbody tr');
  foreach($tr as $row){
    $tds = $row->find('td');
    $winners[] = [
      'medal' => $tds[0]->find('span')->innerHtml,
      'beer' => $tds[1]->innerHtml,
      'brewery' => $tds[2]->innerHtml,
      'style' => $tds[3]->innerHtml,
      'year' => $tds[4]->innerHtml,
      'comp' => 'WBC'
    ];
  }
  file_put_contents(__DIR__."/wbc/json/$year.json", json_encode($winners, JSON_NUMERIC_CHECK));
}
?>