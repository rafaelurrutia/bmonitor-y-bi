{header}
<link rel="stylesheet" type="text/css" href="{url_base}sitio/css/style.php?css=flexigrid" />
<script type="text/javascript" src="{url_base}sitio/js/flexigrid.js"></script>
<script type="text/javascript" src="{url_base}sitio/js/ajaxfileupload.js"></script>
<script type="text/javascript" src="{url_base}sitio/js/form_option.js"></script>
<link rel="stylesheet" type="text/css" href="{url_base}sitio/css/style.php?css=form" />
<script type="text/javascript">
	$(function() {
		$("#tabs").tabs({
			beforeLoad : function(event, ui) {
				ui.jqXHR.fail(function(xhr, status, index, anchor) {
						location.reload();
				});
				ui.jqXHR.error(function() {
					ui.panel.html("Technical problems. Please report to baking.s");
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
                    <li>
                        <a href="{url_base}neutralidad/getNeutralidad">{NEUTRALITY}</a>
                    </li>
                    <li>
 <!-- /*                           <a href="{url_base}neutralidad/getSubtel">{RESUME}</a>  //tab inutil  bmon25*/ --> 
                    </li>
                    <li>
                        <a href="{url_base}neutralidad/getPesos">{WEIGHING}</a>
                    </li>
                    <!--<li><a href="{url_base}neutralidad/getSti">STI</a></li>-->
                    <li>
                        <a href="{url_base}neutralidad/getExportar">{EXPORT}</a>
                    </li>
                    <li>
 <!-- /*                     <a href="{url_base}neutralidad/getImportar">{IMPORT}</a>  //tab inutil  bmon25*/ -->
                    </li>
                </ul>
            </div>
        </div>
        <div class="push"></div>
    </div>
    {footer}
</body>
</html>