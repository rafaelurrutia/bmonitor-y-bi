<script language="JavaScript">
jQuery(function() {

	function genTableNeutralidad () {
		var pesos = $("#fmPeso_neutralidad").val();
		var escala = $("#fmEscala_neutralidad").val();
		var grupo = $("#fmGrupo_neutralidad").val();
		var meses = $("#fmMeses_neutralidad").val();
		var ano = $("#fmAno_neutralidad").val();
		
		$.ajax({
			type: "POST",
			url: "/neutralidad/getTableNeutralidad",
			dataType: "json",
			data: {
				pesos:pesos,
				escala:escala,
				grupo:grupo,
				meses:meses,
				ano:ano
			},
			success: function(data){
				if(data.status) {

					$("#container_tables_center_1").html(data.tablas);

				} else {
					alert("error");
				}
			},
			error: function() {
				alert("Error");
			}
		});
	}
	
	genTableNeutralidad();
      
    function refresh () {
    	genTableNeutralidad();
    }
    
    $("#fmPeso_neutralidad").change(function(){
		refresh();
    });

    $("#fmEscala_neutralidad").change(function(){
		refresh();
    });

    $("#fmGrupo_neutralidad").change(function(){
		refresh();
    });

    $("#fmMeses_neutralidad").change(function(){
		refresh();
    });

    $("#fmAno_neutralidad").change(function(){
		refresh();
    });
});
</script>
<div>
	<div id="loading"></div>
	<div id="imgLoading">
		<img widht="32" height="32" src="/sitio/img/ajax-loader.gif" alt="loading" title="loading" />
	</div>
	
	<div class="paneles ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
	<form id="fmFilter_neutralidad">
	<label for="fmPeso_neutralidad" accesskey="p">{WEIGHING}</label>
	<select name="fmPeso_neutralidad" id="fmPeso_neutralidad">
	<option value='1' selected>{UNWEIGHTED}</option>
	<option value='2'>{WEIGHTED}</option>
	</select>
	
	<label for="fmEscala_neutralidad" accesskey="s">{SCALE}</label>
	<select name="fmEscala_neutralidad" id="fmEscala_neutralidad">
	{option_escalas}
	</select>
	
	<label for="fmGrupo_neutralidad" accesskey="g">{GROUP}</label>
	<select name="fmGrupo_neutralidad" id="fmGrupo_neutralidad">
	{option_groups}
	</select>
	
	<label for="fmMeses_neutralidad" accesskey="m">{MONTH}</label>
	<select name="fmMeses_neutralidad" id="fmMeses_neutralidad">
	{option_meses}
	</select>
	
	<label for="fmAno_neutralidad" accesskey="y">{YEAR}</label>
	<select name="fmAno_neutralidad" id="fmAno_neutralidad">
	{option_anos}
	</select>
	</div>
	<div id='container_tables_center_1'></div>
</div>