<?php
//error_reporting(E_ALL);
//ini_set('display_errors','1');

require 'settings.php';

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
		<title>Bolegweb Web Map Services Harvesting Jobs Script Monitor</title>
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
				document.location.href = 'harvesting-pycsw-wms.php';
			}
		</script>
	
	</head>
	<body onload="refresh()">
		<h1>Harvesting jobs process overview:</h1>
		<div id="content">
			<?php 
		
				// SELECT ALL SERVICES ENDPOINT READY FOR HARVESTING FROM THE DATABASE
				$q = dbq("SELECT * FROM services WHERE type='wms' AND harvest_file IS NOT NULL AND status=1 AND harvested=0 ORDER BY id ASC LIMIT 1");				
				// lupuj dokial su riadky:
				if(!mysql_num_rows($q))
				{
					echo "<h3> Harvesting jobs have been finished! </h3>";
					
				}
				while($r = mysql_fetch_assoc($q))
				{
					// DEFINE VARIABLES
					$now=time();
					
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
					
					// FOR WMS VERSION 1.1.1 -> pycsw_wms
					echo "<h3>RUNNING HARVESTING TASK FOR:</h3>
					<p>Service type: $type</p>
					<p>Version: $version</p>
					<p>Internal ID: $id</p>
					<p>Endpoint: $endpoint</p>
					<p>Harvesting file: $harvest_file</p>";
					$url="https://bolegweb.geof.unizg.hr/pycsw_wms";
					$xml = file_get_contents($harvest_file);
					$ch = curl_init($url);
					curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
					curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					$output = curl_exec($ch);
					if(curl_exec($ch) === false)
							{
								echo "<p>Curl error: " . curl_error($ch). "</p>";
							}
							else
							{
								echo "<h3>RESULTS:</h3><p>HTTP POST Operation completed without any errors.</p>";
							}		
					curl_close($ch);
		
					$p = xml_parser_create();
					xml_parse_into_struct($p, $output, $vals, $index);
					xml_parser_free($p);
					?>
					<!--<pre><?php	//print_r($vals); ?></pre>-->
	
					<?php
					$mdInsertedCSW = $vals[3]['value'];
					$mdUpdatedCSW = $vals[4]['value'];
					$mdDeletedCSW = $vals[5]['value'];
					$owsExceptionCode = $vals[1]['attributes']['EXCEPTIONCODE'];
					$owsExceptionLocator = $vals[1]['attributes']['LOCATOR'];
					$owsExceptionText = str_replace("'", "", $vals[2]['value']);
					$mdSumCSW = ($mdInsertedCSW + $mdUpdatedCSW) - $mdDeletedCSW;
					if ($vals[3]['tag'] == 'CSW:TOTALINSERTED')
					{
					echo "<h3>CSW TRANSACTION OPERATION SUMMARY:</h3>";
					echo "<p>TOTAL METADATA INSERTED: {$vals[3]['value']}</p>";
					echo "<p>TOTAL METADATA UPDATED: {$vals[4]['value']}</p>";
					echo "<p>TOTAL DELETED: {$vals[5]['value']}</p>";
					
					/* potrebujes spravne hodnoty nastavit, teda napr: */
					$uq = "UPDATE services SET harvested=1, mdInserted='$mdInsertedCSW', mdUpdated='$mdUpdatedCSW',mdDeleted='$mdDeletedCSW', time_harvest='$now', metadata='$mdSumCSW' WHERE id='$id'";
					
			
					// run update query
					dbq($uq);
					
					echo "<h3>HARVESTED METADATA IDENTIFICATION: </h3>";
					foreach ($vals as $key => $tag){
							if ($tag['tag'] == 'DC:IDENTIFIER'){
									echo "<p>Metadata record identifier: ". $tag['value']."</p>";
									
								}
							if ($tag['tag'] == 'DC:TITLE'){
									echo "<p>Metadata record title: ". $tag['value']."</p><br><br>";	
							}
						
					}
					}
					if ($owsExceptionCode){
						echo "<h3>CSW TRANSACTION OPERATION SUMMARY:</h3>";
						echo "<p>Exception has been thrown with the following text:" . $owsExceptionText;
						$uq = "UPDATE services SET harvested=1, time_harvest='$now', csw_exception='$owsExceptionText' WHERE id='$id'";
						//echo $uq;
						dbq($uq);
					}
					
			}
				
			?>
		</div>	
	</body>
</html>
