<?php 
require '../settings.php';
?>
<html lang="en">
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title>List of OGC Services discovered on Google</title>
  <link rel="stylesheet" href="../SlickGrid/slick.grid.css" type="text/css"/>
  <link rel="stylesheet" href="../SlickGrid/examples/examples.css" type="text/css"/>
  <link rel="stylesheet" href="../SlickGrid/controls/slick.pager.css" type="text/css"/>
  <style>
    html, body {
      margin: 0;
      padding: 0;
      background-color: White;
      overflow: auto;
    }

    body {
      font: 11px Helvetica, Arial, sans-serif;
    }

    #container {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
	  
    }

    #description {
      position: relative;
      width: 525px;
      background: #A2CAD2;
      border: solid 1px gray;
      z-index: 1000;
	  cursor:move;
	  resize: both;
	  overflow: hidden;
	  float: right;
	  margin-top:45%;
	  margin-right:50px;
	 
	  /*background-color: transparent;*/

    }
	
	#description h1 {

      font-size: 20px;
	  padding-left: 0.5em;
	  padding-right: 0.5em;


    }
    
	#description h2 {

      text-align: right;
	  padding-left: 0.5em;
	  font-size: 12px;
	  padding-right: 0.5em;  
    }
	#description p {

      padding-left: 0.5em;
	  padding-right: 0.5em;
	  font-size: 12px;

	  /*text-align: right;*/
    }
	
	  .slick-headerrow-column {
      background: #A2CAD2;
      text-overflow: clip;
      -moz-box-sizing: border-box;
      box-sizing: border-box;
    }

    .slick-headerrow-column input {
      margin: 0;
      padding: 0;
      width: 100%;
      height: 100%;
      -moz-box-sizing: border-box;
      box-sizing: border-box;
    }
  </style>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
   <script type="text/javascript" src="https://www.google.com/jsapi"></script>
</head>
<body>
<script>
setInterval(function(){
	pocet = $('#pager').find('.slick-pager-status').text().replace(/[^0-9]/g, '');
	$('#d6').html('Number of currently displayed records:' + pocet);
	//console.log (pocet);
	},1000);
</script>
<div id="status"/>
<div id="container">
</div>
<div id="description">
	<h1>Basic statistics</h1>
	<h2></h2>
	<div>
	<p id="d1">First metasearch performed: <?php
							$q=dbq("SELECT MIN(time_import) FROM services WHERE time_import > 0");
							$r = mysql_fetch_assoc($q);
							echo '<i>' . date('H:i:s \o\n l jS F Y' , min ($r)) . '</i>';
							?>
	</p>
	<p id="d2">Last metasearch performed: <?php
							$q=dbq("SELECT MAX(time_import) FROM services");
							$r = mysql_fetch_assoc($q);
							echo '<i>' . date('H:i:s \o\n l jS F Y' , max ($r)) . '</i>';
							?>
	</p>
	<p id="d3">Last verification performed: <?php
							$q=dbq("SELECT MAX(time_check) FROM services");
							$r = mysql_fetch_assoc($q);
							echo '<i>' . date('H:i:s \o\n l jS F Y' , max ($r)) . '</i>';
							?>
	</p>
	<p id="d4">Number of URL addresses: <?php
							$q=dbq("SELECT COUNT(*) AS total FROM services");
							$r = mysql_fetch_assoc($q);
							echo '<i>' . $r['total'] . '</i>';
							?>
	</p>
	<p id="d5">Number of available service endpoints: <?php
							$q=dbq("SELECT COUNT(*) AS totalOnline FROM services WHERE status!=2 AND version!=0");
							$r = mysql_fetch_assoc($q);
							echo '<i>' . $r['totalOnline'] . '</i>';
							?>
	</p>
	<p id="piechart" style="width: 520x; height: 250px;"></p>
	</div>
 	<h2></h2>
	<div style="text-align: right; padding-right: 0.5em;"><b>Powered by: </b><a href="https://github.com/mleibman/SlickGrid/wiki" target="_blank"><b>SlickGrid</b></a>
	</div>
