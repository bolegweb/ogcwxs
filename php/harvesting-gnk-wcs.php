<?php
error_reporting(E_ALL);
ini_set('display_errors','1');

$cats['wcs']['category']=1;
$cats['wcs']['datasetCategory']=2;

require 'settings.php';
// CURL COOKIE PATH
$cpath = "/usr/share/ogcwxs/cookie/wcs.txt";
// AUTHENTIFIKACIA
$auth_url="http://31.147.204.167:8080/gnk_wcs/j_spring_security_check"; 
$auth_xml = 'username=admin&password=only4admin';
$ch = curl_init($auth_url);
curl_setopt($ch,    CURLOPT_AUTOREFERER,         true); 
curl_setopt($ch,	CURLOPT_COOKIEJAR, $cpath); 
curl_setopt($ch,	CURLOPT_COOKIEFILE, $cpath); 
curl_setopt($ch,    CURLOPT_HEADER,             false); 
curl_setopt($ch,    CURLOPT_POST,                 true); 
curl_setopt($ch,    CURLOPT_RETURNTRANSFER,        true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded')); 
//curl_setopt($ch,	CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
curl_setopt($ch,	CURLOPT_POSTFIELDS, "$auth_xml");
$output1 = curl_exec($ch);
curl_close($ch);

// COOkIE GET
$pattern = "#Set-Cookie: (.*?; path=.*?;.*?)\n#"; 
preg_match_all($pattern, $output1, $matches); 
array_shift($matches); 
$cookie = implode("\n", $matches[0]); 

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
		<title>Geonetwork harvesting jobs management</title>
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
			function refresh()
			{
				window.setTimeout(vec, 3000);
			}
			function vec()
			{
				document.location.href = 'harvesting-wcs.php';
			}
		</script>
	
	</head>
	<body onload="refresh()">
		<h1>Harvesting jobs process overview:</h1>
		<div id="content">
			<?php 
				$now=time();
				// BEZI / NEBEZI
				$q=dbq("SELECT * FROM services WHERE type='wcs' AND status=3");
				if(mysql_num_rows($q))
				{
					
					// bezi
					$r=mysql_fetch_assoc($q);
					echo "Currently running harvesting task for the OGC endpoint ID: {$r['id']}<br><br>";
					$url="http://31.147.204.167:8080/gnk_wcs/srv/eng/xml.harvesting.get";
					$xml = "<request><id>{$r['id_gn']}</id></request>";
					$ch = curl_init($url);
					curl_setopt($ch, CURLOPT_COOKIEJAR, $cpath); 
					curl_setopt($ch, CURLOPT_COOKIEFILE, $cpath); 
					curl_setopt($ch, CURLOPT_COOKIE,    $cookie); 
					curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
					curl_setopt($ch, CURLOPT_POSTFIELDS, "$xml");
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					$output2 = curl_exec($ch);
					curl_close($ch);
					
					$p = xml_parser_create();
					// Parse XML data into an array structure
					xml_parse_into_struct($p, $output2, $vals2, $index2);
					$responseXML = xml_parse_into_struct($p, $output2, $vals2, $index2);
					xml_parser_free($p);
					
					
					
					
					if($vals2[59]['value'] == "false" or $vals2[0]['attributes']['ID'] == $vals2[0]['attributes']['ID'] or $vals2[77]['attributes']['ID'] == "error" or $vals2[73]['value'] == "false")
					{
						// TK status zmnenit na 0, aby crawler mohol chekovat ci sluzba je dostupna a
						dbq("UPDATE services SET status=0, harvested=1, time_harvest=$now WHERE id = {$r['id']}");
						echo "Finished harvesting task number: {$r['id_gn']}";
						//echo $responseXML;
					}
					else
					{
						echo "Harvesting task still running with ID: {$r['id_gn']}<br>";
						print_r($vals2);
						//echo "{$vals2[0]['attributes']['ID']}<br>";
						//echo "{$vals2[59]['value']}<br>";
						//echo "{$vals2[77]['attributes']['ID']}<br>";
						//echo "{$vals2[73]['value']}<br>";
						
					}
				}
				else
				{
					// nebezi
					
					// bol / nebol pridany
					$q=dbq("SELECT * FROM services WHERE status=1 AND type='wcs' AND harvested=0 ORDER BY id ASC LIMIT 1");
					if(mysql_num_rows($q))
					{
						// este su take ktore neboli pridane
						
						// pridat
						$r = mysql_fetch_assoc($q);
						dbq("UPDATE services SET time_harvest=$now WHERE id={$r['id']}");
						if($r['type'] == "csw")
						{
							// vynimka CSW typ
							$urlEnc = urlencode($r['url']);
							$xml = "
							<node type=\"csw\">
								<ownerGroup>
									<id>3</id>
								</ownerGroup>
						        <site>
						            <name>{$r['title']}</name>
						            <capabilitiesUrl>$urlEnc</capabilitiesUrl>
						            <icon>csw.gif</icon> 
						        </site>
						        <options>
						            <every>700</every>
						            <oneRunOnly>true</oneRunOnly>
						        </options>
						        <privileges>
						            <group id=\"1\">
						                <operation name=\"view\"></operation>
						                <operation name=\"dynamic\"></operation>
						                <operation name=\"featured\"></operation>
						            </group>
						        </privileges>
						        <categories>
						            <category id=\"{$cats[$r['type']]['category']}\"></category>
						        </categories>
						    </node>
							";
							
						}
						elseif($r['type'] == "wmts")
						{
							// SPECIALNY TYP wmts
							echo "WMTS service endpoint currently not supported by GeoNetwork";
						}
						else
						{
							// vsetky ostatne typy
							$ogctype=strtoupper($r['type']).$r['version'];
							$spl = preg_split("/\?/i",$r['url']);
							
							$xmlurl=$spl[0];
							$xml = "
							<node type=\"ogcwxs\">
								<ownerGroup>
									<id>3</id>
								</ownerGroup>
								<site>
									<name>{$r['title']}</name>
									<ogctype>$ogctype</ogctype>
									<url>$xmlurl</url>
									<icon>{$r['type']}.gif</icon>
									<account>
										<use>false</use>
										<username/>
										<password/>
									</account>
								</site>
								<options>
									<oneRunOnly>true</oneRunOnly>
									<every>1700</every>
									<lang/>
									<topic/>
									<createThumbnails>true</createThumbnails>
									<useLayer>true</useLayer>
									<useLayerMd>false</useLayerMd>
									<datasetCategory>{$cats[$r['type']]['datasetCategory']}</datasetCategory>
									<outputSchema>iso19139</outputSchema>
								</options>
								<content>
									<validate>false</validate>
									<importxslt>none</importxslt>
								</content>
								<privileges>
									<group id=\"1\">
										<operation name=\"view\"/>
										<operation name=\"dynamic\"/>
										<operation name=\"featured\"/>
									</group>
								</privileges>
								<categories>
									<category id=\"{$cats[$r['type']]['category']}\"/>
								</categories>
							</node>
							";
						}
						
						//dbq("INSERT INTO debug (title, text) VALUES ('{$r['title']}', '$xml')");
						
						
						// mame xml - CREATE HARVESTING TASK xml.harvesting.add
						$url = "http://31.147.204.167:8080/gnk_wcs/srv/eng/xml.harvesting.add";

						$ch = curl_init($url);
						curl_setopt($ch, CURLOPT_COOKIEJAR, $cpath); 
						curl_setopt($ch, CURLOPT_COOKIEFILE, $cpath); 
						curl_setopt($ch, CURLOPT_COOKIE,                $cookie); 
						curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
						curl_setopt($ch, CURLOPT_POSTFIELDS, "$xml");
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						$output2 = curl_exec($ch);
						//print_r ($output2);
						curl_close($ch);
										
						
						$p = xml_parser_create();
						xml_parse_into_struct($p, $output2, $vals2, $index2);
						xml_parser_free($p);
						
						// Extract ID of the harvesting task from xml.add.harvesting response
						$nodeId= $vals2[0]['attributes']['ID'];
						
						if(!$nodeId)
						{
							dbq("UPDATE services SET harvested=3 WHERE id={$r['id']}");
							echo "[ERROR] - harvesting task ID retrieval failed for OGC service endpoint ID: {$r['id']}";
						}
						else
						{
							// mame xml - RUN HARVESTING TASK xml.harvesting.run
							$url = "http://31.147.204.167:8080/gnk_wcs/srv/eng/xml.harvesting.run";
							$xml= "<request><id>$nodeId</id></request>";
							$ch = curl_init($url);
							curl_setopt($ch, CURLOPT_COOKIEJAR, $cpath); 
							curl_setopt($ch, CURLOPT_COOKIEFILE, $cpath); 
							curl_setopt($ch, CURLOPT_COOKIE, $cookie); 
							curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
							curl_setopt($ch, CURLOPT_POSTFIELDS, "$xml");
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
							$output3 = curl_exec($ch);
							curl_close($ch);
								
							
							$p = xml_parser_create();
							xml_parse_into_struct($p, $output3, $vals3, $index3);
							xml_parser_free($p);
							
							$running= $vals3[1]['attributes']['STATUS'];
							
							
							dbq("UPDATE services SET status=3, id_gn=$nodeId WHERE id={$r['id']}");
							echo "OGC service endpoint URL: <i>{$r['url']}</i><br>";
							echo "Added and started harvesting task ID: $nodeId";
						}
						
						
					}
					else
					{
						// uz iba spustat podla datumu
						$r=mysql_fetch_assoc(dbq("SELECT * FROM services WHERE status=3 AND id_gn>0 ORDER BY time_harvest ASC LIMIT 1"));
						$nodeId= $r['id_gn'];
						
						
						
						// mame xml - poslat harvesting add
						$url = "http://31.147.204.167:8080/gnk_wcs/srv/eng/xml.harvesting.run";
						$xml= "<request><id>$nodeId</id></request>";
						$ch = curl_init($url);
						curl_setopt($ch, CURLOPT_COOKIEJAR, $cpath); 
						curl_setopt($ch, CURLOPT_COOKIEFILE, $cpath); 
						curl_setopt($ch, CURLOPT_COOKIE, $cookie); 
						curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
						curl_setopt($ch, CURLOPT_POSTFIELDS, "$xml");
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						$output3 = curl_exec($ch);
						curl_close($ch);
							
						
						$p = xml_parser_create();
						xml_parse_into_struct($p, $output3, $vals3, $index3);
						xml_parser_free($p);
						
						$running= $vals3[1]['attributes']['STATUS'];
						
						
						dbq("UPDATE services SET harvested=1, id_gn=$nodeId WHERE id={$r['id']}");
						echo "Started harvesting task ID: $nodeId";
					}
				
				}
			?>
		</div>	
	</body>
</html>
