jQuery(function( $ ) {

	configScreenFilter = new $.formVar( 'screen_fmFilter' );

	configScreenFilter.get('screen_fmEquipo').change(function( ) {
		changeGraph();
	});
	;

	configScreenFilter.get("screen_fmScreen").change(function( ) {
		changeGraph();
	});

	configScreenFilter.get("screen_fmGrupo").change(function( ) {
		$.post("monitor/ultimafecha", {
			id : $(this).val()
		}, function( data ) {
			$("#screen_fmEquipo").html(data);
		});

		getScreenItems();
	});
	
	function changeGraph( ) {
		var screenid = configScreenFilter.get('screen_fmScreen').val();
		var groupid =  configScreenFilter.get("screen_fmGrupo").val();
		var hostid =  configScreenFilter.get("screen_fmEquipo").val();
		var hostName = $('#screen_fmEquipo option:selected',configScreenFilter.form).text();

		if ( hostid != 0 && screenid != 0 ) {
			$('#screen_display').load('graphc/screenDisplay', {
				screenid : screenid,
				groupid : groupid,
				hostid : hostid,
				hostName: hostName
			}, function( ) {

				$( ".column" ).sortable({
			      connectWith: ".column",
			      handle: ".portlet-header",
			      cancel: ".portlet-toggle",
			      placeholder: "portlet-placeholder ui-corner-all"
			    });
			 
			    $( ".portlet" )
			      .addClass( "ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" )
			      .find( ".portlet-header" )
			        .addClass( "ui-widget-header ui-corner-all" )
			        .prepend( "<span class='ui-icon ui-icon-minusthick portlet-toggle'></span>");
			 
			    $( ".portlet-toggle" ).click(function() {
			      var icon = $( this );
			      icon.toggleClass( "ui-icon-minusthick ui-icon-plusthick" );
			      icon.closest( ".portlet" ).find( ".portlet-content" ).toggle();
			    });

			});
		}
	};

	function getScreenItems( ) {
		
			var formData = $('#screen_fmFilter').serialize();
			$.ajax({
				url : "/graphc/getScreenItems",
				type : "POST",
				data : {
					"groupid" : $("#screen_fmGrupo").val()
				} ,
				async: false,
				dataType : "json",
				error : function( ) {
					
				},
				success : function( r ) {
					if(r.status) {
						$('#screen_fmScreen').html(r.option);
					} else {
						$('#screen_fmScreen').html(r.option);
					}
				}
			});
		
	};
	
});