{header}
	<link rel="stylesheet" type="text/css" href="{url_base}sitio/css/flexigrid.theme.css"/>
	<link rel="stylesheet" type="text/css" href="{url_base}sitio/css/form.css" />
	<script type="text/javascript" src="{url_base}sitio/js/flexigrid.theme.js"></script>
	<script type="text/javascript" src="{url_base}sitio/js/highstock.js"></script>
	<script type="text/javascript" src="{url_base}sitio/js/modules/exporting.js"></script>
	<script type="text/javascript" src="{url_base}sitio/js/form_option.js"></script>
	<script type="text/javascript" src="{url_base}sitio/js/modules/adapt-chart-to-legend.js"></script>
	<script type="text/javascript" src="{url_base}sitio/js/modules/export-csv.js"></script>
	<!-- Additional files for the Highslide popup effect -->
	<script type="text/javascript" src="http://www.highcharts.com/media/com_demo/highslide-full.min.js"></script>
	<script type="text/javascript" src="http://www.highcharts.com/media/com_demo/highslide.config.js" charset="utf-8"></script>
	<link rel="stylesheet" type="text/css" href="http://www.highcharts.com/media/com_demo/highslide.css" />
	<style>
	 #toolbar {
    padding: 4px;
    display: inline-block;
  }
  /* support: IE7 */
  *+html #toolbar {
    display: inline;
  }	
	</style>
	<script type="text/javascript">
	$(function() {
		$( "#tabs" ).tabs({
			active:  $.cookie('selected-tab-home'),
			activate: function( event, ui ) {
				$.cookie('selected-tab-home', ui.newTab.index(), { path: '/' });
			},
			beforeLoad: function( event, ui ) {
				ui.jqXHR.fail(function(xhr, status, index, anchor) {
					if(xhr.status = 302) {
						ui.panel.html("Session close.");
						location.reload();						
					} else {
						ui.panel.html(
							"Problemas tecnicos. " +
							"Por favor , reportar a baking." );	
					}					
				});
			}
		});
	});
	</script>
	<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
</head>

<body>
	<div class="container">
		{menu}
		<div id="content">
			{tabs}
		</div>
		<div class="push"></div>	
	</div>
	{footer}
</body>
</html>