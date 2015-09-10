var table = $( '.table_template' );
	
var modal_new = $( '#modal_template_new' );
var modal_new_url = '/admin/createTemplate';
var modal_new_form = $( '#form_new_template' );

var modal_delete = $( '#modal_template_delete' );
var modal_delete_url = '/admin/deleteTemplate';

var modal_edit = '#modal_template_edit';
var modal_edit_url_form = '/admin/getFormTemplate';
var modal_edit_form = '#form_edit_template';

var template_name_new = $( "#template_name" ),
		allFields_new = $( [] ).add( template_name_new ),
		tips_new = $( ".form_new_template_validateTips" );



jQuery(function($){

modal_new.dialog({
	autoOpen: false,
	resizable: false,
	height: 'auto',
	width: 1076,
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
				
			bValid = bValid && $.checkLength( tips_new, template_name_new, "Nombre", 3, 100 );
	
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
            

			
			var template_name_edit = $( "form#form_edit_template #template_name" ),
				allFields_edit = $( [] ).add( template_name_edit ),
				tips_edit = $( ".form_edit_template_validateTips" );
				
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
				
				bValid = bValid && $.checkLength( $( ".form_edit_template_validateTips" ), $( "form#form_edit_template #template_name" ), "Nombre", 3, 100 );
				
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
								$.updateTips( $( ".form_edit_template_validateTips" ), "Error al crear registro" );
							}
						},
						error: function() {
							$.updateTips( $( ".form_edit_template_validateTips" ), "Error de conexion con el servidor" );
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
			$( ".form_edit_template_validateTips" ).text('Todo los elementos del formulario son requeridos');
		}
	});		
}
});

function editar (id,clone) {
  $.editarModal(id,clone);
}


