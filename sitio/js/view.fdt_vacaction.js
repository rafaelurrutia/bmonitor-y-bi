jQuery(function($){

var tips = $( ".validateTips" );

$( "#modal_vacaction_form" ).dialog({
	autoOpen: false,
	resizable: false,
	height: 'auto',
	width: 700,
	modal: true,
	closeOnEscape: true,
	draggable: true,
	open: function() {
        $(this).find('.ui-dialog-titlebar-close').blur();
	},
	buttons: {
		"Crear vacaciones": function() {
			var bValid = true; 
	
			if ( bValid ) {
				
				jQuery('#loading').show();
				jQuery('#imgLoading').show();

				cadena = $( '#fdt_vacaction_form' ).serialize();
		
				$.ajax({
					type: "POST",
					url: "/configScreen/newScreen",
					data: {
						cadena_1 : cadena,
						idValue : "new"
					},
					dataType: "json",
					success: function(data){
						if(data.status) {
							$( "#modal_screen_form" ).dialog( "close" );
							$(".table_screen").flexReload();
						} else {
							$.updateTips( tips, "Error al crear pantalla" );
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
		//tips.text('Todo los elementos del formulario son requeridos');
		//allFieldsPlan.val( "" ).removeClass( "ui-state-error" );
		//allFieldsPlan.css("color","#5A698B");
	}
});

$( "#modal_vacaction_delete" ).dialog({
	autoOpen: false,
	resizable: false,
	height:150,
	width: 400,
	modal: true,
	buttons: { "Borrar las pantallas seleccionadas": function() {
				
		jQuery('#loading').show();
		jQuery('#imgLoading').show();
							
		var select=new Array();
		count = 1;
		grid = $('.table_screen');
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
			url: "/configScreen/deleteScreen",
			data: {
				idSelect:select
			},
			dataType: "json",
			success: function(data){
				if(data.status) {
					$(".table_screen").flexReload();
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
		$( "#modal_screen_delete" ).dialog( "close" );
	},
	Cancel: function() {
		$( "#modal_screen_delete" ).dialog( "close" );
	}
	}
});
});


function editScreen(id,clonar) {
	var url = "/configScreen/getScreenForm";
	
	if(clonar) {
		var id_set = "clone";
		var url_set = '/configScreen/newScreen';
	} else {
		var url_set = '/configScreen/editScreen';
		var id_set = id;
	}

	$("#modal_screen_edit").load(
            url, 
            {IDSelect:id}, // omit this param object to issue a GET request instead a POST request, otherwise you may provide post parameters within the object
            function (responseText, textStatus, XMLHttpRequest) {
                // remove the loading class
                $("#modal_screen_edit").removeClass('loading');
                
				if(clonar) {
					var name1 = $("#screen_name").val();
					$("#screen_name").val(name1+'_1');
				}
				
				$( "#modal_screen_edit" ).dialog( "open" );
            }
	);
	
	$( "#modal_screen_edit" ).dialog({
		autoOpen: false,
		resizable: false,
		height: 'auto',
		width: 700,
		modal: true,
		closeOnEscape: true,
		draggable: true,
		open: function() {
	        $(this).find('.ui-dialog-titlebar-close').blur();
		},
		buttons: {
			"Editar pantalla": function() {
				var bValid = true;

		        var screen_name = $( "#screen_name" ),
		        	screen_hsize = $( "#screen_hsize" ),
		        	screen_vsize = $( "#screen_vsize" ),
					allFields = $( [] ).add( screen_name ).add( screen_hsize ).add( screen_vsize ),
					tipsEdit = $( ".validateTips" );
				
				allFields.removeClass( "ui-state-error" );
				allFields.css("color","#5A698B");
	
				bValid = bValid && $.checkLength( tipsEdit, group_edit_name, "Nombre", 3, 100 );
				
				if ( bValid ) {
					
					jQuery('#loading').show();
					jQuery('#imgLoading').show();
	
					cadena = $( '#modal_screen_edit #config_screen_form' ).serialize();
						
					$.ajax({
						type: "POST",
						url: url_set,
						data: {
							cadena_1 : cadena,
							idValue : id_set
						},
						dataType: "json",
						success: function(data){
							if(data.status) {
								$( "#modal_screen_edit" ).dialog( "close" );
								$(".table_screen").flexReload();
							} else {
								$.updateTips( tipsEdit, "Error al crear pantalla" );
							}
						},
						error: function() {
							$.updateTips( tipsEdit, "Error de conexion con el servidor" );
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
			
 			var screen_name = $( "#screen_name" ),
		        screen_hsize = $( "#screen_hsize" ),
		        screen_vsize = $( "#screen_vsize" ),
				allFields = $( [] ).add( screen_name ).add( screen_hsize ).add( screen_vsize ),
				tipsEdit = $( ".validateTips" );
			
				tipsEdit.text('Todo los elementos del formulario son requeridos');
				allFields.removeClass( "ui-state-error" );
				allFields.css("color","#5A698B");
		}
	});
	return false;
}