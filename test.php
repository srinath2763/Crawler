<!-- Styles -->
<style>
#chartdiv {
	width	: 100%;
	height	: 500px;
}
										
</style>

<!-- Resources -->
<script src="https://www.amcharts.com/lib/3/amcharts.js"></script>
<script src="https://www.amcharts.com/lib/3/serial.js"></script>
<script src="https://www.amcharts.com/lib/3/plugins/export/export.min.js"></script>
<link rel="stylesheet" href="https://www.amcharts.com/lib/3/plugins/export/export.css" type="text/css" media="all" />
<script src="https://www.amcharts.com/lib/3/themes/light.js"></script>

<!-- Chart code -->
<script>
var chart = AmCharts.makeChart("chartdiv", {
    "type": "serial",
    "theme": "chalk",
    "marginRight": 40,
    "marginLeft": 40,
    "autoMarginOffset": 20,
    "mouseWheelZoomEnabled":true,
    "dataDateFormat": "YYYY-MM-DD",
    "valueAxes": [{
        "id": "v1",
        "axisAlpha": 0,
        "position": "left",
        "ignoreAxisWidth":true
    }],
    "balloon": {
        "borderThickness": 1,
        "shadowAlpha": 0
    },
    "graphs": [{
        "id": "g1",
        "balloon":{
          "drop":true,
          "adjustBorderColor":false,
          "color":"#ffffff"
        },
        "bullet": "round",
        "bulletBorderAlpha": 1,
        "bulletColor": "#FFFFFF",
        "bulletSize": 5,
        "hideBulletsCount": 50,
        "lineThickness": 2,
        "title": "red line",
        "useLineColorForBulletBorder": true,
        "valueField": "JOBCOUNT",
        "balloonText": "<span style='font-size:18px;'>[[value]]</span>"
    }],
    "chartScrollbar": {
        "graph": "g1",
        "oppositeAxis":false,
        "offset":30,
        "scrollbarHeight": 80,
        "backgroundAlpha": 0,
        "selectedBackgroundAlpha": 0.1,
        "selectedBackgroundColor": "#888888",
        "graphFillAlpha": 0,
        "graphLineAlpha": 0.5,
        "selectedGraphFillAlpha": 0,
        "selectedGraphLineAlpha": 1,
        "autoGridCount":true,
        "color":"#AAAAAA"
    },
    "chartCursor": {
        "pan": true,
        "valueLineEnabled": true,
        "valueLineBalloonEnabled": true,
        "cursorAlpha":1,
        "cursorColor":"#258cbb",
        "limitToGraph":"g1",
        "valueLineAlpha":0.2,
        "valueZoomable":true
    },
    "valueScrollbar":{
      "oppositeAxis":false,
      "offset":50,
      "scrollbarHeight":10
    },
    "categoryField": "UPDATETIME",
    "categoryAxis": {
        "parseDates": true,
        "dashLength": 1,
        "minorGridEnabled": true
    },
    "export": {
        "enabled": true
    },
    "dataProvider": <?php echo FetchCityJobTrends('los angeles');?>
});


</script>

<!-- HTML -->
<div id="chartdiv"></div>
<?php 

function getCityJobs($city,$days) {
	
	// echo $_SERVER['HTTP_USER_AGENT'];
    $ua = ''.$_SERVER['HTTP_USER_AGENT'];
	$params = array('v' => 1,
					'format' => 'json',
					't.p' => '1234',
					't.k' => '1234',
					'userip' => '0.0.0.0',
					'useragent' => $ua,
						'l' => $city,
					'action' => 'jobs-stats',
					'fromAge' => $days,
					'returnCities' => 'true',
					'admLevelRequested' => 2

			  );
	
    // Authentication request
    $url = 'https://api.glassdoor.com/api/api.htm?' . http_build_query($params);
	$response = file_get_contents($url);
	// Native PHP object, please
	$result = json_decode($response);
			 
			//print_r($result->response->attributionURL);
			//print_r($result->response->cities[0]);
			//var_dump($result->response->cities[0]->numJobs);
			//echo "  ";
			$count = $result->response->cities[0]->numJobs;
	return $count;

}
function getCityJobTrends($city,$days) {
	if($days>1){
		$currentdaycount = getCityJobs($city,$days);
		$nextday = $days - 1;
		$nextdaycount = getCityJobs($city,$nextday);
		$actualcount = $currentdaycount - 	$nextdaycount;
		return $actualcount;
	}
	else{
		$actualcount = getCityJobs($city,$days);
		return $actualcount;
		
	}
}
function FillCityJobTrends($state) {
	
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
	
	for($i = 7; $i > 0; $i--) {
			$numjobs = getCityJobTrends($state,$i);;

		//$cityName = mysqli_escape_string($conn, $results[$i]->name);
		$sql = "INSERT INTO job_count_trends (LOCATION,JOBCOUNT, UPDATETIME)
		VALUES ('".$state."', '".$numjobs."',  DATE_SUB(CURRENT_DATE, INTERVAL '".$i."' DAY))";

	if ($conn->query($sql) === TRUE) {
	} else {
	}
	}
	$conn->close();
}
function FetchCityJobTrends($state) {
	
	$servername = "localhost";
	$username = "root";
	$password = "";
	$dbname = "crawledin";
	$conn = new mysqli($servername, $username, $password, $dbname);
	// Check connection
	$sql = "SELECT  JOBCOUNT,UPDATETIME 
		FROM job_count_trends
		WHERE LOCATION = '".$state."';"; 
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
	//echo json_encode($data);
	return json_encode($data);
	$conn->close();
}
//FetchCityJobTrends('los angeles');
//FillCityJobTrends('los angeles');
/*getCityJobTrends('santa barbara',7);
getCityJobTrends('santa barbara',6);
getCityJobTrends('santa barbara',5);
getCityJobTrends('santa barbara',4);
getCityJobTrends('santa barbara',3);
getCityJobTrends('santa barbara',2);
getCityJobTrends('santa barbara',1);
//getCityJobs('santa barbara',7);*/
?>
