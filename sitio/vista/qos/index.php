{header}  
    <link rel="stylesheet" type="text/css" href="{url_base}sitio/css/flexigrid.theme.css?v=2" />
    <link rel="stylesheet" type="text/css" href="{url_base}sitio/css/form.css" />
    <script type="text/javascript">
    $(function() {
        $( "#tabs" ).tabs({
			active:  $.cookie('selected-tab-qoe'),
			activate: function( event, ui ) {
				$.cookie('selected-tab-qoe', ui.newTab.index(), { path: '/' });
			},
            beforeLoad: function( event, ui ) {            
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
    <script type="text/javascript" src="{url_base}sitio/js/form_config.js"></script>
</body>
</html>