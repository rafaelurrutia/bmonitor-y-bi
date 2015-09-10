jQuery(function( $ ) {

	var plan_plan = $("#plan_plan"), plan_plandesc = $("#plan_plandesc"), plan_planname = $("#plan_planname"), plan_nacD = $("#plan_nacD"), plan_nacDS = $("#plan_nacDS"), plan_nacDT = $("#plan_nacDT"), plan_nacU = $("#plan_nacU"), plan_nacUS = $("#plan_nacUS"), plan_nacUT = $("#plan_nacUT"), plan_locD = $("#plan_locD"), plan_locDS = $("#plan_locDS"), plan_locDT = $("#plan_locDT"), plan_locU = $("#plan_locU"), plan_locUS = $("#plan_locUS"), plan_locUT = $("#plan_locUT"), plan_intD = $("#plan_intD"), plan_intDS = $("#plan_intDS"), plan_intDT = $("#plan_intDT"), plan_intU = $("#plan_intU"), plan_intUS = $("#plan_intUS"), plan_intUT = $("#plan_intUT"), plan_sysctl = $("#plan_sysctl"), plan_ppp = $("#plan_ppp"), allFieldsPlan = $([]).add(plan_plan).add(plan_plandesc).add(plan_planname), tips = $(".validateTipsPlan");

	$("#modal_plan_form").dialog({
		autoOpen : false,
		resizable : false,
		height : 'auto',
		width : 700,
		modal : true,
		closeOnEscape : true,
		draggable : true,
		open : function( ) {
			$(this).find('.ui-dialog-titlebar-close').blur();
			$("#planes_groups").multiselect();
		},
		buttons : {
			"Crear plan" : function( ) {
				var bValid = true;

				allFieldsPlan.removeClass("ui-state-error");
				allFieldsPlan.css("color", "#5A698B");

				bValid = bValid && $.checkLength(tips, plan_plan, "Nombre", 3, 100);
				bValid = bValid && $.checkLength(tips, plan_plandesc, "Descripción", 3, 100);
				bValid = bValid && $.checkLength(tips, plan_planname, "Descripción corta", 3, 100);

				bValid = bValid && $.checkLength(tips, plan_nacD, "NAC Bajada", 1, 100);
				bValid = bValid && $.checkLength(tips, plan_nacDS, "NAC Bajada", 1, 100);
				bValid = bValid && $.checkLength(tips, plan_nacDT, "NAC Bajada", 1, 100);

				if ( bValid ) {

					jQuery('#loading').show();
					jQuery('#imgLoading').show();

					cadena = $('#form_new_plan').serialize();
					order = $('#monitor_groupid_2').sortable('serialize');

					var serialStr = "";
					var order = "";

					$("ul.monitor_groupid_2 li").each(function( i, elm ) {
						serialStr += ( i > 0 ? "|" : "" ) + i + ":" + $(elm).attr("id");
					});

					$.ajax({
						type : "POST",
						url : "/config/createPlan",
						data : {
							cadena_1 : cadena,
							cadena_2 : serialStr,
							idValue : "new"
						},
						dataType : "json",
						success : function( data ) {
							if ( data.status ) {
								$("#modal_plan_form").dialog("close");
								$("#table_planes").flexReload();
							} else {
								$.updateTips(tips, "Error al crear plan");
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
			tips.text('Todo los elementos del formulario son requeridos');
			allFieldsPlan.val("").removeClass("ui-state-error");
			allFieldsPlan.css("color", "#5A698B");
		}

	});

	$("#modal_plan_delete").dialog({
		autoOpen : false,
		resizable : false,
		width : 500,
		modal : true,
		open : function( ) {
			var countSelectPlan = 1;
			$("#modal_plan_delete p#selected").html("Selected:");
			$('.trSelected', $('#table_planes')).each(function( ) {
				var name = $(this).children('td:nth-child(2)').text();
				$("#modal_plan_delete p#selected").append("</br>" + countSelectPlan + " : " + name);
				countSelectPlan = countSelectPlan + 1;
			});
		},
		buttons : {
			"Accept" : function( ) {

				jQuery('#loading').show();
				jQuery('#imgLoading').show();

				var select = new Array( );
				count = 1;
				grid = $('#table_planes');
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
					url : "/config/deletePlan",
					data : {
						idSelect : select,
						groupID : $('#fmGrupoPlanes').val()
					},
					dataType : "json",
					success : function( data ) {
						if ( data.status ) {
							$("#table_planes").flexReload();
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
				$("#modal_plan_delete").dialog("close");
			},
			Cancel : function( ) {
				$("#modal_plan_delete").dialog("close");
			}

		}
	});
});

function editPlan( id, clonar ) {
	var url = "/config/getPlanForm";

	var dialog = $('<div style="display:none" id="editPlanDialog" class="loading"></div>').appendTo('body');

	if ( clonar ) {
		var url_set = "/config/createPlan";
		var id_set = "clone";
	} else {
		var url_set = "/config/createPlan";
		var id_set = id;
	}

	$("#modal_plan_edit").dialog({
		autoOpen : false,
		resizable : false,
		height : 'auto',
		width : 700,
		modal : true,
		closeOnEscape : true,
		draggable : true,
		open : function( ) {
			$(this).find('.ui-dialog-titlebar-close').blur();
		},
		buttons : {
			"Accept" : function( ) {
				var bValid = true;

				var plan_plan = $("#plan_edit_plan"), plan_plandesc = $("#plan_edit_plandesc"), plan_planname = $("#plan_edit_planname"), plan_nacD = $("#plan_edit_nacD"), plan_nacDS = $("#plan_edit_nacDS"), plan_nacDT = $("#plan_edit_nacDT"), allFieldsPlan = $([]).add(plan_plan).add(plan_plandesc).add(plan_planname), tipsEdit = $(".validateTipsPlanEdit");

				allFieldsPlan.removeClass("ui-state-error");
				allFieldsPlan.css("color", "#5A698B");

				bValid = bValid && $.checkLength(tipsEdit, plan_plan, "Nombre", 3, 100);
				bValid = bValid && $.checkLength(tipsEdit, plan_plandesc, "Descripción", 3, 100);
				bValid = bValid && $.checkLength(tipsEdit, plan_planname, "Descripción corta", 3, 100);

				bValid = bValid && $.checkLength(tipsEdit, plan_nacD, "NAC Bajada", 1, 100);
				bValid = bValid && $.checkLength(tipsEdit, plan_nacDS, "NAC Bajada", 1, 100);
				bValid = bValid && $.checkLength(tipsEdit, plan_nacDT, "NAC Bajada", 1, 100);

				if ( bValid ) {

					jQuery('#loading').show();
					jQuery('#imgLoading').show();

					cadena = $('#form_edit_plan').serialize();
					order = $('#plan_edit_groupid_2').sortable('serialize');

					var serialStr = "";
					var order = "";

					$("#plan_edit_groupid_2 li").each(function( i, elm ) {
						serialStr += ( i > 0 ? "|" : "" ) + i + ":" + $(elm).attr("id");
					});

					$.ajax({
						type : "POST",
						url : "/config/createPlan",
						data : {
							cadena_1 : cadena,
							cadena_2 : serialStr,
							idValue : id_set
						},
						dataType : "json",
						success : function( data ) {
							if ( data.status ) {
								$("#modal_plan_edit").dialog("close");
								$("#table_planes").flexReload();
							} else {
								$.updateTips(tipsEdit, "Error al crear sonda");
							}
						},
						error : function( ) {
							$.updateTips(tipsEdit, "Error de conexion con el servidor");
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
			$(".validateTipsPlanEdit").text('Todo los elementos del formulario son requeridos');
			var plan_plan = $("#plan_edit_plan"), plan_plandesc = $("#plan_edit_plandesc"), plan_planname = $("#plan_edit_planname"), plan_nacD = $("#plan_edit_nacD"), plan_nacDS = $("#plan_edit_nacDS"), plan_nacDT = $("#plan_edit_nacDT"), allFieldsPlan = $([]).add(plan_plan).add(plan_plandesc).add(plan_planname);

			allFieldsPlan.val("").removeClass("ui-state-error");
			allFieldsPlan.css("color", "#5A698B");
		}

	});

	$("#modal_plan_edit").load(url, {
		IDSelect : id
	}, // omit this param object to issue a GET request instead a POST request, otherwise you may provide
	// post parameters within the object
	function( responseText, textStatus, XMLHttpRequest ) {
		// remove the loading class
		$("#modal_plan_edit").removeClass('loading');

		if ( clonar ) {
			var name1 = $("#plan_edit_plan").val();
			$("#plan_edit_plan").val(name1 + '_1');
		}

		// para ahorrar un poco de espacio voy a definir a las listas como variables
		var $lista1 = $('#plan_edit_groupid_1'), $lista2 = $('#plan_edit_groupid_2');
		// lista 1
		$('li', $lista1).draggable({
			revert : 'invalid',
			helper : 'clone',
			cursor : 'move'
		});
		$lista1.droppable({
			accept : '#plan_edit_groupid_2 li',
			drop : function( ev, ui ) {
				$.deleteLista2(ui.draggable, $lista1);
			}

		});
		/* lista2 */
		$('li', $lista2).draggable({
			revert : 'invalid',
			helper : 'clone',
			cursor : 'move'
		});
		$lista2.droppable({
			accept : '#plan_edit_groupid_1 > li',
			drop : function( ev, ui ) {
				$.deleteLista1(ui.draggable, $lista2);
			}

		});

		$("#modal_plan_edit").dialog("open");
	});

	return false;
}