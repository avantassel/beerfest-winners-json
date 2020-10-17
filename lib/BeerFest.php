<?php
class BeerFest {
  
  private $mongo_url = 'mongodb://localhost:27017/beer-fest';
  private $google = null;
  private $mongo_winners = null;
  private $mongo_cities = null;
  
  public function __construct($args = []){
    $this->google = new Google($args);
    
    $mongo = new MongoDB\Client($this->mongo_url);
    $this->mongo_winners = $mongo->selectDatabase('beer-fest')->winners;
    $this->mongo_cities = $mongo->selectDatabase('beer-fest')->cities;
  }
  
  public function SaveWinners($comp, $year, $winners){
    $this->mongo_winners->deleteMany(['comp' => "$comp", 'year' => (int) $year]);
    $this->mongo_winners->insertMany($winners);
  }
  
  public function SetIndexes(){
    $this->mongo_winners->createIndex(['brewery' => 1]);
    $this->mongo_winners->createIndex(['coords' => '2dsphere']);
    $this->mongo_cities->createIndex(['city' => 1, 'state_id' => 1]);
  }
  
  public function GetLocation($name, $city, $state){
    // first try google
    $location = $this->google->GeoCode("$name, $city, $state");
    
    // then try mongo
    if(empty($location)){
      $cursor = $this->mongo_cities->find(['city' => $city, 'state_id' => $state]);
      $location = $cursor->toArray();
      // white space issue
      if(empty($location)){
        $cursor = $this->mongo_cities->find(['city' => "/^$city/", 'state_id' => "/^$state/"]);
        $location = $cursor->toArray();
      }
      if(!empty($location[0]))
        return $location[0];
    }
    
    return $location ?? [];
  }
  
  public function GetWinnersByCity($year = null){
    $query = ['coords' => ['$exists' => true, '$ne' => null]];
    if(!empty($year))
      $query['year'] = $year;
    $pipeline = [
      ['$match' => $query],
      ['$group' => [
          '_id' => ['state' => '$state', 'city' => '$city'],
          'count' => ['$sum' => 1],
          'lat' => ['$first' => '$lat'],
          'lng' => ['$first' => '$lng']
        ]
      ]
    ];
    $cursor = $this->mongo_winners->aggregate($pipeline);
    return $cursor->toArray();
  }
  
  public function GetAllWinnersWithCoords(){    
    $cursor = $this->mongo_winners->find(['coords' => ['$exists' => true]]);
    return $cursor->toArray();
  }
  
  public function DownloadContent($url, $fields){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$url");
    curl_setopt($ch, CURLOPT_POST, count($fields));
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    //execute post
    $result = curl_exec($ch);

    //close connection
    curl_close($ch);
    
    return $result;
  }
}
?>