jQuery(function() {
	
	var is_safari = navigator.userAgent.indexOf("Safari") > -1;

	$("#export-download").button();
	
	$("#export-download-excel").button();
	
	$("#fmGrupo_export").change(function(){

	});

	$("#export-download").on("click", function() {
		
		if( $("#fdt_export_medicion option:selected").text() == "Seleccionar" ) {
			alert("Campo medicion sin asignar");
			return;
		} 
	
		if( $("#fdt_export_colegio option:selected").text() == "Seleccionar" ) {
			alert("Campo colegio sin asignar");
			return;
		} 
		
		setTimeout(function() {
			$('#loading').show();
			$('#imgLoading').show();
		}, 10);
		
		var fmGrupo_export = $("#fmGrupo_export").val();
		var fmMeses_export = $("#fmMeses_export").val();
		var fmAno_export = $("#fmAno_export").val();
		var fdt_export_medicion = $("#fdt_export_medicion").val();
		var fdt_export_colegio = $("#fdt_export_colegio").val();
		
		if ($('#cabecera').is(':checked')) {
			var cabecera = 1;
		} else {
			var cabecera = 0;
		}
		
		$.ajax({
			type: "POST",
			url: "/fdt/getExportFDT",
			dataType: "json",
			data: {
				fmGrupo_export:fmGrupo_export,
				fmMeses_export:fmMeses_export,
				fmAno_export:fmAno_export,
				fdt_export_medicion:fdt_export_medicion,
				fdt_export_colegio:fdt_export_colegio,
				cabecera:cabecera
			},
			/*
			headers: {
				"Content-Type": "application/json",
			},
			beforeSend : function (xhr) {
				xhr.setRequestHeader("Content-Type","text/csv");
			},*/
			success: function(data){
				if(data.status) {
					$('#loading').hide();
					$('#imgLoading').hide();
					
					if (is_safari) {
						window.location.href = "/fdt/upload?f="+data.url;
					} else {
						window.location.href = "/fdt/upload?f="+data.url;
					}

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

	})
	
	$("#export-download-excel").on("click", function() {
		
		if( $("#fdt_export_medicion option:selected").text() == "Seleccionar" ) {
			alert("Campo medicion sin asignar");
			return;
		} 
	
		if( $("#fdt_export_colegio option:selected").text() == "Seleccionar" ) {
			alert("Campo colegio sin asignar");
			return;
		} 
		
		setTimeout(function() {
			$('#loading').show();
			$('#imgLoading').show();
		}, 10);
		
		var fmGrupo_export = $("#fmGrupo_export").val();
		var fmMeses_export = $("#fmMeses_export").val();
		var fmAno_export = $("#fmAno_export").val();
		var fdt_export_medicion = $("#fdt_export_medicion").val();
		var fdt_export_colegio = $("#fdt_export_colegio").val();
		
		if ($('#cabecera').is(':checked')) {
			var cabecera = 1;
		} else {
			var cabecera = 0;
		}
		
		$.ajax({
			type: "POST",
			url: "/fdt/getExportFDTNEW",
			dataType: "json",
			data: {
				fmGrupo_export:fmGrupo_export,
				fmMeses_export:fmMeses_export,
				fmAno_export:fmAno_export,
				fdt_export_medicion:fdt_export_medicion,
				fdt_export_colegio:fdt_export_colegio,
				cabecera:cabecera
			},
			/*
			headers: {
				"Content-Type": "application/json",
			},
			beforeSend : function (xhr) {
				xhr.setRequestHeader("Content-Type","text/csv");
			},*/
			success: function(data){
				if(data.status) {
					$('#loading').hide();
					$('#imgLoading').hide();
					
					if (is_safari) {
						window.location.href = "/fdt/upload?f="+data.url;
					} else {
						window.location.href = "/fdt/upload?f="+data.url;
					}

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

	})

});