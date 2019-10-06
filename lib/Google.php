<?php
class Google
{
  private $api_key = '';
	
	public function __construct($args = []){
		if(!empty($args['api_key']))
			$this->api_key = $args['api_key'];
	}
	
	public function GeoCode($address,$format=true){
    
    if(empty($this->api_key))
      return null;
      
		$address=str_replace(' ', '+', $address);
		$address=str_replace('%20', '+', $address);

		$content=self::getData("http://maps.googleapis.com/maps/api/geocode/json?sensor=false&address=$address&key={$this->api_key}");	

		$json_response=json_decode($content);

		if(!empty($json_response->error_message)){
			throw new Error("$json_response->error_message");
			return $json_response;
		}
		
		if($format)
			return self::GeoCodeFormatted($json_response);
		else
			return $json_response;

	}

	public function ReverseGeoCode($latlng,$format=true){
    
    if(empty($this->api_key))
      return null;
      
		$content=self::getData("http://maps.googleapis.com/maps/api/geocode/json?sensor=false&latlng=$latlng&key={$this->api_key}");	

		$json_response=json_decode($content);

		if(!empty($json_response->error_message)){
      throw new Error("$json_response->error_message");
			return $json_response;
		}
		
		if($format)
			return self::GeoCodeFormatted($json_response);
		else
			return $json_response;

	}
	
	public function GeoCodeFormatted($json_response){
		
		$return = array('address'=>'','number'=>'','neighborhood'=>'','city'=>'','state'=>'','postalCode'=>'','country'=>'','country_code'=>'','lat'=>'','lng'=>'','loc'=>array(),'bounds'=>array('ne'=>'','sw'=>''),'viewport'=>array('ne'=>'','sw'=>''));

		if(!empty($json_response->results)){
			foreach($json_response->results as $result){
				if(empty($result->address_components))
					continue;
				foreach($result->address_components as $addr){
					if(empty($return['address']) && in_array( "street_number", $addr->types ))
	                    $return['address']=$addr->long_name;
	                if(empty($return['number']) && in_array( "route", $addr->types )){
	                    $return['number']=$addr->long_name;                
	                    $return['address'].=' '.$addr->long_name;                
	                }
	                if(empty($return['city']) && in_array( "locality", $addr->types ))
	                    $return['city']=$addr->long_name;
	                if(empty($return['postalCode']) && in_array( "postal_code", $addr->types ))
	                    $return['postalCode']=$addr->short_name;
	                if(empty($return['city']) && in_array( "administrative_area_level_2", $addr->types ))
	                    $return['city']=$addr->long_name;
	                if(empty($return['state']) && in_array( "administrative_area_level_1", $addr->types ))
	                	$return['state']=$addr->short_name;
	                if(empty($return['neighborhood']) && in_array( "neighborhood", $addr->types ))
	                    $return['neighborhood']=$addr->long_name;
	                if(empty($return['country_code']) && in_array( "country", $addr->types )){
	                	$return['country']=$addr->long_name;
	                	$return['country_code']=$addr->short_name;
	                }
				}
				//get geo
				if(empty($return['loc']) && !empty($result->geometry->location->lat) && !empty($result->geometry->location->lng)){
					$return['lat']=$result->geometry->location->lat;
					$return['lng']=$result->geometry->location->lng;
					//for mongodb
					$return['loc']=array((float)$return['lng'],(float)$return['lat']);

				}
				//get boundary
				if(empty($return['bounds']['ne']) && !empty($result->geometry->bounds->northeast) && !empty($result->geometry->bounds->southwest)){
					$return['bounds']['ne']=$result->geometry->bounds->northeast;
					$return['bounds']['sw']=$result->geometry->bounds->southwest;
				}
				// get viewport
				if(empty($return['viewport']['ne']) && !empty($result->geometry->viewport->northeast) && !empty($result->geometry->viewport->southwest)){
					$return['viewport']['ne']=$result->geometry->viewport->northeast;
					$return['viewport']['sw']=$result->geometry->viewport->southwest;
				}	
			}
			
		} else if(!empty($json_response->error_message)){
			throw new Error("$json_response->error_message");
		}
		return $return;
  }
  
  public function getData($url) {
    $ch = curl_init();
    $timeout = 5;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
  }
}
?>