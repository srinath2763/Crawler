<?php
include('simple_html_dom.php');
function getStateJobsUrl($state) {
	// echo $_SERVER['HTTP_USER_AGENT'];
    $ua = ''.$_SERVER['HTTP_USER_AGENT'];
	$params = array('v' => 1,
					'format' => 'json',
					't.p' => '1234',
					't.k' => '1234',
					'userip' => '0.0.0.0',
					'useragent' => $ua,
						'l' => $state,
					'action' => 'jobs-stats',
					'fromAge' => 1,
					'returnCities' => 'true',
					'admLevelRequested' => 2

			  );
	
    // Authentication request
    $url = 'https://api.glassdoor.com/api/api.htm?' . http_build_query($params);
	$response = file_get_contents($url);
	// Native PHP object, please
	$result = json_decode($response);
			//print_r($result->response->cities[0]->numJobs);
			//print_r($result->response->attributionURL);
			//print_r($result);
	return $result->response->attributionURL;

}
function GetStateJobCount($state){
	$url = getStateJobsUrl($state);
$options = array(
  'http'=>array(
    'method'=>"GET",
	
    'header'=>"Content-type: application/x-www-form-urlencoded
	Accept-language: en\r\n" .
              "Cookie: foo=bar\r\n" .  // check function.stream-context-create on php.net
              "User-Agent: Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011-10-16 20:23:10\r\n" // i.e. An iPad 
  )
);

$context = stream_context_create($options);
$file = file_get_contents($url, false, $context);
//echo $file;
$html = new simple_html_dom();
$html->load($file);
 
# get an element representing the second paragraph
$element = $html->find("p");
$JobCount = explode("Jobs", $element[1]);
$countvalue = explode(">", $JobCount[0]);
//var_dump($countvalue[1]);
//echo $countvalue[1];
return $countvalue[1];

}

