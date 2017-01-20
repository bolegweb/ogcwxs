<?php
header('Content-Type: application/json');
require '../settings.php';
// VARIABLES AS HTTP GET PARAMETERS
$title=$_GET['title'];
$type = $_GET['type'];
$version = $_GET['version'];
$url=$_GET['url'];
$location = $_GET['location'];
$impDate = $_GET['import_date'];
$status = $_GET['status'];
$checDate = $_GET['check_date'];
$metadata = $_GET['metadata'];
$out=array();
$i=0;
$q="SELECT * FROM services WHERE ";
if ($title || $type || $location || $status || $version || $url || $impDate || $checDate || $metadata)
{
	// dosla aspon jedna z hladanych premennych
	
	if($title )
	{
		// dosiel title
		$q .= "title LIKE '$title' AND ";
		
	}
	if($type )
	{
		// dosiel type
		$q .= "type LIKE '$type' AND ";
		
	}
	if($location )
	{
		// dosiel location
		$q .= "location LIKE '$location' AND ";
		
	}
	
	if($status )
	{
		// dosiel status
		$q .= "status LIKE '$status' AND ";
		
	}
	
	if($version )
	{
		// dosiel version
		$q .= "version LIKE '$version' AND ";
		
	}
	
	if($url )
	{
		// dosiel url
		$q .= "url LIKE '$url' AND ";
		
	}
	
	if($checDate )
	{
		// dosiel checDate
		$q .= "checDate LIKE '$checDate' AND ";
		
	}
	
	if($impDate )
	{
		// dosiel impDate
		$q .= "impDate LIKE '$impDate' AND ";
		
	}
	
	if($metadata )
	{
		// dosiel metadata
		$q .= "metadata LIKE '$metadata' AND ";
		
	}

}
else {
	$q .= "1";
}
$q = rtrim($q, "AND "); 
$run = dbq($q);

if(!mysql_num_rows($run))
 {
 	$out = "No results";
 	
 }
while ($r = mysql_fetch_assoc($run) ) {
	$out[$i]['id'] = $r['id'];
	$out[$i]['title'] = $r['title'];
	$out[$i]['url'] = $r['url'];
	$out[$i]['importDate'] = date('c', $r['time_import']);
	$out[$i]['location'] = $r['location'];
	$out[$i]['endpoint'] = $r['endpoint'];
	$out[$i]['type'] = $r['type'];
	$out[$i]['version'] = $r['version'];
	$out[$i]['status'] = $r['status'];
	//$out[$i]['statusDate'] = date('d/m/Y H:i:s', $r['time_check']);
	$out[$i]['statusDate'] = date('c', $r['time_check']);
	$out[$i]['harvested'] = $r['harvested'];
	$out[$i]['harvestingDate'] = date('c', $r['time_harvest']);
	$out[$i]['metadata'] = $r['metadata'];
	$out[$i]['metadataDate'] = date('c', $r['time_metadata']); 
	
	$i++;
}
//print "<pre>";
print json_encode($out);
//print "</pre>";
?>