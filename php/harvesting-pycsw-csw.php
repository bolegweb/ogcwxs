<?php
//error_reporting(E_ALL);
//ini_set('display_errors','1');


require 'settings.php';

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
		<title>Bolegweb Catalogue Services for Metadata Availability Script Monitor</title>
		<meta name="copyright" content="bolegweb@gmail.com">
		<meta name="author" content="bolegweb@gmail">
		<script src="http://cdn.jquerytools.org/1.2.6/full/jquery.tools.min.js"></script>		
		<style>
			body{
				margin: 0px;
				padding: 0px;
			}
			h3{
				padding-left: 15px;
			}
			h1{
				padding-left: 10px;
			}
			p{
				padding-left: 20px;
			}
		</style>
		
		<script>
			function refresh()
			{
				window.setTimeout(vec, 3000);
			}
			function vec()
			{
				document.location.href = 'harvesting-pycsw-csw.php';
			}
		</script>
	
	</head>
	<body onload="refresh()">
		<h1>Metadata counting checking process overview:</h1>
		<div id="content">
			<?php 
		
				// SELECT ALL SERVICES ENDPOINT READY FOR HARVESTING FROM THE DATABASE
				$q = dbq("SELECT * FROM services WHERE type='csw' AND version='2.0.2' AND status=1 AND HARVESTED=0 ORDER BY id DESC LIMIT 1");				
				// lupuj dokial su riadky:
				if(!mysql_num_rows($q))
				{
					echo "<h3>Metadata count checking has been finished! </h3>";
					
				}
				while($r = mysql_fetch_assoc($q))
				{
					// DEFINE VARIABLES
					$now=time();
					//$curlError = '';
					$url = $r['url'];
					$status = $r['status'];
					$endpoint = $r['endpoint'];
					$type = $r['type'];
					$version = $r['version'];
					$id = $r['id'];
					$harvest_file = $r['harvest_file'];
					$harvested = $r['harvested'];
					$harvest_time = $r['time_harvest'];
					$mdInserted = $r['mdInserted'];
					$mdUpdated = $r['mdUpdated'];
					$mdDeleted = $r['mdDeleted'];
					$cswException = $r['csw_exception'];

					?>
					<!--<pre><?php //print_r($r); ?></pre>-->
					<?php 
					

					echo "<h3>RUNNING METADATA COUNT TASK FOR:</h3>
					<p>Service type: $type</p>
					<p>Version: $version</p>
					<p>Internal ID: $id</p>
					<p>Endpoint: $endpoint</p>";
					$url=$endpoint;
					$xml = '<csw:GetRecords xmlns:csw="http://www.opengis.net/cat/csw/2.0.2" xmlns:ogc="http://www.opengis.net/ogc" service="CSW" version="2.0.2" resultType="hits" startPosition="1" maxRecords="15" outputFormat="application/xml" outputSchema="http://www.opengis.net/cat/csw/2.0.2" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.opengis.net/cat/csw/2.0.2 http://schemas.opengis.net/csw/2.0.2/CSW-discovery.xsd"><csw:Query typeNames="csw:Record"><csw:ElementSetName>full</csw:ElementSetName></csw:Query></csw:GetRecords>';
					$ch = curl_init($url);
					curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
					curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					$output = curl_exec($ch);
					if(curl_exec($ch) === false)
							{
								echo "<h3>RESULTS:</h3><p>Curl error: " . curl_error($ch). "</p>";
								$curlError = curl_error($ch);
							}
					if (!curl_errno($ch)) {
						echo "<h3>RESULTS:</h3><p>HTTP POST Operation completed without any errors.</p>";
						$info = curl_getinfo($ch);
						$httpCode = $info['http_code'];
					}
					curl_close($ch);
					$p = xml_parser_create();
					xml_parse_into_struct($p, $output, $vals, $index);
					xml_parser_free($p);
					?>
					
					<pre><?php	//print_r($vals); ?></pre>
	
					<?php
					if ($httpCode == 200){
						$mdTotal1 = $vals[1]['attributes']['NUMBEROFRECORDSMATCHED'];
						$mdTotal2 = $vals[2]['attributes']['NUMBEROFRECORDSMATCHED'];
						$mdTotal3 = $vals[3]['attributes']['NUMBEROFRECORDSMATCHED'];
						$mdTotal32 = $vals[3]['attributes']['NUMBEROFRECORDSRETURNED'];
						$mdTotal4 = $vals[4]['attributes']['NUMBEROFRECORDSMATCHED'];
						$mdTotal5 = $vals[5]['attributes']['NUMBEROFRECORDSMATCHED'];
						$owsExceptionCode = $vals[1]['attributes']['EXCEPTIONCODE'];
						$owsExceptionLocator = $vals[1]['attributes']['LOCATOR'];
						$owsExceptionText = str_replace("'", "", $vals[2]['value']);
						$tag = $vals[0]['tag'];
						//echo $output;
						$postOutput = substr((strip_tags($output)),0,150);
						//echo $httpCode;
						if (isset($mdTotal1)){
							echo "<h3>CASE 1</h3>";
							echo "<h3>NUMBER OF METADATA RECORDS MATCHED: ". $mdTotal1 ."</h3>";
							$uq = "UPDATE services SET harvested=1, time_harvest='$now', metadata='$mdTotal1' WHERE id='$id'";
							dbq($uq);
						}
						
						
						if (isset($mdTotal2)){
							echo "<h3>CASE 2</h3>";
							echo "<h3>NUMBER OF METADATA RECORDS MATCHED: ". $mdTotal2 ."</h3>";
							$uq = "UPDATE services SET harvested=1, time_harvest='$now', metadata='$mdTotal2' WHERE id='$id'";
							dbq($uq);
						}

						if (isset($mdTotal3)){
							echo "<h3>CASE 3</h3>";
							echo "<h3>NUMBER OF METADATA RECORDS MATCHED: ". $mdTotal3 ."</h3>";
							$uq = "UPDATE services SET harvested=1, time_harvest='$now', metadata='$mdTotal3' WHERE id='$id'";
							dbq($uq);
						}
						
						if (isset($mdTotal32) && !isset($mdTotal3)){
							echo "<h3>CASE 32</h3>";
							echo "<h3>NUMBER OF METADATA RECORDS MATCHED: ". $mdTotal32 ."</h3>";
							$uq = "UPDATE services SET harvested=1, time_harvest='$now', metadata='$mdTotal32' WHERE id='$id'";
							dbq($uq);
						}

						if (isset($mdTotal4)){
							echo "<h3>CASE 4</h3>";
							echo "<h3>NUMBER OF METADATA RECORDS MATCHED: ". $mdTotal4 ."</h3>";
							$uq = "UPDATE services SET harvested=1, time_harvest='$now', metadata='$mdTotal4' WHERE id='$id'";
							dbq($uq);
						}

						
						if (isset($mdTotal5)){
							echo "<h3>CASE 5</h3>";
							echo "<h3>NUMBER OF METADATA RECORDS MATCHED: ". $mdTotal5 ."</h3>";
							$uq = "UPDATE services SET harvested=1, time_harvest='$now', metadata='$mdTotal5' WHERE id='$id'";
							dbq($uq);
						}

						if (isset($postOutput) && empty($vals)){
							echo "<h3>CASE NO VALS</h3>";
							echo "<h3>POST OUTPUT IS: " . $postOutput . "</h3>";
							$uq = "UPDATE services SET harvested=1, time_harvest='$now', csw_exception='$postOutput' WHERE id='$id'";
							dbq($uq);
						}
						if (isset($curlError)){
							echo "<h3>CASE CURL ERROR</h3>";
							echo "<h3>POST OUTPUT IS: " . $curlError . "</h3>";
							$uq = "UPDATE services SET harvested=1, time_harvest='$now', csw_exception='$curlError' WHERE id='$id'";
							dbq($uq);
						}
						if (empty($vals) && empty($postOutput) && empty(curlError)){
							echo "<h3>CASE VAL EMPTY, OUTPUT EMPTY, CURL ERROR EMPTY</h3>";
							echo "<h3>POST OUTPUT IS: " . $val . "</h3>";
							$uq = "UPDATE services SET harvested=1, time_harvest='$now', csw_exception='unknown' WHERE id='$id'";
							dbq($uq);
						}
						
						if (isset($owsExceptionCode)){
							echo "<h3>CSW TRANSACTION OPERATION SUMMARY:</h3>";
							echo "<p>Exception has been thrown with the following text:" . $owsExceptionText;
							$uq = "UPDATE services SET harvested=1, time_harvest='$now', csw_exception='$owsExceptionText' WHERE id='$id'";
							//echo $uq;
							dbq($uq);
						}
						if ($tag == "CSW:CAPABILITIES"){
							echo "<h3>CASE CAPABILITIES RESPONSE</h3>";
							//echo "<p>Exception has been thrown with the following text:" . $owsExceptionText;
							$uq = "UPDATE services SET harvested=1, time_harvest='$now', csw_exception='capabilitiesResponse' WHERE id='$id'";
							//echo $uq;
							dbq($uq);
						}
						if ($tag == "HTML"){
							echo "<h3>CASE HTML RESPONSE</h3>";
							//echo "<p>Exception has been thrown with the following text:" . $owsExceptionText;
							$uq = "UPDATE services SET harvested=1, time_harvest='$now', csw_exception='htmlResponse' WHERE id='$id'";
							//echo $uq;
							dbq($uq);
						}
						
					}
					if($httpCode != 200){
						echo "<h3>HTTP CODE IS: " . $httpCode . "</h3>";
						$uq = "UPDATE services SET harvested=1, time_harvest='$now', csw_exception='$httpCode' WHERE id='$id'";
						dbq($uq);
					}
					
			}
				
			?>
		</div>	
	</body>
</html>