</div>
<script type="text/javascript">
	  google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
	  var wms = <?php $q=dbq("SELECT COUNT(*) AS totalWMS FROM services WHERE status!=2 AND version!=0 AND type='wms'");$r = mysql_fetch_assoc($q);echo $r['totalWMS'];?>;
	  var wfs = <?php $q=dbq("SELECT COUNT(*) AS totalWFS FROM services WHERE status!=2 AND version!=0 AND type='wfs'");$r = mysql_fetch_assoc($q);echo $r['totalWFS'];?>;
	  var wcs = <?php $q=dbq("SELECT COUNT(*) AS totalWCS FROM services WHERE status!=2 AND version!=0 AND type='wcs'");$r = mysql_fetch_assoc($q);echo $r['totalWCS'];?>;
	  var wps = <?php $q=dbq("SELECT COUNT(*) AS totalWPS FROM services WHERE status!=2 AND version!=0 AND type='wps'");$r = mysql_fetch_assoc($q);echo $r['totalWPS'];?>;
	  var sos = <?php $q=dbq("SELECT COUNT(*) AS totalSOS FROM services WHERE status!=2 AND version!=0 AND type='sos'");$r = mysql_fetch_assoc($q);echo $r['totalSOS'];?>;
	  var wmts = <?php $q=dbq("SELECT COUNT(*) AS totalWMTS FROM services WHERE status!=2 AND version!=0 AND type='wmts'");$r = mysql_fetch_assoc($q);echo $r['totalWMTS'];?>;
	  var csw = <?php $q=dbq("SELECT COUNT(*) AS totalCSW FROM services WHERE status!=2 AND version!=0 AND type='csw'");$r = mysql_fetch_assoc($q);echo $r['totalCSW'];?>;
        var data = google.visualization.arrayToDataTable([
          ['Task', 'Hours per Day'],
          ['WMS', wms],
          ['WFS', wfs],
          ['WCS', wcs],
          ['WPS', wps],
          ['SOS', sos],
		  ['WMTS', wmts],
		  ['CSW', csw]
        ]);
		var options = {
			backgroundColor: '#A2CAD2',
			title: 'Available services',
			width: 500,
			height: 250
			};
        var chart = new google.visualization.PieChart(document.getElementById('piechart'));
        chart.draw(data, options);
      }
    </script>
<div id="pager" style="width:100%;height:20px;"/>

<script src="../SlickGrid/lib/firebugx.js"></script>
<script src="../SlickGrid/lib/jquery-1.7.min.js"></script>
<script src="../SlickGrid/lib/jquery-ui-1.8.16.custom.min.js"></script>
<script src="../SlickGrid/lib/jquery.event.drag-2.2.js"></script>

<script src="../SlickGrid/slick.core.js"></script>
<script src="../SlickGrid/slick.dataview.js"></script>
<script src="../SlickGrid/slick.grid.js"></script>
<script src="../SlickGrid/controls/slick.pager.js"></script>

<script>
	$( "#description" ).draggable();
	function onlineOffline(row, cell, value, columnDef, dataContext) {
		if (value == 2) {
			return '<img src="../img/thumbDown.png" width="20" height="20" title="Service Not Available"/>';
		}
		else {
			return '<img src="../img/thumbUp.png" width="20" height="20" title="Service Available"/>';
		}
	}
	
	function harvested(row, cell, value, columnDef, dataContext) {
		if (value == 0) {
			return 'NO';
		}
		else {
			return 'YES';
		}
	}


