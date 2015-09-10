<script language="JavaScript">
jQuery(function() {
	
	var url_export;
	var url_export_status;
	
	function table_pesos(){	
		$.post('{url_base}neutralidad/getSubtelTable',
			$("#fmFilter_neutralidad_subtel").serialize(),
			function(responseText){
				if(responseText.status) {
					$("#subtel_neutralidad").html(responseText.datos);
					url_export_status = responseText.url_status;
					url_export = responseText.url;
					url_export2 = responseText.url2;
					$("#subtel-download").button();
					$("#subtel-download2").button();
                    $("#subtel-download").on("click", function() {
                        if(url_export_status) {
                            var ts = Math.round((new Date()).getTime() / 1000);
                            window.location.href = url_export+"?v="+ts;
                        }       
                    });
                    $("#subtel-download2").on("click", function() {
                        if(url_export_status) {
                            var ts = Math.round((new Date()).getTime() / 1000);
                            window.location.href = url_export2+"?v="+ts;
                        }       
                    });    					
					
				} else {
				
				}
			},
			"json"
		);
	}
	
	table_pesos();

	$("#fmEscala_neutralidad_subtel").change(function(){
		table_pesos();
	});
		
	$("#fmGrupo_neutralidad_subtel").change(function(){
		table_pesos();
	});

	$("#fmMeses_neutralidad_subtel").change(function(){
		table_pesos();
	});

	$("#fmAno_neutralidad_subtel").change(function(){
		table_pesos();
	});
	

	
	
});
</script>
<div>
	<div id="loading"></div>
	<div id="imgLoading">
		<img widht="32" height="32" src="/sitio/img/ajax-loader.gif" alt="loading" title="loading" />
	</div>
	
	<div class="paneles ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
	<form id="fmFilter_neutralidad_subtel">

	<label for="fmEscala_neutralidad_subtel" accesskey="g">{SCALE}</label>
	<select name="fmEscala_neutralidad_subtel" id="fmEscala_neutralidad_subtel">
	{option_escalas}
	</select>
	
	<label for="fmGrupo_neutralidad_subtel" accesskey="g">{GROUP}</label>
	<select name="fmGrupo_neutralidad_subtel" id="fmGrupo_neutralidad_subtel">
	{option_groups}
	</select>
	
	<label for="fmMeses_neutralidad_subtel" accesskey="m">{MONTH}</label>
	<select name="fmMeses_neutralidad_subtel" id="fmMeses_neutralidad_subtel">
	{option_meses}
	</select>
	
	<label for="fmAno_neutralidad_subtel" accesskey="a">{YEAR}</label>
	<select name="fmAno_neutralidad_subtel" id="fmAno_neutralidad_subtel">
	{option_anos}
	</select>
	</form>
	</div>
	<div id='container_tables_center'>
		<div id="subtel_neutralidad">
			
		</div>
	</div>
</div>