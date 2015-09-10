jQuery(function($){
	//Formularios    
	var sonda_name = $( "#sonda_name" ),
		sonda_dns = $( "#sonda_dns" ),
		sonda_group = $( "#sonda_group" ),
		sonda_plan = $( "#sonda_plan" ),
		sonda_ip_wan = $( "#sonda_ip_wan" ),
		sonda_mac_wan = $( "#sonda_mac_wan" ),
		allFields = $( [] ).add( sonda_name ).add( sonda_dns ).add( sonda_ip_wan ).add( sonda_mac_wan ),
		tips = $( ".validateTips" );
	
	$.loading_in = function() {
		jQuery('#loading').show();
		jQuery('#imgLoading').show();
	};
	
	$.loading_out = function() {
		jQuery('#loading').hide();
		jQuery('#imgLoading').hide();
	};	

	$.checkLength = function(  o, n, min, max ){
		if ( o.val().length > max || o.val().length < min ) {
			o.addClass( "ui-state-error" );
			o.css("color","#FFFFFF");
			updateTips( "El largo del campo " + n + " debe estar entre " +
				min + " y " + max + "." );
			return false;
		} else {
			return true;
		}
	};
	
	$.updateTips = function( o, t ){
		o.text( t ).addClass( "ui-state-highlight" );
		setTimeout(function() {
			o.removeClass( "ui-state-highlight", 1500 );
		}, 500 );	
	};
	
	$.checkSelect = function( o, n, not ) {
		if ( o.val() == not ) {
			updateTips( "Seleccione un valor en el campo " + n + "." );
			return false;
		} else {
			return true;
		}
	};
	
	function updateTips( t ) {
		tips
			.text( t )
			.addClass( "ui-state-highlight" );
		setTimeout(function() {
			tips.removeClass( "ui-state-highlight", 1500 );
		}, 500 );
	}
	
	function checkLength( o, n, min, max ) {
		if ( o.val().length > max || o.val().length < min ) {
			o.addClass( "ui-state-error" );
			o.css("color","#FFFFFF");
			updateTips( "El largo del campo " + n + " debe estar entre " +
				min + " y " + max + "." );
			return false;
		} else {
			return true;
		}
	};
	
	function checkSelect( o, n, not ) {
		if ( o.val() == not ) {
			updateTips( "Seleccione un valor en el campo " + n + "." );
			return false;
		} else {
			return true;
		}
	};
	
  	var max = 100;  
     
    /*
    $("label").each(function(){  
        if ($(this).width() > max)  
            max = $(this).width();  
    }); 
    
    $("label").width(max);
    
    */
    
	$( "#dialog:ui-dialog" ).dialog( "destroy" );
	
	var TabsSonda = $( "#tabs_form_new_sonda" ).tabs({
		add: function( event, ui ) {
			
			var groupid_select = $( "#sonda_group" );
			
			var tab_content;
			
			$.ajax({
				type: "POST",
				url: "/config/getFormFeature",
				data: "groupid="+groupid_select.val(),
				dataType: "json",
				success: function(data){
					if(data.status) {
						$( ui.panel ).append(data.datos);
					} else {
						$( ui.panel ).append('Error en el servidor');
					}
				}, 
				error: function(){
					$( ui.panel ).append('Error de conexion');
  				}
			});			
			//var tab_content = $( "#new_sonda_tabs_op_"+tabsSelect.val() ).html();
		}
	});

	$("#sonda_group").change(function(){
		TabsSonda.tabs( "remove", 2 );
		var tabsSelect = $( "#sonda_group" );
		if(tabsSelect.val() == 1) {
			var tab_title_input ='Neutralidad';
			TabsSonda.tabs( "add", "#tabs-3", tab_title_input );
		} else if(tabsSelect.val() == 2) {
			var tab_title_input ='FDT';
			TabsSonda.tabs( "add", "#tabs-3", tab_title_input );
		}
		
		if(tabsSelect.val() > 0) {	
			$.ajax({
				type: "POST",
				url: "/config/getPlanOption",
				data: {
					groupid:tabsSelect.val()
				},
				dataType: "json",
				success: function(data){
					if(data.status) {
						$('#sonda_plan').html(data.datos);
					} else {
						alert("Error en el servidor");
					}
				},
				error: function () {
				  alert("Error de conexion");
				}
			});
		} else {
			$('#sonda_plan').html('<option selected value="0">Seleccione un grupo</option>');
			
		}
		
	});
	
	//Monitores:

	$("#monitor_type").change(function(){
		
			var typeMonitor = $("#monitor_type").val();
			
			if(typeMonitor == "1") {
				$("#snmp1").show();
				$("#snmp2").show();
				$("#snmp3").show();
			} else {
				$("#snmp1").hide();
				$("#snmp2").hide();
				$("#snmp3").hide();
			}
	});
	
	
	// para ahorrar un poco de espacio voy a definir a las listas como variables
	var $lista1 = $('#monitor_groupid_1'), $lista2 = $('#monitor_groupid_2');
	// lista 1
	$('li',$lista1).draggable({
		revert: 'invalid',		
		helper: 'clone',		
		cursor: 'move'
	});
	$lista1.droppable({
		accept: '#monitor_groupid_2 li',
		drop: function(ev, ui) {
			deleteLista2(ui.draggable);
		}
	});
	/* lista2 */
	$('li',$lista2).draggable({
		revert: 'invalid',
		helper: 'clone',	
		cursor: 'move'
	});
	$lista2.droppable({
		accept: '#monitor_groupid_1 > li',
		drop: function(ev, ui) {
			deleteLista1(ui.draggable);		
		}
	});
	
	// listas	
	function deleteLista1($item) {
		$item.fadeOut(function() {
			$($item).appendTo($lista2).fadeIn();;
		});
		$item.fadeIn();
	}
	function deleteLista2($item) {
		$item.fadeOut(function() {			
			$item.appendTo($lista1).fadeIn();
		});
	}
    
	var monitor_name = $( "#monitor_name" ),
		monitor_description = $( "#monitor_description" ),
		monitor_type = $("#monitor_type").val(),
		monitor_snmp_community = $( "#monitor_snmp_community" ),
		monitor_snmp_oid = $( "#monitor_snmp_oid" ),
		monitor_snmp_port = $( "#monitor_snmp_port" ),
		monitor_unit = $( "#monitor_unit" ),
		monitor_delay = $( "#monitor_delay" ),
		monitor_history = $( "#monitor_history" ),
		monitor_trend = $( "#monitor_trend" ),
		allFieldsMonitor = $( [] ).add( monitor_name ).add( monitor_description ).add( monitor_type ).add( monitor_snmp_community ).add( monitor_snmp_oid ).add( monitor_snmp_port ).add( monitor_unit ).add( monitor_delay ),
		tips = $( ".validateTips" );

	$( "#modal_monitor_form" ).dialog({
		autoOpen: false,
		resizable: false,
		height: 'auto',
		width: 650,
		modal: true,
		closeOnEscape: true,
		draggable: true,
		open: function() {
            $(this).find('.ui-dialog-titlebar-close').blur();
		},
		buttons: {
			"Crear monitor": function() {
				var bValid = true;
				
				allFieldsMonitor.removeClass( "ui-state-error" );
				allFieldsMonitor.css("color","#5A698B");
			
				bValid = bValid && checkLength( monitor_name, "Nombre", 3, 100 );
				bValid = bValid && checkSelect( monitor_description, "Monitor", 3,100);
				
				if($("#monitor_type").val() == "1") {
					bValid = bValid && checkLength( monitor_snmp_community, "Comunidad SNMP", 2, 40 );
					bValid = bValid && checkLength( monitor_snmp_oid, "SNMP OID", 2, 40 );
					bValid = bValid && checkLength( monitor_snmp_port, "Puerto SNMP", 2, 40 );	
				}
				
				bValid = bValid && checkLength( monitor_unit, "Unidad", 0, 17 );
				bValid = bValid && checkLength( monitor_delay, "Intervalo de actualización (en segundos)", 0, 17 );	

				if ( bValid ) {
		
					$.loading_in();

					cadena = $( '#form_new_monitor' ).serialize();
					order = $( '#monitor_groupid_2' ).sortable('serialize');
					
					var serialStr = "";
					var order = "";
					
					$("ul.monitor_groupid_2 li").each(function(i, elm){
						serialStr += (i > 0 ? "|" : "") +i+":"+$(elm).attr("id");	
					});
						
					$.ajax({
						type: "POST",
						url: "/config/createMonitor",
						data: {
							cadena_1 : cadena,
							cadena_2 : serialStr,
							idMonitor : 'new'
						},
						dataType: "json",
						success: function(data){
							if(data.status) {
								$( "#modal_monitor_form" ).dialog( "close" );
								$(".monitores").flexReload();
							} else {
								updateTips( "Error al crear sonda" );
							}
						},
						error: function() {
							updateTips( "Error de conexion con el servidor" );
						}
					});
					
					$.loading_out();
				}
			},
			Cancel: function() {
				$( this ).dialog( "close" );
			}
		},
		close: function() {
			tips.text('Todo los elementos del formulario son requeridos');
			allFieldsMonitor.val( "" ).removeClass( "ui-state-error" );
			allFieldsMonitor.css("color","#5A698B");
			$.loading_out();
		}
	});
	
	$( "#monitor-delete" ).dialog({
			autoOpen: false,
			resizable: false,
			height:250,
			width: 350,
			modal: true,
			buttons: {
				"Borrar los monitores seleccionados": function() {
					
					jQuery('#loading').show();
  					jQuery('#imgLoading').show();
  								
					var monitorSelect=new Array();
					count = 1;
					grid = $('.monitores');
					$('.trSelected', grid).each(function() {
						var id = $(this).attr('id');
						id = id.substring(id.lastIndexOf('row')+3);
						if($.isNumeric(id)) {
							monitorSelect[count]=id;
							count = count + 1;
						}
					});
					
					$.ajax({
						type: "POST",
						url: "/config/deleteMonitor",
						data: {
							idMonitor:monitorSelect
						},
						dataType: "json",
						success: function(data){
							if(data.status) {
								$(".monitores").flexReload();
							} else {
								alert("Acceso Denegado");
							}
						},
						error: function () {
						  alert("Error de conexion");
						}
					});				
					jQuery('#loading').hide();
					jQuery('#imgLoading').hide();
					$( "#monitor-delete" ).dialog( "close" );
				},
				Cancel: function() {
					$( "#monitor-delete" ).dialog( "close" );
				}
			}
	});
	
});

