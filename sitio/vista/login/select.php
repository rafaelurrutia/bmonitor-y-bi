{header}
<script type="text/javascript">
	jQuery(function($) {
		$("#username").focus();
	}); 
</script>

<style type="text/css">
    .alert0 {
        min-width: 358px;
        min-height: 75px;
        position: relative;
    }
    .alert1 {
        min-width: 358px;
        min-height: 55px;
        position: relative;
    }
    
    .boxSection {
        width: 300px; 
        float: left; 
        margin: 10px;
        text-align: center;
    }
    
</style>

</head>

<body>

    <div class="container ui-widget-overlay">
        {menu}
        <div style="margin: 0px auto; width: 640px" class="row">
           
            <div id="bmonitorSection" class="well boxSection">
                <h2 class="title">
                    Bmonitor
                </h2>
                <a href="/"><img style="border: none" src="/sitio/img/1384373509_Admin.png" alt="Baking" width="180"/></a>
            </div>
            <div id="biSection" class="well boxSection">
                <h2 class="title">
                    BI
                </h2>
                <a href="/bi"><img style="border: none" src="/sitio/img/pie-chart-icon.png" alt="Baking" width="180"/></a>
            </div>
           
        </div>
    </div>
    {footer}
    <script type="text/javascript" src="{url_base}sitio/js/view.login.js"></script>
</body>
</html>