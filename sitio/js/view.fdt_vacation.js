jQuery(function($){

var table_1 =  $('.table_vacation');
var table_2 =  $('.table_vacation_active');
			
var modal_new = $( '#modal_vacation_new' );
var modal_new_url = '/fdtVacaciones/newVacation';
var modal_new_form = $( '#form_new_vacation' );

var modal_delete = $( '#modal_vacation_delete' );
var modal_delete_url = '/fdtVacaciones/deleteVacation';

var modal_edit = '#modal_vacation_edit';
var modal_edit_url_form = '/fdtVacaciones/getVacationForm';
var modal_edit_form = '#form_edit_vacation';

var vacation_host_new = $( "#vacation_host" ),
	fecha_inicio_new = $( "#vacation_fecha_inicio" ),
	fecha_fin_new = $( "#vacation_fecha_fin" ),
	motivo_new = $( "#vacation_motivo" ),
	allFields_new = $( [] ).add( vacation_host_new ).add( fecha_inicio_new ).add( fecha_fin_new ).add( motivo_new ),
	tips_new = $( ".form_new_vacation_validateTips" );
		
var vacation_host_edit = "#vacation_host",
	fecha_inicio_edit = "#vacation_fecha_inicio",
	fecha_fin_edit = "#vacation_fecha_fin",
	motivo_edit = "#vacation_motivo",
	tips_edit = ".form_edit_vacation_validateTips";
	
//$( "select" ).combobox();
//$( "select" ).closest(".ui-widget").find("input, button" ).prop("disabled", true)

var dates = $( "#form_new_vacation_vacation_fecha_inicio, #form_new_vacation_vacation_fecha_fin" ).datepicker({
	defaultDate: "+1w",
	changeMonth: true,
	numberOfMonths: 1,
	minDate: 0,
	dateFormat: "yy-mm-dd",
	onSelect: function( selectedDate ) {
		var option = this.id == "form_new_vacation_vacation_fecha_inicio" ? "minDate" : "maxDate",
			instance = $( this ).data( "datepicker" ),
			date = $.datepicker.parseDate(
				instance.settings.dateFormat ||
				$.datepicker._defaults.dateFormat,
				selectedDate, instance.settings );
		dates.not( this ).datepicker( "option", option, date );
	}
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
				
			//bValid = bValid && $.checkLength( tips_new, template_name_new, "Nombre", 3, 100 );
	
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
							table_1.flexReload();
							table_2.flexReload();
						} else {
							$.updateTips( tips_new, data.error );
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
		$('.trSelected', table_1).each(function() {
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
					table_1.flexReload();
					table_2.flexReload();
					modal_delete.dialog( "close" );
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

	$.ajax({
			type: "POST",
			url: modal_edit_url_form,
			data:  {IDSelect:id},
			dataType: "json",
			success: function(data){
				if(data.status) {
					$( modal_edit ).html(data.html);
					$( modal_edit ).removeClass('loading');
					 
					if(clonar) {
						var name1 = vacation_host_edit.val();
						vacation_host_edit.val(name1+'_1');
					}
			
					var diferencia = data.diferencia;
	
					$( "#form_edit_vacation_vacation_fecha_fin" ).datepicker({defaultDate: "+1w", changeMonth: true, numberOfMonths: 1, minDate: diferencia, dateFormat: "yy-mm-dd"});
					
					$( modal_edit ).dialog( "open" );
				}
			},
			error: function () {
			  alert("Error de conexion");
			}
	});				
	/*
	$( modal_edit ).load(
        modal_edit_url_form, 
        {IDSelect:id}, // omit this param object to issue a GET request instead a POST request, otherwise you may provide post parameters within the object
        function (responseText, textStatus, XMLHttpRequest) {
            // remove the loading class
            $( modal_edit ).removeClass('loading');
            
			if(clonar) {
				var name1 = vacation_host_edit.val();
				vacation_host_edit.val(name1+'_1');
			}
			
			$( modal_edit ).dialog( "open" );
        }
	);*/
		
	$( modal_edit ).dialog({
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
			"Guardar": function() {
				
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
								$( table_1 ).flexReload();
								$( table_2 ).flexReload();
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
			Cancel: function() {
				$( this ).dialog( "close" );
			}
		},
		close: function() {
			$( ".form_edit_vacation_validateTips" ).text('Todo los elementos del formulario son requeridos');
			//allFields_edit.val( "" ).removeClass( "ui-state-error" );
			//allFields_edit.css("color","#5A698B");
		}
	});		
}
});

function edit (id,clone) {
  $.editarModal(id,clone);
}