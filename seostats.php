<?php
ini_set('display_startup_errors',1);
ini_set('display_errors',1);
set_time_limit(99999999);
error_reporting(-1);

// Depending on how you installed SEOstats
#require_once __DIR__ . DIRECTORY_SEPARATOR . 'SEOstats' . DIRECTORY_SEPARATOR . 'bootstrap.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

//html header stuff
echo <<<HERE
<!DOCTYPE html>
<html lang='en'>
<head>
<meta name='viewport' content='width=device-width, initial-scale=1', user-scalable=no'>
<link href='http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css' rel='stylesheet'>
<script src="http://code.jquery.com/jquery.js"></script>
<script src='http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js'></script>
<script type="text/javascript" src="http://www.steamdev.com/zclip/js/jquery.zclip.min.js"></script>
<script type="text/javascript" src="http://www.steamdev.com/zclip/js/jquery.snippet.min.js"></script>
</head>
<div class="container">
HERE;

//this function will... turn our csv into an array! and, btw-- the csv i'm using comes from google's webmaster tools..
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


//----------------------------------------------------------------------------------------|
//--------------------uncomment this stuff when you want to insert into MySQL!!-----------|
//----------------------------------------------------------------------------------------|

//connect to mysql, which we'll be using to keep track of everything.
//$mysqli  = new mysqli('127.0.0.1', 'root', '', 'test', '3306');

/* check connection */
//if (mysqli_connect_errno()) {
//    printf("Connect failed: %s\n", mysqli_connect_error());
//    exit();
//}

//put the path to your csv here.
$csv = csv_to_array('backlinks.csv');

use \SEOstats\Services as SEOstats;

// Create a new SEOstats instance.
$seostats = new \SEOstats\SEOstats;

//--------------------------------------------------------------------------|
//----------echo HTML, in case we just want to take a quick look------------|
//--------------------------------------------------------------------------|

echo "<div class='row'><h1>External Backlinks Stats</h1></div>";

foreach($csv as $row){

	try {
	  $url = "http://www." . $row[0];
	  
	  //get rid of that comma
	  $link_num = str_replace(",","",$row[1]);
	  $linked_pages = $row[2];
	  
	  // Bind the URL to the current SEOstats instance.
	  if($seostats->setUrl($url)) {

			//-------------------------------------------------------------------------------------------------------------------------------|
			//-----if you start getting a bunch of 0's or NA's, remember that there are pretty low limits to Alexa and Google calls...-------|
			//-------------------------------------------------------------------------------------------------------------------------------|
			$alexa = SEOstats\Alexa::getGlobalRank();
			$pagerank = SEOstats\Google::getPageRank();
			$g_links = SEOstats\Google::getBacklinksTotal("www.".$row[0]);
			$g_links_no_www = SEOstats\Google::getBacklinksTotal($row[0]);
			$g_plus_count = SEOstats\Social::getGooglePlusShares();
			$twitter_shares = SEOstats\Social::getTwitterShares();
			$linkedin_shares = SEOstats\Social::getLinkedInShares();
			
		 echo "<div class='row'><div class='col-md-12'><h3>" . $row[0] . "</h3>";
		 echo "<div class='row'><div class='col-md-7'>Alexa rank = " . SEOstats\Alexa::getGlobalRank() . "<br />";
		 echo "Daily traffic graph: ".SEOstats\Alexa::getTrafficGraph(1) . "<br /></div>";
		 echo "<div class='col-md-3'>PageRank = " . SEOstats\Google::getPageRank() . "<br />";
		 echo "Google links = " . SEOstats\Google::getBacklinksTotal("www.".$row[0]) . "<br />";
		 echo "Google links (no www) = " . SEOstats\Google::getBacklinksTotal($row[0]) . "<br />";
		 echo "Twitter shares: " . SEOstats\Social::getTwitterShares() . "<br />";
		 echo "Plus ones: " . SEOstats\Social::getGooglePlusShares() . "<br />";
		 echo "Twitter shares: " . SEOstats\Social::getTwitterShares() . "<br />";
		 echo "Linkedin shares: " . SEOstats\Social::getLinkedInShares() . "<br /><br /></div>";
		 echo "<div class='col-md-3'>SEMRush Search Engine Traffic Graph: " . SEOstats\SemRush::getDomainGraph(1) . "<br /></div></div></div></div>";
		
		 
		//----------------------------------------------------------------------------------------|
		//--------------------uncomment this stuff when you want to insert into MySQL!!-----------|
		//----------------------------------------------------------------------------------------|
		
		 /* Prepared statement, stage 1: prepare */
		//if (!($stmt = $mysqli->prepare("INSERT INTO seo (url, alexa, pagerank, link_num, linked_pages, g_links, g_links_no_www, g_plus_ones, linkedin_shares) VALUES (?,?,?,?,?,?,?,?)"))) {
		//	 echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		//}

		//if (!$stmt->bind_param("siiiiiiii", $url, $alexa, $pagerank, $link_num, $linked_pages, $g_links, $g_links_no_www, $g_plus_ones, $linkedin_shares)) {
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
