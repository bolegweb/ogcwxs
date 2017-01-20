<?php 
require '../../settings.php';
?>
<html lang="en">
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title>ZOznam OGC služieb nájdených v Google</title>
  <link rel="stylesheet" href="../../SlickGrid/slick.grid.css" type="text/css"/>
  <link rel="stylesheet" href="../../SlickGrid/examples/examples.css" type="text/css"/>
  <link rel="stylesheet" href="../../SlickGrid/controls/slick.pager.css" type="text/css"/>
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
	  overflow: auto;
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
</head>
<body>
<script>
setInterval(function(){
	pocet = $('#pager').find('.slick-pager-status').text().replace(/[^0-9]/g, '');
	$('#d6').html('Počet zobrazených služieb:' + pocet);
	//console.log (pocet);
	},1000);
</script>
	
<div id="status"/>
<div id="container"></div>
<div id="description">
	<h1>Základné štatistiky</h1>
	<h2></h2>
	<p id="d1">Prvé metavyhľadávanie: <?php
							$q=dbq("SELECT MIN(time_import) FROM services WHERE time_import > 0");
							$r = mysql_fetch_assoc($q);
							echo date('H:i:s \o\n l jS F Y' , min ($r));
							?>
	</p>
	<p id="d2">Posledné metavyhľadávanie: <?php
							$q=dbq("SELECT MAX(time_import) FROM services");
							$r = mysql_fetch_assoc($q);
							echo date('H:i:s \o\n l jS F Y' , max ($r));
							?>
	</p>
	<p id="d3">Posledné overenie: <?php
							$q=dbq("SELECT MAX(time_check) FROM services");
							$r = mysql_fetch_assoc($q);
							echo date('H:i:s \o\n l jS F Y' , max ($r));
							?>
	</p>
	<p id="d4">Počet URL adries služieb: <?php
							$q=dbq("SELECT COUNT(*) AS total FROM services");
							$r = mysql_fetch_assoc($q);
							echo $r['total'];
							?>
	</p>
	<p id="d5">Počet dostupných služieb: <?php
							$q=dbq("SELECT COUNT(*) AS totalOnline FROM services WHERE status!=2 AND version!=0");
							$r = mysql_fetch_assoc($q);
							echo $r['totalOnline'];
							?>
	</p>
	<p id="d6"/>
 	<h2></h2>
	<div style="text-align: right; padding-right: 0.5em;"><b>Poháňané: </b><a href="https://github.com/mleibman/SlickGrid/wiki" target="_blank"><b>SlickGrid</b></a>
	</div>
	
</div>
<div id="pager" style="width:100%;height:20px;"/>

<script src="../../SlickGrid/lib/firebugx.js"></script>
<script src="../../SlickGrid/lib/jquery-1.7.min.js"></script>
<script src="../../SlickGrid/lib/jquery-ui-1.8.16.custom.min.js"></script>
<script src="../../SlickGrid/lib/jquery.event.drag-2.2.js"></script>

<script src="../../SlickGrid/slick.core.js"></script>
<script src="../../SlickGrid/slick.dataview.js"></script>
<script src="../../SlickGrid/slick.grid.js"></script>
<script src="../../SlickGrid/controls/slick.pager.js"></script>

<script>
	$( "#description" ).draggable();
	function onlineOffline(row, cell, value, columnDef, dataContext) {
		if (value == 2) {
			return '<img src="../../img/thumbDown.png" width="20" height="20" title="Service Not Available"/>';
		}
		else {
			return '<img src="../../img/thumbUp.png" width="20" height="20" title="Service Available"/>';
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
			return "N/A";
		}
		else {
			//return '<a href="http://31.147.204.167:8080/gnk_'+ dataContext.type +'/srv/eng/search#fast=index&from=1&to=50&any_OR_geokeyword='+ endpointParam +'" target="_new">'+ value +'</a>';
			
			return '<a href="http://31.147.204.167:8080/gnk_'+ dataContext.type +'/srv/eng/search#fast=index&from=1&to=50&any_OR_geokeyword='+ endpointParam +'?bigmap_x=0&bigmap_y=0&bigmap_zoom=14&bigmap_visibility_OpenStreetMap=true&bigmap_opacity_OpenStreetMap=1&bigmap_visibility_Feature%20info=true&bigmap_opacity_Feature%20info=1&bigmap_visibility_Search%20results=true&bigmap_opacity_Search%20results=1&minimap_x=0&minimap_y=0&minimap_zoom=18&minimap_visibility_OpenStreetMap=true&minimap_opacity_OpenStreetMap=1&minimap_visibility_Search%20results=true&minimap_opacity_Search%20results=1&s_search=&s_E_any_OR_geokeyword='+ endpointParam +'&s_O_dynamic=false&s_O_download=false&s_O_nodynamicdownload=false&s_scaleOn=false&s_timeType=true" target="_new">'+ value +'</a>';
			
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
		{id: "title", name: "Google Názov", field: "title", width: 350, sortable: true},
		{id: "type", name: "Type Služby", field: "type", width: 40, sortable: true},
		{id: "version", name: "Verzia", field: "version", width: 50, sortable: true},
		{id: "url", name: "Google URL", field: "url",width: 350 },
		{id: "location", name: "Lokalizácia Servera", field: "location", width: 100, sortable: true},
		{id: "time", name: "Dátum vyhľadania", field: "importDate", width: 80, sortable: true},
		{id: "status", name: "Dostupnosť", field: "status", width: 40, sortable: true, formatter: onlineOffline},
		{id: "statusDate", name: "Dátum overenia", field: "statusDate", width: 115 },
		//{id: "harvested", name: "Harvested", field: "harvested", width: 65, sortable: true, formatter: harvested},
		{id: "metadata", name: "Metadáta", field: "metadata", width: 65, sortable: true, formatter: metadata}
		
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
		  d["title"] = "Chvíľka strpenia nahrávam dáta ...";
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
		url = "http://bolegweb.geof.unizg.hr/ogcwxs/rest/json.php";
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
 
</body>
</html>