function metadata (row, cell, value, columnDef, dataContext) {
		var endpoint = dataContext.url.substr(0, dataContext.url.indexOf('?'));
		var endpointParam = encodeURIComponent(endpoint);
		if (value == 0){
			return "-1";
		}
		else {
			//return '<a href="http://31.147.204.167:8080/gnk_'+ dataContext.type +'/srv/eng/search#fast=index&from=1&to=50&any_OR_geokeyword='+ endpointParam +'" target="_new">'+ value +'</a>';
			
			//return '<a href="http://31.147.204.167:8080/gnk_'+ dataContext.type +'/srv/eng/search#fast=index&from=1&to=50&any_OR_geokeyword='+ endpointParam +'?bigmap_x=0&bigmap_y=0&bigmap_zoom=14&bigmap_visibility_OpenStreetMap=true&bigmap_opacity_OpenStreetMap=1&bigmap_visibility_Feature%20info=true&bigmap_opacity_Feature%20info=1&bigmap_visibility_Search%20results=true&bigmap_opacity_Search%20results=1&minimap_x=0&minimap_y=0&minimap_zoom=18&minimap_visibility_OpenStreetMap=true&minimap_opacity_OpenStreetMap=1&minimap_visibility_Search%20results=true&minimap_opacity_Search%20results=1&s_search=&s_E_any_OR_geokeyword='+ endpointParam +'&s_O_dynamic=false&s_O_download=false&s_O_nodynamicdownload=false&s_scaleOn=false&s_timeType=true" target="_new">'+ value +'</a>';
			
			//return '<a href="https://bolegweb.geof.unizg.hr/pycsw_'+ dataContext.type +'?mode=sru&operation=searchRetrieve&query='+ endpointParam +'" target="_new">'+ value +'</a>'; 
			
			return '<a href="https://bolegweb.geof.unizg.hr/pycsw_'+ dataContext.type +'?service=CSW&version=2.0.2&request=GetRecords&outputSchema=http://www.isotc211.org/2005/gmd&typenames=gmd:MD_Metadata&elementsetname=full&resulttype=results&constraintlanguage=FILTER&constraint=%3Cogc:Filter%20xmlns:ogc%3D%22http://www.opengis.net/ogc%22%3E%3Cogc:PropertyIsEqualTo%3E%3Cogc:PropertyName%3Edc:source%3C/ogc:PropertyName%3E%3Cogc:Literal%3E'+ endpointParam +'%3C/ogc:Literal%3E%3C/ogc:PropertyIsEqualTo%3E%3C/ogc:Filter%3E" target="_new">'+ value +'</a>';
			
		}
	}
	
	function HTML(row, cell, value, columnDef, dataContext) {
        return value;
    }
	
	var dataView;
	var filter;
	var grid;
	var data = [];
	var columns = [
		{id: "id", name: "ID", field: "id", width: 40,sortable: true},
		{id: "title", name: "Google Title", field: "title", width: 350, sortable: true},
		{id: "type", name: "Type", field: "type", width: 40, sortable: true},
		{id: "version", name: "Version", field: "version", width: 50, sortable: true},
		{id: "url", name: "Metaseach URL discovered", field: "url",width: 350 },
		{id: "location", name: "Server location", field: "location", width: 100, sortable: true},
		{id: "time", name: "Import Date", field: "importDate", width: 80, sortable: true},
		{id: "status", name: "Status", field: "status", width: 40, sortable: true, formatter: onlineOffline},
		{id: "statusDate", name: "Crawling Date", field: "statusDate", width: 115 },
		//{id: "harvested", name: "Harvested", field: "harvested", width: 65, sortable: true, formatter: harvested},
		{id: "metadata", name: "Metadata", field: "metadata", width: 65, sortable: true, formatter: metadata}
		
	];
  
	var columnFilters = {};
	
	var options = {
		enableCellNavigation: true,
		showHeaderRow: true,
		headerRowHeight: 30,
		enableColumnReorder: true,
		multiColumnSort: true,
		explicitInitialization: true,
                forceFitColumns: true
	};
	
	
	$(function () {
		var data = [];
		for (var i = 0; i < 1; i++) {
		  var d = (data[i] = {});
		  d["id"] = i;
		  d["title"] = "Loading the data ...";
		  d["type"] = "";
		  d["version"] = "";
		  d["url"] = "";
		  d["location"] = "";
		  d["time"] = "";
		  d["status"] = "";
		  d["statusDate"] = "";
                  d["metadata"] = "";
		  
		}
	dataView = new Slick.Data.DataView({ inlineFilters: true });
	grid = new Slick.Grid("#container", dataView, columns, options);
	// MAKE <a href> HTML element
	$(".slick-cell l9 r9").hide();
	
	/* FILTERING THE DATA */
	function filter(item) {
	
		for (var columnId in columnFilters) {
			if (columnId !== undefined && columnFilters[columnId] !== "") {
				var c = grid.getColumns()[grid.getColumnIndex(columnId)];
				if (item[c.field] != columnFilters[columnId]) {
					if (item[c.field].toString().toLowerCase().indexOf(columnFilters[columnId].toLowerCase()) == -1) {
					return false;
					}
				}
			}
		}
    return true;
	}
	// function for updating the counts in the top row
		updateRequestCounts = function(obj){
			$.each( obj, function( key, value ) {
				$('.count'+key).text(value);
			});
			}

		
		dataView.onRowCountChanged.subscribe(function (e, args) {
			grid.updateRowCount();
			grid.render();
		});

		dataView.onRowsChanged.subscribe(function (e, args) {
			grid.invalidateRows(args.rows);
			grid.render();
		});
		
	
	
		grid.onHeaderRowCellRendered.subscribe(function(e, args) {
			$(args.node).empty();
			$("<input type='text'>")
				.data("columnId", args.column.id)
				.val(columnFilters[args.column.id])
				.appendTo(args.node);
			});
			
		$(grid.getHeaderRow()).delegate(":input", "change keyup", function (e) {
			var columnId = $(this).data("columnId");
			if (columnId != null) {
				columnFilters[columnId] = $.trim($(this).val());
				dataView.refresh();
			}
		});	
		
		/* PAGER */
		var pager = new Slick.Controls.Pager(dataView, grid, $("#pager"));
		/* SORTING */
		grid.onSort.subscribe(function (e, args) {
		
		var cols = args.sortCols;

		dataView.sort(function (dataRow1, dataRow2) {
			for (var i = 0, l = cols.length; i < l; i++) {
				var field = cols[i].sortCol.field;
				var sign = cols[i].sortAsc ? 1 : -1;
				var value1 = dataRow1[field], value2 = dataRow2[field];
				var result = (value1 == value2 ? 0 : (value1 > value2 ? 1 : -1)) * sign;
				if (result != 0) {
					return result;
				}
			}	
			return 0;
			});
			grid.invalidate();
		});
		
		/* DOUBLE CLICK TO OPEN GET CAPABILITIES URL ON NEW TAB */
		
		grid.onDblClick.subscribe(function(e, args) {
			vec = args;  // toto je pre KONZOLU
			window.open( args.grid.getDataItem(args.row).url );
		})
		
		grid.init();

		dataView.beginUpdate();
		dataView.setItems(data);
		dataView.setFilter(filter);
		dataView.endUpdate();

		/* LOAD DATA FROM JSON API */
		url = "../rest/json.php";
		vals={}	
		//vals.status=1
		$.getJSON(url,vals,function(res){
			data = res;
			dataView.beginUpdate();
			dataView.setItems(data);
			dataView.endUpdate();
			dataView.refresh();
		})
	})
</script>
<!-- Piwik -->
<script type="text/javascript">
  var _paq = _paq || [];
  _paq.push(['trackPageView']);
  _paq.push(['enableLinkTracking']);
  (function() {
    var u="//bolegweb.geof.unizg.hr/piwik/";
    _paq.push(['setTrackerUrl', u+'piwik.php']);
    _paq.push(['setSiteId', 1]);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
  })();
</script>
<noscript><p><img src="//bolegweb.geof.unizg.hr/piwik/piwik.php?idsite=1" style="border:0;" alt="" /></p></noscript>
<!-- End Piwik Code -->
</body>
</html>