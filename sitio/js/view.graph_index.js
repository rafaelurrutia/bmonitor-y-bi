$(document).ready(function( ) {

	var singleFilterGraph = $('#singleFilterGraph');

	function getGraphIndex( ) {
		if ( $('#hostid', singleFilterGraph).val() > 0 && $('#graphid', singleFilterGraph).val() > 0 ) {
			var formData = singleFilterGraph.serialize();
			$.post('graphc/getCharts', formData, function( data ) {
				$('#graph_ultimafecha_index').html(data);
			});
		}
	}

	function getGraphItems( ) {
		if ( $('#dataGrouping', singleFilterGraph).val() > 0 ) {
			var formData = singleFilterGraph.serialize();
			$.ajax({
				url : "/graphc/getChartsItems",
				type : "POST",
				data : formData,
				async: false,
				dataType : "json",
				error : function( ) {
					
				},
				success : function( r ) {
					if(r.status) {
						$('#graphid').html(r.option);
					} else {
						$('#graphid').html(r.option);
					}
				}
			});
		}
	}


	$('#singleFilterGraph #groupid').change(function( ) {

		getGraphItems();
		$("#graphid option[value=0]").attr("selected", true);
		var valueGroups = $('#groupid', singleFilterGraph).val();
		$.ajax({
			url : "/graphc/getHost",
			type : "POST",
			data : {
				groupid : valueGroups
			},
			dataType : "json",
			error : function( ) {
				alert("hay un error en el servicio de datos");
				return false;
			},
			success : function( j ) {
				$("#hostid").html(j);
			}

		});
	});

	$("#hostid", singleFilterGraph).change(function( ) {
		getGraphIndex();
	});

	$("#graphid", singleFilterGraph).change(function( ) {
		getGraphIndex();
	});

	$("#dataGrouping", singleFilterGraph).change(function( ) {
		getGraphIndex();
	});
});
