<?php
$results = new stdClass();

function getTotalPageCount($city,$category) {
	$params = array(
					't.p' => 'abcd', /*Partner ID */
					't.k' => '1234', /*Partner key*/
					'userip' => '0.0.0.0',
					'v' => '1',
						'format' => 'json',
					'action' => $category,
					'countryId' => '1',
					'city' => $city,
					'pn' => 1,
					'ps' => 50
			  );
	
	// Access Token request
	$url = 'http://api.glassdoor.com/api/api.htm?' . http_build_query($params);
	$response = file_get_contents($url);
	
	$result = json_decode($response);

	return floor($result->response->totalNumberOfPages/10);
}
function getCompanyRatingsByCity($city,$pagenum) {
	if(strlen($city)==2){
			$params = array(
					't.p' => '201954',
					't.k' => 'jt63OtDFZI3',
					'userip' => '0.0.0.0',
					'v' => '1',
						'format' => 'json',
					'action' => 'employers',
					'countryId' => '1',
					'state' => $city,
					'pn' => $pagenum,
					'ps' => 50
			  );
	}
	else{
	$params = array(
					't.p' => '201954',
					't.k' => 'jt63OtDFZI3',
					'userip' => '0.0.0.0',
					'v' => '1',
						'format' => 'json',
					'action' => 'employers',
					'countryId' => '1',
					'city' => $city,
					'pn' => $pagenum,
					'ps' => 50
			  );
	}
	// Access Token request
	$url = 'http://api.glassdoor.com/api/api.htm?' . http_build_query($params);
	$response = file_get_contents($url);
	
	$result = json_decode($response);
			//print_r($result->response->totalRecordCount);
echo $url;
	return $result->response->employers;
}
function FillCompanyRatingsByCity($city,$page){
	$servername = "localhost";
$username = "root";
$password = "";
$dbname = "crawledin";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
for($a = 0; $a < $page; $a++) {
	$results = getCompanyRatingsByCity($city,$a);

for($i = 0; $i < 10; $i++) {
	$nameofCompany = mysqli_escape_string($conn, $results[$i]->name);
	$sql = "INSERT INTO companyratings (Id, Name, Overall_Rating, Industry, location)
	VALUES ('".$results[$i]->id."', '".$nameofCompany."', '".$results[$i]->overallRating."', '".$results[$i]->industry."', '".$city."')";

if ($conn->query($sql) === TRUE) {
} else {
}

}
}
$conn->close();

}
function getJobStatResults() {
	// echo $_SERVER['HTTP_USER_AGENT'];
    $ua = ''.$_SERVER['HTTP_USER_AGENT'];
	$params = array('v' => 1,
					'format' => 'json',
					't.p' => '201937',
					't.k' => 'ekzeKEVcdNw',
					'userip' => '0.0.0.0',
					'useragent' => $ua,
//					'q' => 'analyst',
					'l' => 'Newark',	

					'returnEmployers' => 'true',
					'jt' => 'cashier',
//					'jc' => 'Software',		
					'action' => 'jobs-stats',
					'returnCities' => 'true',
					'admLevelRequested' => 1

			  );
	
    // Authentication request
    $url = 'https://api.glassdoor.com/api/api.htm?' . http_build_query($params);
	$response = file_get_contents($url);
	// Native PHP object, please
	$result = json_decode($response);
			
	return $result;

}

function SortCompanyRatingsByCity($city){
	$servername = "localhost";
$username = "root";
$password = "";
$dbname = "crawledin";
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
$sql = "SELECT name,overall_rating FROM `companyratings` WHERE LOCATION = '".$city."' ORDER BY `companyratings`.`OVERALL_RATING` DESC LIMIT 20";  
// perform the query and store the result
$result = $conn->query($sql);
$json = array();
// if the $result contains at least one row
if ($result->num_rows > 0) {
  // output data of each row from $result
  while($row = $result->fetch_assoc()) {
	  
	$row['overall_rating'] = floatval($row['overall_rating']);
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


//FillCompanyRatingsByCity("new york",3);


?>
