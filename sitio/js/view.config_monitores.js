jQuery(function($) {

	var table = $('#monitores');
	
    var formMonitorNew = new $.formVar('config_monitores_form_new');
    var formMonitorEdit = new $.formVar('config_monitores_form_edit');

	var modal_new = $('#modal_monitor_new');
	var modal_new_url = '/config/createMonitor';
	var modal_new_form = $('#config_monitores_form_new');

	var modal_delete = $('#modal_monitor_delete');
	var modal_delete_url = '/config/deleteMonitor';

	var modal_edit = '#modal_monitor_edit';
	var modal_edit_url_form = '/config/getMonitorForm';
	var modal_edit_form = '#config_monitores_form_edit';

	formMonitorNew.get('type').change(function() {

		var typeMonitor = formMonitorNew.get('type').val();

		if (typeMonitor == "1") {
			formMonitorNew.get('snmp_1').show();
			formMonitorNew.get('snmp_2').show();
			formMonitorNew.get('snmp_3').show();
		} else {
			formMonitorNew.get('snmp_1').hide();
			formMonitorNew.get('snmp_2').hide();
			formMonitorNew.get('snmp_3').hide();
		}
	});

	var sampleTags = ['speedtest', 'youtube', 'dns'];

    formMonitorNew.get('tags').tagit({
        availableTags : sampleTags
    });
    
	modal_new.dialog({
		autoOpen : false,
		resizable : false,
		height : 'auto',
		width : 660,
		modal : false,
		closeOnEscape : true,
		draggable : true,
		open : function() {
			modal_new.find('.ui-dialog-titlebar-close').blur();
			formMonitorNew.get('groupid').multiselect();
		},
		buttons : {
			"Save" : function() {

				var bValid = true;

                formMonitorNew.all.removeClass("ui-state-error");
                formMonitorNew.all.css("color", "#5A698B");

				bValid = bValid && formMonitorNew.checkLength( 'descriptionLong', "auto", 3, 100,language.code);
				bValid = bValid && formMonitorNew.checkLength( 'description', "auto", 3, 100,language.code);

				if (formMonitorNew.get('type').val() == "1") {
					bValid = bValid && formMonitorNew.checkLength('snmp_community', "auto", 2, 40,language.code);
					bValid = bValid && formMonitorNew.checkLength('snmp_oid', "auto", 2, 40,language.code);
					bValid = bValid && formMonitorNew.checkLength('snmp_port', "auto", 2, 40,language.code);
				}

				bValid = bValid && formMonitorNew.checkSelect( 'type_item', "auto", 0,language.code);
				bValid = bValid && formMonitorNew.checkLength( 'unit', "auto", 2, 17,language.code);
				bValid = bValid && formMonitorNew.checkLength( 'delay', "auto", 2, 17,language.code);
				bValid = bValid && formMonitorNew.checkLength( 'history', "auto", 2, 17,language.code);
				bValid = bValid && formMonitorNew.checkLength( 'trend', "auto", 2, 17,language.code);

				if (bValid) {

					jQuery('#loading').show();
					jQuery('#imgLoading').show();

					$.ajax({
						type : "POST",
						url : modal_new_url,
						data : "id=new" + "&" + formMonitorNew.form.serialize(),
						dataType : "json",
						success : function(data) {
							if (data.status) {
								modal_new.dialog("close");
								table.flexReload();
							} else {
							    formMonitorNew.tipsBox("Error al crear registro",'alert');
							}
						},
						error : function() {
							formMonitorNew.errorSystem(language.code);
						}
					});

					jQuery('#loading').hide();
					jQuery('#imgLoading').hide();

				}
			},
			Cancel : function() {
				modal_new.dialog("close");
			}
		},
		close : function() {
			formMonitorNew.all.val("").removeClass("ui-state-error").css("color", "#5A698B");
		}
	});

	modal_delete.dialog({
		autoOpen : false,
		resizable : true,
		width : 650,
		modal : true,
		open : function() {
			var countSelect = 1;
			$("#modal_monitor_delete p#selected").html("Selected:");
			$('.trSelected', $('#monitores')).each(function() {
				var name = $(this).children('td:nth-child(2)').text();
				$("#modal_monitor_delete p#selected").append("</br>" + countSelect + " : " + name);
				countSelect = countSelect + 1;
			});
		},
		buttons : {
			"OK" : function() {

				jQuery('#loading').show();
				jQuery('#imgLoading').show();

				var select = new Array();
				count = 0;
				grid = $('#monitores');
				$('.trSelected', grid).each(function() {
					var id = $(this).attr('id').substr(3);
					if ($.isNumeric(id)) {
						select[count] = id;
						count = count + 1;
					}
				});

				$.ajax({
					type : "POST",
					url : modal_delete_url,
					data : {
						idSelect : select,
						groupid : $('#fmGrupoMonitores').val()
					},
					dataType : "json",
					success : function(data) {
						if (data.status) {
							table.flexReload();
							modal_delete.dialog("close");
						} else {
							alert(data.error);
						}
					},
					error : function() {
						alert("Error de conexion");
					}
				});
				jQuery('#loading').hide();
				jQuery('#imgLoading').hide();

			},
			Cancel : function() {
				modal_delete.dialog("close");
			}
		}
	});

	$.editarModal = function(id, clonar) {

		if (clonar) {
			var id_set = "clone";
		} else {
			var id_set = id;
		}

		$(modal_edit).load(modal_edit_url_form, {
			IDSelect : id
		}, // omit this param object to issue a GET request instead a POST request,
		// otherwise you may provide post parameters within the object
		function(responseText, textStatus, XMLHttpRequest) {
		    
		    formMonitorEdit.refresh();
		    
			// remove the loading class
			$(modal_edit).removeClass('loading');

			if (clonar) {
				var name1 = formMonitorEdit.get('descriptionLong').val();
				formMonitorEdit.get('descriptionLong').val(name1 + '_1');
			}

			$(modal_edit).dialog("open");

            formMonitorEdit.get('tags').tagit({
                availableTags : sampleTags
            });
 
		});

		$(modal_edit).dialog({
			autoOpen : false,
			resizable : false,
			height : 'auto',
			width : 650,
			modal : true,
			closeOnEscape : true,
			draggable : true,
			open : function() {
				$(this).find('.ui-dialog-titlebar-close').blur();
				formMonitorEdit.get('groupid').multiselect();
				
                var typeMonitor = formMonitorEdit.get('type').val();

                if (typeMonitor == "1") {
                    formMonitorEdit.get('snmp_1').show();
                    formMonitorEdit.get('snmp_2').show();
                    formMonitorEdit.get('snmp_3').show();
                } else {
                    formMonitorEdit.get('snmp_1').hide();
                    formMonitorEdit.get('snmp_2').hide();
                    formMonitorEdit.get('snmp_3').hide();
                }
        
                formMonitorEdit.get('type').change(function() {
            
                    var typeMonitor = formMonitorEdit.get('type').val();
            
                    if (typeMonitor == "1") {
                        formMonitorEdit.get('snmp_1').show();
                        formMonitorEdit.get('snmp_2').show();
                        formMonitorEdit.get('snmp_3').show();
                    } else {
                        formMonitorEdit.get('snmp_1').hide();
                        formMonitorEdit.get('snmp_2').hide();
                        formMonitorEdit.get('snmp_3').hide();
                    }
                });
			},
			buttons : {
				"Save" : function() {

					var bValid = true;

                    formMonitorEdit.all.removeClass("ui-state-error");
                    formMonitorEdit.all.css("color", "#5A698B");
    
                    bValid = bValid && formMonitorEdit.checkLength( 'descriptionLong', "auto", 3, 100,language.code);
                    bValid = bValid && formMonitorEdit.checkLength( 'description', "auto", 3, 100,language.code);
    
                    if (formMonitorEdit.get('type').val() == "1") {
                        bValid = bValid && formMonitorEdit.checkLength('snmp_community', "auto", 2, 40,language.code);
                        bValid = bValid && formMonitorEdit.checkLength('snmp_oid', "auto", 2, 40,language.code);
                        bValid = bValid && formMonitorEdit.checkLength('snmp_port', "auto", 2, 40,language.code);
                    }
    
                    bValid = bValid && formMonitorEdit.checkSelect( 'type_item', "auto", 0);
                    bValid = bValid && formMonitorEdit.checkLength( 'unit', "auto", 2, 17);
                    bValid = bValid && formMonitorEdit.checkLength( 'delay', "auto", 2, 17);
                    bValid = bValid && formMonitorEdit.checkLength( 'history', "auto", 2, 17);
                    bValid = bValid && formMonitorEdit.checkLength( 'trend', "auto", 2, 17);

					if (bValid) {

						jQuery('#loading').show();
						jQuery('#imgLoading').show();

						$.ajax({
							type : "POST",
							url : modal_new_url,
							data : "id=" + id_set + "&" + formMonitorEdit.form.serialize(),
							dataType : "json",
							success : function(data) {
								if (data.status) {
									$(modal_edit).dialog("close");
									$(table).flexReload();
								} else {
                                    formMonitorEdit.tipsBox("Error al crear registro",'alert');
                                }
                            },
                            error : function() {
                                formMonitorEdit.tipsBox("Error de conexion con el servidor",'alert');
                            }
						});

						jQuery('#loading').hide();
						jQuery('#imgLoading').hide();

					}

				},
				Cancel : function() {
					$(this).dialog("close");
				}
			},
			close : function() {
				formMonitorEdit.all.val("").removeClass("ui-state-error").css("color", "#5A698B");
			}
		});
	};
});

function editar(id, clone) {
	$.editarModal(id, clone);
}

function statusMonitor(id, groupid, status) {

	if (status) {
		status_value = 0;
	} else {
		status_value = 1;
	}

	$.ajax({
		type : "POST",
		url : "/config/statusMonitor",
		data : {
			status : status_value,
			idmonitor : id,
			groupid : groupid
		},
		dataType : "json",
		success : function(data) {
			if (data.status) {
				$('#monitores').flexReload();
			} else {
				alert("Error interno");
			}
		},
		error : function() {
			alert("Error de conexion con el servidor");
		}
	});
}
