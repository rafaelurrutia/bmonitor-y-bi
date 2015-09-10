jQuery(function( $ ) {

	configScreenFilter = new $.formVar( 'tableScreenFilter' );
	configScreenFormNew = new $.formVar( 'configScreenFormNew' );

	configScreenFilter.get('groupid').change(function( ) {
		table_screen.flexReload();
	});
		
	$("#modal_screen_form").dialog({
		autoOpen : false,
		resizable : false,
		height : 'auto',
		width : 400,
		modal : false,
		closeOnEscape : true,
		draggable : true,
		open : function( ) {
			$(this).find('.ui-dialog-titlebar-close').blur();
		},
		buttons : {
			"Accept" : function( ) {
				var bValid = true;

				bValid = bValid && configScreenFormNew.checkLength('name', "auto", 3, 200,language.code);
				bValid = bValid && configScreenFormNew.checkLength('hsize', "auto", 1, 1,language.code);
				bValid = bValid && configScreenFormNew.checkLength('vsize', "auto", 1, 1,language.code);
				bValid = bValid && configScreenFormNew.checkSelect('groupid', "auto", 0,language.code);

				if ( bValid ) {

					jQuery('#loading').show();
					jQuery('#imgLoading').show();

					$.ajax({
						type : "POST",
						url : "/configScreen/newScreen",
						data : "id=new" + "&" + configScreenFormNew.form.serialize(),
						dataType : "json",
						success : function( data ) {
							if ( data.status ) {
								$("#modal_screen_form").dialog("close");
								table_screen.flexReload();
							} else {
								configScreenFormNew.tipsBox(data.error, 'alert');
							}
						},
						error : function( ) {
							configScreenFormNew.errorSystem(language.code);
						}

					});

					jQuery('#loading').hide();
					jQuery('#imgLoading').hide();

				}
			},
			"Cancel" : function( ) {
				$(this).dialog("close");
			}

		},
		close : function( ) {
		}
	});

	$("#modal_screen_delete").dialog({
		autoOpen : false,
		resizable : false,
		modal : true,
		open : function( ) {

			var grid = $('#table_screen');
			var countSelect = 1;
			$("#modal_screen_delete #selected").html("Selected:");
			$('.trSelected', grid).each(function( ) {
				var name = $(this).children('td:nth-child(3)').text();
				$("#modal_screen_delete #selected").append("</br>" + countSelect + " : " + name);
				countSelect = countSelect + 1;
			});

		},
		buttons : {

			"Accept" : function( ) {

				jQuery('#loading').show();
				jQuery('#imgLoading').show();

				var select = new Array( );
				count = 1;
				grid = $('#table_screen');
				$('.trSelected', grid).each(function( ) {
					var id = $(this).attr('id');
					id = id.substring(id.lastIndexOf('row') + 3);
					if ( $.isNumeric(id) ) {
						select[count] = id;
						count = count + 1;
					}
				});

				$.ajax({
					type : "POST",
					url : "/configScreen/deleteScreen",
					data : {
						idSelect : select
					},
					dataType : "json",
					success : function( data ) {
						if ( data.status ) {
							table_screen.flexReload();
						} else {
							alert(data.error);
						}
					},
					error : function( ) {
						alert("Error de conexion");
					}

				});
				jQuery('#loading').hide();
				jQuery('#imgLoading').hide();
				$("#modal_screen_delete").dialog("close");
			},
			Cancel : function( ) {
				$("#modal_screen_delete").dialog("close");
			}

		}
	});
});

