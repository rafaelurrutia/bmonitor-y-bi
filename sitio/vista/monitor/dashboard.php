<!--
<script type="text/javascript">

	function gridFormat() {
	    $('.estadosistema tr').each( function(){ 
	        var cellCritica = $('td[abbr="critica"] >div', this); 
	        if(cellCritica.text() > 0){
	        	cellCritica.addClass( 'color_red' );  	
	        }   
	        var cellCritica = $('td[abbr="media"] >div', this); 
	        cellCritica.addClass( 'color_yellow' );	      	    	        
	    }); 
	    return true; 
	}

	$(".estadosistema").flexigrid({
		url: '{url_base}tablas/estadoSistema',
		dataType: 'json',
		title: 'Estado del sistema',
		colModel : [
			{display: 'Grupo del equipo', name : 'grupo_equipo', width : 200, sortable : true, align: 'left'},
			{display: 'Crítica', name : 'critica', width : 100, sortable : true, align: 'left'},
			{display: 'Media', name : 'media', width : 100, sortable : true, align: 'left'}
			],
		height: 'auto',
		width: 438,
		useRp: false,
		rp: 50,
		showTableToggleBtn: true,
		resizable: false,
        usepager: false,
		onSuccess: gridFormat
	});

	$(".estadobsw").flexigrid({
		url: 'tablas/estadoBsw',
		dataType: 'json',
		title: 'Estado de BSW',
		colModel : [
			{display: 'Parámetro', name : 'parametro', width :409, sortable : true, align: 'left'},
			{display: 'Estado', name : 'estado', width : 46, sortable : true, align: 'left'},
			{display: 'Detalles', name : 'detalle', width : 81, sortable : true, align: 'left'}
		],
		height: 'auto',
		width: 575,
		usepager: false,
		useRp: true,
		resizable: false,
		rp: 15,
		showTableToggleBtn: true
	});
	
	function ultimos20problemas() {

		$(".ultimos20problemas").flexigrid({
			url: 'tablas/ultimos20problemas',
			dataType: 'json',
			title: 'Últimos 20 problemas',
			colModel : [
				{display: 'Equipo/Plantilla', name : 'parametro', width : 250, sortable : true, align: 'left'},
				{display: 'Problema', name : 'estado', width : 130, sortable : true, align: 'left'},
				{display: 'Última conexion SNMP', name : 'detalle', width : 112, sortable : true, align: 'left'},
				{display: 'Ultima conexion SONDA', name : 'detalle', width : 121, sortable : true, align: 'left'},
				{display: 'DNS', name : 'detalle', width : 100, sortable : true, align: 'left'}
			],
			height: 'auto',
			width: 775,
			usepager: false,
			resizable: false,
			useRp: true,
			rp: 15,
			showTableToggleBtn: true
		});
	
	}
	
	window.setTimeout("ultimos20problemas()",200);
	
	//reload
	var refreshId = setInterval(function() {
		$(".estadosistema").flexReload();
    	$(".estadobsw").flexReload();
	}, 60000);

	var refreshId = setInterval(function() {
		$(".ultimos20problemas").flexReload();
	}, 70000);
		
	

</script>
<div class='container_tables_center'>
	<table class="estadosistema"></table>
	<table class="estadobsw"></table>
	<table class="ultimos20problemas"></table>
</div>

-->