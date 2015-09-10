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

	var name_new = $("#config_monitores_form_new_descriptionLong"), description_new = $("#config_monitores_form_new_description"), snmp_community_new = $("#config_monitores_form_new_snmp_community"), snmp_oid_new = $("#config_monitores_form_new_snmp_oid"), snmp_port_new = $("#config_monitores_form_new_snmp_port"), type_new = $("#config_monitores_form_new_type_item"), unit_new = $("#config_monitores_form_new_unit"), delay_new = $("#config_monitores_form_new_delay"), history_new = $("#config_monitores_form_new_history"), trend_new = $("#config_monitores_form_new_trend"), allFields_new = $([]).add(name_new), tips_new = $("#config_monitores_form_new_validateTips");

	var name_edit = "#config_monitores_form_edit_descriptionLong", description_edit = "#config_monitores_form_edit_description", snmp_community_edit = "#config_monitores_form_edit_snmp_community", snmp_oid_edit = "#config_monitores_form_edit_snmp_oid", snmp_port_edit = "#config_monitores_form_edit_snmp_port", unit_edit = "#config_monitores_form_edit_unit", delay_edit = "#config_monitores_form_edit_delay", history_edit = "#config_monitores_form_edit_history", trend_edit = "#config_monitores_form_edit_trend", allFields_edit = $([]).add(name_edit), tips_edit = "#config_monitores_form_edit_validateTips";

	$("#config_monitores_form_new_type").change(function() {

		var typeMonitor = $("#config_monitores_form_new_type").val();

		if (typeMonitor == "1") {
			$("#config_monitores_form_new_snmp_1").show();
			$("#config_monitores_form_new_snmp_2").show();
			$("#config_monitores_form_new_snmp_3").show();
		} else {
			$("#config_monitores_form_new_snmp_1").hide();
			$("#config_monitores_form_new_snmp_2").hide();
			$("#config_monitores_form_new_snmp_3").hide();
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
		width : 650,
		modal : true,
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

				bValid = bValid && formMonitorNew.checkLength( 'descriptionLong', "auto", 3, 100, 'en');
				bValid = bValid && formMonitorNew.checkLength( 'description', "auto", 3, 100, 'en');

				if ($("#config_monitores_form_new_type").val() == "1") {
					bValid = bValid && $.checkLength(tips_new, snmp_community_new, "Comunidad SNMP", 2, 40);
					bValid = bValid && $.checkLength(tips_new, snmp_oid_new, "SNMP OID", 2, 40);
					bValid = bValid && $.checkLength(tips_new, snmp_port_new, "Puerto SNMP", 2, 40);
				}

				bValid = bValid && $.checkSelect(tips_new, type_new, "Tipo monitor", 0);
				bValid = bValid && $.checkLength(tips_new, unit_new, "Unidad", 2, 17);
				bValid = bValid && $.checkLength(tips_new, delay_new, "Intervalo de actualización (en segundos)", 2, 17);
				bValid = bValid && $.checkLength(tips_new, history_new, "Conservar el histórico durante (en días)", 2, 17);
				bValid = bValid && $.checkLength(tips_new, trend_new, "Conservar las tendencias durante (en días)", 2, 17);

				if (bValid) {

					jQuery('#loading').show();
					jQuery('#imgLoading').show();

					$.ajax({
						type : "POST",
						url : modal_new_url,
						data : "id=new" + "&" + modal_new_form.serialize(),
						dataType : "json",
						success : function(data) {
							if (data.status) {
								modal_new.dialog("close");
								table.flexReload();
							} else {
								$.updateTips(tips_new, "Error al crear registro");
							}
						},
						error : function() {
							$.updateTips(tips_new, "Error de conexion con el servidor");
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
			tips_new.text('Todo los elementos del formulario son requeridos');
			allFields_new.val("").removeClass("ui-state-error");
			allFields_new.css("color", "#5A698B");
		}
	});

	modal_delete.dialog({
		autoOpen : false,
		resizable : false,
		modal : true,
		open : function() {
			var countSelect = 1;
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
			// remove the loading class
			$(modal_edit).removeClass('loading');

			if (clonar) {
				var name1 = $(name_edit).val();
				$(name_edit).val(name1 + '_1');
			}

			$(modal_edit).dialog("open");

			$('#config_monitores_form_edit_tags').tagit({
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
				$("#config_monitores_form_edit_groupid").multiselect();
				var typeMonitor = $("#config_monitores_form_edit_type").val();

				if (typeMonitor == "1") {
					$("#config_monitores_form_edit_snmp_1").show();
					$("#config_monitores_form_edit_snmp_2").show();
					$("#config_monitores_form_edit_snmp_3").show();
				} else {
					$("#config_monitores_form_edit_snmp_1").hide();
					$("#config_monitores_form_edit_snmp_2").hide();
					$("#config_monitores_form_edit_snmp_3").hide();
				}
				$("#config_monitores_form_edit_type").change(function() {

					var typeMonitor = $("#config_monitores_form_edit_type").val();

					if (typeMonitor == "1") {
						$("#config_monitores_form_edit_snmp_1").show();
						$("#config_monitores_form_edit_snmp_2").show();
						$("#config_monitores_form_edit_snmp_3").show();
					} else {
						$("#config_monitores_form_edit_snmp_1").hide();
						$("#config_monitores_form_edit_snmp_2").hide();
						$("#config_monitores_form_edit_snmp_3").hide();
					};

				});
			},
			buttons : {
				"Guardar" : function() {

					var bValid = true;

					allFields_edit.removeClass("ui-state-error");
					allFields_edit.css("color", "#5A698B");

					bValid = bValid && $.checkLength2(tips_edit, name_edit, "Nombre descriptivo", 3, 100);
					bValid = bValid && $.checkSelect2(tips_edit, description_edit, "Monitor", 3, 100);

					if ($("#config_monitores_form_edit_type").val() == "1") {
						bValid = bValid && $.checkLength2(tips_edit, snmp_community_edit, "Comunidad SNMP", 2, 40);
						bValid = bValid && $.checkLength2(tips_edit, snmp_oid_edit, "SNMP OID", 2, 40);
						bValid = bValid && $.checkLength2(tips_edit, snmp_port_edit, "Puerto SNMP", 2, 40);
					}

					bValid = bValid && $.checkLength2(tips_edit, unit_edit, "Unidad", 2, 17);

					bValid = bValid && $.checkLength2(tips_edit, delay_edit, "Intervalo de actualización (en segundos)", 2, 17);
					bValid = bValid && $.checkLength2(tips_edit, history_edit, "Conservar el histórico durante (en días)", 2, 17);
					bValid = bValid && $.checkLength2(tips_edit, trend_edit, "Conservar las tendencias durante (en días)", 2, 17);

					if (bValid) {

						jQuery('#loading').show();
						jQuery('#imgLoading').show();

						$.ajax({
							type : "POST",
							url : modal_new_url,
							data : "id=" + id_set + "&" + $(modal_edit_form).serialize(),
							dataType : "json",
							success : function(data) {
								if (data.status) {
									$(modal_edit).dialog("close");
									$(table).flexReload();
								} else {
									$.updateTips2(tips_edit, "Error al crear registro");
								}
							},
							error : function() {
								$.updateTips2(tips_edit, "Error de conexion con el servidor");
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
				$.updateTips2(tips_edit, "Todo los elementos del formulario son requeridos");
				allFields_edit.val("").removeClass("ui-state-error");
				allFields_edit.css("color", "#5A698B");
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
