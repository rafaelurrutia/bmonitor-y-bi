var table = $( '.table_firmware' );
var modal_new = $( '#modal_firmware_new' ),
	modal_new_url = '/admin/createFirmware',
	modal_new_form = $( '#form_new_firmware' );

var firmware_version = $( "#firmware_version" ),
	firmware_responsable = $( "#firmware_responsable" ),
	firmeware_branch = $( "#firmeware_branch" ),
	firmeware_upload = $( "#firmeware_upload" ),
	allFields_new = $( [] ).add( firmware_version ).add( firmware_responsable ),
	tips_new = $( ".form_new_firmware_validateTips" );

jQuery(function($){

$( '#modal_firmware_new' ).dialog({
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
				
			bValid = bValid && $.checkLength( tips_new, firmware_version, "Version", 3, 100 );
			bValid = bValid && $.checkLength( tips_new, firmware_responsable, "Responsible", 3, 100 );
			bValid = bValid && $.checkInputFile( tips_new, firmeware_upload, "Firmware (.tar)", 'tar' );
			
			console.log(bValid);
			if ( bValid ) {
				
				jQuery('#loading').show();
				jQuery('#imgLoading').show();
		
				$.ajaxFileUpload({
					url:modal_new_url,
					secureuri:false,
					fileElementId:'firmeware_upload',
					dataType: 'json',
					data:{firmware_version:firmware_version.val(), firmware_responsable:firmware_responsable.val(), firmeware_branch:firmeware_branch.val()},
					success: function (data, status){
						if(data.status) {
							modal_new.dialog( "close" );
							table.flexReload();
						} else {
							$.updateTips( tips_new, "Error creating registration" );
						}
					},
					error: function (data, status, e)
					{
						$.updateTips( tips_new, "Error connecting to server" );
					}
				});
				
				jQuery('#loading').hide();
				jQuery('#imgLoading').hide();
				
			}
		},
		Cancel: function() {
			$( '#modal_firmware_new' ).dialog( "close" );
		}
	},
	close: function() {
		tips_new.text('All form elements are required');
		allFields_new.val( "" ).removeClass( "ui-state-error" );
		allFields_new.css("color","#5A698B");
	}
});

$( "#modal_firmware_confirm" ).dialog({
			resizable: false,
			autoOpen: false,
			height:140,
			modal: true,
			buttons: {
				"Accept": function() {
					$.ajax({
						type: "POST",
						url: "/admin/setFirmware",
						data:  "id=update",
						dataType: "json",
						success: function(data){
							if(data.status) {
								$( "#modal_firmware_confirm" ).dialog( "close" );
							} else {
								alert("Failed to update version");
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
		
});