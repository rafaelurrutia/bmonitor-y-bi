$(document).ready(function( ) {

	var singleFilterChartGrouped = new $.formVar( 'singleFilterChartGrouped' );

	function getChartGrouped( ) {
		if ( singleFilterChartGrouped.get('hostid').val() > 0 && singleFilterChartGrouped.get('graphid').val() > 0 ) {
			singleFilterChartGrouped.get('refresh','.').show();
			var hostName = $('#hostid option:selected',singleFilterChartGrouped.form).text();
			var formData = singleFilterChartGrouped.form.serialize()+"&hostName="+hostName;
			$.post('graphc/getCharts', formData, function( data ) {
				$('#chartGroupedIndex').html(data);
			});
		}
	}
		
	function getChartGroupedItems( ) {
		if(singleFilterChartGrouped.get('groupid').val() > 0) {		
			
			var request = $.ajax({
				url : "/graphc/getChartsItems",
				type : "POST",
				data : singleFilterChartGrouped.form.serialize(),
				dataType : "json"
			});
			request.done(function( data ) {
				if ( data.status ) {
					$('#graphid').html(data.option);
				} else {
					$('#graphid').html(data.option);
				}
			});	
		}	
	}
	
	singleFilterChartGrouped.get('refresh').click(function(event) {
		event.preventDefault();
		getChartGrouped();
	});	
	
	singleFilterChartGrouped.get('refresh').button({
		icons: {
			primary: "ui-icon-refresh"
		},
		text: true
	});
    
	singleFilterChartGrouped.get('groupid').change(function( ) {		
		getChartGroupedItems();
		singleFilterChartGrouped.get('graphid').val(0);
		//$("#graphid option[value=0]").attr("selected", true);
		
		var valueGroups = singleFilterChartGrouped.get('groupid').val();
		
		var request = $.ajax({
			url : "/graphc/getHost",
			type : "POST",
			data : {
				groupid : valueGroups
			},
			dataType : "json"
		});
		
		request.fail(function(  jqXHR, textStatus ) {
			alert("hay un error en el servicio de datos");
		});
		request.done(function( data ) {
			singleFilterChartGrouped.get('hostid').html(data);
		});
	});

	singleFilterChartGrouped.get('hostid').change(function( ) {
		getChartGrouped();
	});

	singleFilterChartGrouped.get('graphid').change(function( ) {
		getChartGrouped();
	});

	singleFilterChartGrouped.get('dataGrouping').change(function( ) {
		getChartGrouped();
	});
});
