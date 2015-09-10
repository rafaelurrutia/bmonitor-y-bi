<script language="JavaScript">
jQuery(function() {
		
	
	var grupo = $("#fmGrupo_export");
	var meses = $("#fmMeses_export");
	var ano = $("#fmAno_export");
	var medicion = $("#neutralidad_export_medicion");
	var sonda = $("#neutralidad_export_sonda");
	var plan = $("#neutralidad_export_plan");
	var estadisticas = $("#neutralidad_export_estadisticas");
	var tips = $(".validateTips");
	var first = 0; 

	sonda.change(function(){		
		$.post("/neutralidad/getAllPlan",{ id:sonda.val() },function(data){plan.html(data)})
	});

	grupo.change(function(){
		$.ajax({
			type: "POST",
			url: "/neutralidad/getAllHost",
			dataType: "json",
			data: {
				id:grupo.val()
			},
			success: function(data){
				if(data.status) {
					sonda.html(data.option);
					return true;
				} else {
					sonda.html(data.msg);
				}
			},
			error: function() {
				alert("Error");
			}
		});
	});

	$("#export-download").on("click", function() {
			
  		var bValid = true;
  		
  		bValid = bValid && $.checkSelect( tips, medicion, "Medición", 0);
  		
  		if ( bValid ) {
  		
			$.ajax({
				type: "POST",
				url: "/neutralidad/getExportNeutralidad",
				dataType: "json",
				data: {
					fmGrupo_export:grupo.val(),
					fmMeses_export:meses.val(),
					fmAno_export:ano.val(),
					neutralidad_export_medicion:medicion.val(),
					neutralidad_export_sonda:sonda.val(),
					neutralidad_export_plan:plan.val(),
					neutralidad_export_estadisticas:estadisticas.val()
				},
				success: function(data){
					if(data.status) {
						
						window.location.href = "/fdt/upload?f="+data.url;
						
						alert(data.msg);
						return true;
					} else {
						$('#loading').hide();
						$('#imgLoading').hide();
						alert(data.msg);
					}
				},
				error: function() {
					alert("Error");
				}
			});	
		}

	})
	
	$( "#export-download" ).button();
	
	$(window).ready(function() {
		$.ajax({
			type: "POST",
			url: "/neutralidad/getAllHost",
			dataType: "json",
			data: {
				id:grupo.val()
			},
			success: function(data){
				if(data.status) {
					sonda.html(data.option);
					$.post("/neutralidad/getAllPlan",{ id:data.first },function(data){plan.html(data)});
					return true;
				} else {
					alert(data.msg);
				}
			},
			error: function() {
				alert("Error");
			}
		});
		
	});
});
</script>
<div id="loading"></div>
<div id="imgLoading">
	<img widht="32" height="32" src="/sitio/img/ajax-loader.gif" alt="loading" title="loading" />
</div>
	
<div id="tableResumenConsFDT" class="ui-grid ui-widget ui-widget-content ui-corner-all">
	<form id="neutralidad_export">
	<div class="ui-grid-header ui-widget-header ui-corner-top">Export STI</div>
	<p  class="validateTips">Todo los elementos del formulario son requeridos.</p>
	<table class="ui-grid ui-component ui-component-content" cellpadding="0" cellspacing="0" width="100%" height="100%">
		<thead>
			<tr>
				<th class="ui-state-default">Campo</th>
				<th class="ui-state-default">Seleccion</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class="ui-widget-content"><label for="neutralidad_sti_groups" accesskey="a">Grupo:</label></td>
				<td class="ui-widget-content">
					<select name="neutralidad_sti_groups" id="neutralidad_sti_groups">
						{option_groups}
					</select></td>
			</tr>
			<tr>
				<td class="ui-widget-content"><label for="neutralidad_sti_month" accesskey="a">Mes:</label></td>
				<td class="ui-widget-content">						
					<select name="neutralidad_sti_month" id="neutralidad_sti_month">
						{option_meses}
					</select>
				</td>
			</tr>
			<tr>
				<td class="ui-widget-content"><label for="neutralidad_sti_year" accesskey="a">Año:</label></td>
				<td class="ui-widget-content">						
					<select name="neutralidad_sti_year" id="neutralidad_sti_year">
						{option_anos}
					</select>
				</td>
			</tr>
			<tr>
				<td class="ui-widget-content"><label for="neutralidad_sti_report" accesskey="a">Reporte:</label></td>
				<td class="ui-widget-content">						
					<select name="neutralidad_sti_report" id="neutralidad_sti_report">
						{option_report}
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="2"><button type='button' id='export-download'>Descargar</button></td>
			</tr>
		</tbody>
	</table>
	</form>
</div>
