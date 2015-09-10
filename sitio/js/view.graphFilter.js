$(document).ready(function() {

    var singleFilterGraph2 = $('#singleFilterGraph2');							

	function filterGraph() {

		var valueFilter =  $('#filterid',singleFilterGraph2).val();

		if (valueFilter == 1) {
		    $('.planidLabel',singleFilterGraph2).hide();
		} else if (valueFilter == 2) {
            $('.planidLabel',singleFilterGraph2).show();
			var valueGroups =  $('#groupid',singleFilterGraph2).val();

			if (valueGroups > 0) {

				$.ajax({
					url : "/graphc/getPlan",
					type : "POST",
					data : {
						groupid : valueGroups
					},
					dataType : "json",
					error : function() {
						alert("hay un error en el servicio de datos");
						return false;
					},
					success : function(j) {

						$('#planid',singleFilterGraph2).html(j.datos);
					}
				});

			} else {
				$('#planid',singleFilterGraph2).html('<option value="0">'+language.SELECT_A_GROUP+'</option>');
			}
		}
	}
	
	//$("#separatorCheck2" ).buttonset( "destroy" );
	
	//filterGraph2.get("separatorCheck2").buttonset();

	function getHost() {

		//$("#singleFilterGraph2 option[value=0]").attr("selected", true);
		
		var valueGroups = $('#groupid',singleFilterGraph2).val();
		var valueFilter = $('#filterid',singleFilterGraph2).val();
		var valuePlan = $('#planid',singleFilterGraph2).val();

		if (valueGroups > 0) {

			$.ajax({
				url : "/graphc/getHost",
				type : "POST",
				data : {
					groupid : valueGroups,
					valueFilter : valueFilter,
					valuePlan : valuePlan
				},
				dataType : "json",
				error : function() {
					alert("hay un error en el servicio de datos");
					return false;
				},
				success : function(j) {
					$('#hostid',singleFilterGraph2).html(j);
					getGraphFilter();
				}
			});

		}

	}

	function getMonitores() {
		var option = '<option selected="" value="0">'+language.SELECT_A_PROBE+'</option>';
		$('#monitorid',singleFilterGraph2).html(option);
			
		var valueGroups = $('#groupid',singleFilterGraph2).val();
		var valueCategory = $('#categoriesid',singleFilterGraph2).val();
		if (valueGroups > 0 && valueCategory > 0) {
			$.ajax({
				url : "/graphc/getMonitores",
				type : "POST",
				data : {
					"groupid" : valueGroups,
					"class" : valueCategory,
				},
				dataType : "json",
				error : function() {
					alert("hay un error en el servicio de datos");
					return false;
				},
				success : function(j) {
					$('#monitorid',singleFilterGraph2).html(j.datos);
				}
			});
		}
	}
	
	function getClass() {
		var valueGroups = $('#groupid',singleFilterGraph2).val();
		if (valueGroups > 0) {
			$.ajax({
				url : "/graphc/getClass",
				type : "POST",
				data : {
					"groupid" : valueGroups
				},
				dataType : "json",
				error : function() {
					alert("hay un error en el servicio de datos");
					return false;
				},
				success : function(j) {
					$('#categoriesid',singleFilterGraph2).html(j.datos);
					getMonitores();
				}
			});

		}
	}

	$('#refresh',singleFilterGraph2).click(function(event) {
		event.preventDefault();
		getGraphFilter();
		return false;
	});	
	
	$('#refresh',singleFilterGraph2).button({
		icons: {
			primary: "ui-icon-refresh"
		},
		text: true
	});

	$('#filterid',singleFilterGraph2).change(function() {
		filterGraph();
		getHost();
	});

	$('#groupid',singleFilterGraph2).change(function() {
		
		if($(this).val() == 0){
			var option = '<option selected="" value="0">'+language.SELECT_A_GROUP+'</option>';
			$('#hostid',singleFilterGraph2).html(option);
			$('#planid',singleFilterGraph2).html(option);
			$('#categoriesid',singleFilterGraph2).html(option);
			$('#monitorid',singleFilterGraph2).html(option);
		} else {
			var option = '<option selected="" value="0">'+language.SELECT_A_PROBE+'</option>';
			$('#monitorid',singleFilterGraph2).html(option);
			filterGraph();
			getHost();
			getClass();
		}		
	});

	$('#categoriesid',singleFilterGraph2).change(function() {
		getMonitores();
	});
	
	$('#planid',singleFilterGraph2).change(function() {
		getHost();
	});

	$('#hostid',singleFilterGraph2).change(function() {
		getGraphFilter();
	});
	
	$('#dataGrouping',singleFilterGraph2).change(function() {
		getGraphFilter();
	});

	$('#monitorid',singleFilterGraph2).change(function() {
		getGraphFilter();

	});
		
	function getGraphFilter() {
        if ($('#monitorid',singleFilterGraph2).val() > 0) {
        	$('.refresh',singleFilterGraph2).show();
            var formData = singleFilterGraph2.serialize();
            $.post('graphc/getChartsFilter', formData, function(data){
                $('#graph2_ultimafecha_index').html(data);                                
            });
        }
	}

});
