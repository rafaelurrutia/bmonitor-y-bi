jQuery(function( $ ) {

	var formGraphNew = new $.formVar( 'config_graph_form' );
	var formGraphEdit = new $.formVar( 'config_graph_form_edit' );

	formGraphNew.get('items').multiselect();


	formGraphNew.get('groupid').on("change", function(event){
		refreshItems(formGraphNew);
	});
		
	function refreshItems(form) {
		$.ajax({
			type : "POST",
			url : "/config/getConfigItems/"+form.get('groupid').val(),
			dataType : "json",
			async : false,
			success : function( data )
			{
				if ( data.status ) {
					form.get('items').html(data.result);
					form.get('items').multiselect('refresh');
				} else {
					return false;
					      
				}
			},
			error : function( )
			{
				alert("Error");
			}
		});	  
	}
	
	
	$("#modal_graph_form").dialog({
		autoOpen : false,
		resizable : false,
		width : 650,
		modal : true,
		closeOnEscape : true,
		draggable : true,
		open : function( ) {
			$(this).find('.ui-dialog-titlebar-close').blur();
			refreshItems(formGraphNew);
		},
		buttons : {
			"Accept" : function( ) {
				var bValid = true;

				bValid = bValid && formGraphNew.checkLength('name', "auto", 3, 100, language.code);
				bValid = bValid && formGraphNew.checkSelect('groupid', "auto", 0, language.code);

				if ( bValid ) {

					jQuery('#loading').show();
					jQuery('#imgLoading').show();

					$.ajax({
						type : "POST",
						url : "/config/createGraph",
						data : "id=new" + "&" + formGraphNew.form.serialize(),
						dataType : "json",
						success : function( data ) {
							if ( data.status ) {
								$("#modal_graph_form").dialog("close");
								$("#table_graph").flexReload();
							} else {
								formGraphNew.tipsBox(data.error, 'alert');
							}
						},
						error : function( ) {
							formGraphNew.errorSystem(language.code);
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
			formGraphNew.get('name').val("");
			formGraphNew.get('groupid').prop('selectedIndex',0);
			formGraphNew.resetAllSelect();
			formGraphNew.get('items').multiselect("refresh");
		}

	});

	$("#modal_graph_delete").dialog({
		autoOpen : false,
		resizable : false,
		modal : true,
		open : function( ) {
			var countSelectGraph = 1;
			$("#modal_graph_delete #selected").html("Selected:");
			$('.trSelected', $('#table_graph')).each(function( ) {
				var name = $(this).children('td:nth-child(2)').text();
				$("#modal_graph_delete #selected").append("</br>" + countSelectGraph + " : " + name);
				countSelectGraph = countSelectGraph + 1;
			});
		},
		buttons : {
			"Accept" : function( ) {

				jQuery('#loading').show();
				jQuery('#imgLoading').show();

				var select = new Array( );
				count = 1;
				$('.trSelected', $('#table_graph')).each(function( ) {

					var id = $(this).attr('id');
					id = id.substring(id.lastIndexOf('row') + 3);
					if ( $.isNumeric(id) ) {
						select[count] = id;
						count = count + 1;
					}
				});

				$.ajax({
					type : "POST",
					url : "/config/deleteGraph",
					data : {
						idSelect : select
					},
					dataType : "json",
					success : function( data ) {
						if ( data.status ) {
							$("#table_graph").flexReload();
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
				$("#modal_graph_delete").dialog("close");
			},
			Cancel : function( ) {
				$("#modal_graph_delete").dialog("close");
			}

		}
	});

	$.editGraph = function( id, clonar ) {

		var url = "/config/getGraphForm";

		var dialog = $('<div style="display:none" id="editGroupDialog" class="loading"></div>').appendTo('body');

		if ( clonar ) {
			var id_set = "clone";
		} else {
			var id_set = id;
		}


		$("#modal_graph_edit").load(url, {
			IDSelect : id
		}, // omit this param object to issue a GET request instead a POST request,
		// otherwise you may provide post parameters within the object
		function( responseText, textStatus, XMLHttpRequest ) {
			// remove the loading class
			$("#modal_graph_edit").removeClass('loading');

			if ( clonar ) {

				var name1 = formGraphEdit.get('name').val();
				formGraphEdit.get('name').val(name1 + '_1');
			}
			formGraphEdit.refresh();
			
			formGraphEdit.get('groupid').on("change", function(event){
				refreshItems(formGraphEdit);
			});
	
			$("#modal_graph_edit").dialog("open");
		});
		
		$("#modal_graph_edit").dialog({
			autoOpen : false,
			resizable : false,
			width : 650,
			modal : true,
			closeOnEscape : true,
			draggable : true,
			open : function( ) {
				$(this).find('.ui-dialog-titlebar-close').blur();
				formGraphEdit.get('items').multiselect();
				formGraphEdit.get('items').multiselect('locale', lang);
			},
			buttons : {
				"Accept" : function( ) {
					var bValid = true;

					bValid = bValid && formGraphEdit.checkLength('name', "auto", 3, 100, language.code);
					bValid = bValid && formGraphEdit.checkSelect('groupid', "auto", 0, language.code);

					if ( bValid ) {

						jQuery('#loading').show();
						jQuery('#imgLoading').show();

						$.ajax({
							type : "POST",
							url : "/config/createGraph",
							data : "id=" + id_set + "&" + formGraphEdit.form.serialize(),
							dataType : "json",
							success : function( data ) {
								if ( data.status ) {
									$("#modal_graph_edit").dialog("close");
									$("#table_graph").flexReload();
								} else {
									formGraphEdit.tipsBox(data.error, 'alert');
								}
							},
							error : function( ) {
								formGraphEdit.errorSystem(language.code);
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

			}

		});

		return false;
	};

});

