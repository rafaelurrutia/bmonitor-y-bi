{header}
    <link rel="stylesheet" type="text/css" href="{url_base}sitio/css/jquery.uix.multiselect.css?v=2013092301">
    <link rel="stylesheet" type="text/css" href="{url_base}sitio/css/flexigrid.theme.css?v=1" />
	<link rel="stylesheet" type="text/css" href="{url_base}sitio/css/form.css" />
	<script type="text/javascript">
	jQuery(function($){
		
		var tabs_active = 6;
				
		var tabs_admin = $("#tabsAdmin").tabs({
			active:  $.cookie('selected-tab-admin'),
			activate: function( event, ui ) {
				$.cookie('selected-tab-admin', ui.newTab.index(), { path: '/' });
			},
			beforeLoad: function (event, ui) {			    
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
		
		// close icon: removing the tab on click
		tabs_admin.delegate( "span.ui-icon-close", "click", function() {
			var panelId = $( this ).closest( "li" ).remove().attr( "aria-controls" );
			$( "#" + panelId ).remove();
			tabs_admin.tabs( "refresh" );
			tabs_active--;
			tabs_admin.tabs('option', 'active', 1);
		});
		 
		tabs_admin.bind( "keyup", function( event ) {
			if ( event.altKey && event.keyCode === $.ui.keyCode.BACKSPACE ) {
				var panelId = tabs.find( ".ui-tabs-active" ).remove().attr( "aria-controls" );
				$( "#" + panelId ).remove();
				tabs_admin.tabs( "refresh" );
				tabs_active--;
				tabs_admin.tabs('option', 'active', 1);
			}
		});
		

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
    <script type="text/javascript" src="{url_base}sitio/js/flexigrid.theme.js"></script>
    <script type="text/javascript" src="{url_base}sitio/js/form_option.js"></script>
    <script type="text/javascript" src="{url_base}sitio/js/ajaxfileupload.js"></script>
    <script type="text/javascript" src="{url_base}sitio/js/plugins/localisation/jquery.localisation-min.js"></script>
    <script type="text/javascript" src="{url_base}sitio/js/jquery.uix.multiselect.min.js"></script>
    <script type="text/javascript" src="{url_base}sitio/js/locales/jquery.uix.multiselect_{language}.js"></script>
</body>
</html>