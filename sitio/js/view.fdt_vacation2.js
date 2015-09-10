jQuery(function($){

var tips = $( ".validateTips" );

var host = $( "#vacation_host" ),
	fecha_inicio = $( "#vacation_fecha_inicio" ),
	fecha_fin = $( "#vacation_fecha_fin" ),
	motivo = $( "#vacation_motivo" );
		
$( "#modal_vacation_form" ).dialog({
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
		
				$.ajax({
					type: "POST",
					url: "/fdtVacaciones/newVacation",
					data:  "id=new" +"&"+ $( '#fdt_vacation_form' ).serialize(),
					dataType: "json",
					success: function(data){
						if(data.status) {
							$( "#modal_vacation_form" ).dialog( "close" );
							$(".table_vacation").flexReload();
							$(".table_vacation_active").flexReload();
						} else {
							$.updateTips( tips, data.error );
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


$( "select" ).combobox();

var dates = $( "#vacation_fecha_inicio, #vacation_fecha_fin" ).datepicker({
	defaultDate: "+1w",
	changeMonth: true,
	numberOfMonths: 1,
	minDate: 0,
	dateFormat: "yy-mm-dd",
	onSelect: function( selectedDate ) {
		var option = this.id == "vacation_fecha_inicio" ? "minDate" : "maxDate",
			instance = $( this ).data( "datepicker" ),
			date = $.datepicker.parseDate(
				instance.settings.dateFormat ||
				$.datepicker._defaults.dateFormat,
				selectedDate, instance.settings );
		dates.not( this ).datepicker( "option", option, date );
	}
});		

$( "#modal_vacation_delete" ).dialog({
	autoOpen: false,
	resizable: false,
	height:150,
	width: 400,
	modal: true,
	buttons: { "Borrar": function() {
				
		jQuery('#loading').show();
		jQuery('#imgLoading').show();
							
		var select=new Array();
		count = 1;
		grid = $('.table_vacation');
		
		var items = $('.trSelected',grid);
        var itemlist ='';
        for(i=0;i<items.length;i++){
        	itemlist=items[i].id.substring(items[i].id.lastIndexOf('row')+3);
        	if($.isNumeric(itemlist)) {
				select[count]=itemlist;
				count = count + 1;
			}
        }
        	
		$.ajax({
			type: "POST",
			url: "/fdtVacaciones/deleteVacation",
			data: {
				idSelect:select
			},
			dataType: "json",
			success: function(data){
				if(data.status) {
					$(".table_vacation").flexReload();
					$(".table_vacation_active").flexReload();
				} else {
					alert(data.msg);
				}
			},
			error: function () {
			  alert("Error de conexion");
			}
		});				
		jQuery('#loading').hide();
		jQuery('#imgLoading').hide();
		$( "#modal_vacation_delete" ).dialog( "close" );
	},
	Cancel: function() {
		$( "#modal_vacation_delete" ).dialog( "close" );
	}
	}
});
});


function editScreen(id,clonar) {
	var url = "/fdtVacaciones/getVacationForm";
	
	if(clonar) {
		var id_set = "clone";
	} else {
		var id_set = id;
	}

	$("#modal_vcation_edit").load(
            url, 
            {IDSelect:id}, // omit this param object to issue a GET request instead a POST request, otherwise you may provide post parameters within the object
            function (responseText, textStatus, XMLHttpRequest) {
                // remove the loading class
                $("#modal_vcation_edit").removeClass('loading');
                
				if(clonar) {
					var name1 = $("#screen_name").val();
					$("#screen_name").val(name1+'_1');
				}
				
				$( "#modal_vcation_edit" ).dialog( "open" );
            }
	);
	
	$( "#modal_vcation_edit" ).dialog({
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
						
					$.ajax({
						type: "POST",
						url: '/fdtVacaciones/newVacation',
						data:  "id=new" +"&"+ $( '#modal_vcation_edit #fdt_vacation_form' ).serialize(),
						dataType: "json",
						success: function(data){
							if(data.status) {
								$( "#modal_vcation_edit" ).dialog( "close" );
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