function editScreen( id, clonar ) {
	var url = "/configScreen/getScreenForm";

	if ( clonar ) {
		var id_set = "clone";
	} else {
		var id_set = id;
	}

	$("#modal_screen_edit").load(url, {
		IDSelect : id
	}, // omit this param object to issue a GET request instead a POST request, otherwise you may provide
	// post parameters within the object
	function( responseText, textStatus, XMLHttpRequest ) {
		// remove the loading class
		$("#modal_screen_edit").removeClass('loading');	
		configScreenFormEdit = new $.formVar( 'configScreenFormEdit' );
		$("#modal_screen_edit").dialog("open");
	});

	$("#modal_screen_edit").dialog({
		autoOpen : false,
		resizable : false,
		height : 'auto',
		width : 400,
		modal : false,
		closeOnEscape : true,
		draggable : true,
		open : function( ) {
			$(this).find('.ui-dialog-titlebar-close').blur();
		},
		buttons : {
			"Accept" : function( ) {
				var bValid = true;

				bValid = bValid && configScreenFormEdit.checkLength('name', "auto", 3, 200,language.code);
				bValid = bValid && configScreenFormEdit.checkLength('hsize', "auto", 1, 1,language.code);
				bValid = bValid && configScreenFormEdit.checkLength('vsize', "auto", 1, 1,language.code);
				bValid = bValid && configScreenFormEdit.checkSelect('groupid', "auto", 0,language.code);
				
				if ( bValid ) {

					jQuery('#loading').show();
					jQuery('#imgLoading').show();

					$.ajax({
						type : "POST",
						url : "/configScreen/newScreen",
						data : "id=" + id_set + "&" + configScreenFormEdit.form.serialize(),
						dataType : "json",
						success : function( data ) {
							if ( data.status ) {
								$("#modal_screen_edit").dialog("close");
								table_screen.flexReload();
							} else {
	
								configScreenFormEdit.tipsBox("Failed to edit screen", 'alert');
							}
						},
						error : function( ) {
							configScreenFormEdit.tipsBox("Error connecting to server", 'alert');
						}
					});

					jQuery('#loading').hide();
					jQuery('#imgLoading').hide();

				}
			},
			Cancel : function( ) {
				$(this).dialog("close");
			}

		},
		close : function( ) {
			//tips.text('Todo los elementos del formulario son requeridos');
			//allFieldsPlan.val( "" ).removeClass( "ui-state-error" );
			//allFieldsPlan.css("color","#5A698B");
		}

	});
	return false;
}

function asigScreen( id ) {

	$.ajax({
		type : "POST",
		url : "/configScreen/getScreenAsig",
		data : {
			IDSelect : id
		},
		dataType : "json",
		success : function( data ) {
			if ( data.status ) {
				$("#modal_screen_asig").html(data.html);
				$("#modal_screen_asig").removeClass('loading');
				$("#modal_screen_asig").dialog("option", "title", data.screen_dialog_title);
				$("#modal_screen_asig").dialog("open");
			}
		},
		error : function( ) {
			alert("Error de conexion");
		}

	});

	$("#modal_screen_asig").dialog({
		autoOpen : false,
		resizable : false,
		height : 'auto',
		width : 'auto',
		modal : true,
		closeOnEscape : true,
		draggable : true,
		open : function( ) {
			$(this).find('.ui-dialog-titlebar-close').blur();

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
		},
		buttons : {
			"Save" : function( ) {
				var bValid = true;

				if ( bValid ) {

					jQuery('#loading').show();
					jQuery('#imgLoading').show();

					$.ajax({
						type : "POST",
						url : "/configScreen/asigScreen",
						data : "id=" + id + "&" + $('#config_screen_form_asig').serialize(),
						dataType : "json",
						success : function( data ) {
							if ( data.status ) {
								$("#modal_screen_asig").dialog("close");
							} else {
								$.updateTips(tips, "Error al asignar pantalla");
							}
						},
						error : function( ) {
							$.updateTips(tips, "Error de conexion con el servidor");
						}

					});

					jQuery('#loading').hide();
					jQuery('#imgLoading').hide();

				}
			},
			Cancel : function( ) {
				$(this).dialog("close");
			}

		},
		close : function( ) {
			//tips.text('Todo los elementos del formulario son requeridos');
			//allFieldsPlan.val( "" ).removeClass( "ui-state-error" );
			//allFieldsPlan.css("color","#5A698B");
		}

	});
	return false;
}