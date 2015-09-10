var table = $( '.table_groups' );
	
var modal_new = $( '#modal_groups_new' );
var modal_new_url = '/config/createGroup';
var modal_new_form = $( '#form_new_group' );

var modal_delete = $( '#modal_groups_delete' );
var modal_delete_url = '/admin/deleteGroups';

var modal_edit = '#modal_groups_edit';
var modal_edit_url_form = '/config/getGroupForm';
var modal_edit_form = '#form_edit_group';

var template_name_new = $( "#template_name" ),
		allFields_new = $( [] ).add( template_name_new ),
		tips_new = $( ".form_new_group_validateTips" );

var template_name_edit = $( "#template_name" ),
		allFields_edit = $( [] ).add( template_name_edit ),
		tips_edit = $( ".form_edit_group_validateTips" );

jQuery(function($){
	
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

modal_new.dialog({
	autoOpen: false,
	resizable: false,
	height: 'auto',
	width: 1000,
	modal: true,
	closeOnEscape: true,
	draggable: true,
	open: function() {
        modal_new.find('.ui-dialog-titlebar-close').blur();
	},
	buttons: {
		"Guardar": function() {		
			
			var bValid = true;
	
			allFields_new.removeClass( "ui-state-error" );
			allFields_new.css("color","#5A698B");
				
		
			if ( bValid ) {
				
				jQuery('#loading').show();
				jQuery('#imgLoading').show();
		
				$.ajax({
					type: "POST",
					url: modal_new_url,
					data:  "id=new" +"&"+ modal_new_form.serialize(),
					dataType: "json",
					success: function(data){
						if(data.status) {
							modal_new.dialog( "close" );
							table.flexReload();
						} else {
							$.updateTips( tips_new, "Error al crear registro" );
						}
					},
					error: function() {
						$.updateTips( tips_new, "Error de conexion con el servidor" );
					}
				});
				
				jQuery('#loading').hide();
				jQuery('#imgLoading').hide();
				
			}
		},
		Cancel: function() {
			modal_new.dialog( "close" );
		}
	},
	close: function() {
		tips_new.text('Todo los elementos del formulario son requeridos');
		allFields_new.val( "" ).removeClass( "ui-state-error" );
		allFields_new.css("color","#5A698B");
	}
});

modal_delete.dialog({
	autoOpen: false,
	resizable: false,
	height:150,
	width: 400,
	modal: true,
	buttons: { "Borrar los registros seleccionados": function() {
				
		jQuery('#loading').show();
		jQuery('#imgLoading').show();
							
		var select=new Array();
		count = 1;
		$('.trSelected', table).each(function() {
			var id = $(this).attr('id');
			id = id.substring(id.lastIndexOf('row')+3);
			if($.isNumeric(id)) {
				select[count]=id;
				count = count + 1;
			}
		});
				
		$.ajax({
			type: "POST",
			url: modal_delete_url,
			data: {
				idSelect:select
			},
			dataType: "json",
			success: function(data){
				if(data.status) {
					table.flexReload();
					modal_delete.dialog( "close" );
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

		
	},
	Cancel: function() {
		modal_delete.dialog( "close" );
	}
	}
});

$.editarModal = function( id, clonar){
		
	if(clonar) {
		var id_set = "clone";
	} else {
		var id_set = id;
	}

	$( modal_edit ).load(
        modal_edit_url_form, 
        {IDSelect:id}, // omit this param object to issue a GET request instead a POST request, otherwise you may provide post parameters within the object
        function (responseText, textStatus, XMLHttpRequest) {
            // remove the loading class
            $( modal_edit ).removeClass('loading');
            
			if(clonar) {
				var name1 = template_name_edit.val();
				template_name_edit.val(name1+'_1');
			}
			
			$( modal_edit ).dialog( "open" );
        }
	);
		
	$( modal_edit ).dialog({
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
				
				if ( bValid ) {
					
					jQuery('#loading').show();
					jQuery('#imgLoading').show();
			
					$.ajax({
						type: "POST",
						url: modal_new_url,
						data:  "id=" + id_set +"&"+ $( modal_edit_form ).serialize(),
						dataType: "json",
						success: function(data){
							if(data.status) {
								$( modal_edit ).dialog( "close" );
								$( table ).flexReload();
							} else {
								$.updateTips( tips_edit, "Error al crear registro" );
							}
						},
						error: function() {
							$.updateTips( tips_edit, "Error de conexion con el servidor" );
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
			tips_edit.text('Todo los elementos del formulario son requeridos');
			allFields_edit.val( "" ).removeClass( "ui-state-error" );
			allFields_edit.css("color","#5A698B");
		}
	});		
}
});

function editar (id,clone) {
  $.editarModal(id,clone);
}