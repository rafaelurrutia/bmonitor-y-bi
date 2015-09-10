<script language="JavaScript">
jQuery(function() {
	
	function gridFormat() { 
	    var lblStatus = { 
	        'No Cumple' : { 
	            css : 'color_red'
	        }, 
	        'Cumple' : { 
	            css : 'color_verdenina'
	        }
	    }; 
	    $('.disponibilidad tr').each( function(){ 
	        var cell = $('td[abbr="cumple"] >div', this); 
	        cell.addClass( lblStatus[cell.text()].css );
	    }); 
	    return true; 
	}
	
	$(".consolidado").flexigrid({
		url: '{url_base}fdt/getConsolidadoTable',
		dataType: 'json',
		title: 'INFORME CONSOLIDADO',
		colModel : [
			{display: 'Fecha', name : 'fecha' , width : '55'  , sortable : false, align: 'center'},
			{table_column},
			{display: 'Renta', name : 'renta' , width : '40'  , sortable : false, align: 'center'},
			{display: 'Cumplimiento </br> Bajada', name : 'com_bajada' , width : '40'  , sortable : false, align: 'center'},
			{display: 'Cumplimiento </br> Subida', name : 'com_subida' , width : '40'  , sortable : false, align: 'center'},
			{display: 'Promedio </br> Cumplimiento </br> Velocidad', name : 'prom_cump' , width : '40'  , sortable : false, align: 'center'},
			{display: 'Descuento </br> Subtel', name : 'descuento_subtel_vel' , width : '40'  , sortable : false, align: 'center'},
			{display: 'Disponibilidad', name : 'disponibilidad' , width : '40'  , sortable : false, align: 'center'},
			{display: 'Descuento </br> Subtel', name : 'descuento_subtel_disp' , width : '40'  , sortable : false, align: 'center'},
			{display: 'A pagar', name : 'fdt_pagar' , width : '40'  , sortable : false, align: 'center'}
		],
		height: 'auto',
		striped:false,
		rp: 2000,
		onSubmit : function(){
			$('.consolidado').flexOptions({params: [{name:'callId', value:'consolidado'}].concat($('#fmFilter_consolidado').serializeArray())});
			return true;
		},
   		onSuccess:  function(){
        	gridFormat();
        	getResumenConsFDT();
       	},
		singleSelect: true
	});

	$.ajaxSetup ({
		cache: false
	});

	$("#fmGrupo_consolidado").change(function(){
		$(".consolidado").flexReload();
		setTimeout(function() {getResumenConsFDT();} ,1000);
	});
		
	$("#fmMeses_consolidado").change(function(){
		$(".consolidado").flexReload();
		setTimeout(function() {getResumenConsFDT();} ,1000);	
	});
	
	$("#fmAno_consolidado").change(function(){
		$(".consolidado").flexReload();
		setTimeout(function() {getResumenConsFDT();} ,1000);
	});
	
	
	
	function getResumenConsFDT(){
		$.ajax({
			type: "POST",
			url: "/fdt/getResumenFDTConsolidado",
			dataType: "json",
			success: function(data){
				if(data.status) {
					$("#escuelasFDT").html(data.escuelas);
					$("#rentaTotalFDT").html(data.tarifaTotal);
					$("#descuentosFDT").html(data.descuentos);
					$("#AcobrarFDT").html(data.tarifaAPagar);
					$("#descuentoPORFDT").html(data.porcentaje);
				} else {
					alert("Error");
				}
			},
			error: function() {
				alert("Error");
			}
		});	
	}
});
</script>

<div class="paneles ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
		
	<form id="fmFilter_consolidado">
		<label for="fmGrupo_consolidado" accesskey="g">Grupo</label>
		<select name="fmGrupo_consolidado" id="fmGrupo_consolidado">
			{option_groups}
		</select>
		
		<label for="fmMeses_consolidado" accesskey="m">Mes</label>
		<select name="fmMeses_consolidado" id="fmMeses_consolidado">
			{option_meses}
		</select>
		
		<label for="fmAno_consolidado" accesskey="a">Año</label>
		<select name="fmAno_consolidado" id="fmAno_consolidado">
			{option_anos}
		</select>
	</form>
	
</div>
	
<div id="loading"></div>
<div id="imgLoading">
	<img widht="32" height="32" src="/sitio/img/ajax-loader.gif" alt="loading" title="loading" />
</div>

<table class="consolidado"></table>

<div id="tableResumenConsFDT" class="ui-grid ui-widget ui-widget-content ui-corner-all">
<div class="ui-grid-header ui-widget-header ui-corner-top">Resumen Informe Consolidado</div>
<table id="tableResumenConsFDT_int" class="ui-grid-content ui-widget-content">
	<thead>
		<tr>
			<th class="ui-state-default">Nombre</th>
			<th class="ui-state-default">Valor</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="ui-widget-content">Número de Escuelas:</td>
			<td class="ui-widget-content"><span id="escuelasFDT"></span></td>
		</tr>
		<tr>
			<td class="ui-widget-content">Renta Total:</td>
			<td class="ui-widget-content"><span id="rentaTotalFDT"></span></td>
		</tr>
		<tr>
			<td class="ui-widget-content">Descuento:</td>
			<td class="ui-widget-content"><span id="descuentosFDT"></span></td>
		</tr>
		<tr>
			<td class="ui-widget-content">Renta a cobrar:</td>
			<td class="ui-widget-content"><span id="AcobrarFDT"></span></td>

		</tr>
		<tr>
			<td class="ui-widget-content">% descuento: </td>
			<td class="ui-widget-content"><span id="descuentoPORFDT"></span></td>
		</tr>
	</tbody>
</table>
<div class="ui-grid-footer ui-widget-header ui-corner-bottom ui-helper-clearfix">

</div>
</div>
