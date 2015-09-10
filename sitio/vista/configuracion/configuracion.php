{header}
<link rel="stylesheet" type="text/css" href="{url_base}sitio/css/jquery.uix.multiselect.css?v=2013092301">
<link rel="stylesheet" type="text/css" href="{url_base}sitio/css/form.css" />
<link rel="stylesheet" type="text/css" href="{url_base}sitio/css/jquery.tagit.css">
<link rel="stylesheet" type="text/css" href="{url_base}sitio/css/flexigrid.theme.css?v=1" />
<script>
	$(function( ) {
		var tabs_configuracion = $("#tabsConfigurationHome").tabs({
			active:  $.cookie('selected-tab-config'),
			activate: function( event, ui ) {
				$.cookie('selected-tab-config', ui.newTab.index(), { path: '/' });
			},
			beforeLoad : function( event, ui ) {

				if ( ui.tab.data( "loaded" ) ) {
                    event.preventDefault();
                    return;
                }
         
                ui.jqXHR.success(function() {
                    ui.tab.data( "loaded", true );
                });

				ui.jqXHR.error(function( ) {
					ui.panel.html("Technical problems. Please report baking.");
				});
			}

		}); 
		
		{tabs_monitor_active}
	}); 
</script>
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
	<script type="text/javascript" src="{url_base}sitio/js/flexigrid.theme.js?v=4"></script>
	<script type="text/javascript" src="{url_base}sitio/js/form_config.js"></script>
	<script type="text/javascript" src="{url_base}sitio/js/ui.selectmenu.js"></script>
	<script type="text/javascript" src="{url_base}sitio/js/tag-it.js"></script>
	<script type="text/javascript" src="{url_base}sitio/js/jquery.uix.multiselect.min.js"></script>
	<script type="text/javascript" src="{url_base}sitio/js/locales/jquery.uix.multiselect_{language}.js"></script>
</body>
</html>