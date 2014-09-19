<?php
ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(-1);

// Depending on how you installed SEOstats
#require_once __DIR__ . DIRECTORY_SEPARATOR . 'SEOstats' . DIRECTORY_SEPARATOR . 'bootstrap.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

//this function will... turn our csv into an array!
function csv_to_array($filename='')
{
    if(!file_exists($filename) || !is_readable($filename))
        return FALSE;

    $header = NULL;
    $data = array();
    if (($handle = fopen($filename, 'r')) !== FALSE)
    {
        while (($row = fgets($handle)) !== FALSE)
        {
            $data[] = $row;
        }
        
        fclose($handle);
    }
    $final_data = array();
    foreach($data as $parse_it){
    	$final_data[] = str_getcsv($parse_it, ',', '"');
    	}
    return $final_data;
}


//connect to mysql, which we'll be using to keep track of everything.
$mysqli  = new mysqli('127.0.0.1', 'root', '', 'test', '3306');

/* check connection */
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

//put the path to your csv here.. this one comes from google webmaster tools. 
$csv = csv_to_array('backlinks.csv');

use \SEOstats\Services as SEOstats;

// Create a new SEOstats instance.
$seostats = new \SEOstats\SEOstats;

echo "External Backlinks Quality Checks <br /><br />";

foreach($csv as $row){

	try {
	  $url = "http://www." . $row[0];
	  
	  //get rid of that comma
	  $link_num = str_replace(",","",$row[1]);
	  $linked_pages = $row[2];
	  
	  // Bind the URL to the current SEOstats instance.
	  if($seostats->setUrl($url)) {

			$alexa = SEOstats\Alexa::getGlobalRank();
			$pagerank = SEOstats\Google::getPageRank();
		
		 echo "<h3>" . $domains[0] . "</h3>Alexa rank = " . SEOstats\Alexa::getGlobalRank() . "<br />";
		 echo "PageRank = " . SEOstats\Google::getPageRank() . "<br /><br />";
		 
		//----------------------------------------------------------------------------------------|
		//--------------------uncomment this stuff when you want to insert into MySQL!!-----------|
		//----------------------------------------------------------------------------------------|
		
		 /* Prepared statement, stage 1: prepare */
		//if (!($stmt = $mysqli->prepare("INSERT INTO seo (url, alexa, pagerank, link_num, linked_pages) VALUES (?,?,?,?,?)"))) {
		//	 echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		//}

		//if (!$stmt->bind_param("siiii", $url, $alexa, $pagerank, $link_num, $linked_pages)) {
		//	 echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		//}

		//if (!$stmt->execute()) {
		//	 echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		//}
		 
	  }
	}
	catch (SEOstatsException $e) {
	  die($e->getMessage());
	}
}
?>
