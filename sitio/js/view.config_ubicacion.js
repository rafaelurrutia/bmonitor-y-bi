jQuery(function($) {

	var formNew = new $.formVar('form_new_location');

	var formEdit = new $.formVar('form_edit_location');

	var table_1 = $('.table_ubicacion');

	var modal_new = $('#modal_ubicacion_new');
	var modal_new_url = '/config/createUbicacion';
	var modal_new_form = $('#config_ubicacion_form_new');

	var modal_delete = $('#modal_ubicacion_delete');
	var modal_delete_url = '/config/deleteubicacion';

	var modal_edit = '#modal_ubicacion_edit';
	var modal_edit_url_form = '/config/getUbicacionForm';
	var modal_edit_form = '#config_ubicacion_form_edit';

	formNew.get('location_REGION_ID').change(function() {
		$.post("/config/ubicacion", {
			id : $(this).val(),
			type : 'provincia'
		}, function(data) {
			formNew.get('location_PROVINCIA_ID').html(data);
		});
		formNew.get('formNew').html('<option selected value="0">Seleccionar Provincia</option>');
	});

	formNew.get('location_PROVINCIA_ID').change(function() {
		$.post("/config/ubicacion", {
			id : $(this).val(),
			type : 'comuna'
		}, function(data) {
			formNew.get('location_COMUNA_ID').html(data);
		});
	});

	modal_new.dialog({
		autoOpen : false,
		resizable : false,
		height : 'auto',
		width : 700,
		modal : true,
		closeOnEscape : true,
		draggable : true,
		open : function() {
			modal_new.find('.ui-dialog-titlebar-close').blur();
		},
		buttons : {
			"Guardar" : function() {

				var bValid = true;

				formNew.all.removeClass("ui-state-error");
				formNew.all.css("color", "#5A698B");

				bValid = bValid && $.checkLength(formNew.tips, formNew.get('location_establecimiento'), "Nombre Localidad", 3, 200);
				bValid = bValid && $.checkLength(formNew.tips, formNew.get('location_nodo'), "Nodo", 1, 100);
				bValid = bValid && $.checkLength(formNew.tips, formNew.get('location_rdb'), "Código localidad", 1, 100);
				bValid = bValid && $.checkLength(formNew.tips, formNew.get('location_tarifa'), "Renta Mensual $", 3, 100);
				bValid = bValid && $.checkSelect(formNew.tips, formNew.get('location_planfdt'), "Plan", 0);

				if (bValid) {

					jQuery('#loading').show();
					jQuery('#imgLoading').show();

					$.ajax({
						type : "POST",
						url : modal_new_url,
						data : "id=new" + "&" + formNew.form.serialize(),
						dataType : "json",
						success : function(data) {
							if (data.status) {
								modal_new.dialog("close");
								table_1.flexReload();
							} else {
								$.updateTips(formNew.tips, data.error);
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
				modal_new.dialog("close");
			}
		},
		close : function() {
			formNew.tips.text('Todo los elementos del formulario son requeridos');
			formNew.all.val("").removeClass("ui-state-error").css("color", "#5A698B");
		}
	});

	modal_delete.dialog({
		autoOpen : false,
		resizable : false,
		height : 150,
		width : 400,
		modal : true,
		buttons : {
			"Borrar los registros seleccionados" : function() {

				jQuery('#loading').show();
				jQuery('#imgLoading').show();

				var select = new Array();
				count = 1;
				$('.trSelected', table_1).each(function() {
					var id = $(this).attr('id');
					id = id.substring(id.lastIndexOf('row') + 3);
					if ($.isNumeric(id)) {
						select[count] = id;
						count = count + 1;
					}
				});

				$.ajax({
					type : "POST",
					url : modal_delete_url,
					data : {
						idSelect : select
					},
					dataType : "json",
					success : function(data) {
						if (data.status) {
							table_1.flexReload();
							modal_delete.dialog("close");
						} else {
							alert(data.msg);
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

		var id_set = id;

		$.ajax({
			type : "POST",
			url : modal_edit_url_form,
			data : {
				IDSelect : id
			},
			dataType : "json",
			success : function(data) {
				if (data.status) {
					$(modal_edit).html(data.html);
					$(modal_edit).removeClass('loading');
					var diferencia = data.diferencia;
					$(modal_edit).dialog("open");
					formEdit.refresh();

					formEdit.get('location_REGION_ID').change(function() {
						$.post("/config/ubicacion", {
							id : $(this).val(),
							type : 'provincia'
						}, function(data) {
							formEdit.get('location_PROVINCIA_ID').html(data);
						});
						formEdit.get('formNew').html('<option selected value="0">Seleccionar Provincia</option>');
					});

					formEdit.get('location_PROVINCIA_ID').change(function() {
						$.post("/config/ubicacion", {
							id : $(this).val(),
							type : 'comuna'
						}, function(data) {
							formEdit.get('location_COMUNA_ID').html(data)
						});
					});

				}
			},
			error : function() {
				alert("Error de conexion");
			}
		});

		$(modal_edit).dialog({
			autoOpen : false,
			resizable : true,
			height : 'auto',
			width : 700,
			modal : true,
			closeOnEscape : true,
			draggable : true,
			open : function() {
				$(this).find('.ui-dialog-titlebar-close').blur();
			},
			buttons : {
				"Guardar" : function() {

					var bValid = true;

					formEdit.all.removeClass("ui-state-error");
					formEdit.all.css("color", "#5A698B");

					bValid = bValid && $.checkLength(formEdit.tips, formEdit.get('location_establecimiento'), "Nombre Localidad", 3, 200);
					bValid = bValid && $.checkLength(formEdit.tips, formEdit.get('location_nodo'), "Nodo", 1, 100);
					bValid = bValid && $.checkLength(formEdit.tips, formEdit.get('location_rdb'), "Código localidad", 1, 100);
					bValid = bValid && $.checkLength(formEdit.tips, formEdit.get('location_tarifa'), "Renta Mensual $", 3, 100);
					bValid = bValid && $.checkNumeric(formEdit.tips, formEdit.get('location_tarifa'), "Renta Mensual $");
					bValid = bValid && $.checkSelect(formEdit.tips, formEdit.get('location_planfdt'), "Plan", 0);

					if (bValid) {

						jQuery('#loading').show();
						jQuery('#imgLoading').show();

						$.ajax({
							type : "POST",
							url : modal_new_url,
							data : "id=" + id_set + "&" + formEdit.form.serialize(),
							dataType : "json",
							success : function(data) {
								if (data.status) {
									$(modal_edit).dialog("close");
									$(table_1).flexReload();
								} else {
									$.updateTips(formEdit.tips, "Error al crear registro");
								}
							},
							error : function() {
								$.updateTips(formEdit.tips, "Error de conexion con el servidor");
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
				formEdit.tips.text('Todo los elementos del formulario son requeridos');
				formEdit.all.val("").removeClass("ui-state-error").css("color", "#5A698B");
			}
		});
	}
});

function edit(id, clone) {
	$.editarModal(id, clone);
}