function statusMonitor(id,groupid) {
	
	var status =  $( '#statusMonitor_'+id ).html();
	
	if(status == 'Activo') {
		status_value = 0;
	} else {
		status_value = 1;
	}

	$.ajax({
		type: "POST",
		url: "/config/statusMonitor",
		data: {status:status_value, idmonitor:id, groupid:groupid },
		dataType: "json",
		success: function(data){
			if(data.status) {
				if(status == 'Activo') {
					$( '#statusMonitor_'+id ).html('Desactivado');
				} else {
					$( '#statusMonitor_'+id ).html('Activo');
				}
			} else {
				alert("Error interno");
			}
		},
		error: function() {
			alert( "Error de conexion con el servidor" );
		}
	});
}

function editMonitor(id,clonar) {
	var url = "/config/getMonitorForm";
	
	var dialog = $('<div style="display:none" id="editMonitorDialog" class="loading"></div>').appendTo('body');
	
	
	if(clonar) {
		var url_set = "/config/createMonitor";
		var id_set = "clone";
	} else {
		var url_set = "/config/editMonitor";
		var id_set = id;
	}

	$("#modal_monitor_edit").dialog({
		resizable: false,
		height: 'auto',
		width: 650,
		modal: true,
		cache: false,
		position:['middle',20],
		buttons: {
			"Guardar monitor": function() {
					
				$.loading_in();
	
				var bValid = true;
				
				var monitor_edit_name = $( "#form_edit_monitor #monitor_name" ),
				monitor_description = $( "#form_edit_monitor #monitor_description" ),
				monitor_type = $("#form_edit_monitor #monitor_type").val(),
				monitor_snmp_community = $( "#monitor_snmp_community" ),
				monitor_snmp_oid = $( "#monitor_snmp_oid" ),
				monitor_snmp_port = $( "#monitor_snmp_port" ),
				monitor_unit = $( "#monitor_unit" ),
				monitor_delay = $( "#monitor_delay" ),
				monitor_history = $( "#monitor_history" ),
				monitor_trend = $( "#monitor_trend" ),
				allFieldsMonitor_edit = $( [] ).add( monitor_edit_name ).add( monitor_description ).add( monitor_type ).add( monitor_snmp_community ).add( monitor_snmp_oid ).add( monitor_snmp_port ).add( monitor_unit ).add( monitor_delay ),
				tips_edit_monitor = $( ".validateTips_edit" );
				
				allFieldsMonitor_edit.removeClass( "ui-state-error" );
				allFieldsMonitor_edit.css("color","#5A698B");
			
				bValid = bValid && $.checkLength( monitor_edit_name, "Nombre", 3, 100 );
				bValid = bValid && $.checkLength( monitor_description, "Monitor", 3,100);
				
				if($("#monitor_edit_type").val() == "1") {
					bValid = bValid && $.checkLength( monitor_snmp_community, "Comunidad SNMP", 1, 20 );
					bValid = bValid && $.checkLength( monitor_snmp_oid, "SNMP OID", 2, 20 );
					bValid = bValid && $.checkLength( monitor_snmp_port, "Puerto SNMP", 2, 20 );	
				}
				
				bValid = bValid && $.checkLength( monitor_unit, "Unidad", 0, 17 );
				bValid = bValid && $.checkLength( monitor_delay, "Intervalo de actualización (en segundos)", 0, 17 );	

				if ( bValid ) {

					var serialStr = "";
					var order = "";
					
					$("ul.monitor_groupid_2 li").each(function(i, elm){
						serialStr += (i > 0 ? "|" : "") +i+":"+$(elm).attr("id");	
					});
						
					$.ajax({
						type: "POST",
						url: url_set,
						data:  "idSelect="+ id_set +"&"+ $( '#form_edit_monitor' ).serialize() +"&orden="+ serialStr,
						dataType: "json",
						success: function(data){
							if(data.status) {
								$( "#modal_monitor_edit" ).dialog( "close" );
								$(".monitores").flexReload();
							} else {
								$.updateTips( tips_edit_monitor, "Error al crear sonda" );
							}
						},
						error: function() {
							$.updateTips( tips_edit_monitor, "Error de conexion con el servidor" );
						}
					});
					
					$.loading_out();
				} else {
					$.loading_out();
				}	
			},
			Cancel: function() {
				$( this ).dialog( "close" );
				$.loading_out();
			}
		}
	});

	$("#modal_monitor_edit").load(
            url, 
            {monitorID:id}, // omit this param object to issue a GET request instead a POST request, otherwise you may provide post parameters within the object
            function (responseText, textStatus, XMLHttpRequest) {
                // remove the loading class
                $("#modal_sonda_edit").removeClass('loading');
                
            	var typeMonitor = $("#monitor_edit_type").val();
		
				if(typeMonitor == "1") {
					$("#snmp_edit_1").show();
					$("#snmp_edit_2").show();
					$("#snmp_edit_3").show();
				} else {
					$("#snmp_edit_1").hide();
					$("#snmp_edit_2").hide();
					$("#snmp_edit_3").hide();
				}
				
				
				if(clonar) {
					var name1 = $("#monitor_edit_name").val();
					$("#monitor_edit_name").val(name1+'_1');
	
					var name1 = $("#monitor_edit_description").val();
					$("#monitor_edit_description").val(name1+'_1');
				}	
			
				// para ahorrar un poco de espacio voy a definir a las listas como variables
				var $lista1 = $('#monitor_edit_groupid_1'), $lista2 = $('#monitor_edit_groupid_2');
				// lista 1
				$('li',$lista1).draggable({
					revert: 'invalid',		
					helper: 'clone',		
					cursor: 'move'
				});
				$lista1.droppable({
					accept: '#monitor_edit_groupid_2 li',
					drop: function(ev, ui) {
						deleteLista2(ui.draggable);
					}
				});
				/* lista2 */
				$('li',$lista2).draggable({
					revert: 'invalid',
					helper: 'clone',	
					cursor: 'move'
				});
				$lista2.droppable({
					accept: '#monitor_edit_groupid_1 > li',
					drop: function(ev, ui) {
						deleteLista1(ui.draggable);		
					}
				});
				
				// listas	
				function deleteLista1($item) {
					$item.fadeOut(function() {
						$($item).appendTo($lista2).fadeIn();;
					});
					$item.fadeIn();
				}
				function deleteLista2($item) {
					$item.fadeOut(function() {			
						$item.appendTo($lista1).fadeIn();
					});
				}	
			
            }
	);

	return false;
}