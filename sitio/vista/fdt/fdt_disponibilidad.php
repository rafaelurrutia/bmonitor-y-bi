<script language="JavaScript">
jQuery(function() {
	

	$("#disp_fmGrupo").change(function(){
		$.post("/fdt/getInformeDisponibilidad",{ groupid:$(this).val() },function(data){$("#disp_fmHost").html(data)})
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
	
	$( "#disp_fmFrom" ).datepicker({
		changeYear: true,
	    changeMonth: true,
	    numberOfMonths: 1,
	    showButtonPanel: true,
	    onClose: function( selectedDate ) {
	        $( "#disp_fmTo" ).datepicker( "option", "minDate", selectedDate );
	    }
	});
	$( "#disp_fmTo" ).datepicker({
	    defaultDate: "+1w",
	    changeMonth: true,
	    numberOfMonths: 1,
	    showButtonPanel: true,
	    onClose: function( selectedDate ) {
	        $( "#disp_fmFrom" ).datepicker( "option", "maxDate", selectedDate );
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
	    $('#disponibilidad tr').each( function(){ 
	        var cell = $('td[abbr="cumple"] >div', this); 
	        cell.addClass( lblStatus[cell.text()].css );
	    }); 
	    return true; 
	}
	
	$("#disponibilidad").flexigrid({
		url: '{url_base}fdt/getDisponibilidadTable',
		dataType: 'json',
		title: 'ESTADÍSTICAS DISPONIBILIDAD',
		colModel : [
				{table_column}
		],
		usepager: true,
		useRp: false,
		rp: 50,
		buttons : [
			{name: 'Guardar', bclass: 'add', onpress : dialogBox},
			{separator: true}
		],
		resizable: false,
		onSubmit : function(){
			$('#disponibilidad').flexOptions({params: [{name:'callId', value:'equipo'}].concat($('#disp_fmFilter').serializeArray())});
			return true;
		},
		onSuccess   : gridFormat,
		showTableToggleBtn: true,
		singleSelect: true
	});

	$.ajaxSetup ({
		cache: false
	});
		
	function dialogBox(com, grid) {
		if (com == 'Guardar') {
			jQuery('#loading').show();
  			jQuery('#imgLoading').show();
  			value = $("#form_no_cumple_disponibilidad").serialize();
 			$.post(
			'{url_base}fdt/setDisponibilidad',
				value,
				function(responseText){
					jQuery('#loading').hide();
					jQuery('#imgLoading').hide();
					alert(responseText.msg)
					$("#disponibilidad").flexReload();
				},
				"jsonp"
			);
		}
	}
		
	$("#disp_fmEstados").change(function(){
		$("#disponibilidad").flexReload();
	});
	
	$("#disp_fmHost").change(function(){
		$("#disponibilidad").flexReload();
	});

	$("#disp_fmTo").change(function(){
		$("#disponibilidad").flexReload();
	});
	
	$("#disp_fmFrom").change(function(){
		$("#disponibilidad").flexReload();
	});
});
</script>
<div class="panel fg-toolbar ui-toolbar ui-widget-header ui-corner-tl ui-corner-tr ui-helper-clearfix">
	<form id="disp_fmFilter">
		
		<fieldset>
			<p>
				<label for="disp_fmFrom">Desde</label>
				<input type="text" id="disp_fmFrom" value="{disp_fmFrom}" name="disp_fmFrom"/>
			</p>
			<p>
				<label for="disp_fmTo">Hasta</label>
				<input type="text" id="disp_fmTo" value="{disp_fmTo}" name="disp_fmTo"/>
			</p>
		</fieldset>
		
		<fieldset>
			<p>
				<label for="disp_fmGrupo" accesskey="g">Grupo</label>
				<select name="disp_fmGrupo" id="disp_fmGrupo">
					{option_groups}
				</select>
			</p>
            <p>
                <label for="disp_fmHost" accesskey="e">Escuelas</label>
                <select name="disp_fmHost" id="disp_fmHost">
                {option_escuela}
                </select>
            </p>
		</fieldset>
		<fieldset>
		    <p></p>
			<p>
				<label for="disp_fmEstados" accesskey="s">Estados</label>
				<select name="disp_fmEstados" id="disp_fmEstados">
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
<form id="form_no_cumple_disponibilidad">
	<table id="disponibilidad"></table>
</form>
