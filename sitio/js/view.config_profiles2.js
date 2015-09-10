var tableProfile = $('#tableProfile');
///START - PROFILE INDEX////
jQuery(function( $ ) {

	modalProfileNew = $('#modalProfileNew');
	modalProfileNewUrl = '/profiles2/createProfile';
	modalProfileNewForm = new $.formVar( 'formNewProfile' );

	modalProfileDelete = $('#modalProfileDelete');
	modalProfileDeleteUrl = '/profiles2/deleteProfile';

	var buttonModalProfileNew = {};

	buttonModalProfileNew[language.accept] = function(evt) {
		var buttonDomElement = evt.target;
		
		var bValid = true;

		bValid = bValid && modalProfileNewForm.checkLength('name', "auto", 3, 200,language.code);

		bValid = bValid && modalProfileNewForm.checkSelect('groupid', "auto", 0,language.code);

		if ( bValid ) {

			if($(buttonDomElement).attr('disabled')) {
				return true;
			} else {
				$(buttonDomElement).attr('disabled', true).addClass('ui-state-disabled');
			}
			
			$('#loading').show();
			$('#imgLoading').show();

			var request = $.ajax({
				type : "POST",
				url : modalProfileNewUrl,
				data : modalProfileNewForm.form.serialize(),
				dataType : "json"
			});

			request.done(function( msg ) {
				if ( msg.status ) {
					modalProfileNew.dialog("close");
					tableProfile.flexReload();
				} else {
					modalProfileNewForm.tipsBox(msg.error, 'alert');
				}
			});

			request.fail(function( jqXHR, textStatus ) {
				modalProfileNewForm.errorSystem(language.code);
			});

			request.always(function() {
				$(buttonDomElement).attr('disabled', false).removeClass('ui-state-disabled');
				$('#loading').hide();
				$('#imgLoading').hide();
			});			
		}
	}

	buttonModalProfileNew[language.cancel] = function( ) {
		$(this).dialog("close");
	};
		
	modalProfileNew.dialog({
		autoOpen : false,
		resizable : true,
		width : 650,
		modal : true,
		zIndex : 20000,
		closeOnEscape : true,
		draggable : true,
		open : function( ) {
			$(this).find('.ui-dialog-titlebar-close').blur();
			modalProfileNewForm.get('groupid').multiselect();
		},
		buttons : buttonModalProfileNew,
		close : function( ) {
			modalProfileNewForm.get('name').val("");
			modalProfileNewForm.get('groupid').multiselect('destroy');
		}

	});

	var buttonModalProfileDelete = {};

	buttonModalProfileDelete[language.delete] = function(evt) {
		var select = new Array( );
		var buttonDomElement = evt.target;
		count = 0;
		select[count] = deleteProfileID;

		$('#loading').show();
		$('#imgLoading').show();

		if($(buttonDomElement).attr('disabled')) {
			return true;
		} else {
			$(buttonDomElement).attr('disabled', true).addClass('ui-state-disabled');
		}
			
		var request = $.ajax({
			type : "POST",
			url : modalProfileDeleteUrl,
			data : {
				id : select
			},	
			dataType : "json"
		});

		request.done(function( data ) {
			if ( data.status ) {
				modalProfileDelete.dialog("close");
				tableProfile.flexReload();
			} else {
				alert(data.error);
			}
		});
			
		request.fail(function( jqXHR, textStatus ) {
			alert("Error connecting to server");			
		});
		
		request.always(function() {
			$(buttonDomElement).attr('disabled', false).removeClass('ui-state-disabled');
		});
			
		$('#loading').hide();
		$('#imgLoading').hide();		
	}

	buttonModalProfileDelete[language.cancel] = function( ) {
		$(this).dialog("close");
	};
	
	var deleteProfileID = 0 ;
	
	$.deleteProfile = function( id ) {
		deleteProfileID = id;
		$("#row"+id).addClass('trSelected');
		modalProfileDelete.dialog("open");
	}
		
	modalProfileDelete.dialog({
		autoOpen : false,
		resizable : true,
		height : 'auto',
		width : 600,
		modal : true,
		zIndex : 20000,
		closeOnEscape : true,
		draggable : true,
		open : function( ) {
			$(this).find('.ui-dialog-titlebar-close').blur();
			var countSelect = 1;
			$("p#selected", modalProfileDelete).html(language.SELECTION+":");
			$('.trSelected', tableProfile).each(function( ) {
				var name = $(this).children('td:first').text();
				$("p#selected", modalProfileDelete).append("</br>" + countSelect + " : " + name);
				countSelect = countSelect + 1;
			});
		},
		buttons : buttonModalProfileDelete
	});

});
///END - PROFILE INDEX////

