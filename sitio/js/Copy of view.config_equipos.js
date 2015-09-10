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
	
	$("#sonda_mac_wan").mask("**:**:**:**:**:**");
	$("#sonda_mac_lan").mask("**:**:**:**:**:**");
	$('#sonda_ip_lan').ipAddress();
	$('#sonda_netmask_lan').ipAddress();
	$('#sonda_ip_wan').ipAddress();
	 
	$( "#modal_sonda_form" ).dialog({
		autoOpen: false,
		resizable: true,
		height: 'auto',
		width: 500,
		modal: true,
		closeOnEscape: true,
		draggable: true,
		open: function() {
            $(this).find('.ui-dialog-titlebar-close').blur();
		},
		buttons: {
			"Crear sonda": function() {
				var bValid = true;
				
				allFields.removeClass( "ui-state-error" );
				allFields.css("color","#5A698B");
			
				bValid = bValid && $.checkLength( tips, sonda_name, "Nombre", 3, 40 );
				bValid = bValid && $.checkSelect( tips, sonda_group, "Grupo", 0);
				bValid = bValid && $.checkSelect( tips, sonda_plan, "Plan", 0);
				bValid = bValid && $.checkLength( tips, sonda_dns, "Nombre DNS", 15, 15 );
				bValid = bValid && $.checkLength( tips, sonda_ip_wan, "Dirección IP WAN", 6, 15 );
				bValid = bValid && $.checkLength( tips, sonda_mac_wan, "Mac WAN", 17, 17 );	
				
				if ( bValid ) {
					
					jQuery('#loading').show();
  					jQuery('#imgLoading').show();

					cadena = $( '#form_new_sonda' ).serialize();
					
					$.ajax({
						type: "POST",
						url: "/config/createSonda",
						data: cadena,
						dataType: "json",
						success: function(data){
							if(data.status) {
								$( "#modal_sonda_form" ).dialog( "close" );
								$("#fmGrupo").val(sonda_group.val());
								$(".sondas").flexReload();
							} else {
								$.updateTips( tips, data.msg );
							}
						},
						error: function() {
							$.updateTips( tips, "Error de conexion con el servidor" );
						}
					});
					
					jQuery('#loading').hide();
					jQuery('#imgLoading').hide();
					
				}
			},
			Cancel: function() {
				$( this ).dialog( "close" );
			}
		},
		close: function() {
			tips.text('Todo los elementos del formulario son requeridos');
			allFields.val( "" ).removeClass( "ui-state-error" );
			allFields.css("color","#5A698B");
			sonda_group.val(0);
			sonda_plan.val(0);
			TabsSonda.tabs( "remove", 2 );
		}
	});
              
	$( "#sondas-delete" ).dialog({
			autoOpen: false,
			resizable: false,
			height:150,
			width: 350,
			modal: true,
			buttons: {
				"Borrar las sondas seleccionadas": function() {
					
					jQuery('#loading').show();
  					jQuery('#imgLoading').show();
  								
					var sondasSelect=new Array();
					count = 1;
					grid = $('.sondas');
					$('.trSelected', grid).each(function() {
						var id = $(this).attr('id');
						id = id.substring(id.lastIndexOf('row')+3);
						if($.isNumeric(id)) {
							sondasSelect[count]=id;
							count = count + 1;
						}
					});
					
					$.ajax({
						type: "POST",
						url: "/config/deleteSonda",
						data: {
							idSonda:sondasSelect
						},
						dataType: "json",
						success: function(data){
							if(data.status) {
								$(".sondas").flexReload();
							} else {
								alert("Acceso Denegado");
							}
						},
						error: function () {
						  alert("Error de conexion");
						  $( "#sondas-delete" ).dialog( "close" );
						}
					});				
					jQuery('#loading').hide();
					jQuery('#imgLoading').hide();
					$( "#sondas-delete" ).dialog( "close" );
				},
				Cancel: function() {
					$( "#sondas-delete" ).dialog( "close" );
				}
			}
	});

	$( "#sonda-config" ).dialog({
			autoOpen: false,
			resizable: false,
			height:150,
			width: 400,
			modal: true,
			buttons: {
				"Configurar las sondas seleccionadas": function() {
					
					jQuery('#loading').show();
  					jQuery('#imgLoading').show();
  								
					var sondasSelect=new Array();
					count = 1;
					grid = $('.sondas');
					$('.trSelected', grid).each(function() {
						var id = $(this).attr('id');
						id = id.substring(id.lastIndexOf('row')+3);
						if($.isNumeric(id)) {
							sondasSelect[count]=id;
							count = count + 1;
						}
					});
					
					$.ajax({
						type: "POST",
						url: "/config/setConfigSonda",
						data: {
							idSonda:sondasSelect
						},
						dataType: "json",
						success: function(data){
							if(data.status) {
								$(".sondas").flexReload();
							} else {
								alert("Acceso Denegado");
							}
						},
						error: function () {
						  alert("Error de conexion");
						  $( "#sondas-delete" ).dialog( "close" );
						}
					});				
					jQuery('#loading').hide();
					jQuery('#imgLoading').hide();
					$( "#sonda-config" ).dialog( "close" );
				},
				Cancel: function() {
					$( "#sonda-config" ).dialog( "close" );
				}
			}
	});
	
		
	$("#sonda_group").change(function(){
		TabsSonda.tabs( "remove", 2 );
		var tabsSelect = $( "#sonda_group" );
		var tabsSelectText = $( "#sonda_group option:selected" ).text();


		var tab_title_input =tabsSelectText;

		TabsSonda.tabs( "add", "#tabs-3", tab_title_input );
		
		
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
});

