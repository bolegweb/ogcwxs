<?php
// sem treba dat settings aby bolo DBQ do dbq
require '../settings.php';

$status = $_GET['status'];
$type = $_GET['type'];
$version = $_GET['version'];
$location = $_GET['location'];
$metadata = $_GET['metadata'];


$out=array();
$i=0;

if($type && $version && $status && $location)
{
$q=dbq("SELECT id, title, type, version, status, url, location, time_import, time_check, id_gn FROM services WHERE type = '$type' AND version = '$version' AND status = '$status' AND location LIKE '$location'");
}
else
{
if($status>0){
	$q=dbq("SELECT id, title, type, version, status, url, location, time_import, time_check, id_gn FROM services WHERE status='$status'");
}
else
{
$q=dbq("SELECT id, title, type, version, status, url, location, time_import, time_check, time_metadata, harvested, id_gn, metadata FROM services");
}
}


while ($r = mysql_fetch_assoc($q) ) {

	$out[$i]['id'] = $r['id'];
	$out[$i]['title'] = $r['title'];
	$out[$i]['url'] = $r['url'];
	$out[$i]['type'] = $r['type'];
	$out[$i]['importDate'] = date('H:i:s d/m/Y', $r['time_import']);
	$out[$i]['version'] = $r['version'];
	$out[$i]['status'] = $r['status'];
	$out[$i]['statusDate'] = date('d/m/Y H:i:s', $r['time_check']);
	$out[$i]['location'] = $r['location'];
	$out[$i]['harvestingDate'] = date('d/m/Y H:i:s', $r['time_harvest']);
	$out[$i]['metadata'] = $r['metadata'];
	$out[$i]['metadataDate'] = date('d/m/Y H:i:s', $r['time_metadata']);
	// dalej si to dopises ...
	
	$i++;
}
print json_encode($out);	
?>