function FillStateJobCount($state){
	$servername = "localhost";
$username = "root";
$password = "";
$dbname = "crawledin";
$end = GetStateJobCount($state);
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

$sql = "INSERT INTO state_job_counts (state_id, jobcount,updatetime)VALUES ('".$state."', '".$end."', CURRENT_DATE )";
$conn->query($sql);

//echo $sql;
$conn->close();

}
function FillMapData1(){
$states = array( 
		"AK", "AL", "AR", "AZ", "CA", "CO", "CT", "DC",  
    "DE", "FL", "GA", "HI", "IA", "ID", "IL", "IN", "KS", "KY", "Louisiana",  
    "MA", "MD", "ME", "MI", "MN", "MO", "MS", "MT");
	for ($i = 0; $i < count($states); $i++) {
		FillStateJobCount($states[$i]);
  
}
}
function FillMapData2(){
$states = array( 
		"NC", "ND", "NE",  
    "NH", "NJ", "NM", "NV", "NY", "OH", "OK", "OR", "PA", "RI", "SC",  
    "SD", "TN", "TX", "UT", "VA", "VT", "WA", "WI", "WV", "WY");
	for ($i = 0; $i < count($states); $i++) {
		FillStateJobCount($states[$i]);
  
}
}
function FillGeoInfo(){
$statesjson = file_get_contents('states.json');
  $GeoInfo = json_decode($statesjson);
  $servername = "localhost";
$username = "root";
$password = "";
$dbname = "crawledin";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
for($i = 0; $i < 50; $i++) {
	$sql = "INSERT INTO geo_info (state_id, latitude,longitude)
	VALUES ('".$GeoInfo[$i]->title."', '".$GeoInfo[$i]->latitude."', '".$GeoInfo[$i]->longitude."')";

if ($conn->query($sql) === TRUE) {
} else {
}

}}
function getMapData(){
	$servername = "localhost";
$username = "root";
$password = "";
$dbname = "crawledin";
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
$sql = "SELECT  '0.5' as 'scale',sd.STATE_ID as 'title', p.LATITUDE as 'latitude', p.LONGITUDE as 'longitude', sd.JOBCOUNT AS 'jobs'
FROM state_job_counts AS sd
    JOIN geo_info AS p 
    ON sd.STATE_ID = p.STATE_ID
WHERE sd.UPDATETIME = '2017-11-29';"; 
// perform the query and store the result
$result = $conn->query($sql);
$json = array();
// if the $result contains at least one row
if ($result->num_rows > 0) {
  // output data of each row from $result
  while($row = $result->fetch_assoc()) {
	   $row['zoomLevel'] = 5;
	  $row['scale'] = 0.5;
	  	$row['latitude'] = floatval($row['latitude']);
	$row['longitude'] = floatval($row['longitude']);

	//$row['overall_rating'] = floatval($row['overall_rating']);
	  /*echo $row['OVERALL_RATING'];
	    echo '<br>';*/
		$json =$row;
		   $data[] = $json;
    //$jsonres .= json_encode($row);
  }
}
else {
  echo '0 results';
}
return json_encode($data);
$conn->close();
}
function getTopJobStatResults($state,$days) {
	// echo $_SERVER['HTTP_USER_AGENT'];
    $ua = ''.$_SERVER['HTTP_USER_AGENT'];
	$params = array('v' => 1,
					'format' => 'json',
					't.p' => '201954',
					't.k' => 'jt63OtDFZI3',
					'userip' => '0.0.0.0',
					'useragent' => $ua,
						'l' => $state,
				
					'action' => 'jobs-stats',
					'fromAge' => $days,
					'returnCities' => 'true',
					'returnJobTitles' => 'true',
					'admLevelRequested' => 2

			  );
	
    // Authentication request
    $url = 'https://api.glassdoor.com/api/api.htm?' . http_build_query($params);
	$response = file_get_contents($url);
	// Native PHP object, please
	$result = json_decode($response);
			//print_r($result->response->cities[0]->numJobs);
			//print_r($result->response->attributionURL);
			//print_r($result);
	return $result->response->cities;

}
function FillCityJobStatResults($state) {
	if(strlen($state)>2){
		$state = 'NY';
		
	}
	$servername = "localhost";
	$username = "root";
	$password = "";
	$dbname = "crawledin";
	
	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	// Check connection
	if ($conn->connect_error) {
    	die("Connection failed: " . $conn->connect_error);
	} 
	$results = getTopJobStatResults($state,1);
	
	for($i = 0; $i < 5; $i++) {
		//$cityName = mysqli_escape_string($conn, $results[$i]->name);
		$sql = "INSERT INTO TopCitiesByState (NUMJOBS, CITY, STATE_ID, UPDATETIME)
		VALUES ('".$results[$i]->numJobs."', '".$results[$i]->name."', '".$results[$i]->stateAbbreviation."', CURRENT_DATE )";

	if ($conn->query($sql) === TRUE) {
	} else {
	}
	}
	$conn->close();
}
function getJobStatData($state){
	if(strlen($state)>2){
		$state = 'NY';
		
	}
	$servername = "localhost";
	$username = "root";
	$password = "";
	$dbname = "crawledin";
	$conn = new mysqli($servername, $username, $password, $dbname);
	// Check connection
	$sql = "SELECT CITY, NUMJOBS 
		FROM TopCitiesByState
		WHERE STATE_ID = '".$state."' AND UPDATETIME = CURRENT_DATE ORDER BY NUMJOBS DESC LIMIT 5;"; 
	// perform the query and store the result
	$result = $conn->query($sql);
	$json = array();
	// if the $result contains at least one row
	if ($result->num_rows > 0) {
  		// output data of each row from $result
  		while($row = $result->fetch_assoc()) {
			$json =$row;
		   	$data[] = $json;
  		}
	} else {
  		echo '0 results';
	}
	return json_encode($data);
	$conn->close();
}
//FillGeoInfo();
/*"NC", "ND", "NE",  
    "NH", "NJ", "NM", "NV", "NY", "OH", "OK", "OR", "PA", "RI", "SC",  
    "SD", "TN", "TX", "UT", "VA", "VT", "WA", "WI", "WV", "WY"*/
//FillStateJobCount("DC");
  FillCityJobStatResults("CA");
//FillMapData2();
//FillStateJobCount("CA");
?>
