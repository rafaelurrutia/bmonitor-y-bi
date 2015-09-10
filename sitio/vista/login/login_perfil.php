{header}
	<link rel="stylesheet" type="text/css" href="{url_base}sitio/css/style.php?css=flexigrid" />
	<script type="text/javascript" src="{url_base}sitio/js/flexigrid.js"></script>
	<script type="text/javascript" src="{url_base}sitio/js/jquery.cookie.js"></script>
	<script type="text/javascript" src="{url_base}sitio/js/form_option.js"></script>
	<link rel="stylesheet" type="text/css" href="{url_base}sitio/css/style.php?css=form" />
	<script>
	$(function() {
		var tabs_login = $("#tabs").tabs({
		beforeLoad: function (event, ui) {
			if (ui.tab.data("loaded")) {
				event.preventDefault();
	            return;
	        }
	
			ui.jqXHR.success(function () {
	            ui.tab.data("loaded", true);
	        });
	
	        ui.jqXHR.error(function () {
	            ui.panel.html(
	                "Problemas tecnicos. " +
	                "Por favor , reportar a baking.");
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
					<li><a href="{url_base}login/getPerfil">{PROFILE}</a></li>
				</ul>
			</div>
		</div>
		<div class="push"></div>	
	</div>
	{footer}
</body>
</html>