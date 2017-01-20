<?php
//require 'settings.php';

?>

<html xmlns="http://www.w3.org/1999/xhtml"> 
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
		<title>Bolegweb project crawler admin page</title>
		<meta name="copyright" content="bolegweb@gmail.com">
		<meta name="author" content="bolegweb@gmail.com">
</head>		

<h1>Welcome to the  <a href="https://bolegweb.geof.unizg.hr/">BOLEGWEB project</a> OGC services meta search crawler and metadata harvester main web page</h1>
	<p>This page provides links to run the following PHP scripts:</p>
	<p>Script to import the data files (CSV) to the database:</p>
		<ul>
			<li><a href='import.php'>IMPORT.PHP</a></li>
		</ul>
	<p>Script to launch the verification process for collected OGC Services endpoints:</p>
		<ul>
			<li><a href='crawl.php'>CRAWL.PHP</a></li>
		</ul>
	<p>Scripts to harvest metadata from individual OGC services types that are online into geonetwork catalogues: </p>
	<p style="color:red";>OUTDATED!<?php print ' ('. date("F d Y H:i:s",filemtime("crawl-old.php")) . ')'; ?></p>
		<ul>
			<li><a href='harvesting-gnk.php'>HARVESTING.PHP</a></li>
			<li><a href='harvesting-gnk-csw.php'>HARVESTING-GNK-CSW.PHP</a></li>
			<li><a href='harvesting-gnk-sos.php'>HARVESTING-GNK-SOS.PHP</a></li>
			<li><a href='harvesting-gnk-wms.php'>HARVESTING-GNK-WMS.PHP</a></li>
			<li><a href='harvesting-gnk-wfs.php'>HARVESTING-GNK-WFS.PHP</a></li>
			<li><a href='harvesting-gnk-wcs.php'>HARVESTING-GNK-WCS.PHP</a></li>
			<li><a href='harvesting-gnk-wps.php'>HARVESTING-GNK-WPS.PHP</a></li>
		</ul>
	<p>Scripts to harvest metadata from individual OGC services types into pycsw catalogues: </p>
	<p style="color:green";>UPDATED!<?php print ' ('. date("F d Y H:i:s",filemtime("harvesting-pycsw-wms.php")) . ')'; ?></p>
		<ul>
			<li><a href='harvesting-pycsw-wms.php'>HARVESTING-PYCSW-WMS.PHP</a></li>
			<li><a href='harvesting-pycsw-wfs.php'>HARVESTING-PYCSW-WFS.PHP</a></li>
			<li><a href='harvesting-pycsw-wcs.php'>HARVESTING-PYCSW-WCS.PHP</a></li>
			<li><a href='harvesting-pycsw-wps.php'>HARVESTING-PYCSW-WPS.PHP</a></li>
			<li><a href='harvesting-pycsw-sos.php'>HARVESTING-PYCSW-SOS.PHP</a></li>
			<li><a href='harvesting-pycsw-wmts.php'>HARVESTING-PYCSW-WMTS.PHP</a></li>
			<li><a href='harvesting-pycsw-csw.php'>HARVESTING-PYCSW-CSW.PHP</a></li>
		</ul>
		</html>