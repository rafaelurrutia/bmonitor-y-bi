var table = $( '.table_parameter' );
var modal_new = $( '#modal_parameter_new' ),
	modal_new_url = '/admin/createParameter',
	modal_new_form = $( '#form_new_parameter' );

var parameter_name = $( "#parameter_name" ),
	parameter_desc = $( "#parameter_desc" ),
	parameter_value = $( "#parameter_value" ),
	parameter_type = $( "#parameter_type" ),
	allFields_new = $( [] ).add( parameter_name ).add( parameter_desc ).add( parameter_value ),
	tips_new = $( ".form_new_parameter_validateTips" );

jQuery(function($){

$( '#modal_parameter_new' ).dialog({
	autoOpen: false,
	resizable: false,
	height: 'auto',
	width: 600,
	modal: true,
	closeOnEscape: true,
	draggable: true,
	open: function() {
        modal_new.find('.ui-dialog-titlebar-close').blur();
	},
	buttons: {
		"Accept": function() {		
			
			var bValid = true;
	
			allFields_new.removeClass( "ui-state-error" );
			allFields_new.css("color","#5A698B");
				
			bValid = bValid && $.checkLength( tips_new, parameter_name, "Name", 3, 100 );
			bValid = bValid && $.checkLength( tips_new, parameter_desc, "Description", 3, 100 );
			bValid = bValid && $.checkLength( tips_new, parameter_value, "Value", 3, 100 );
			
			console.log(bValid);
			if ( bValid ) {
				
				jQuery('#loading').show();
				jQuery('#imgLoading').show();
				
				$.ajax({
					url: modal_new_url,
					type: "post",
					dataType: 'json',
					data:{parameter_name:parameter_name.val(), parameter_desc:parameter_desc.val(), parameter_value:parameter_value.val(), parameter_type: parameter_type.val()},
					success: function(data){
						if(data.status){
							modal_new.dialog("close");
							table.flexReload();
						}else{
							$.updateTips(tips_new, "Error creating parameter");
						}
					},
					error: function(data, status, e){
						$.updateTips(tips_new, "Error connectiong to server");
					}
				});
				
				jQuery('#loading').hide();
				jQuery('#imgLoading').hide();
				
			}
		},
		Cancel: function() {
			$( '#modal_parameter_new' ).dialog( "close" );
		}
	},
	close: function() {
		tips_new.text('All form elements are required');
		allFields_new.val( "" ).removeClass( "ui-state-error" );
		allFields_new.css("color","#5A698B");
	}
});

$( "#modal_parameter_confirm" ).dialog({
			resizable: false,
			autoOpen: false,
			height:140,
			modal: true,
			buttons: {
				"Accept": function() {
					var select = new Array( );
					count = 1;
					
					$('.trSelected', table).each(function( ) {
						var id = $(this).attr('id');
						id = id.substring(id.lastIndexOf('row') + 3);
						if ( $.isNumeric(id) ) {
							select[count] = id;
							count = count + 1;
						}
					});
					
					$.ajax({
						type: "POST",
						url: "/admin/deleteParameter",
						data:  {
							idSelect : select
						},
						dataType: "json",
						success: function(data){
							if(data.status) {
								$( "#modal_parameter_confirm" ).dialog( "close" );
								table.flexReload();
							} else {
								alert("Failed to update parameter");
							}
						},
						error: function() {
							alert("Error connecting to server");
						}
					});
				},
				Cancel: function() {
					$( this ).dialog( "close" );
				}
			}
});

$.checkLengthEdit = function(tips, o, n, min, max) {

        if ( typeof o.val() == "undefined" || o.val().length > max || o.val().length < min) {
            o.addClass("ui-state-error");
            o.css("color", "#FFFFFF");
            $.updateTips(tips, "El largo del campo " + n + " debe estar entre " + min + " y " + max + ".");
            return false;
        } else {
            return true;
        }
};


$.editParameter = function(idparameter){
	var allFields_edit = $( [] ).add( $( "#form_edit_parameter #parameter_name" ) ).add( $( "#form_edit_parameter #parameter_desc" ) ).add( $( "#form_edit_parameter #parameter_value" ) );
	
	$("#modal_parameter_edit").load("/admin/getFormEditParam", {
		paramid : idparameter
	}, function( responseText,textStatus,XMLHttpRequest ){
		$(this).dialog("open");
	});
	
	$("#modal_parameter_edit").dialog({
		autoOpen : false,
		resizable : false,
		height : 'auto',
		width : 640,
		modal : true,
		buttons : {
			"Accept": function() {
				var bValid = true;
	
				allFields_edit.removeClass( "ui-state-error" );
				allFields_edit.css("color","#5A698B");
				
				bValid = bValid && $.checkLengthEdit( $( ".form_edit_parameter_validateTips" ), $("#form_edit_parameter #parameter_name"), "Name", 3, 100 );
				bValid = bValid && $.checkLengthEdit( $( ".form_edit_parameter_validateTips" ), $("#form_edit_parameter #parameter_desc"), "Description", 3, 100 );
				bValid = bValid && $.checkLengthEdit( $( ".form_edit_parameter_validateTips" ), $("#form_edit_parameter #parameter_value"), "Value", 3, 100 );
			
				console.log(bValid);
				console.log($("#form_edit_parameter #parameter_name").val());
				console.log($("#form_edit_parameter #parameter_desc").val());
				console.log($("#form_edit_parameter #parameter_value").val());
				console.log($("#form_edit_parameter #parameter_type").val());
				if ( bValid ) {
					jQuery('#loading').show();
					jQuery('#imgLoading').show();
				
					$.ajax({
						url: "/admin/setParameter",
						type: "post",
						dataType: 'json',
						data:{parameter_id: idparameter,parameter_name: $("#form_edit_parameter #parameter_name").val(), parameter_desc: $("#form_edit_parameter #parameter_desc").val(), parameter_value: $("#form_edit_parameter #parameter_value").val(), parameter_type: $("#form_edit_parameter #parameter_type").val()},
						success: function(data){
							if(data.status){
								$("#modal_parameter_edit").dialog("close");
								table.flexReload();
							}else{
								$.updateTips(tips_new, "Error editing parameter");
							}
						},
						error: function(data, status, e){
							$.updateTips(tips_new, "Error connectiong to server");
						}
					});
				
					jQuery('#loading').hide();
					jQuery('#imgLoading').hide();
				}
			},
			Cancel: function() {
				$(this).dialog( "close" );
			}
		},
		close: function() {
			$( ".form_edit_parameter_validateTips" ).text('All form elements are required');
			allFields_edit.val( "" ).removeClass( "ui-state-error" );
			allFields_edit.css("color","#5A698B");
		}
	});
};
		
});