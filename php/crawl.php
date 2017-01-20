<?php 
//ini_set('max_execution_time','0');
//ini_set('display_errors','1');
$date = date('H:i:s \o\n l jS F Y');


function isValidURL($url)
{
	return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
}
// Output buffer start
// ob_start();
require 'settings.php';
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
		<title>OGC Services endpoint URLs verification script</title>
		<meta name="copyright" content="bolegweb@gmail.com">
		<meta name="author" content="bolegweb@gmail.com">
		<script src="http://cdn.jquerytools.org/1.2.6/full/jquery.tools.min.js"></script>
		<style>
			body{
				margin: 0px;
				padding: 0px;
			}
		</style>
		<script>
			function refreshMe()
			{
				document.location.href = 'https://bolegweb.geof.unizg.hr/ogcwxs/crawl.php';
			}
		</script>
	</head>
	<body onload="refreshMe()">
		<h1>OGC WxS URLs verification script monitor</h1>
		<div id="content" style="padding-left: 2em;">
			<?php 
				$q = dbq("SELECT * FROM services WHERE status NOT IN(3,11) ORDER BY time_check ASC LIMIT 1");
				if(mysql_num_rows($q))
				{
					$r = mysql_fetch_assoc($q);	
					$url = $r['url'];
					$spl = preg_split("/\?/i",$r['url']);
					$endpoint = $spl[0];
					$harvested = $r['harvested'];
					$type = $r['type'];
					$id = $r['id'];
					$harvest_file = $r['harvest_file'];
					if ( $harvested )
					{
						//echo 'NOT an WMTS';
						//$spl = preg_split("/\?/i",$r['url']);
						//$endpoint = $spl[0];
						$GetRecURL = 'http://bolegweb.geof.unizg.hr/gnk_'. $r['type'] .'/srv/eng/csw?request=GetRecords&service=CSW&version=2.0.2&resultType=hits&namespace=csw:http://www.opengis.net/cat/csw&elementSetName=brief&constraint=%3CFilter%20xmlns=%22http://www.opengis.net/ogc%22%20xmlns:gml=%22http://www.opengis.net/gml%22%3E%3CPropertyIsLike%20wildCard=%22*%22%3E%3CPropertyName%3EAnyText%3C/PropertyName%3E%3CLiteral%3E'. $endpoint .'%3C/Literal%3E%3C/PropertyIsLike%3E%3C/Filter%3E&constraintLanguage=FILTER&constraint_language_version=1.1.0';
						$GetRecURL2 = 'http://bolegweb.geof.unizg.hr/pycsw/csw.py?service=CSW&version=2.0.2&request=GetRecords&outputSchema=http://www.isotc211.org/2005/gmd&typenames=gmd:MD_Metadata&elementsetname=full&resulttype=hits&constraintlanguage=FILTER&constraint=<ogc:Filter xmlns:ogc="http://www.opengis.net/ogc"><ogc:PropertyIsLike wildCard="$"><ogc:PropertyName>apiso:AnyText</ogc:PropertyName><ogc:Literal>$http://wms.ess-ws.nrcan.gc.ca/wms/toporama_en$</ogc:Literal></ogc:PropertyIsLike></ogc:Filter>';
						
						$GetRecURL3 = 'http://bolegweb.geof.unizg.hr/pycsw/csw.py?SERVICE=CSW&REQUEST=GetRecords&version=2.0.2&elementsetname=full&resulttype=hits&typenames=csw:Record&CONSTRAINTLANGUAGE=FILTER&constraint=%3Cogc:Filter%20xmlns:ogc%3D%22http://www.opengis.net/ogc%22%20xmlns:dc%3D%22http://purl.org/dc/elements/1.1/%22%3E%3Cogc:PropertyIsLike%20wildCard%3D%22*%22%20singleChar%3D%22?%22%20escapeChar%3D%22\%22%3E%3Cogc:PropertyName%3Ecsw:AnyText%3C/ogc:PropertyName%3E%3Cogc:Literal%3E*'. $endpoint.'*dataset%3C/ogc:Literal%3E%3C/ogc:PropertyIsLike%3E%3C/ogc:Filter%3E';
						
						$GetRecOpen=fopen($GetRecURL3, "r", false);
						$cntnt = "";
						while($fr=fgets($GetRecOpen))
						{
							$cntnt .= $fr;
						}	
						$parser = xml_parser_create();
						xml_parse_into_struct($parser, $cntnt, $value_arr, $index_arr);
						xml_parser_free($parser);
						/*
						print "<pre>";
						print_r ($value_arr);
						print "</pre>";
						*/
						$matched=@$value_arr[2]['attributes']['NUMBEROFRECORDSMATCHED'];
						echo "<br><br>RESULTS:". $matched. "<br><br>";
						//dbq("UPDATE services SET metadata='$matched', time_metadata='$now' WHERE id='{$r['id']}'");
					}

					$context = stream_context_create( array(
					  'http'=>array(
						'timeout' => 30.0
					  )
					));
					$fo = fopen($url, 'r', false, $context);
					// $fo=fopen($url, "r"); // old FOPEN()
					//print_r ($fo);
					if ( !$fo ) {
					  // TIMEOUT 30s
					  dbq("UPDATE services SET status = 2, status_fail_timeout=$now, time_check='$now', endpoint='$endpoint' WHERE id='{$r['id']}'");
					  echo "<br><h3>STATUS: <i>TIMEOUT 30s EXCEEDED</i></h3>";
					}
					else {	
						// fopen success
						dbq("UPDATE services SET status=11 WHERE id='{$r['id']}'");
						$cntnt = "";
						while($fr=fgets($fo))
						{
							$cntnt .= $fr;
						}	
						
						$p = xml_parser_create();
						xml_parse_into_struct($p, $cntnt, $vals, $index);
						xml_parser_free($p);
						
						$version = @$vals[0]['attributes']['VERSION'];
						//echo $version;
						$now=time();
						if(strlen($version) >= 5)
						{
							dbq("UPDATE services SET version='$version', status=1, time_check='$now', endpoint='$endpoint' WHERE id='{$r['id']}'");
							echo "<br><h3>STATUS: <i>OK</i></h3>";
							
							if ($type == 'sos' && $version == '1.0.0'){
								 $resourceType = 'http://www.opengis.net/sos/1.0';
								 $resourceFolder = 'sos1';

							}
							if ($type == 'sos' && $version == '2.0.0'){
								 $resourceType = 'http://www.opengis.net/sos/2.0';
								 $resourceFolder = 'sos2';

							}
							if ($type == 'csw' && $version == '2.0.2'){
								 $resourceType = 'http://www.opengis.net/cat/csw/2.0.2';
								 $resourceFolder = 'csw202';

							}
							if ($type == 'wms' && $version == '1.1.1'){
								 $resourceType = 'http://www.opengis.net/wms';
								 $resourceFolder = 'wms111';

							}
							if ($type == 'wms' && $version == '1.3.0'){
								 $resourceType = 'http://www.opengis.net/wms';
								 $resourceFolder = 'wms130';

							}
							if ($type == 'wmts' && $version == '1.0.0'){
								 $resourceType = 'http://www.opengis.net/wmts/1.0';
								 $resourceFolder = 'wmts100';

							}
							if ($type == 'wfs' && $version == '1.1.0'){
								 $resourceType = 'http://www.opengis.net/wfs';
								 $resourceFolder = 'wfs110';

							}
							if ($type == 'wfs' && $version == '1.0.0'){
								 $resourceType = 'http://www.opengis.net/wfs';
								 $resourceFolder = 'wfs100';

							}
							if ($type == 'wfs' && $version == '2.0.0'){
								 $resourceType = 'http://www.opengis.net/wfs';
								 $resourceFolder = 'wfs200';

							}
							if ($type == 'wcs' && $version == '1.0.0'){
								 $resourceType = 'http://www.opengis.net/wcs';
								 $resourceFolder = 'wcs100';

							}
							if ($type == 'wps' && $version == '1.0.0'){
								 $resourceType = 'http://www.opengis.net/wps/1.0.0';
								 $resourceFolder = 'wps100';
								 
							}
							
							if ($resourceType != ''){
							$pycswXML = '
							<Harvest xmlns="http://www.opengis.net/cat/csw/2.0.2" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.opengis.net/cat/csw/2.0.2 http://schemas.opengis.net/csw/2.0.2/CSW-publication.xsd" service="CSW" version="2.0.2">
								<Source>'. $endpoint . '</Source>
								<ResourceType>'. $resourceType .'</ResourceType>
								<ResourceFormat>application/xml</ResourceFormat>
							</Harvest>'; //<ResponseHandler>pycsw/'.$resourceFolder.'/results/'.$id.'.txt</ResponseHandler>;
							$fh = fopen('pycsw/'.$resourceFolder.'/'.$id.'.xml', 'w'); 
								 fwrite($fh, $pycswXML);
							$harvest_file_path = ('pycsw/'.$resourceFolder.'/'.$id.'.xml');
								 
							dbq("UPDATE services SET harvest_file='$harvest_file_path' WHERE id='$id'");
							}
							//echo '<pre>', htmlentities($pycswXML), '</pre>';;
							
						}
						else
						{
							dbq("UPDATE services SET status=2, status_fail_version=$now, time_check='$now', endpoint='$endpoint', harvest_file='' WHERE id='{$r['id']}'");
							echo "<br><h3>STATUS: <i>VERSION NOT DETECTED IN //@version </i></h3>";
						}						
					}
					$parse = parse_url($url);
						$hostname=$parse['host'];
						$country=geoip_country_name_by_name($hostname);
						if ($country) {
							dbq("UPDATE services SET location='$country' WHERE id='{$r['id']}'");
						}
					
				}
				else
				{
					$url = "not-available";
					$version = "not-available";
				}
				
			?>
			<h3>Verified OGC service endpoint ID: <i><?php echo $id; ?></i></h3>
			<h3>Verified URL address: <i><?php echo $url; ?></i></h3>
			<h3>OGC Service Type: <i><?php echo strtoupper($type); ?></i></h3>
			<h3>Version detected: <i><?php echo $version; ?></i></h3>
			<h3>Service endpoint: <i><?php echo $endpoint; ?></i></h3>
			<h3>Server location: <i><?php echo $country; ?></i></h3>
			<h3>Verification date: <i><?php echo $date; ?></i></h3>
			<h3>Harvesting file: <i><?php echo $harvest_file_path; ?></i></h3>
			<h3>Has been harvested: <i><?php echo $harvested; ?></i></h3>
			<h3>Metadata available: <i><?php echo $matched; ?></i></h3>

		</div>	
	</body>
</html>