function statusSonda(id) {

	var status =  $( '#statusSonda_span_'+id ).html();
	
	if(status == 'Activo') {
		status_value = 0;
	} else {
		status_value = 1;
	}
		
	$.ajax({
		type: "POST",
		url: "/config/statusSonda",
		data: {status:status_value, id:id },
		dataType: "json",
		success: function(data){
			if(data.status) {
				if(status == 'Activo') {
					$( '#statusSonda_span_'+id ).html('Inactivo');
					$( '#statusSonda_span_'+id ).removeClass("status_on").addClass("status_off");
				} else {
					$( '#statusSonda_span_'+id ).html('Activo');
					$( '#statusSonda_span_'+id ).removeClass("status_off").addClass("status_on");
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

function editSonda (id,clonar) {
	var url = "/config/getSondaForm";
	
	var dialog = $('<div style="display:none" id="editSondaDialog" class="loading"></div>').appendTo('body');
	
	if(clonar) {
		var id_set = "clone";
		var typeEDIT = 'clone';
		var url_send = "/config/createSonda";
	} else {
		var typeEDIT = 'edit';
		var id_set = id;
		var url_send = "/config/editSonda";
	}

	$("#modal_sonda_edit").dialog({
		resizable: false,
		height: 'auto',
		width: 650,
		modal: true,
		cache: false,
		position:['middle',20],
		buttons: {
			"Guardar sonda": function() {
					
				var bValid = true;
				
				jQuery('#loading').show();
  				jQuery('#imgLoading').show();
  					
  				bValid = bValid && $.checkLength( $( '#tips_edit_sonda' ), $( 'form#form_edit_sonda #sonda_name' ), "Nombre", 3, 40 );
				bValid = bValid && $.checkSelect( $( '#tips_edit_sonda' ), $( 'form#form_edit_sonda #sonda_group' ), "Grupo", 0);
				bValid = bValid && $.checkSelect( $( '#tips_edit_sonda' ), $( 'form#form_edit_sonda #sonda_plan' ), "Plan", 0);
				bValid = bValid && $.checkLength( $( '#tips_edit_sonda' ), $( 'form#form_edit_sonda #sonda_dns' ), "Nombre DNS", 15, 15 );
				bValid = bValid && $.checkLength( $( '#tips_edit_sonda' ), $( 'form#form_edit_sonda #sonda_ip_wan' ), "Dirección IP WAN", 6, 15 );
				bValid = bValid && $.checkLength( $( '#tips_edit_sonda' ), $( 'form#form_edit_sonda #sonda_mac_wan' ), "Mac WAN", 17, 17 );	
				
				if ( bValid ) {
					$.ajax({
						type: "POST",
						url: url_send,
						dataType: "json",
						data:  "id=" + id_set +"&"+ $( '#form_edit_sonda' ).serialize(),
						success: function(data){
							if(data.status) {
								$( "#modal_sonda_edit" ).dialog( "close" );
								$(".sondas").flexReload();
							} else {
								$.updateTips( $( '#tips_edit_sonda' ), data.msg );
							}
						},
						error: function() {
							$.updateTips( $( '#tips_edit_sonda' ), "Error de conexion con el servidor" );
						}
					});
					
				}
				jQuery('#loading').hide();
				jQuery('#imgLoading').hide();	
			},
			Cancel: function() {
				$( this ).dialog( "close" );
			}
		}
	});

	$("#modal_sonda_edit").load(
            url, 
            {sondaID:id, type:typeEDIT}, // omit this param object to issue a GET request instead a POST request, otherwise you may provide post parameters within the object
            function (responseText, textStatus, XMLHttpRequest) {
                // remove the loading class
				if(clonar) {
					var name1 = $( 'form#form_edit_sonda #sonda_name' ).val();
					$( 'form#form_edit_sonda #sonda_name' ).val(name1+'_1');
				}
                $("#modal_sonda_edit").removeClass('loading');
            }
	);

	return false;
}


var sendIDButton = '';

$( "#modal_sonda_trigger" ).dialog({
		autoOpen: false,
		resizable: false,
		height: 200,
		width: 450,
		modal: true,
		buttons: {
			"Iniciar Trigger": function() {
				
				var bValid = true;
				
				jQuery('#loading').show();
				jQuery('#imgLoading').show();
				
				bValid = bValid && $.checkLength( $( '#config_equipos_trigger_validateTips' ), $( '#trigger_responsable'),  "Responsable", 3, 40 );
				bValid = bValid && $.checkSelect( $( '#config_equipos_trigger_validateTips' ), $( '#trigger_id' ), "Trigger", 0);				
				
				if ( bValid ) {
					$.ajax({
						type: "POST",
						url: "/config/setTriggerSonda",
						dataType: "json",
						data:  "idSonda=" + sendIDButton +"&"+ $( '#config_equipos_trigger' ).serialize(),
						success: function(data){
							if(data.status) {
								$( "#modal_sonda_trigger" ).dialog( "close" );
							} else {
								alert("Acceso Denegado");
							}
						},
						error: function () {
						  alert("Error de conexion");
						}
					});	 
				}			
				jQuery('#loading').hide();
				jQuery('#imgLoading').hide();
		
			},
			Cancel: function() {
				$( "#modal_sonda_trigger" ).dialog( "close" );
			}
		}

		});
	
	function triggerSonda (id) {
		sendIDButton = id;
		$( "#modal_sonda_trigger" ).dialog( "open" );
	}


function configSonda(id) {
  
  	var sondasSelect=new Array();
  	sondasSelect[0]=id;
  	
	$.ajax({
		type: "POST",
		url: "/config/setConfigSonda",
		data: {
			idSonda:sondasSelect
		},
		dataType: "json",
		success: function(data){
			if(data.status) {
				alert("Configuracion informada");
			} else {
				alert("Acceso Denegado");
			}
		},
		error: function () {
		  alert("Error de conexion");
		  $( "#sondas-delete" ).dialog( "close" );
		}
	});
}