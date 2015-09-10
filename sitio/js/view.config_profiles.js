//Start Section 1

var tableProfile = $('#tableProfile');

var modalNewProfile = $('#modalNewProfile');
var modalNewProfileUrl = '/profiles/createProfile';
var modalNewProfileForm = new formVar('formNewProfile');

var modalDeleteProfile = $('#modalDeleteProfile');
var modalDeleteProfileUrl = '/profiles/deleteProfile';

var modalEditProfile = '#modalEditValue';
var modalEditProfileUrl = '/config/getFormProfile';
var modalEditProfileForm = new formVar('formEditProfile');

//End section 1

//Start Section 2

var tableCategories = 'tableCategories';

var modalNewCategories = $('#modalNewCategories');
var modalNewCategoriesUrl = '/profiles/createCategories';
var modalNewCategoriesForm = new formVar('formNewCategories');

var modalEditCategories = $('#modalEditCategories');
var modalEditCategoriesUrl = '/profiles/editCategories';
var modalEditCategoriesForm = new formVar('formEditCategories');

var modalDeleteCategories = $('#modalDeleteCategories');
var modalDeleteCategoriesUrl = '/profiles/deleteCategories';

//End section 2

//Start Section 3

var tableItem = 'tableItem';

var modalNewItemParam = $('#modalNewItemParam');
var modalNewItemParamUrl = '/profiles/createItem';
var modalNewItemParamForm = new formVar('formNewItemParam');

var modalNewItemMonitor = $('#modalNewItemMonitor');
var modalNewItemMonitorForm = new formVar('formNewItemMonitor');

var modalDeleteItem = $('#modalDeleteItem');
var modalDeleteItemUrl = '/profiles/deleteItem';

//End section 3

//Start section 1 Categorias

var tableCategoriesValue = 'tableCategoriesValue';

var tableCategoryValue = 'tableCategoryValue';

var modalNewCategoryValue = $('#modalNewCategoryValue');
var modalNewCategoryValueUrl = '/profiles/createCategoryValue';
var modalNewCategoryValueGetForm = '/profiles/getFormCategoryValue';
var modalNewCategoryValueForm = new formVar('formNewCategoryValue');

var modalEditCategoryValue = $('#modalEditCategoryValue');
var modalEditCategoryValueUrl = '/profiles/editCategoryValue';
var modalEditCategoryValueForm = new formVar('formEditCategoryValue');

var modalDeleteCategoryValue = $('#modalDeleteCategoryValue');
var modalDeleteCategoryValueUrl = '/profiles/deleteCategoryValue';

//End section 1 Categorias

