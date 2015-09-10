{header}
    <link rel="stylesheet" type="text/css" href="{url_base}sitio/css/flexigrid.theme.css?v=1" />
    <script type="text/javascript" src="{url_base}sitio/js/flexigrid.theme.js"></script>
	<script type="text/javascript" src="{url_base}sitio/js/form_option.js"></script>
	<link rel="stylesheet" type="text/css" href="{url_base}sitio/css/style.php?css=form" />
	<script type="text/javascript">
	$(function() {
		$( "#tabs" ).tabs({
			remote: false,
			cookie: {
				expires: 1
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
</head>

<body>
	<div class="container">
		{menu}
		<div id="content">
			<div id="tabs">
				<ul>
					<li><a href="{url_base}inventario/getGrupoEquipos">{AGENTS}</a></li>
				</ul>
			</div>
		</div>
		<div class="push"></div>	
	</div>
	{footer}
</body>
</html>