$(document).ready(function() {

	$("#fm_graph_Grupo").change(function(){
		$("#fm_graph_Graph option[value=0]").attr("selected",true);
		var valueGroups = $("#fm_graph_Grupo").val();

		$.ajax({
			url: "/graphc/getHost",
			type: "POST",
			data: { groupid: valueGroups },
			dataType: "json",
			error: function() {
				alert("hay un error en el servicio de datos");
				return false;
			},
			success: function(j) {
				$("#fm_graph_Host_1").html(j);
				$("#fm_graph_Host_2").html(j);
			}
		});
	});
	
	$("#fm_graph_Host_1").change(function(){
		$("#fm_graph_Graph option[value=0]").attr("selected",true);
	});
	
	$("#fm_graph_Host_2").change(function(){
		$("#fm_graph_Graph option[value=0]").attr("selected",true);
	});	
	
	$("#fm_graph_Graph").change(function(){
		var id_graph = $("#fm_graph_Graph").val();
		var id_host_1 = $("#fm_graph_Host_1").val();
		var id_host_2 = $("#fm_graph_Host_2").val();
		var id_groups = $("#fm_graph_Grupo").val();
		var limit  = $("#fm_graph_limit").val();
	
		if(id_graph > 0) {
			$('#graph_compare').load('graphc/graphCompare',{graphid:id_graph, id_host_1:id_host_1, id_host_2:id_host_2, id_groups:id_groups, limit:limit, graph:"unic"});
		}

	});
  
});