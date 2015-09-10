jQuery(function($){

var tips = $( ".validateTipsPlan" );

$( "#modal_groups_form" ).dialog({
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
		"Crear grupo": function() {
			var bValid = true; 
	
			if ( bValid ) {
				
				jQuery('#loading').show();
				jQuery('#imgLoading').show();

				cadena = $( '#form_new_group' ).serialize();
		
				$.ajax({
					type: "POST",
					url: "/config/createGroup",
					data: {
						cadena_1 : cadena,
						idValue : "new"
					},
					dataType: "json",
					success: function(data){
						if(data.status) {
							$( "#modal_groups_form" ).dialog( "close" );
							$(".table_groups").flexReload();
						} else {
							$.updateTips( tips, "Plan creation error" );
						}
					},
					error: function() {
						$.updateTips( tips, "Server conection error" );
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

$( "#modal_groups_delete" ).dialog({
	autoOpen: false,
	resizable: false,
	height:150,
	width: 400,
	modal: true,
	buttons: { "Delete all selected groups": function() {
				
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
			  alert("Conection errorn");
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

	$( "#modal_groups_edit" ).dialog({
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
			"Editar Grupo": function() {
				var bValid = true;

		        var group_edit_name = $( "#group_edit_name" ),
					allFields = $( [] ).add( group_edit_name ),
					tipsEdit = $( ".validateTipsPlanEdit" );
				
				allFields.removeClass( "ui-state-error" );
				allFields.css("color","#5A698B");
	
				bValid = bValid && $.checkLength( tipsEdit, group_edit_name, "Name", 3, 100 );
				
				if ( bValid ) {
					
					jQuery('#loading').show();
					jQuery('#imgLoading').show();
	
					cadena = $( '#form_edit_group' ).serialize();
						
					$.ajax({
						type: "POST",
						url: "/config/createGroup",
						data: {
							cadena_1 : cadena,
							idValue : id_set
						},
						dataType: "json",
						success: function(data){
							if(data.status) {
								$( "#modal_groups_edit" ).dialog( "close" );
								$(".table_groups").flexReload();
							} else {
								$.updateTips( tipsEdit, "Error at group creation" );
							}
						},
						error: function() {
							$.updateTips( tipsEdit, "Server conection error" );
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
			
	        var group_edit_name = $( "#group_edit_name" ),
				allFields = $( [] ).add( group_edit_name ),
				tipsEdit = $( ".validateTipsPlanEdit" );
			tipsEdit.text('All form fields are required');
			allFields.removeClass( "ui-state-error" );
			allFields.css("color","#5A698B");
		}
	});

	$("#modal_groups_edit").load(
            url, 
            {IDSelect:id}, // omit this param object to issue a GET request instead a POST request, otherwise you may provide post parameters within the object
            function (responseText, textStatus, XMLHttpRequest) {
                // remove the loading class
                $("#modal_groups_edit").removeClass('loading');
                
				if(clonar) {
					var name1 = $("#group_edit_name").val();
					$("#group_edit_name").val(name1+'_1');
				}
				
				$( "#modal_groups_edit" ).dialog( "open" );
            }
	);

	return false;
}