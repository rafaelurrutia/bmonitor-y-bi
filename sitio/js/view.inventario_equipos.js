jQuery(function($){

var table =  '.equipo';

var inventario_cparental = "#inventario_cparental",
allFields_edit = $( [] ).add( inventario_cparental ),
tips_edit = ".validateTips_wifi";
			

var modal_edit = '#modal_edit_wifi';
var modal_edit_url_form = '/inventario/getFormWifi';
var modal_new_url = '/inventario/setFormWifi';
var modal_edit_form = '#form_edit_wifi';


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
		resizable: true,
		height: 'auto',
		modal: true,
		closeOnEscape: true,
		draggable: true,
		open: function() {
	        $(this).find('.ui-dialog-titlebar-close').blur();
		},
		buttons: {
			"Save": function() {
				
				//allFields_edit.removeClass( "ui-state-error" );
				//allFields_edit.css("color","#5A698B");
				
				var bValid = true;
				
				//bValid = bValid && $.checkLength( tips_edit, template_name_edit, "Nombre", 3, 100 );
				
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
								$.updateTips2( tips_edit, "Error al crear registro" );
							}
						},
						error: function() {
							$.updateTips2( tips_edit, "Error de conexion con el servidor" );
						}
					});
					
					jQuery('#loading').hide();
					jQuery('#imgLoading').hide();
					
				}
			},
			"Cancel": function() {
				$( this ).dialog( "close" );
			}
		},
		close: function() {
			//tips_edit.text('Todo los elementos del formulario son requeridos');
			//allFields_edit.val( "" ).removeClass( "ui-state-error" );
			//allFields_edit.css("color","#5A698B");
		}
	});		
}
});

function editWifi (id,clone) {
  $.editarModal(id,clone);
}