///START - PROFILE STRUCTURE////
jQuery(function( $ ) {

	var tableStructure = 'tableStructure';

	var modalStructureNew = $('#modalStructureNew');
	var modalStructureNewUrl = '/profiles2/createStructure';
	var modalStructureNewForm = new $.formVar( 'formStructureNew', modalStructureNew );

	var modalStructureDelete = $('#modalStructureDelete');
	var modalStructureDeleteUrl = '/profiles2/deleteStructure';

	modalStructureNewForm.get('class').NumericAndLetterOnly();
	modalStructureNewForm.get('codeSequence').NumericAndLetterOnly();

	$.structureProfile = function( profileid ) {

		profileidActive = profileid;

		$('#containerProfile').append('<div style="float: left;" id="' + tableStructure + '"></div>');

		tableStructureOBJ = $("#" + tableStructure).flexigrid({
			url : '/profiles2/getTableStructure/' + profileid,
			title : language.PROFILE+' -> '+language.STRUCTURE,
			dataType : 'json',
			colModel : [{
				display : language.CATEGORY,
				name : 'category',
				width : '100',
				sortable : true,
				align : 'left'
			}, {
				display : language.CLASS,
				name : 'class',
				width : '100',
				sortable : true,
				align : 'left'
			}, {
				display : language.ACTION,
				name : 'action',
				width : '198',
				sortable : false,
				align : 'left'
			}],
			buttons : [{
				name : language.new,
				bclass : 'add',
				onpress : toolboxStructure,
				option : profileid
			}, {
				name : language.delete,
				bclass : 'delete',
				onpress : toolboxStructure
			}, {
				separator : true
			}, {
				name : language.BACK,
				bclass : 'ui-icon ui-icon-circle-triangle-w',
				onpress : toolboxStructure
			}, {
				separator : true
			}],
			usepager : true,
			useRp : true,
			rp : 15,
			showTableToggleBtn : false,
			resizable : true,
			width : 'auto',
			height : 'auto',
			striped : false,
			onSuccess : function( ) {
				$("#toolbar #toolbarSet").buttonset();
			}

		});
		$('#tableProfile').parent("div.bDiv").parent("div.flexigrid").hide();
	};

	var modalStructureNewUrlSet = false;

	function toolboxStructure( com, grid, profileid ) {
		if ( com == language.new ) {

			modalStructureNewUrlSet = modalStructureNewUrl + '/' + profileid;

			modalStructureNew.dialog("open");

		} else if ( com == language.delete ) {
			lengthSelect = $('.trSelected', grid).length;
			if ( lengthSelect > 0 ) {
				modalStructureDelete.dialog("open");
			}
		} else if ( com == language.BACK ) {
			$('#' + tableStructure).parent("div.bDiv").parent("div.flexigrid").remove();
			$('#tableProfile').parent("div.bDiv").parent("div.flexigrid").show();
		}
	}


	modalStructureNew.dialog({
		autoOpen : false,
		resizable : true,
		height : 'auto',
		width : 600,
		modal : true,
		zIndex : 20000,
		closeOnEscape : true,
		draggable : true,
		open : function( ) {
			$(this).find('.ui-dialog-titlebar-close').blur();

		},
		buttons : {
			"Accept" : function( ) {
				var bValid = true;

				modalStructureNewForm.refresh();

				bValid = bValid && modalStructureNewForm.checkLength("name", 'auto', 2, 200, language.code);
				bValid = bValid && modalStructureNewForm.checkLength("name", 'class', 2, 200, language.code);
				bValid = bValid && modalStructureNewForm.checkLength("name", 'codeSequence', 1, 200, language.code);

				if ( bValid ) {

					jQuery('#loading').show();
					jQuery('#imgLoading').show();

					$.ajax({
						type : "POST",
						url : modalStructureNewUrlSet,
						data : modalStructureNewForm.form.serialize(),
						dataType : "json",
						success : function( data ) {
							if ( data.status ) {
								modalStructureNew.dialog("close");
								tableStructureOBJ.flexReload();
							} else {
								modalStructureNewForm.tipsBox(data.error, 'alert');
							}
						},
						error : function( ) {
							modalStructureNewForm.tipsBox("Error connecting to server", 'alert');
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
			modalStructureNewForm.get('name').val("");
			modalStructureNewForm.get('class').val("");
			modalStructureNewForm.get('codeSequence').val("");
		}

	});

	modalStructureDelete.dialog({
		autoOpen : false,
		resizable : true,
		height : 'auto',
		width : 600,
		modal : true,
		zIndex : 20000,
		closeOnEscape : true,
		draggable : true,
		open : function( ) {
			$(this).find('.ui-dialog-titlebar-close').blur();
			var countSelect = 1;
			$("p#selected", modalStructureDelete).html("Selected:");
			$('.trSelected', tableStructureOBJ).each(function( ) {
				var name = $(this).children('td:first').text();
				$("p#selected", modalStructureDelete).append("</br>" + countSelect + " : " + name);
				countSelect = countSelect + 1;
			});
		},
		buttons : {
			"Delete" : function( ) {

				var select = new Array( );
				count = 0;
				$('.trSelected', tableStructureOBJ).each(function( ) {
					var id = $(this).attr('id').substr(3);
					if ( $.isNumeric(id) ) {
						select[count] = id;
						count = count + 1;
					}
				});

				jQuery('#loading').show();
				jQuery('#imgLoading').show();

				$.ajax({
					type : "POST",
					url : modalStructureDeleteUrl,
					data : {
						id : select
					},
					dataType : "json",
					success : function( data ) {
						if ( data.status ) {
							modalStructureDelete.dialog("close");
							tableStructureOBJ.flexReload();
						} else {
							alert(data.error);
						}
					},
					error : function( ) {
						alert("Error connecting to server");
					}

				});

				jQuery('#loading').hide();
				jQuery('#imgLoading').hide();

			},
			Cancel : function( ) {
				$(this).dialog("close");
			}

		}
	});
});
///END - PROFILE STRUCTURE////

///START - PROFILE STRUCTURE PARAM/MONITOR////
jQuery(function( $ ) {

	var tableStructureItem = 'tableStructureItem';

	var modalStructureItemNewUrl = '/profiles2/createStructureItem';

	var modalStructureItemNewParam = $('#modalStructureItemNewParam');
	var modalStructureItemNewParamForm = new $.formVar( 'formNewItemParam' );

	var modalStructureItemNewMonitor = $('#modalStructureItemNewMonitor');
	var modalStructureItemNewMonitorForm = new $.formVar( 'formNewItemMonitor' );

	var modalStructureItemDelete = $('#modalStructureItemDelete');
	var modalStructureItemDeleteUrl = '/profiles2/deleteStructureItem';

	var modalStructureItemEdit = $('#modalStructureItemEdit');
	var modalStructureItemEditFormUrl = '/profiles2/getFormStructureItem';
	var modalStructureItemEditForm = new $.formVar( 'formEditItem' );

	$.getStructureItem = function( categoriesid, type ) {

		$('#containerProfile').append('<div style="float: left;" id="' + tableStructureItem + '"></div>');

		if ( type == 'param' ) {
			section = language.PARAMETERS;
		} else {
			section = language.monitors;
		}

		tableStructureItemAPI = $("#" + tableStructureItem).flexigrid({
			url : '/profiles2/getTableItem/' + categoriesid + "/" + type,
			title : language.PROFILE + ' -> '+ language.STRUCTURE + ' -> ' + section,
			dataType : 'json',
			colModel : [{
				display : 'Item',
				name : 'item',
				width : '100',
				sortable : true,
				align : 'left'
			}, {
				display : language.DISPLAY,
				name : 'item_display',
				width : '230',
				sortable : true,
				align : 'left'
			}, {
				display : language.TYPE,
				name : 'type_result',
				width : '41',
				sortable : false,
				align : 'left'
			}, {
				display : language.ACTION,
				name : 'action',
				width : '80',
				sortable : false,
				align : 'left'
			}],
			buttons : [{
				name : language.new,
				bclass : 'add',
				onpress : toolboxStructureItem,
				option : {
					id : categoriesid,
					type : type
				}
			}, {
				name : language.delete,
				bclass : 'delete',
				onpress : toolboxStructureItem
			}, {
				name : language.BACK,
				bclass : 'ui-icon ui-icon-circle-triangle-w',
				onpress : toolboxStructureItem
			}],
			usepager : true,
			useRp : true,
			rp : 15,
			showTableToggleBtn : false,
			resizable : true,
			width : 'auto',
			height : 'auto',
			striped : false,
			onSuccess : function( ) {
				$( '#' + tableStructureItem + " button" ).button({
			      icons: {
			        primary: "ui-icon-pencil"
			      },
			      text: false
			    });
			}

		});
		$('#tableStructure').parent("div.bDiv").parent("div.flexigrid").hide();
	};

	function toolboxStructureItem( com, grid, data ) {
		if ( com == language.new ) {

			modalNewItemUrlSet = modalStructureItemNewUrl + '/' + data.id;

			if ( data.type == 'param' ) {
				modalStructureItemNewParam.dialog("open");
			} else if ( data.type == 'result' ) {
				modalStructureItemNewMonitor.dialog("open");
			} else {
				//console.log("Error / option : " + data.type);
			}

		} else if ( com == language.delete ) {
			lengthSelect = $('.trSelected', grid).length;
			if ( lengthSelect > 0 ) {
				modalStructureItemDelete.dialog("open");
			}
		} else if ( com == language.BACK ) {
			$("#" + tableStructureItem).parent("div.bDiv").parent("div.flexigrid").remove();
			$('#tableStructure').parent("div.bDiv").parent("div.flexigrid").show();
		}
	}


	modalStructureItemNewParam.dialog({
		autoOpen : false,
		resizable : true,
		width : 600,
		modal : true,
		zIndex : 20000,
		closeOnEscape : true,
		draggable : true,
		open : function( ) {
			$(this).find('.ui-dialog-titlebar-close').blur();
		},
		buttons : {
			"Accept" : function( ) {
				var bValid = true;
					
				bValid = bValid && modalStructureItemNewParamForm.checkLength("name", 'auto', 2, 200, language.code);
				bValid = bValid && modalStructureItemNewParamForm.checkLength("display", 'auto', 2, 200, language.code);				

				if ( bValid ) {

					jQuery('#loading').show();
					jQuery('#imgLoading').show();

					$.ajax({
						type : "POST",
						url : modalNewItemUrlSet,
						data : modalStructureItemNewParamForm.form.serialize(),
						dataType : "json",
						success : function( data ) {
							if ( data.status ) {
								modalStructureItemNewParam.dialog("close");
								tableStructureItemAPI.flexReload();
							} else {
								modalStructureItemNewParamForm.tipsBox(data.error, 'alert');
							}
						},
						error : function( ) {
							modalStructureItemNewParamForm.tipsBox("Error connecting to server", 'alert');
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
			modalStructureItemNewParamForm.get('name').val("");
			modalStructureItemNewParamForm.get('display').val("");
			modalStructureItemNewParamForm.get('typeData').prop("selectedIndex", 1);
			modalStructureItemNewParamForm.get('default').val("");
		}

	});

	$("#profileReport input", modalStructureItemNewMonitorForm.form).switchButton();
	$("#profileSaveip input", modalStructureItemNewMonitorForm.form).switchButton();
	$("#profileAlert input", modalStructureItemNewMonitorForm.form).switchButton();

	modalStructureItemNewMonitor.dialog({
		autoOpen : false,
		resizable : true,
		height : 'auto',
		width : 600,
		modal : true,
		zIndex : 20000,
		closeOnEscape : true,
		draggable : true,
		open : function( ) {
			$(this).find('.ui-dialog-titlebar-close').blur();
		},
		buttons : {
			"Accept" : function( ) {
				var bValid = true;

				bValid = bValid && modalStructureItemNewMonitorForm.checkLength("name", 'auto', 2, 200, language.code);
				bValid = bValid && modalStructureItemNewMonitorForm.checkLength("display", 'auto', 2, 200, language.code);
				
				if ( bValid ) {

					jQuery('#loading').show();
					jQuery('#imgLoading').show();

					$.ajax({
						type : "POST",
						url : modalNewItemUrlSet,
						data : modalStructureItemNewMonitorForm.form.serialize(),
						dataType : "json",
						success : function( data ) {
							if ( data.status ) {
								modalStructureItemNewMonitor.dialog("close");
								tableStructureItemAPI.flexReload();
							} else {
								modalStructureItemNewMonitorForm.tipsBox(data.error, 'alert');
							}
						},
						error : function( ) {
							modalStructureItemNewMonitorForm.tipsBox("Error connecting to server", 'alert');
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
			modalStructureItemNewMonitorForm.get('name').val("");
			modalStructureItemNewMonitorForm.get('display').val("");
			modalStructureItemNewMonitorForm.get('default').val("");
			modalStructureItemNewMonitorForm.get('unit').val("");
			modalStructureItemNewMonitorForm.get('typeData').prop("selectedIndex", 1);
		}

	});

	modalStructureItemDelete.dialog({
		autoOpen : false,
		resizable : true,
		height : 'auto',
		width : 600,
		modal : true,
		zIndex : 20000,
		closeOnEscape : true,
		draggable : true,
		open : function( ) {
			$(this).find('.ui-dialog-titlebar-close').blur();
			var countSelect = 1;
			$("p#selected", modalStructureItemDelete).html("Selected:");
			$('.trSelected', tableStructureItemAPI).each(function( ) {
				var name = $(this).children('td:first').text();
				$("p#selected", modalStructureItemDelete).append("</br>" + countSelect + " : " + name);
				countSelect = countSelect + 1;
			});
		},
		buttons : {
			"Delete" : function( ) {

				var select = new Array( );
				count = 0;
				$('.trSelected', tableStructureItemAPI).each(function( ) {
					var id = $(this).attr('id').substr(3);
					if ( $.isNumeric(id) ) {
						select[count] = id;
						count = count + 1;
					}
				});

				jQuery('#loading').show();
				jQuery('#imgLoading').show();

				$.ajax({
					type : "POST",
					url : modalStructureItemDeleteUrl,
					data : {
						id : select
					},
					dataType : "json",
					success : function( data ) {
						if ( data.status ) {
							modalStructureItemDelete.dialog("close");
							tableStructureItemAPI.flexReload();
						} else {
							alert(data.error);
						}
					},
					error : function( ) {
						alert("Error connecting to server");
					}

				});

				jQuery('#loading').hide();
				jQuery('#imgLoading').hide();

			},
			Cancel : function( ) {
				$(this).dialog("close");
			}

		}
	});

	$.editStructureItem = function( itemId, type ) {

		modalStructureItemEdit.load(modalStructureItemEditFormUrl, {
			ID : itemId,
			TYPE : type
		}, function( responseText, textStatus, XMLHttpRequest ) {
			modalStructureItemEditForm.refresh();
			modalStructureItemEdit.removeClass('loading');
			modalStructureItemEdit.dialog("open");
		});

		var buttonStructureItemEdit = {};

		buttonStructureItemEdit[language.SAVE] = function(evt) {
			var bValid = true;

			if ( bValid ) {

				jQuery('#loading').show();
				jQuery('#imgLoading').show();

				$.ajax({
					type : "POST",
					url : modalStructureItemNewUrl + "/" + type + "/" + itemId,
					data : modalStructureItemEditForm.form.serialize(),
					dataType : "json",
					success : function( data ) {
						if ( data.status ) {
							modalStructureItemEdit.dialog("close");
							tableStructureItemAPI.flexReload();
						} else {
							modalStructureItemEditForm.tipsBox(data.error, 'alert');
						}
					},
					error : function( ) {
						modalStructureItemEditForm.tipsBox("Error connecting to server", 'alert');
					}

				});

				jQuery('#loading').hide();
				jQuery('#imgLoading').hide();
			}
		}

		buttonStructureItemEdit[language.cancel] = function( ) {
			$(this).dialog("close");
		};
	

		modalStructureItemEdit.dialog({
			autoOpen : false,
			resizable : true,
			height : 'auto',
			width : 600,
			modal : true,
			zIndex : 20000,
			closeOnEscape : true,
			draggable : true,
			open : function( ) {
				$(this).find('.ui-dialog-titlebar-close').blur();
				$("#profileReport input", modalStructureItemEditForm.form).switchButton();
				$("#profileSaveip input", modalStructureItemEditForm.form).switchButton();
				$("#profileAlert input", modalStructureItemEditForm.form).switchButton();
			},
			buttons : buttonStructureItemEdit,
			close : function( ) {

			}

		});

	};
});
///END - PROFILE STRUCTURE PARAM/MONITOR////

///START - PROFILE CATEGORY////
jQuery(function( $ ) {

	tableCategories = 'tableCategories';

	$.categoriesProfile = function( categoriesid ) {

		$('#containerProfile').html('<div style="float: left;" id="' + tableCategories + '"></div>');

		tableCategoriesValueAPI = $('#' + tableCategories).flexigrid({
			url : '/profiles2/getTableCategoriesValue/' + categoriesid,
			title : language.PROFILE +' -> '+language.monitors ,
			dataType : 'json',
			colModel : [{
				display : language.CATEGORY,
				name : 'category',
				width : '100',
				sortable : true,
				align : 'left'
			}, {
				display : language.CLASS,
				name : 'class',
				width : '100',
				sortable : true,
				align : 'left'
			}, {
				display : language.ACTION,
				name : 'action',
				width : '47',
				sortable : false,
				align : 'left'
			}],
			buttons : [{
				name : language.BACK,
				bclass : 'ui-icon-circle-triangle-w',
				onpress : toolboxCategories
			}],
			usepager : true,
			useRp : true,
			rp : 15,
			showTableToggleBtn : false,
			resizable : true,
			width : 'auto',
			height : 'auto',
			striped : false,
			onSuccess : function( ) {
				$( '#' + tableCategories + " button" ).button({
			      icons: {
			        primary: "ui-icon-pencil"
			      },
			      text: false
			    });
			}

		});
		$('#tableProfile').parent("div.bDiv").parent("div.flexigrid").hide();
	};

	function toolboxCategories( com, grid, profileid ) {
		if ( com == language.BACK ) {
			$("#" + tableCategories).parent("div.bDiv").parent("div.flexigrid").remove();
			$('#tableProfile').parent("div.bDiv").parent("div.flexigrid").show();
		};
	}

});
///END - PROFILE CATEGORY////

///START - PROFILE CATEGORY - INSERT////
jQuery(function( $ ) {

	var tableCategory = 'tableCategory';
	var CategoryValueStatusUrl = '/profiles2/setTableCategoryValueStatus';
	var CategoryValueViewUrl = '/profiles2/setTableCategoryValueView';
	var modalNewCategory = $('#modalNewCategoryValue');
	var modalNewCategoryUrl = '/profiles2/createCategoryValue';
	var modalNewCategoryGetForm = '/profiles2/getFormCategoryValue';
	var modalNewCategoryForm = new $.formVar( 'formNewCategoryValue' );

	var modalDeleteCategory = $('#modalDeleteCategory');
	var modalDeleteCategoryUrl = '/profiles2/deleteCategoryValue';

	$.getCategoryValue = function( categoriesid ) {
		$('#containerProfile').append('<div style="float: left;" id="' + tableCategory + '"></div>');

		tableCategoryOBJ = $('#' + tableCategory).flexigrid({
			url : '/profiles2/getTableCategoryValue/' + categoriesid,
			title : 'Profile -> Monitors -> Monitor',
			dataType : 'json',
			colModel : [{
				display : 'ID',
				name : 'id_category',
				width : '45',
				sortable : true,
				align : 'left'
			}, {
				display : language.CATEGORY,
				name : 'category',
				width : '100',
				sortable : true,
				align : 'left'
			}, {
				display : language.DISPLAY,
				name : 'display',
				width : '150',
				sortable : true,
				align : 'left'
			}, {
				display : language.STATUS,
				name : 'status',
				width : '42',
				sortable : false,
				align : 'center'
			},{
				display : language.VIEW,
				name : 'view',
				width : '42',
				sortable : false,
				align : 'center'
			}, {
				display : language.ACTION,
				name : 'action',
				width : '41',
				sortable : false,
				align : 'center'
			}],
			buttons : [{
				name : language.new,
				bclass : 'add',
				onpress : toolboxCategoyValue,
				option : categoriesid
			}, {
				name : language.delete,
				bclass : 'delete',
				onpress : toolboxCategoyValue
			}, {
				name : language.BACK,
				bclass : 'ui-icon-circle-triangle-w',
				onpress : toolboxCategoyValue
			}],
			usepager : true,
			useRp : true,
			rp : 15,
			showTableToggleBtn : false,
			resizable : true,
			width : 'auto',
			height : 'auto',
			striped : false,
			onSuccess : function( ) {
				$( '#' + tableCategory + " button" ).button({
			      icons: {
			        primary: "ui-icon-pencil"
			      },
			      text: false
			    });
			}

		});
		$('#' + tableCategories).parent("div.bDiv").parent("div.flexigrid").hide();
	};

	function toolboxCategoyValue( com, grid, categoriesid ) {
		if ( com == language.new ) {
			createNewCategoryValue(categoriesid);
		} else if ( com == language.delete ) {
			lengthSelect = $('.trSelected', grid).length;
			if ( lengthSelect > 0 ) {
				modalDeleteCategory.dialog("open");
			}
		} else if ( com == language.BACK ) {
			$("#" + tableCategory).parent("div.bDiv").parent("div.flexigrid").remove();
			$('#' + tableCategories).parent("div.bDiv").parent("div.flexigrid").show();
		}
	}


	modalDeleteCategory.dialog({
		autoOpen : false,
		resizable : true,
		height : 'auto',
		width : 600,
		modal : true,
		zIndex : 20000,
		closeOnEscape : true,
		draggable : true,
		open : function( ) {
			$(this).find('.ui-dialog-titlebar-close').blur();
			var countSelect = 1;
			$("p#selected", modalDeleteCategory).html("Selected:");
			$('.trSelected', tableCategoryOBJ).each(function( ) {
				var name = $(this).children('td:nth-child(2)').text();
				$("p#selected", modalDeleteCategory).append("</br>" + countSelect + " : " + name);
				countSelect = countSelect + 1;
			});
		},
		buttons : {
			"Delete" : function( ) {

				var select = new Array( );
				count = 0;
				$('.trSelected', tableCategoryOBJ).each(function( ) {
					var id = $(this).attr('id').substr(3);
					if ( $.isNumeric(id) ) {
						select[count] = id;
						count = count + 1;
					}
				});

				jQuery('#loading').show();
				jQuery('#imgLoading').show();

				$.ajax({
					type : "POST",
					url : modalDeleteCategoryUrl,
					data : {
						id : select
					},
					dataType : "json",
					success : function( data ) {
						if ( data.status ) {
							modalDeleteCategory.dialog("close");
							tableCategoryOBJ.flexReload();
						} else {
							alert(data.error);
						}
					},
					error : function( ) {
						alert("Error connecting to server");
					}

				});

				jQuery('#loading').hide();
				jQuery('#imgLoading').hide();

			},
			Cancel : function( ) {
				$(this).dialog("close");
			}

		}
	});

	function createNewCategoryValue( categoriesid ) {
		modalNewCategoryUrlSet = modalNewCategoryUrl + '/' + categoriesid;

		$("#dialogGenerator").load(modalNewCategoryGetForm, {
			id : categoriesid
		}, function( responseText, textStatus, XMLHttpRequest ) {
			modalNewCategoryForm.refresh();
			$("#dialogGenerator").dialog("open");
		});

		var modalNewCategory = $("#dialogGenerator").dialog({
			autoOpen : false,
			resizable : true,
			height : 'auto',
			width : 800,
			modal : true,
			zIndex : 20000,
			closeOnEscape : true,
			draggable : true,
			open : function( ) {
				$(this).find('.ui-dialog-titlebar-close').blur();
			},
			buttons : {
				"Aceptar" : function( ) {

					var bValid = true;

					bValid = bValid && modalNewCategoryForm.checkLength("display", 'auto', 2, 200, language.code);
				
					if ( bValid ) {

						jQuery('#loading').show();
						jQuery('#imgLoading').show();

						$.ajax({
							type : "POST",
							url : modalNewCategoryUrlSet,
							data : modalNewCategoryForm.form.serialize(),
							dataType : "json",
							success : function( data ) {
								if ( data.status ) {
									modalNewCategory.dialog("close");
									tableCategoryOBJ.flexReload();
								} else {
									modalNewCategoryForm.tipsBox(data.error, 'alert');
								}
							},
							error : function( ) {
								modalNewCategoryForm.tipsBox("Error connecting to server", 'alert');
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

	}


	$.CategoryValueStatus = function( id, status ) {

		if ( status ) {
			status_value = 0;
		} else {
			status_value = 1;
		}

		$.ajax({
			type : "POST",
			url : CategoryValueStatusUrl,
			data : {
				status : status_value,
				id : id
			},
			dataType : "json",
			success : function( data ) {
				if ( data.status ) {
					tableCategoryOBJ.flexReload();
				} else {
					alert("Error interno");
				}
			},
			error : function( ) {
				alert("Error connecting to server");
			}

		});
	};


	$.CategoryValueView = function( id, status ) {

		if ( status ) {
			status_value = 0;
		} else {
			status_value = 1;
		}

		$.ajax({
			type : "POST",
			url : CategoryValueViewUrl,
			data : {
				status : status_value,
				id : id
			},
			dataType : "json",
			success : function( data ) {
				if ( data.status ) {
					tableCategoryOBJ.flexReload();
				} else {
					alert("Error interno");
				}
			},
			error : function( ) {
				alert("Error connecting to server");
			}

		});
	};	
	
	
	

	var modalEditCategory = $('#dialogGenerator');
	var modalEditCategoryUrl = '/profiles2/editCategoryValue';
	var modalEditCategoryGetForm = '/profiles2/getEditFormCategoryValue';
	var modalEditCategoryForm = new $.formVar( 'formEditCategoryValue' );

	$.editCategoryValue = function( itemId ) {

		modalEditCategory.load(modalEditCategoryGetForm, {
			id : itemId
		}, function( responseText, textStatus, XMLHttpRequest ) {
			modalEditCategoryForm.refresh();
			modalEditCategory.removeClass('loading');
			modalEditCategory.dialog("open");
		});

		modalEditCategory.dialog({
			autoOpen : false,
			resizable : true,
			height : 'auto',
			width : 800,
			modal : true,
			zIndex : 20000,
			closeOnEscape : true,
			draggable : true,
			open : function( ) {
				$(this).find('.ui-dialog-titlebar-close').blur();
			},
			buttons : {
				"Accept" : function( ) {

					var bValid = true;

					bValid = bValid && modalEditCategoryForm.checkLength("display", 'auto', 2, 200, language.code);

					if ( bValid ) {

						jQuery('#loading').show();
						jQuery('#imgLoading').show();

						$.ajax({
							type : "POST",
							url : modalEditCategoryUrl + "/" + itemId,
							data : modalEditCategoryForm.form.serialize(),
							dataType : "json",
							success : function( data ) {
								if ( data.status ) {
									modalEditCategory.dialog("close");
									tableCategoryOBJ.flexReload();
								} else {
									modalEditCategoryForm.tipsBox(data.error, 'alert');
								}
							},
							error : function( ) {
								modalEditCategoryForm.tipsBox("Error connecting to server", 'alert');
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
				modalEditCategoryForm.all.removeClass("ui-state-error").css("color", "#5A698B");
			}

		});
	};
});
///END - PROFILE CATEGORY - INSERT////

///START - PROFILE - CLONE////
jQuery(function( $ ) {
	var modalProfileClone = $('#modalProfileClone');
	var modalProfileCloneUrl = '/profiles2/cloneProfile';
	var modalProfileCloneForm = new $.formVar( 'formCloneProfile' );

	var buttonModalProfileClone = {};

	buttonModalProfileClone[language.accept] = function(evt) {

		var buttonDomElement = evt.target;	
	
		var bValid = true;

		bValid = bValid && modalProfileCloneForm.checkLength("name", 'auto', 2, 200, language.code);
		bValid = bValid && modalProfileCloneForm.checkSelect('cloneMethod', "auto", 0, language.code);
		bValid = bValid && modalProfileCloneForm.checkSelect('groupid', "auto", 0, language.code);

		if ( bValid ) {

			if($(buttonDomElement).attr('disabled')) {
				return true;
			} else {
				$(buttonDomElement).attr('disabled', true).addClass('ui-state-disabled');
			}
		
			$('#loading').show();
			$('#imgLoading').show();

			var request = $.ajax({
				type : "POST",
				url : modalProfileCloneUrl + "/" + idProfileClone,
				data : modalProfileCloneForm.form.serialize(),
				dataType : "json"
			});

			request.done(function( msg ) {
				if ( msg.status ) {
					modalProfileClone.dialog("close");
					tableProfile.flexReload();
				} else {
					modalProfileCloneForm.tipsBox(msg.error, "alert");
				}
			});

			request.fail(function( jqXHR, textStatus ) {
				modalProfileCloneForm.errorSystem(language.code);
			});

			request.always(function() {
				$(buttonDomElement).attr('disabled', false).removeClass('ui-state-disabled');
			});
		
			$('#loading').hide();
			$('#imgLoading').hide();
		}
	};

	buttonModalProfileClone[language.cancel] = function( ) {
		$(this).dialog("close");
	};

	modalProfileClone.dialog({
		autoOpen : false,
		resizable : true,
		width : 650,
		modal : true,
		zIndex : 20000,
		closeOnEscape : true,
		draggable : true,
		open : function( ) {
			$(this).find('.ui-dialog-titlebar-close').blur();
			modalProfileCloneForm.get('groupid').multiselect();
		},
		buttons : buttonModalProfileClone,
		close : function( ) {
			modalProfileCloneForm.get('cloneMethod').val(0);
			modalProfileCloneForm.get('groupid').val(0);
			modalProfileCloneForm.get('groupid').multiselect('destroy');
		}
	});

	$.cloneProfile = function( profileId, nameprofile ) {
		modalProfileCloneForm.get('name').val(nameprofile);
		idProfileClone = profileId;
		modalProfileClone.dialog("open");
	};
});
///END - PROFILE - CLONE////
///START - PROFILE - SEQUENCE////
jQuery(function( $ ) {
	var modalProfileSequence = $('#dialogGenerator');
	var modalProfileSequenceUrl = '/profiles2/getSequenceForm';

	$.sequenceProfile = function( profileId ) {
		
		var request = $.ajax({
			type : "GET",
			url : modalProfileSequenceUrl + "/" + profileId,
			dataType : "json"
		});
		
		request.done(function( data ) {
			if ( data.status ) {
					$('#tableProfile').parent("div.bDiv").parent("div.flexigrid").hide();
					var containerTabs = $( "#tableProfile" ).parent().parent().parent();
					var dialogGenerator = $('#dialogGenerator',containerTabs);
					if(dialogGenerator.length) {
						$('#dialogGenerator').html(data.html);
					} else {
						containerTabs.append('<div id="dialogGenerator"></div>');
						$('#dialogGenerator').html(data.html);		
					}
					return true;				
				} else {
					return false;
				}
		});
			
		request.fail(function( jqXHR, textStatus ) {
					
		});
	};
});
///END - PROFILE - SEQUENCE////
///START - PROFILE - SCHEDULE////
jQuery(function( $ ) {
	var modalProfileSchedule = $('#dialogGenerator');
	var modalProfileScheduleUrl = '/profiles2/getScheduleForm';

	$.scheduleProfile = function( profileId ) {
		var request = $.ajax({
			type : "GET",
			url : modalProfileScheduleUrl + "/" + profileId,
			dataType : "json"
		});
		request.done(function( data ) {
			if ( data.status ) {
					$('#tableProfile').parent("div.bDiv").parent("div.flexigrid").hide();
					$('#dialogGenerator').html(data.html);
				} else {
					return false;
				}
		});
			
		request.fail(function( jqXHR, textStatus ) {
					
		});
	};
});
///END - PROFILE - SCHEDULE////
///START - PROFILE - PARAM////
jQuery(function( $ ) {

	tableParameters = 'tableParameters';

	var modalParametersProfileNewUrl = '/profiles2/createParameterProfile';

	var modalParametersProfileNew = $('#modalParametersProfileNew');
	var modalParametersProfileNewForm = new $.formVar( 'formProfileParametersNew' );

	var modalParametersProfileDelete = $('#modalParametersProfileDelete');
	var modalParametersProfileDeleteUrl = '/profiles2/deleteParameterProfile';

	var modalParametersProfileEdit = $('#modalParametersProfileEdit');
	var modalParametersProfileEditFormUrl = '/profiles2/getFormParameterProfile';
	var modalParametersProfileEditForm = new $.formVar( 'formProfileParametersEdit' );
	var modalParametersProfileEditUrl = '/profiles2/editParameterProfile';

	var modalParametersProfileChangeStatusURL = '/profiles2/changeStatusParamProfile';

	$.paramProfile = function( profileId, title ) {

		$('#containerProfile').html('<div style="float: left;" id="' + tableParameters + '"></div>');

		tableParametersObj = $('#' + tableParameters).flexigrid({
			url : '/profiles2/getTableParameters/' + profileId,
			title : 'Profile -> ' + title,
			dataType : 'json',
			colModel : [{
				display : 'ID',
				name : 'id_param',
				width : 50,
				sortable : true,
				align : 'left'
			}, {
				display : language.NAME,
				name : 'name',
				width : 150,
				sortable : true,
				align : 'left'
			}, {
				display : language.DESCRIPTION,
				name : 'description',
				width : 119,
				sortable : false,
				align : 'left'
			}, {
				display : language.STATUS,
				name : 'status',
				width : 37,
				sortable : false,
				align : 'center'
			}, {
				display : language.options,
				name : 'option',
				width : 67,
				sortable : false,
				align : 'center'
			}],
			buttons : [{
				name : language.new,
				bclass : 'add',
				onpress : toolboxParametersProfile,
				option : profileId
			}, {
				name : language.delete,
				bclass : 'delete',
				onpress : toolboxParametersProfile
			}, {
				separator : true
			}, {
				name : language.BACK,
				bclass : 'ui-icon-circle-triangle-w',
				onpress : toolboxParametersProfile
			}],
			usepager : true,
			useRp : true,
			rp : 15,
			showTableToggleBtn : false,
			resizable : true,
			width : 'auto',
			height : 'auto',
			striped : false,
			onSuccess : function( ) {
				$(".flexigrid div.bDiv td div button").button();
			}

		});
		$('#tableProfile').parent("div.bDiv").parent("div.flexigrid").hide();
	};

	function toolboxParametersProfile( com, grid, profileid ) {
		if ( com == language.new ) {

			modalParametersProfileNewUrlSet = modalParametersProfileNewUrl + '/' + profileid;

			modalParametersProfileNew.dialog("open");

		} else if ( com == language.delete ) {
			lengthSelect = $('.trSelected', grid).length;
			if ( lengthSelect > 0 ) {
				modalParametersProfileDelete.dialog("open");
			}
		} else if ( com == language.BACK ) {
			$("#" + tableParameters).parent("div.bDiv").parent("div.flexigrid").remove();
			$('#tableProfile').parent("div.bDiv").parent("div.flexigrid").show();
		}
		;
	}


	modalParametersProfileNew.dialog({
		autoOpen : false,
		resizable : false,
		height : 'auto',
		width : 400,
		modal : false,
		zIndex : 20000,
		closeOnEscape : true,
		draggable : true,
		open : function( ) {
			$(this).find('.ui-dialog-titlebar-close').blur();
		},
		buttons : {
			"Accept" : function( ) {
				var bValid = true;

				modalParametersProfileNewForm.refresh();

				bValid = bValid && modalParametersProfileNewForm.checkLength("name", 'auto', 2, 200, language.code);
				bValid = bValid && modalParametersProfileNewForm.checkLength("description", 'auto', 1, 200, language.code);
				bValid = bValid && modalParametersProfileNewForm.checkSelect('type', "auto", 0);
				//bValid = bValid && modalParametersProfileNewForm.checkLength("value", 'auto', 1, 200, language.code);

				if ( bValid ) {

					jQuery('#loading').show();
					jQuery('#imgLoading').show();

					$.ajax({
						type : "POST",
						url : modalParametersProfileNewUrlSet,
						data : modalParametersProfileNewForm.form.serialize(),
						dataType : "json",
						success : function( data ) {
							if ( data.status ) {
								modalParametersProfileNew.dialog("close");
								tableParametersObj.flexReload();
							} else {
								modalParametersProfileNewForm.tipsBox(data.error, 'alert');
							}
						},
						error : function( ) {
							modalParametersProfileNewForm.tipsBox("Error connecting to server", 'alert');
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
			modalParametersProfileNewForm.get('name').val("");
			modalParametersProfileNewForm.get('description').val("");
			modalParametersProfileNewForm.get('value').val("");
		}

	});

	modalParametersProfileDelete.dialog({
		autoOpen : false,
		resizable : false,
		height : 'auto',
		width : 400,
		modal : true,
		zIndex : 20000,
		closeOnEscape : true,
		draggable : true,
		open : function( ) {
			$(this).find('.ui-dialog-titlebar-close').blur();
			var countSelect = 1;
			$("p#selected", modalParametersProfileDelete).html("Selected:");
			$('.trSelected', tableParametersObj).each(function( ) {
				var name = $(this).children('td:nth-child(2)').text();
				$("p#selected", modalParametersProfileDelete).append("</br>" + countSelect + " : " + name);
				countSelect = countSelect + 1;
			});
		},
		buttons : {
			"Delete" : function( ) {

				var select = new Array( );

				count = 0;

				$('.trSelected', tableParametersObj).each(function( ) {
					var id = $(this).attr('id').substr(3);
					if ( $.isNumeric(id) ) {
						select[count] = id;
						count = count + 1;
					}
				});

				jQuery('#loading').show();
				jQuery('#imgLoading').show();

				$.ajax({
					type : "POST",
					url : modalParametersProfileDeleteUrl,
					data : {
						id : select
					},
					dataType : "json",
					success : function( data ) {
						if ( data.status ) {
							modalParametersProfileDelete.dialog("close");
							tableParametersObj.flexReload();
						} else {
							modalParametersProfileDelete.tipsBox(data.error, 'alert');
						}
					},
					error : function( ) {
						modalParametersProfileNewForm.tipsBox("Error connecting to server", 'alert');
					}

				});

				jQuery('#loading').hide();
				jQuery('#imgLoading').hide();

			},
			Cancel : function( ) {
				$(this).dialog("close");
			}

		}
	});

	$.paramProfileEdit = function( paramId ) {

		modalParametersProfileEdit.load(modalParametersProfileEditFormUrl, {
			id : paramId
		}, function( responseText, textStatus, XMLHttpRequest ) {
			modalParametersProfileEditForm.refresh();
			modalParametersProfileEdit.removeClass('loading');
			modalParametersProfileEdit.dialog("open");
		});

		modalParametersProfileEdit.dialog({
			autoOpen : false,
			resizable : false,
			height : 'auto',
			width : 400,
			modal : false,
			zIndex : 20000,
			closeOnEscape : true,
			draggable : true,
			open : function( ) {
				$(this).find('.ui-dialog-titlebar-close').blur();
			},
			buttons : {
				"Accept" : function( ) {

					var bValid = true;

					bValid = bValid && modalParametersProfileEditForm.checkLength("name", 'auto', 2, 200, language.code);
					bValid = bValid && modalParametersProfileEditForm.checkLength("description", 'auto', 1, 200, language.code);
					bValid = bValid && modalParametersProfileEditForm.checkSelect('type', "auto", 0);
					//bValid = bValid && modalParametersProfileEditForm.checkLength("value", 'auto', 1, 200, language.code);

					if ( bValid ) {

						jQuery('#loading').show();
						jQuery('#imgLoading').show();

						$.ajax({
							type : "POST",
							url : modalParametersProfileEditUrl + "/" + paramId,
							data : modalParametersProfileEditForm.form.serialize(),
							dataType : "json",
							success : function( data ) {
								if ( data.status ) {
									modalParametersProfileEdit.dialog("close");
									tableParametersObj.flexReload();
								} else {
									modalParametersProfileEditForm.tipsBox(data.error, 'alert');
								}
							},
							error : function( ) {
								modalParametersProfileEditForm.tipsBox("Error connecting to server", 'alert');
							}

						});

						jQuery('#loading').hide();
						jQuery('#imgLoading').hide();
					}
				},
				"Cancel" : function( ) {
					$(this).dialog("close");
				}

			}

		});
	};

	$.paramProfileStatus = function( paramID, status ) {
		$.ajax({
			type : "GET",
			url : modalParametersProfileChangeStatusURL + "/" + paramID + "/" + status,
			dataType : "json",
			success : function( data ) {
				if ( data.status ) {
					tableParametersObj.flexReload();
				} else {
					return false;
				}
			},
			error : function( ) {

			}

		});
	};
});
///END - PROFILE PARAM////
///START - PROFILE - EXPORT - IMPORT////
jQuery(function( $ ) {

	var idProfile = 0;

	modalProfileExport = $('#modalProfileExport');
	modalProfileExportUrl = '/profiles2/export';
	modalProfileExportForm = new $.formVar( 'formProfileExport' );

	modalProfileImport = $('#modalProfileImport');
	modalProfileImportUrl = '/profiles2/import';
	modalProfileImportForm = new $.formVar( 'formProfileImport' );
			
	modalProfileExportForm.get('radioStructure1').buttonset();
	modalProfileExportForm.get('radioProbe1').buttonset();
	modalProfileExportForm.get('radioType').buttonset();

	modalProfileImportForm.get('radioStructure2').buttonset();
	modalProfileImportForm.get('radioProbe2').buttonset();
	modalProfileImportForm.get('uploadfile').button();

	modalProfileImportForm.get('uploadfile').click(function( ) {
		modalProfileImportForm.get('fileXML').trigger('click');
	});	

	var filesImport;
	
	modalProfileImportForm.get('fileXML').change(function( event ) {	
		filesImport = event.target.files;
	
		var FileName = modalProfileImportForm.get('fileXML').val().substr(12);
		if ( FileName.length > 20 ){
			//FileName = FileName.substring(0,20) + "...";
		}
		modalProfileImportForm.get('fileName').html("<p>" + FileName + '</p>');
	});
	
	modalProfileImportForm.form.submit(function( event ) {
    	event.preventDefault();
	});
						
	var buttonModalProfileImport = {};
	
	buttonModalProfileImport[language.accept] = function(evt) {
		var buttonDomElement = evt.target;
		
		if($(buttonDomElement).attr('disabled')) {
			return true;
		} else {
			$(buttonDomElement).attr('disabled', true).addClass('ui-state-disabled');
		}
		
		modalProfileImportForm.refresh();

		var formData = new FormData($('#formProfileImport')[0]);

		$('#loading').show();
		$('#imgLoading').show();
		
		var request = $.ajax({
			type : "POST",
			url : modalProfileImportUrl+"/"+idProfile,
			processData: false, 
        	contentType: false, 
			data : formData,
			dataType : "json"
		});

		request.done(function( msg ) {
			if ( msg.status ) {
				modalProfileImport.dialog("close");
			} else {
				modalProfileImportForm.tipsBox(msg.error, 'alert');
			}
		});

		request.fail(function( jqXHR, textStatus ) {
			modalProfileImportForm.errorSystem(language.code);
		});

		request.always(function() {
			$(buttonDomElement).attr('disabled', false).removeClass('ui-state-disabled');
			$('#loading').hide();
			$('#imgLoading').hide();
		});			
	}

	buttonModalProfileImport[language.cancel] = function( ) {
		$(this).dialog("close");
	};
		
	modalProfileImport.dialog({
		autoOpen : false,
		resizable : true,
		width : 599,
		modal : true,
		zIndex : 20000,
		closeOnEscape : true,
		draggable : true,
		open : function( ) {
			$(this).find('.ui-dialog-titlebar-close').blur();
		},
		buttons : buttonModalProfileImport,
		close : function( ) {
			modalProfileImportForm.get('name').val("");
		}
	});
	
	var buttonModalProfileExport = {};

	buttonModalProfileExport[language.accept] = function(evt) {
		var buttonDomElement = evt.target;
		
		if($(buttonDomElement).attr('disabled')) {
			return true;
		} else {
			$(buttonDomElement).attr('disabled', true).addClass('ui-state-disabled');
		}
		
		$('#loading').show();
		$('#imgLoading').show();
		
		var request = $.ajax({
			type : "POST",
			url : modalProfileExportUrl,
			data : modalProfileExportForm.form.serialize()+"&idProfile="+idProfile,
			dataType : "json"
		});

		request.done(function( msg ) {
			if ( msg.status ) {
				modalProfileExport.dialog("close");
				window.location.href = msg.file;
			} else {
				modalProfileExportForm.tipsBox(msg.error, 'alert');
			}
		});

		request.fail(function( jqXHR, textStatus ) {
			modalProfileExportForm.errorSystem(language.code);
		});

		request.always(function() {
			$(buttonDomElement).attr('disabled', false).removeClass('ui-state-disabled');
			$('#loading').hide();
			$('#imgLoading').hide();
		});			
		
	}

	buttonModalProfileExport[language.cancel] = function( ) {
		$(this).dialog("close");
	};

	modalProfileExport.dialog({
		autoOpen : false,
		resizable : true,
		width : 400,
		modal : true,
		zIndex : 20000,
		closeOnEscape : true,
		draggable : true,
		open : function( ) {
			$(this).find('.ui-dialog-titlebar-close').blur();
		},
		buttons : buttonModalProfileExport,
		close : function( ) {
			modalProfileExportForm.get('name').val("");
		}
	});	
	
	$.exportProfile = function( id ) {
		$("#row"+idProfile).addClass('trSelected');
		idProfile = id;
		modalProfileExport.dialog("open");
	}
	
	$.importProfile = function( id ) {
		$("#row"+idProfile).addClass('trSelected');
		idProfile = id;
		modalProfileImport.dialog("open");
	}
});
///END - PROFILE PARAM////
