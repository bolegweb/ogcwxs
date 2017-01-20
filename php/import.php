<?php 
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
		<title>geonetwork CSV crawler</title>
		<meta name="copyright" content="bolegweb@gmail.com">
		<meta name="author" content="bolegweb@gmail.com">
		<script src="http://cdn.jquerytools.org/1.2.6/full/jquery.tools.min.js"></script>
		
		<style>
			body{
				margin: 0px;
				padding: 0px;
			}
		</style>
	</head>
	<body>
		<h1>Checking .CSV files availability</h1>
		<div id="content">
			<?php 
				// csw sos wcs wfs wms wps sps wcps
				$types[]="csw";
				$types[]="sos";
				$types[]="wcs";
				$types[]="wfs";
				$types[]="wms";
				$types[]="wps";
				$types[]="wmts";
				//$types[]="sps";
				//$types[]="wcps";

				$now=time();
				$dokopy['types']=0;
				$dokopy['total']=0;
				$dokopy['ok-insert']=0;
				$dokopy['ok-was-there']=0;
				$dokopy['not-valid-url']=0;
				foreach($types as $type)
				{
					$urlcsv = "http://localhost/ogcwxs/csv/".$type.".csv";
					$fo = fopen($urlcsv, "r");
					while( $fr = fgets($fo, 1024) )
					{
						$spl = preg_split ( "/,/i" , $fr );
						$URL = rtrim(preg_replace('/"/i', "", $spl[1]), ".");
						if(isValidURL($URL))
						{
							// OK
							$q=dbq("SELECT * FROM services WHERE url LIKE '$URL'");
							if(!mysql_num_rows($q))
							{
								$dokopy['ok-insert']++;
								$nm=mysql_real_escape_string($spl[0]);
								//echo "<font color=green>OK-INSERT: ".$URL."</font><br>INSERT INTO services (title, url, type, status, time) VALUES ('$nm', '$URL', '$type', '0', '$now')<br>";
								dbq("INSERT INTO services (title, url, type, status, time_import) VALUES ('$nm', '$URL', '$type', '0', '$now')");
							}
							else
							{
								//echo "<font color=green>OK-WAS-THERE: ".$URL."</font><br><br>";
								$dokopy['ok-was-there']++;
							}
							
						}
						else
						{
							// NOT-VALID-URL
							//echo "<font color=red>NOT-VALID-URL: ".$URL."</font><br><br>";
							$dokopy['not-valid-url']++;
						}
					$dokopy['total']++;
					}
				$dokopy['types']++;
				}
				
				
				foreach($dokopy as $key=>$val)
				{
					echo "$key: $val <br>";
				}
				
				
			?>
		</div>	
	</body>
</html>
