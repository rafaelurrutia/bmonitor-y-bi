<script language="JavaScript">
jQuery(function() {

	$("#fmGrupo_bandwidth").change(function(){
		$.post("/fdt/getInformeVelocidad",{ groupid:$(this).val() },function(data){$("#fmHost_bandwidth").html(data)})
	});	

	$.datepicker.regional['es'] = {
		closeText: 'Cerrar',
		prevText: '&#x3c;Ant',
		nextText: 'Sig&#x3e;',
		currentText: 'Hoy',
		monthNames: ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
		'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
		monthNamesShort: ['Ene','Feb','Mar','Abr','May','Jun',
		'Jul','Ago','Sep','Oct','Nov','Dic'],
		dayNames: ['Domingo','Lunes','Martes','Mi&eacute;rcoles','Jueves','Viernes','S&aacute;bado'],
		dayNamesShort: ['Dom','Lun','Mar','Mi&eacute;','Juv','Vie','S&aacute;b'],
		dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','S&aacute;'],
		weekHeader: 'Sm',
		dateFormat: 'yy-mm-dd',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};

	$.datepicker.setDefaults($.datepicker.regional['es']);



$( "#fmFrom_bandwidth" ).datepicker({
	changeYear: true,
    changeMonth: true,
    numberOfMonths: 1,
    showButtonPanel: true,
    onClose: function( selectedDate ) {
        $( "#fmTo_bandwidth" ).datepicker( "option", "minDate", selectedDate );
    }
});
$( "#fmTo_bandwidth" ).datepicker({
    defaultDate: "+1w",
    changeMonth: true,
    numberOfMonths: 1,
    showButtonPanel: true,
    onClose: function( selectedDate ) {
        $( "#fmFrom_bandwidth" ).datepicker( "option", "maxDate", selectedDate );
    }
});
        
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
	
	$(".bandwidth").flexigrid({
		url: '{url_base}fdt/getBandwidthTable',
		dataType: 'json',
		title: 'ESTADÍSTICAS DISPONIBILIDAD',
		colModel : [
				{table_column}
		],
		usepager: true,
		useRp: true,
		height: 500,
		rp: 50,
		buttons : [
			{name: 'Guardar', bclass: 'add', onpress : toolbox_bandwidth},
			{separator: true}
		],
		resizable: true,
		onSubmit : function(){
			$('.bandwidth').flexOptions({params: [{name:'callId', value:'bandwidth'}].concat($('#fmFilter_bandwidth').serializeArray())});
			return true;
		},
		onSuccess:  function(){
			gridFormat;
	 		$('.bandwidth input').button().addClass('inputUI input-medium-length');
       	} ,  
		showTableToggleBtn: true,
		singleSelect: true
	});

	$.ajaxSetup ({
		cache: false
	});
		
	function toolbox_bandwidth(com, grid) {
		if (com == 'Guardar') {
			jQuery('#loading').show();
  			jQuery('#imgLoading').show();
  			value = $("#form_no_cumple_bandwidth").serialize();
 			$.post(
			'{url_base}fdt/setBandwidth',
				value,
				function(responseText){
					jQuery('#loading').hide();
					jQuery('#imgLoading').hide();
					alert(responseText.msg)
					$(".bandwidth").flexReload();
				},
				"jsonp"
			);
		}
	}
		
	$("#fmEstados_bandwidth").change(function(){
		$(".bandwidth").flexReload();
	});
	
	$("#fmHost_bandwidth").change(function(){
		$(".bandwidth").flexReload();
	});

	$("#fmFrom_bandwidth").change(function(){
		$(".bandwidth").flexReload();
	});
	
	$("#fmTo_bandwidth").change(function(){
		$(".bandwidth").flexReload();
	});

	$("#fmMedicion_bandwidth").change(function(){
		$(".bandwidth").flexReload();
	});  
});
</script>

<div class="panel fg-toolbar ui-toolbar ui-widget-header ui-corner-tl ui-corner-tr ui-helper-clearfix">
		
	<form id="fmFilter_bandwidth">

		<fieldset>
			<p>
				<label for="fmFrom_bandwidth">Desde</label>
				<input type="text" id="fmFrom_bandwidth" value="{datepicker_fmFrom}" name="fmFrom_bandwidth"/>
			</p>
			<p>
				<label for="fmTo_bandwidth">Hasta</label>
				<input type="text" id="fmTo_bandwidth" value="{datepicker_fmTo}" name="fmTo_bandwidth"/>
			</p>
		</fieldset>
				
		<fieldset>
			<p>
			<label for="fmGrupo_bandwidth" accesskey="g">Grupo</label>
			<select name="fmGrupo_bandwidth" id="fmGrupo_bandwidth">
				{option_groups}
			</select>
			</p>
            <p>
            <label for="fmHost_bandwidth" accesskey="e">Escuelas</label>
            <select name="fmHost_bandwidth" id="fmHost_bandwidth">
                {option_escuela}
            </select>
            </p>
		</fieldset>
		<fieldset>
			<p>
			<label for="fmMedicion_bandwidth" accesskey="s">Medicion</label>
			<select name="fmMedicion_bandwidth" id="fmMedicion_bandwidth">
				{option_medicion}
			</select>
			</p>
            <p>
            <label for="fmEstados_bandwidth" accesskey="s">Estados</label>
            <select name="fmEstados_bandwidth" id="fmEstados_bandwidth">
                {option_estados}
            </select>
            </p>
		</fieldset>
	</form>
</div>

<div id="loading"></div>
<div id="imgLoading">
	<img widht="32" height="32" src="/sitio/img/ajax-loader.gif" alt="loading" title="loading" />
</div> 

<form id="form_no_cumple_bandwidth">
	<table class="bandwidth"></table>
</form>