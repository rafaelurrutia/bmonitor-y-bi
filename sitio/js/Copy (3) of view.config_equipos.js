jQuery(function($) {

	function FormVar(formid) {

		this.form = $('#' + formid);

		this.formid = formid;

		this.getVar = [];

		this.all = this.form.find(":input");

		this.value = function(secc) {
			return this.form.find("#" + secc).val();
		}

		this.get = function(secc) {
			return this.form.find("#" + secc);
		}

		this.getAll = function() {
			return this.form.find(":input");
		}

		this.setVar = function(name, value) {
			this.getVar[name] = value;
		}

		this.refresh = function() {
			this.form = $('#' + this.formid);
		}

		this.tips = $('#' + formid + "_validateTips");

	}

	var formNew = new FormVar('form_new_sonda');
	var formEdit = new FormVar('form_edit_sonda');

	// Formularios

	var form_new = "form_new_sonda";
	var form_edit = "form_edit_sonda";

	var url_get_sonda_form = "/config/getSondaForm", url_create_sonda = "/config/createSonda", url_edit_sonda = "/config/editSonda";

	//Form new

	var sonda_name_new = "#" + form_new + " #sonda_name", sonda_dns_new = "#" + form_new + " #sonda_dns", sonda_group_new = "#" + form_new + " #sonda_group", sonda_plan_new = "#" + form_new + " #sonda_plan", sonda_ip_wan_new = "#" + form_new + " #sonda_ip_wan", sonda_mac_wan_new = "#" + form_new + " #sonda_mac_wan", sonda_group_new = "#" + form_new + " #sonda_group", sonda_mac_wan_new = "#" + form_new + " #sonda_mac_wan", sonda_mac_lan_new = "#" + form_new + " #sonda_mac_lan", sonda_ip_lan_new = "#" + form_new + " #sonda_ip_lan", sonda_netmask_lan_new = "#" + form_new + " #sonda_netmask_lan", sonda_ip_wan_new = "#" + form_new + " #sonda_ip_wan", sonda_tabs_new = "#tabs_" + form_new, allFields_new = $([]).add($(sonda_name_new)).add($(sonda_dns_new)).add($(sonda_ip_wan_new)).add($(sonda_mac_wan_new)), tips_new = $("#" + form_new + "_validateTips"), sonda_secc_identificator_new = "#" + form_new + " #secc_identificator", sonda_secc_plan_new = "#" + form_new + " #secc_plan", sonda_secc_profile_edit = "#" + form_new + " #secc_profile";

	//Form edit

	var sonda_name_edit = "#" + form_edit + " #sonda_name", sonda_dns_edit = "#" + form_edit + " #sonda_dns", sonda_group_edit = "#" + form_edit + " #sonda_group", sonda_plan_edit = "#" + form_edit + " #sonda_plan", sonda_ip_wan_edit = "#" + form_edit + " #sonda_ip_wan", sonda_mac_wan_edit = "#" + form_edit + " #sonda_mac_wan", sonda_group_edit = "#" + form_edit + " #sonda_group", sonda_mac_wan_edit = "#" + form_edit + " #sonda_mac_wan", sonda_mac_lan_edit = "#" + form_edit + " #sonda_mac_lan", sonda_ip_lan_edit = "#" + form_edit + " #sonda_ip_lan", sonda_netmask_lan_edit = "#" + form_edit + " #sonda_netmask_lan", sonda_ip_wan_edit = "#" + form_edit + " #sonda_ip_wan", sonda_tabs_edit = "#tabs_" + form_edit, allFields_edit = $([]).add($(sonda_name_edit)).add($(sonda_dns_edit)).add($(sonda_ip_wan_edit)).add($(sonda_mac_wan_edit)), tips_edit = "#" + form_edit + "_validateTips", sonda_secc_identificator_edit = "#" + form_edit + " #secc_identificator", sonda_secc_plan_edit = "#" + form_edit + " #secc_plan", sonda_secc_profile_edit = "#" + form_edit + " #secc_profile";

	//Set box
	$("#dialog:ui-dialog").dialog("destroy");

	var TabsSonda_new = $("#tabs_" + form_new).tabs();

	// Param Global

	var profile_status = 0;
	var plan_status = 0;
	var auth_metod = 'MAC';

	formNew.get('sonda_mac_wan').mask("**:**:**:**:**:**");
	formNew.get('sonda_mac_lan').mask("**:**:**:**:**:**");
	formNew.get('sonda_ip_lan').ipAddress();
	formNew.get('sonda_netmask_lan').ipAddress();
	formNew.get('sonda_ip_wan').ipAddress();

	function getPlan(form) {
		if (form.get('sonda_group').val() > 0) {

			$.ajax({
				type : "POST",
				url : "/config/getPlanOption",
				data : {
					groupid : form.get('sonda_group').val()
				},
				dataType : "json",
				success : function(data) {
					if (data.status) {
						form.get('sonda_plan').html(data.datos);
					} else {
						alert("Error en el servidor");
					}
				},
				error : function() {
					alert("Error de conexion");
				}
			});

		} else {
			form.get('sonda_plan').html('<option selected value="0">Seleccione un grupo</option>');
		}
	}

	function getProfile(form) {
		if (form.get('sonda_group').val() > 0) {

			$.ajax({
				type : "POST",
				url : "/config/getProfileOption",
				data : {
					groupid : form.get('sonda_group').val()
				},
				dataType : "json",
				success : function(data) {
					if (data.status) {
						form.get('sonda_profile').html(data.datos);
					} else {
						alert("Error en el servidor");
					}
				},
				error : function() {
					alert("Error de conexion");
				}
			});

		} else {
			form.get('sonda_profile').html('<option selected value="0">Seleccione un grupo</option>');
		}
	}

    function tabProfile(form,tabs,idsonda) {

        var tab = tabs.find(".ui-tabs-nav li:eq(3)").remove();
        
        var panelId = tab.attr("aria-controls");
        
        $("#" + panelId).remove();
    
        tabs.tabs("refresh");
    
        var tab_title_input = 'Perfil';
    
        tabs.find(".ui-tabs-nav").append("<li><a href='#tabs-4'>" + tab_title_input + "</a></li>");
    
        $.ajax({
            type : "POST",
            url : "/config/getFormProfile",
            data : "profileid=" + form.get('sonda_profile').val() + "&idsonda=" + idsonda,
            dataType : "json",
            success : function(data) {
                if (data.status) {
                    form.form.append("<div id='tabs-4' style='display: none;'><p>" + data.datos + "</p></div>");
                } else {
                    form.form.append("<div id='tabs-4' style='display: none;'><p>" + data.error + "</p></div>");
                }
            },
            error : function() {
                form.form.append("<div id='tabs-4' style='display: none;'><p>Error de conexion</p></div>");
            }
        });
        
        tabs.tabs("refresh");
    }

	formNew.get('sonda_group').change(function() {

		var tab = TabsSonda_new.find(".ui-tabs-nav li:eq(3)").remove();
		var tab = TabsSonda_new.find(".ui-tabs-nav li:eq(2)").remove();

		formNew.get('sonda_profile').eq(0).prop('selected', true);

		var panelId = tab.attr("aria-controls");
		$("#" + panelId).remove();

		TabsSonda_new.tabs("refresh");

		var tab_title_input = formNew.get("sonda_group").find("option:selected").text();

		TabsSonda_new.find(".ui-tabs-nav").append("<li><a href='#tabs-3'>" + tab_title_input + "</a></li>");

		var profile = 0;
		var plan = 1;
		var method_auth = 'MAC';

		$.ajax({
			type : "POST",
			url : "/config/getFormFeature",
			data : "groupid=" + formNew.get('sonda_group').val(),
			dataType : "json",
			success : function(data) {
				if (data.status) {

					formNew.form.append("<div id='tabs-3' style='display: none;'><p>" + data.datos + "</p></div>");

					if (data.profile == 1) {
						formNew.get('secc_profile').show();
						getProfile(formNew);
					} else {
						formNew.get('secc_profile').hide();
					}

					if (data.method_auth == 'IDENTIFICATIONID') {
						formNew.get('secc_identificator').show();
					} else {
						formNew.get('secc_identificator').hide();
					}

					if (data.plan == 1) {
						formNew.get('secc_plan').show();
						getPlan(formNew);
					} else {
						formNew.get('secc_plan').hide();
						TabsSonda_new.tabs("refresh");
					}

				} else {
					formNew.form.append("<div id='tabs-3' style='display: none;'><p>" + data.error + "</p></div>");
				}
			},
			error : function() {
				formNew.form.append("<div id='tabs-3' style='display: none;'><p>Error de conexion</p></div>");
			}
		});
		TabsSonda_new.tabs("refresh");
	});

	formNew.get('sonda_profile').change(function() {

		var tab = TabsSonda_new.find(".ui-tabs-nav li:eq(3)").remove();
		var panelId = tab.attr("aria-controls");
		$("#" + panelId).remove();

		TabsSonda_new.tabs("refresh");

		var tab_title_input = 'Perfil';

		TabsSonda_new.find(".ui-tabs-nav").append("<li><a href='#tabs-4'>" + tab_title_input + "</a></li>");

		$.ajax({
			type : "POST",
			url : "/config/getFormProfile",
			data : "profileid=" + formNew.get('sonda_profile').val(),
			dataType : "json",
			success : function(data) {
				if (data.status) {
					formNew.form.append("<div id='tabs-4' style='display: none;'><p>" + data.datos + "</p></div>");
				} else {
					formNew.form.append("<div id='tabs-4' style='display: none;'><p>" + data.error + "</p></div>");
				}
			},
			error : function() {
				formNew.form.append("<div id='tabs-4' style='display: none;'><p>Error de conexion</p></div>");
			}
		});
		TabsSonda_new.tabs("refresh");
	});

	$("#modal_sonda_form").dialog({
		autoOpen : false,
		resizable : true,
		height : 'auto',
		width : 600,
		modal : true,
		zIndex : 20000,
		closeOnEscape : true,
		draggable : true,
		open : function() {
			$(this).find('.ui-dialog-titlebar-close').blur();
		},
		buttons : {
			"Crear sonda" : function() {
				var bValid = true;

				formNew.all.removeClass("ui-state-error");
				formNew.all.css("color", "#5A698B");

				bValid = bValid && $.checkLength(formNew.tips, formNew.get('sonda_name'), "Nombre", 3, 200);
				bValid = bValid && $.checkSelect(formNew.tips, formNew.get('sonda_group'), "Grupo", 0);

				if (formNew.get('sonda_plan').css('display') !== 'none') {
					bValid = bValid && $.checkSelect(formNew.tips, formNew.get('sonda_plan'), "Plan", 0);
				}

				bValid = bValid && $.checkLength(formNew.tips, formNew.get('sonda_dns'), "Nombre DNS", 15, 15);

				if (formNew.get('secc_identificator').css('display') !== 'none') {
					bValid = bValid && $.checkLength(formNew.tips, formNew.get('sonda_ip_wan'), "Dirección IP WAN", 6, 15);
					bValid = bValid && $.checkLength(formNew.tips, formNew.get('sonda_mac_wan'), "Mac WAN", 17, 17);
				}

				if (bValid) {

					jQuery('#loading').show();
					jQuery('#imgLoading').show();

					cadena = formNew.form.serialize();

					$.ajax({
						type : "POST",
						url : url_create_sonda,
						data : cadena,
						dataType : "json",
						success : function(data) {
							if (data.status) {
								$("#modal_sonda_form").dialog("close");
								formNew.get('fmGrupo').val(formNew.get('sonda_group').val());
								$(".sondas").flexReload();
							} else {
								$.updateTips(formNew.tips, data.msg);
							}
						},
						error : function() {
							$.updateTips(formNew.tips, "Error de conexion con el servidor");
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
			formNew.tips.text('Todo los elementos del formulario son requeridos');
			formNew.all.val("").removeClass("ui-state-error").css("color", "#5A698B");
			formNew.get('sonda_group').val(0);
			formNew.get('sonda_plan').val(0);
			formNew.get('secc_plan').hide();
			formNew.get('secc_profile').hide();
			formNew.get('secc_identificator').hide();

			var tab = TabsSonda_new.find(".ui-tabs-nav li:eq(2)").remove();
			var tab = TabsSonda_new.find(".ui-tabs-nav li:eq(2)").remove();
			var panelId = tab.attr("aria-controls");
			$("#" + panelId).remove();
			$("tabs").tabs("refresh");
		}
	});

	$("#sondas-delete").dialog({
		autoOpen : false,
		resizable : false,
		height : 150,
		width : 350,
		modal : true,
		zIndex : 20000,
		buttons : {
			"Borrar las sondas seleccionadas" : function() {

				jQuery('#loading').show();
				jQuery('#imgLoading').show();

				var sondasSelect = new Array();
				count = 1;
				grid = $('.sondas');
				$('.trSelected', grid).each(function() {
					var id = $(this).attr('id');
					id = id.substring(id.lastIndexOf('row') + 3);
					if ($.isNumeric(id)) {
						sondasSelect[count] = id;
						count = count + 1;
					}
				});

				$.ajax({
					type : "POST",
					url : "/config/deleteSonda",
					data : {
						idSonda : sondasSelect
					},
					dataType : "json",
					success : function(data) {
						if (data.status) {
							$(".sondas").flexReload();
						} else {
							alert("Acceso Denegado");
						}
					},
					error : function() {
						alert("Error de conexion");
						$("#sondas-delete").dialog("close");
					}
				});
				jQuery('#loading').hide();
				jQuery('#imgLoading').hide();
				$("#sondas-delete").dialog("close");
			},
			Cancel : function() {
				$("#sondas-delete").dialog("close");
			}
		}
	});

	$("#sonda-config").dialog({
		autoOpen : false,
		resizable : false,
		height : 150,
		width : 400,
		zIndex : 20000,
		modal : true,
		buttons : {
			"Configurar las sondas seleccionadas" : function() {

				jQuery('#loading').show();
				jQuery('#imgLoading').show();

				var sondasSelect = new Array();
				count = 1;
				grid = $('.sondas');
				$('.trSelected', grid).each(function() {
					var id = $(this).attr('id');
					id = id.substring(id.lastIndexOf('row') + 3);
					if ($.isNumeric(id)) {
						sondasSelect[count] = id;
						count = count + 1;
					}
				});

				$.ajax({
					type : "POST",
					url : "/config/setConfigSonda",
					data : {
						idSonda : sondasSelect
					},
					dataType : "json",
					success : function(data) {
						if (data.status) {
							$(".sondas").flexReload();
						} else {
							alert("Acceso Denegado");
						}
					},
					error : function() {
						alert("Error de conexion");
						$("#sondas-delete").dialog("close");
					}
				});
				jQuery('#loading').hide();
				jQuery('#imgLoading').hide();
				$("#sonda-config").dialog("close");
			},
			Cancel : function() {
				$("#sonda-config").dialog("close");
			}
		}
	});

	$.editarSonda = function(id, clonar) {

		var dialog = $('<div style="display:none" id="editSondaDialog" class="loading"></div>').appendTo('body');

		if (clonar) {
			var id_set = "clone";
			var type = 'clone';
			var url_send = url_create_sonda;
		} else {
			var type = 'edit';
			var id_set = id;
			var url_send = url_edit_sonda;
		}

		$("#modal_sonda_edit").load(url_get_sonda_form, {
			sondaID : id,
			type : type
		}, function(responseText, textStatus, XMLHttpRequest) {

			formEdit.refresh();

			if (clonar) {
				var name1 = formEdit.get('sonda_name').val();
				formEdit.get('sonda_name').val(name1 + '_1');
			}

			$("#modal_sonda_edit").removeClass('loading');

			formEdit.get('sonda_mac_wan').mask("**:**:**:**:**:**");
			formEdit.get('sonda_mac_lan').mask("**:**:**:**:**:**");
			formEdit.get('sonda_ip_lan').ipAddress();
			formEdit.get('sonda_netmask_lan').ipAddress();
			formEdit.get('sonda_ip_wan').ipAddress();
			
			
			var TabsSonda_edit = $("#tabs_" + form_edit).tabs();
			
            $.ajax({
                type : "POST",
                url : "/config/getFormFeature",
                data : "groupid=" + formEdit.get('sonda_group').val(),
                dataType : "json",
                success : function(data) {
                    if (data.status) {
    
                        formEdit.form.append("<div id='tabs-3' style='display: none;'><p>" + data.datos + "</p></div>");
    
                        if (data.profile == 1) {
                            formEdit.get('secc_profile').show();
                            tabProfile(formEdit,TabsSonda_edit,id_set);
                        } else {
                            formEdit.get('secc_profile').hide();
                        }
    
                        if (data.method_auth == 'IDENTIFICATIONID') {
                            formEdit.get('secc_identificator').show();
                        } else {
                            formEdit.get('secc_identificator').hide();
                        }
    
                        if (data.plan == 1) {
                            formEdit.get('secc_plan').show();
                            //getPlan(formEdit);
                        } else {
                            formEdit.get('secc_plan').hide();
                            TabsSonda_edit.tabs("refresh");
                        }
    
                    } else {
                        formEdit.form.append("<div id='tabs-3' style='display: none;'><p>" + data.error + "</p></div>");
                    }
                },
                error : function() {
                    formEdit.form.append("<div id='tabs-3' style='display: none;'><p>Error de conexion</p></div>");
                }
            });
            TabsSonda_edit.tabs("refresh");
        
			
			

			var TabsSonda_edit = $("#tabs_" + form_edit).tabs({
				add : function(event, ui) {

					var tab_content;

					$.ajax({
						type : "POST",
						url : "/config/getFormFeature",
						data : {
							groupid : formEdit.get('sonda_group').val(),
							id : id_set
						},
						dataType : "json",
						success : function(data) {
							if (data.status) {
								$(ui.panel).append(data.datos);

								if (data.profile == 1) {
									formEdit.get('secc_profile').show();
									// getProfile(formEdit);
									tabProfile(formEdit,TabsSonda_edit,id_set);
								} else {
									formEdit.get('secc_profile').hide();
								}

								if (data.method_auth == 'IDENTIFICATIONID') {
									formEdit.get('secc_identificator').show();
								} else {
									formEdit.get('secc_identificator').hide();
								}

								if (data.plan == 1) {
									formEdit.get('secc_plan').show();
									//getPlan(formEdit);
								} else {
									formEdit.get('secc_plan').hide();
									TabsSonda_edit.tabs("refresh");
								}

							} else {
								$(ui.panel).append('Error en el servidor');
							}
						},
						error : function() {
							$(ui.panel).append('Error de conexion');
						}
					});
					//var tab_content = $( "#new_sonda_tabs_op_"+tabsSelect.val() ).html();
				}
			});

			var tab_title_input = formEdit.get("sonda_group").find("option:selected").text();

			TabsSonda_edit.tabs("add", "#tabs-3", tab_title_input);

			formEdit.get("sonda_group").change(function() {

				var tab = TabsSonda_edit.find(".ui-tabs-nav li:eq(2)").remove();
				var panelId = tab.attr("aria-controls");
				$("#" + panelId).remove();
				$("tabs").tabs("refresh");

				//TabsSonda_edit.tabs( "remove", 2 );

				var tabsSelect = $(sonda_group_edit);
				var tabsSelectText = $(sonda_group_edit + " option:selected").text();
				tab_title_input = tabsSelectText;

				TabsSonda_edit.tabs("add", "#tabs-3", tab_title_input);

				if (tabsSelect.val() > 0) {
					$.ajax({
						type : "POST",
						url : "/config/getPlanOption",
						data : {
							groupid : tabsSelect.val()
						},
						dataType : "json",
						success : function(data) {
							if (data.status) {
								$(sonda_plan_edit).html(data.datos);
							} else {
								alert("Error en el servidor");
							}
						},
						error : function() {
							alert("Error de conexion");
						}
					});
				} else {
					$(sonda_plan_edit).html('<option selected value="0">Seleccione un grupo</option>');
				}
			});

			formEdit.get('sonda_profile').change(function() {

				var tab = TabsSonda_new.find(".ui-tabs-nav li:eq(3)").remove();
				var panelId = tab.attr("aria-controls");
				$("#" + panelId).remove();

				TabsSonda_new.tabs("refresh");

				var tab_title_input = 'Perfil';

				TabsSonda_new.find(".ui-tabs-nav").append("<li><a href='#tabs-4'>" + tab_title_input + "</a></li>");

				$.ajax({
					type : "POST",
					url : "/config/getFormProfile",
					data : "profileid=" + formEdit.get('sonda_profile').val(),
					dataType : "json",
					success : function(data) {
						if (data.status) {
							formEdit.form.append("<div id='tabs-4' style='display: none;'><p>" + data.datos + "</p></div>");
						} else {
							formEdit.form.append("<div id='tabs-4' style='display: none;'><p>" + data.error + "</p></div>");
						}
					},
					error : function() {
						formEdit.form.append("<div id='tabs-4' style='display: none;'><p>Error de conexion</p></div>");
					}
				});
				TabsSonda_new.tabs("refresh");
			});
			

		});

		$("#modal_sonda_edit").dialog({
			resizable : false,
			height : 'auto',
			width : 650,
			zIndex : 20000,
			modal : true,
			cache : false,
			position : ['middle', 20],
			buttons : {
				"Guardar sonda" : function() {

					var bValid = true;

					jQuery('#loading').show();
					jQuery('#imgLoading').show();

                    formEdit.all.removeClass("ui-state-error");
                    formEdit.all.css("color", "#5A698B");
						
    				bValid = bValid && $.checkLength(formEdit.tips, formEdit.get('sonda_name'), "Nombre", 3, 200);
                    bValid = bValid && $.checkSelect(formEdit.tips, formEdit.get('sonda_group'), "Grupo", 0);
    
                    if (formEdit.get('sonda_plan').css('display') !== 'none') {
                        bValid = bValid && $.checkSelect(formEdit.tips, formEdit.get('sonda_plan'), "Plan", 0);
                    }
    
                    bValid = bValid && $.checkLength(formEdit.tips, formEdit.get('sonda_dns'), "Nombre DNS", 15, 15);
    
                    if (formEdit.get('secc_identificator').css('display') !== 'none') {
                        bValid = bValid && $.checkLength(formEdit.tips, formEdit.get('sonda_ip_wan'), "Dirección IP WAN", 6, 15);
                        bValid = bValid && $.checkLength(formEdit.tips, formEdit.get('sonda_mac_wan'), "Mac WAN", 17, 17);
                    }
                    
					if (bValid) {
						$.ajax({
							type : "POST",
							url : url_send,
							dataType : "json",
							data : "id=" + id_set + "&" + formEdit.form.serialize(),
							success : function(data) {
								if (data.status) {
									$("#modal_sonda_edit").dialog("close");
									$(".sondas").flexReload();
								} else {
									$.updateTips(formEdit.tips, data.msg);
								}
							},
							error : function() {
								$.updateTips(formEdit.tips, "Error de conexion con el servidor");
							}
						});

					}
					jQuery('#loading').hide();
					jQuery('#imgLoading').hide();
				},
				Cancel : function() {
					$(this).dialog("close");
				}
			}
		});

	}
});

function statusSonda(id) {

	var status = $('#statusSonda_span_' + id).html();

	if (status == 'Activo') {
		status_value = 0;
	} else {
		status_value = 1;
	}

	$.ajax({
		type : "POST",
		url : "/config/statusSonda",
		data : {
			status : status_value,
			id : id
		},
		dataType : "json",
		success : function(data) {
			if (data.status) {
				if (status == 'Activo') {
					$('#statusSonda_span_' + id).html('Inactivo');
					$('#statusSonda_span_' + id).removeClass("status_on").addClass("status_off");
				} else {
					$('#statusSonda_span_' + id).html('Activo');
					$('#statusSonda_span_' + id).removeClass("status_off").addClass("status_on");
				}
			} else {
				alert("Error interno");
			}
		},
		error : function() {
			alert("Error de conexion con el servidor");
		}
	});
}

function editSonda(id, clonar) {
	$.editarSonda(id, clonar);
	return false;
}

var sendIDButton = '';

$("#modal_sonda_trigger").dialog({
	autoOpen : false,
	resizable : false,
	height : 200,
	width : 450,
	modal : true,
	buttons : {
		"Iniciar Trigger" : function() {

			var bValid = true;

			jQuery('#loading').show();
			jQuery('#imgLoading').show();

			bValid = bValid && $.checkLength($('#config_equipos_trigger_validateTips'), $('#trigger_responsable'), "Responsable", 3, 40);
			bValid = bValid && $.checkSelect($('#config_equipos_trigger_validateTips'), $('#trigger_id'), "Trigger", 0);

			if (bValid) {
				$.ajax({
					type : "POST",
					url : "/config/setTriggerSonda",
					dataType : "json",
					data : "idSonda=" + sendIDButton + "&" + $('#config_equipos_trigger').serialize(),
					success : function(data) {
						if (data.status) {
							$("#modal_sonda_trigger").dialog("close");
						} else {
							alert("Acceso Denegado");
						}
					},
					error : function() {
						alert("Error de conexion");
					}
				});
			}
			jQuery('#loading').hide();
			jQuery('#imgLoading').hide();

		},
		Cancel : function() {
			$("#modal_sonda_trigger").dialog("close");
		}
	}

});

function triggerSonda(id) {
	sendIDButton = id;
	$("#modal_sonda_trigger").dialog("open");
}

function configSonda(id) {

	var sondasSelect = new Array();
	sondasSelect[0] = id;

	$.ajax({
		type : "POST",
		url : "/config/setConfigSonda",
		data : {
			idSonda : sondasSelect
		},
		dataType : "json",
		success : function(data) {
			if (data.status) {
				alert("Configuracion informada");
			} else {
				alert("Acceso Denegado");
			}
		},
		error : function() {
			alert("Error de conexion");
			$("#sondas-delete").dialog("close");
		}
	});
}