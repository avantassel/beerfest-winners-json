<?php

echo "No Need to run this as the data already exists in this repo.";
exit;

require "vendor/autoload.php";
use PHPHtmlParser\Dom;
$dom = new Dom;

$winners = [];
if (ob_get_level() == 0) ob_start();

if ($handle = opendir(__DIR__."/gabf/html")) {
  while (false !== ($file = readdir($handle))) {
    $winners = [];
    $year = '';
    $contents = file_get_contents(__DIR__."/gabf/html/$file");
    $dom->load($contents);
    $tr = $dom->find('.winners tbody tr');
    foreach($tr as $row){
      $tds = $row->find('td');
      $winners[] = [
        'medal' => trim($tds[0]->find('span')->innerHtml),
        'beer' => trim($tds[1]->innerHtml),
        'brewery' => trim($tds[2]->innerHtml),
        'city' => trim($tds[3]->innerHtml),
        'state' => trim($tds[4]->innerHtml),
        'style' => trim($tds[5]->innerHtml),
        'year' => trim($tds[6]->innerHtml),
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
    $winners = [];
    $year = '';
    $contents = file_get_contents(__DIR__."/wbc/html/$file");
    $dom->load($contents);
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
      $year = $tds[4]->innerHtml;
    }
    if(!empty($winners))
      file_put_contents(__DIR__."/wbc/json/$year.json", json_encode($winners, JSON_NUMERIC_CHECK));
  }
  closedir($handle);
}
?>