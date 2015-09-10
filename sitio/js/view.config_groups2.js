jQuery(function($){

var group_name = $( "#group_name" ),
	allFields = $( [] ).add( group_name ),
	tipsGroups = $( ".form_new_group_validateTips" );

$( "#modal_groups_form" ).dialog({
	autoOpen: false,
	resizable: false,
	height: 'auto',
	width: 1124,
	modal: true,
	closeOnEscape: true,
	draggable: true,
	open: function() {
        $(this).find('.ui-dialog-titlebar-close').blur();
	},
	buttons: {
		"Crear grupo": function() {
			var bValid = true;
				
			allFields.removeClass( "ui-state-error" );
			allFields.css("color","#5A698B");
	
			bValid = bValid && $.checkLength( tipsGroups, group_name, "Nombre", 3, 100 );

			if ( bValid ) {
				
				jQuery('#loading').show();
				jQuery('#imgLoading').show();
		
				$.ajax({
					type: "POST",
					url: "/config/createGroup",
					data:  "id=new" +"&"+ $( '#form_new_group' ).serialize(),
					dataType: "json",
					success: function(data){
						if(data.status) {
							$( "#modal_groups_form" ).dialog( "close" );
							$(".table_groups").flexReload();
						} else {
							$.updateTips( tips, "Error al crear grupo" );
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
		tipsGroups.text('Todo los elementos del formulario son requeridos');
		allFields.val( "" ).removeClass( "ui-state-error" );
		allFields.css("color","#5A698B");
	}
});

//Plantillas

var TabsTemplate = $( "#tabs_form_new_group" ).tabs({
	add: function( event, ui ) {
		
		var id_template_select = $( "#group_id_template" );
		
		var tab_content;
		
		$.ajax({
			type: "POST",
			url: "/config/getFormTemplate",
			data: "id_template="+id_template_select.val(),
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
	}
});
	
$("#group_id_template").change(function(){
	TabsTemplate.tabs( "remove", 1 );
	
	var NameTab = $('#group_id_template option:selected').html();
	
	TabsTemplate.tabs( "add", "#new_group_tabs_2", NameTab );
	
});


// Ventana de Borrado

$( "#modal_groups_delete" ).dialog({
	autoOpen: false,
	resizable: false,
	height:150,
	width: 400,
	modal: true,
	buttons: { "Borrar los grupos seleccionados": function() {
				
		jQuery('#loading').show();
		jQuery('#imgLoading').show();
							
		var select=new Array();
		count = 1;
		grid = $('.table_groups');
		$('.trSelected', grid).each(function() {
			var id = $(this).attr('id');
			id = id.substring(id.lastIndexOf('row')+3);
			if($.isNumeric(id)) {
				select[count]=id;
				count = count + 1;
			}
		});
				
		$.ajax({
			type: "POST",
			url: "/config/deleteGroups",
			data: {
				idSelect:select
			},
			dataType: "json",
			success: function(data){
				if(data.status) {
					$(".table_groups").flexReload();
				} else {
					alert(data.error);
				}
			},
			error: function () {
			  alert("Error de conexion");
			}
		});				
		jQuery('#loading').hide();
		jQuery('#imgLoading').hide();
		$( "#modal_groups_delete" ).dialog( "close" );
	},
	Cancel: function() {
		$( "#modal_groups_delete" ).dialog( "close" );
	}
	}
});
});


function editGroup(id,clonar) {
	var url = "/config/getGroupForm";
	
	var dialog = $('<div style="display:none" id="editGroupDialog" class="loading"></div>').appendTo('body');
	
	if(clonar) {
		var id_set = "clone";
	} else {
		var id_set = id;
	}

	var group_name_edit = $( "#form_edit_group #group_name" ),
				allFields_edit = $( [] ).add( group_name_edit ),
				tipsGroups_edit = $( ".form_edit_group_validateTips" );
					
	$( "#modal_groups_edit" ).dialog({
		autoOpen: false,
		resizable: false,
		height: 'auto',
		width: 1000,
		modal: true,
		closeOnEscape: true,
		draggable: true,
		open: function() {
	        $(this).find('.ui-dialog-titlebar-close').blur();
		},
		buttons: {
			"Guardar": function() {
				var bValid = true;
					
				allFields_edit.removeClass( "ui-state-error" );
				allFields_edit.css("color","#5A698B");
		
				bValid = bValid && $.checkLength( tipsGroups_edit, group_name_edit, "Nombre", 3, 100 );
	
				if ( bValid ) {
					
					jQuery('#loading').show();
					jQuery('#imgLoading').show();
			
					$.ajax({
						type: "POST",
						url: "/config/createGroup",
						data:  "id=" + id_set + "&" + $( '#form_edit_group' ).serialize(),
						dataType: "json",
						success: function(data){
							if(data.status) {
								$( "#modal_groups_edit" ).dialog( "close" );
								$(".table_groups").flexReload();
							} else {
								$.updateTips( tipsGroups_edit, "Error al crear grupo" );
							}
						},
						error: function() {
							$.updateTips( tipsGroups_edit, "Error de conexion con el servidor" );
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
			tipsGroups_edit.text('Todo los elementos del formulario son requeridos');
			allFields_edit.val( "" ).removeClass( "ui-state-error" );
			allFields_edit.css("color","#5A698B");
		}
	});

	$("#modal_groups_edit").load(
            url, 
            {IDSelect:id}, // omit this param object to issue a GET request instead a POST request, otherwise you may provide post parameters within the object
            function (responseText, textStatus, XMLHttpRequest) {
                // remove the loading class
                $("#modal_groups_edit").removeClass('loading');
                
				if(clonar) {
					var name1 = $("#form_edit_group #group_name").val();
					$("#form_edit_group #group_name").val(name1+'_1');
				}
				
				$( "#modal_groups_edit" ).dialog( "open" );
            }
	);

	return false;
}