jQuery(function($) {

	//Section 1

	modalNewProfile.dialog({
		autoOpen : false,
		resizable : false,
		height : 'auto',
		width : 623,
		modal : true,
		zIndex : 20000,
		closeOnEscape : true,
		draggable : true,
		open : function() {
			$(this).find('.ui-dialog-titlebar-close').blur();
			modalNewProfileForm.get('groupid').multiselect();
		},
		buttons : {
			"Crear perfil" : function() {
				var bValid = true;

				modalNewProfileForm.all.removeClass("ui-state-error");
				modalNewProfileForm.all.css("color", "#5A698B");

				bValid = bValid && $.checkLength(modalNewProfileForm.tips, modalNewProfileForm.get('name'), "Nombre", 3, 200);

				bValid = bValid && $.checkSelect(modalNewProfileForm.tips, modalNewProfileForm.get('groupid'), "Grupo", 0);

				if (bValid) {

					jQuery('#loading').show();
					jQuery('#imgLoading').show();

					$.ajax({
						type : "POST",
						url : modalNewProfileUrl,
						data : modalNewProfileForm.form.serialize(),
						dataType : "json",
						success : function(data) {
							if (data.status) {
								modalNewProfile.dialog("close");
								tableProfile.flexReload();
							} else {
								$.updateTips(modalNewProfileForm.tips, data.error);
							}
						},
						error : function() {
							$.updateTips(modalNewProfileForm.tips, "Error de conexion con el servidor");
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
			modalNewProfileForm.get('name').val("");
			modalNewProfileForm.get('groupid').multiselect('destroy');
			modalNewProfileForm.tips.text('Todo los elementos del formulario son requeridos');
			modalNewProfileForm.all.val("").removeClass("ui-state-error").css("color", "#5A698B");
		}
	});

	modalDeleteProfile.dialog({
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
			"Borrar" : function() {

				var select = new Array();
				count = 0;
				$('.trSelected', tableProfile).each(function() {
					var id = $(this).attr('id').substr(3);
					if ($.isNumeric(id)) {
						select[count] = id;
						count = count + 1;
					}
				});

				jQuery('#loading').show();
				jQuery('#imgLoading').show();

				$.ajax({
					type : "POST",
					url : modalDeleteProfileUrl,
					data : {
						id : select
					},
					dataType : "json",
					success : function(data) {
						if (data.status) {
							modalDeleteProfile.dialog("close");
							tableProfile.flexReload();
						} else {
							alert(data.error);
						}
					},
					error : function() {
						alert("Error de conexion con el servidor");
					}
				});

				jQuery('#loading').hide();
				jQuery('#imgLoading').hide();

			},
			Cancel : function() {
				$(this).dialog("close");
			}
		},
		close : function() {

		}
	});

	//Section 2

	$.structureProfile = function(profileid) {

		$('#containerProfile').append('<div style="float: left;" id="' + tableCategories + '"></div>');

		tableCategoriesAPI = $("#" + tableCategories).flexigrid({
			url : '/profiles/getTableCategories/' + profileid,
			title : 'Profile -> Categories',
			idFlexigrid : tableCategories,
			dataType : 'json',
			colModel : [{
				display : 'Categoria',
				name : 'category',
				width : '100',
				sortable : true,
				align : 'left'
			}, {
				display : 'Clase',
				name : 'class',
				width : '100',
				sortable : true,
				align : 'left'
			}, {
				display : 'Accion',
				name : 'action',
				width : '174',
				sortable : false,
				align : 'left'
			}],
			buttons : [{
				name : 'Nuevo',
				bclass : 'add',
				onpress : toolboxCategories,
				option : profileid
			}, {
				name : 'Borrar',
				bclass : 'delete',
				onpress : toolboxCategories
			}, {
				separator : true
			}, {
				name : 'Cerrar',
				bclass : 'close',
				onpress : toolboxCategories
			}, {
				separator : true
			}],
			usepager : true,
			useRp : true,
			rp : 15,
			showTableToggleBtn : false,
			resizable : false,
			width : 300,
			onSuccess : function() {
				$("#toolbar #toolbarSet").buttonset();
			}
		});

		$('#tableProfile').hide();
	};
	
	var modalNewCategoriesUrlSet = false;

	function toolboxCategories(com, grid, profileid) {
		if (com == 'Nuevo') {

			modalNewCategoriesUrlSet = modalNewCategoriesUrl + '/' + profileid;

			modalNewCategories.dialog("open");

		} else if (com == 'Borrar') {
			lengthSelect = $('.trSelected', grid).length;
			if (lengthSelect > 0) {
				modalDeleteCategories.dialog("open");
			}
		} else if (com == 'Cerrar') {
			$('#' + tableCategories).remove();
			$('#tableProfile').show();
		}
	}


	modalNewCategories.dialog({
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
			"Crear perfil" : function() {
				var bValid = true;

				modalNewCategoriesForm.all.removeClass("ui-state-error");
				modalNewCategoriesForm.all.css("color", "#5A698B");

				bValid = bValid && $.checkLength(modalNewCategoriesForm.tips, modalNewCategoriesForm.get('name'), "Nombre", 2, 200);

				bValid = bValid && $.checkLength(modalNewCategoriesForm.tips, modalNewCategoriesForm.get('class'), "Clase", 2, 200);

				if (bValid) {

					jQuery('#loading').show();
					jQuery('#imgLoading').show();

					$.ajax({
						type : "POST",
						url : modalNewCategoriesUrlSet,
						data : modalNewCategoriesForm.form.serialize(),
						dataType : "json",
						success : function(data) {
							if (data.status) {
								modalNewCategories.dialog("close");
								tableCategoriesAPI.flexReload();
							} else {
								$.updateTips(modalNewCategoriesForm.tips, data.error);
							}
						},
						error : function() {
							$.updateTips(modalNewCategoriesForm.tips, "Error de conexion con el servidor");
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
			modalNewCategoriesForm.get('name').val("");
			modalNewCategoriesForm.get('class').val("");
			modalNewCategoriesForm.tips.text('Todo los elementos del formulario son requeridos');
			modalNewCategoriesForm.all.val("").removeClass("ui-state-error").css("color", "#5A698B");
		}
	});

	modalDeleteCategories.dialog({
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
			"Borrar" : function() {

				var select = new Array();
				count = 0;
				$('.trSelected', tableCategoriesAPI).each(function() {
					var id = $(this).attr('id').substr(3);
					if ($.isNumeric(id)) {
						select[count] = id;
						count = count + 1;
					}
				});

				jQuery('#loading').show();
				jQuery('#imgLoading').show();

				$.ajax({
					type : "POST",
					url : modalDeleteCategoriesUrl,
					data : {
						id : select
					},
					dataType : "json",
					success : function(data) {
						if (data.status) {
							modalDeleteCategories.dialog("close");
							tableCategoriesAPI.flexReload();
						} else {
							alert(data.error);
						}
					},
					error : function() {
						alert("Error de conexion con el servidor");
					}
				});

				jQuery('#loading').hide();
				jQuery('#imgLoading').hide();

			},
			Cancel : function() {
				$(this).dialog("close");
			}
		},
		close : function() {

		}
	});

	// section 3

	$.getItem = function(categoriesid, type) {

		$('#containerProfile').append('<div style="float: left;" id="' + tableItem + '"></div>');

		if (type == 'param') {
			section = 'Parametros';
		} else {
			section = 'Monitores';
		}

		tableItemAPI = $("#" + tableItem).flexigrid({
			url : '/profiles/getTableItem/' + categoriesid + "/" + type,
			title : 'Profile -> Categories -> ' + section,
			dataType : 'json',
			idFlexigrid : tableItem,
			colModel : [{
				display : 'Item',
				name : 'item',
				width : '100',
				sortable : true,
				align : 'left'
			}, {
				display : 'Display',
				name : 'item_display',
				width : '100',
				sortable : true,
				align : 'left'
			}, {
				display : 'Tipo',
				name : 'type_result',
				width : '174',
				sortable : false,
				align : 'left'
			}, {
				display : 'Accion',
				name : 'action',
				width : '174',
				sortable : false,
				align : 'left'
			}],
			buttons : [{
				name : 'Nuevo',
				bclass : 'add',
				onpress : toolboxItem,
				option : {
					id : categoriesid,
					type : type
				}
			}, {
				name : 'Borrar',
				bclass : 'delete',
				onpress : toolboxItem
			}, {
				name : 'Regresar',
				bclass : 'ui-icon ui-icon-circle-triangle-w',
				onpress : toolboxItem
			}],
			usepager : true,
			useRp : true,
			rp : 15,
			showTableToggleBtn : false,
			resizable : false,
			width : 300,
			onSuccess : function() {
				$("#toolbar #toolbarSet").buttonset();
			}
		});

		$('#' + tableCategories).hide();
	}
	function toolboxItem(com, grid, data) {
		if (com == 'Nuevo') {

			modalNewItemUrlSet = modalNewItemParamUrl + '/' + data.id;

			if (data.type == 'param') {
				modalNewItemParam.dialog("open");
			} else if (data.type == 'result') {
				modalNewItemMonitor.dialog("open");
			} else {
				console.log("Error / option : " + data.type);
			}

		} else if (com == 'Borrar') {
			lengthSelect = $('.trSelected', grid).length;
			if (lengthSelect > 0) {
				modalDeleteItem.dialog("open");
			}
		} else if (com == 'Regresar') {
			$("#" + tableItem).remove();
			$('#' + tableCategories).show();
		}
	}


	modalNewItemParam.dialog({
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
			"Crear perfil" : function() {
				var bValid = true;

				modalNewItemParamForm.all.removeClass("ui-state-error");
				modalNewItemParamForm.all.css("color", "#5A698B");

				bValid = bValid && $.checkLength(modalNewItemParamForm.tips, modalNewItemParamForm.get('name'), "Nombre", 2, 200);
				bValid = bValid && $.checkLength(modalNewItemParamForm.tips, modalNewItemParamForm.get('display'), "Display", 2, 200);

				if (bValid) {

					jQuery('#loading').show();
					jQuery('#imgLoading').show();

					$.ajax({
						type : "POST",
						url : modalNewItemUrlSet,
						data : modalNewItemParamForm.form.serialize(),
						dataType : "json",
						success : function(data) {
							if (data.status) {
								modalNewItemParam.dialog("close");
								tableItemAPI.flexReload();
							} else {
								$.updateTips(modalNewItemParamForm.tips, data.error);
							}
						},
						error : function() {
							$.updateTips(modalNewItemParamForm.tips, "Error de conexion con el servidor");
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
			modalNewItemParamForm.get('name').val("");
			modalNewItemParamForm.get('display').val("");
			modalNewItemParamForm.get('typeData').prop("selectedIndex", 1);
			modalNewItemParamForm.get('default').val("");
			modalNewItemParamForm.tips.text('Todo los elementos del formulario son requeridos');
			modalNewItemParamForm.all.removeClass("ui-state-error").css("color", "#5A698B");
		}
	});

	modalNewItemMonitor.dialog({
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
			$(".reportCheck").buttonset();
		},
		buttons : {
			"Crear perfil" : function() {
				var bValid = true;

				modalNewItemMonitorForm.all.removeClass("ui-state-error");
				modalNewItemMonitorForm.all.css("color", "#5A698B");

				bValid = bValid && $.checkLength(modalNewItemMonitorForm.tips, modalNewItemMonitorForm.get('name'), "Nombre", 2, 200);
				bValid = bValid && $.checkLength(modalNewItemMonitorForm.tips, modalNewItemMonitorForm.get('display'), "Display", 2, 200);

				if (bValid) {

					jQuery('#loading').show();
					jQuery('#imgLoading').show();

					$.ajax({
						type : "POST",
						url : modalNewItemUrlSet,
						data : modalNewItemMonitorForm.form.serialize(),
						dataType : "json",
						success : function(data) {
							if (data.status) {
								modalNewItemMonitor.dialog("close");
								tableItemAPI.flexReload();
							} else {
								$.updateTips(modalNewItemMonitorForm.tips, data.error);
							}
						},
						error : function() {
							$.updateTips(modalNewItemMonitorForm.tips, "Error de conexion con el servidor");
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
			modalNewItemMonitorForm.get('name').val("");
			modalNewItemMonitorForm.get('display').val("");
			modalNewItemMonitorForm.get('default').val("");
			modalNewItemMonitorForm.get('unit').val("");
			modalNewItemMonitorForm.get('typeData').prop("selectedIndex", 1);
			modalNewItemMonitorForm.tips.text('Todo los elementos del formulario son requeridos');
			modalNewItemMonitorForm.all.removeClass("ui-state-error").css("color", "#5A698B");
		}
	});

	modalDeleteItem.dialog({
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
			"Borrar" : function() {

				var select = new Array();
				count = 0;
				$('.trSelected', tableItemAPI).each(function() {
					var id = $(this).attr('id').substr(3);
					if ($.isNumeric(id)) {
						select[count] = id;
						count = count + 1;
					}
				});

				jQuery('#loading').show();
				jQuery('#imgLoading').show();

				$.ajax({
					type : "POST",
					url : modalDeleteItemUrl,
					data : {
						id : select
					},
					dataType : "json",
					success : function(data) {
						if (data.status) {
							modalDeleteItem.dialog("close");
							tableItemAPI.flexReload();
						} else {
							alert(data.error);
						}
					},
					error : function() {
						alert("Error de conexion con el servidor");
					}
				});

				jQuery('#loading').hide();
				jQuery('#imgLoading').hide();

			},
			Cancel : function() {
				$(this).dialog("close");
			}
		},
		close : function() {

		}
	});

	// Section Categorias del perfil (Asignar monitores y parametros)

	$.categoriesProfile = function(categoriesid) {

		$('#containerProfile').html('<div style="float: left;" id="' + tableCategoriesValue + '"></div>');

		tableCategoriesValueAPI = $('#' + tableCategoriesValue).flexigrid({
			url : '/profiles/getTableCategoriesValue/' + categoriesid,
			title : 'Profile -> Categories Value',
			idFlexigrid : tableCategoriesValue,
			dataType : 'json',
			colModel : [{
				display : 'Categoria',
				name : 'category',
				width : '100',
				sortable : true,
				align : 'left'
			}, {
				display : 'Clase',
				name : 'class',
				width : '100',
				sortable : true,
				align : 'left'
			}, {
				display : 'Accion',
				name : 'action',
				width : '119',
				sortable : false,
				align : 'left'
			}],
			buttons : [{
				name : 'Regresar',
				bclass : 'ui-icon-circle-triangle-w',
				onpress : toolboxCategoriesValue
			}],
			usepager : true,
			useRp : true,
			rp : 15,
			showTableToggleBtn : false,
			resizable : false,
			width : 300,
			onSuccess : function() {
				$("#toolbar #toolbarSet").buttonset();
			}
		});
		$('#tableProfile').hide();
	}
	
	function toolboxCategoriesValue(com, grid, profileid) {
		if (com == 'Regresar') {
			$("#" + tableCategoriesValue).remove();
			$('#tableProfile').show();
		}
	}


	$.getCategoryValue = function(categoriesid) {
		$('#containerProfile').append('<div style="float: left;" id="' + tableCategoryValue + '"></div>');

		tableCategoryValueAPI = $('#' + tableCategoryValue).flexigrid({
			url : '/profiles/getTableCategoryValue/' + categoriesid,
			title : 'Profile -> Categories Value',
			idFlexigrid : tableCategoryValue,
			dataType : 'json',
			colModel : [{
				display : 'Categoria',
				name : 'category',
				width : '100',
				sortable : true,
				align : 'left'
			}, {
				display : 'Display',
				name : 'display',
				width : '100',
				sortable : true,
				align : 'left'
			}, {
				display : 'Accion',
				name : 'action',
				width : '174',
				sortable : false,
				align : 'left'
			}],
			buttons : [{
				name : 'Nuevo',
				bclass : 'new',
				onpress : toolboxCategoyValue,
				option : categoriesid
			}, {
				name : 'Borrar',
				bclass : 'close',
				onpress : toolboxCategoyValue
			}, {
				name : 'Regresar',
				bclass : 'ui-icon-circle-triangle-w',
				onpress : toolboxCategoyValue
			}],
			usepager : true,
			useRp : true,
			rp : 15,
			showTableToggleBtn : false,
			resizable : false,
			width : 300,
			onSuccess : function() {
				$("#toolbar #toolbarSet").buttonset();
			}
		});

		$('#' + tableCategoriesValue).hide();
	};
	
	function toolboxCategoyValue(com, grid, categoriesid) {
		if (com == 'Nuevo') {
			createNewCategoryValue(categoriesid);
		} else if (com == 'Borrar') {
			lengthSelect = $('.trSelected', grid).length;
			if (lengthSelect > 0) {
				modalDeleteCategoryValue.dialog("open");
			}
		} else if (com == 'Regresar') {
			$("#" + tableCategoryValue).remove();
			$('#' + tableCategoriesValue).show();
		}
	}

	function createNewCategoryValue(categoriesid) {
		modalNewCategoryValueUrlSet = modalNewCategoryValueUrl + '/' + categoriesid;

		$("#dialogGenerator").load(modalNewCategoryValueGetForm, {
			id : categoriesid
		}, function(responseText, textStatus, XMLHttpRequest) {
		    modalNewCategoryValueForm.refresh();
			$("#dialogGenerator").dialog("open");
		});

		var modalNewCategoryValue = $("#dialogGenerator").dialog({
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
				"Aceptar" : function() {

					var bValid = true;

					modalNewCategoryValueForm.all.removeClass("ui-state-error");
					modalNewCategoryValueForm.all.css("color", "#5A698B");

					bValid = bValid && $.checkLength(modalNewCategoryValueForm.tips, modalNewCategoryValueForm.get('display'), "Display", 2, 200);
					bValid = bValid && $.checkLength(modalNewCategoryValueForm.tips, modalNewCategoryValueForm.get('default'), "Default", 2, 200);

					if (bValid) {
                    
						jQuery('#loading').show();
						jQuery('#imgLoading').show();

						$.ajax({
							type : "POST",
							url : modalNewCategoryValueUrlSet,
							data : modalNewCategoryValueForm.form.serialize(),
							dataType : "json",
							success : function(data) {
								if (data.status) {
									modalNewCategoryValue.dialog("close");
									tableCategoryValueAPI.flexReload();
								} else {
									$.updateTips(modalNewCategoryValueForm.tips, data.error);
								}
							},
							error : function() {
								$.updateTips(modalNewCategoryValueForm.tips, "Error de conexion con el servidor");
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
                modalNewCategoryValueForm.tips.text('Todo los elementos del formulario son requeridos');
                modalNewCategoryValueForm.all.removeClass("ui-state-error").css("color", "#5A698B");  
			}
		});

	}

});

