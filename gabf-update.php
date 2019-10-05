<?php
require "vendor/autoload.php";
use PHPHtmlParser\Dom;
$dom = new Dom;

$year = date('Y');
$fields = ['action' => 'search-winners',
  '_medal' => 0,
  '_state' => 0,
  '_style' => 0,
  '_year' => $year,
  '_fulltext' => ''
];
$winners = [];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://www.greatamericanbeerfestival.com/wp-admin/admin-ajax.php');
curl_setopt($ch, CURLOPT_POST, count($fields));
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

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
if(!empty($winners))
  file_put_contents(__DIR__."/gabf/json/$year.json", json_encode($winners, JSON_NUMERIC_CHECK));
?>