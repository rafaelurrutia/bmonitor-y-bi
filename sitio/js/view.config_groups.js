var tableGroups = $('#table_groups');

var modalGroupNew = $('#modal_groups_new');
var modalGroupNewUrl = '/config/createGroup';
var modalGroupNewForm =  new $.formVar('form_new_group');


var modalGroupEdit = $('#modal_groups_edit');
var modalGroupEditUrlForm = '/config/getGroupForm';
var modalGroupEditForm = new $.formVar('form_edit_group');

var modalGroupDelete = $('#modal_groups_delete');
var modalGroupDeleteUrl = '/config/deleteGroups';

jQuery(function($) {

	$.updateGroups = function() {
		$.ajax({
			type : "POST",
			url : "/config/getOptionGroups",
			async : false,
			dataType : "json",
			success : function(data) {
				if (data.status) {
					$("body #groupid").html(data.data);
				}
			},
		});
	};

	var buttonModalGroupNew = {};

	buttonModalGroupNew[language.accept] = function(evt) {
		
		var buttonDomElement = evt.target;
		
		var bValid = true;
		
		bValid = bValid && modalGroupNewForm.checkLength('group_name', "auto", 3, 200,language.code);

		if (bValid) {

			if($(buttonDomElement).attr('disabled')) {
				return true;
			} else {
				$(buttonDomElement).attr('disabled', true).addClass('ui-state-disabled');
			}
			
			jQuery('#loading').show();
			jQuery('#imgLoading').show();

			var request = $.ajax({
				type : "POST",
				url : modalGroupNewUrl,
				data : modalGroupNewForm.form.serialize(),
				dataType : "json"
			});
			
			request.done(function( msg ) {
				$.updateGroups();
				if ( msg.status ) {
					modalGroupNew.dialog("close");
					tableGroups.flexReload();
				} else {
					modalGroupNewForm.tipsBox(msg.error, 'alert');
				}
			});

			request.fail(function( jqXHR, textStatus ) {
				modalGroupNewForm.errorSystem(language.code);
			});

			request.always(function() {
				$(buttonDomElement).attr('disabled', false).removeClass('ui-state-disabled');
				$('#loading').hide();
				$('#imgLoading').hide();
			});
		}
	};

	buttonModalGroupNew[language.cancel] = function() {
		$(this).dialog("close");
	};

	modalGroupNew.dialog({
		autoOpen : false,
		resizable : false,
		height : 'auto',
		width : 400,
		modal : false,
		closeOnEscape : true,
		draggable : true,
		open : function() {
			modalGroupNew.find('.ui-dialog-titlebar-close').blur();
		},
		buttons : buttonModalGroupNew,
		close : function() {
			modalGroupNewForm.get('group_name').val("");
		}
	});

	var buttonModalGroupDelete = {};

	buttonModalGroupDelete[language.accept] = function(evt) {
		var buttonDomElement = evt.target;
		
		if($(buttonDomElement).attr('disabled')) {
			return true;
		} else {
			$(buttonDomElement).attr('disabled', true).addClass('ui-state-disabled');
		}
		
		jQuery('#loading').show();
		jQuery('#imgLoading').show();


		var select = new Array();
		count = 1;
		$('.trSelected', tableGroups).each(function() {
			var id = $(this).attr('id');
			id = id.substring(id.lastIndexOf('row') + 3);
			if ($.isNumeric(id)) {
				select[count] = id;
				count = count + 1;
			}
		});
			
		var request = $.ajax({
			type : "POST",
			url : modalGroupDeleteUrl,
			data : {
				idSelect : select
			},
			dataType : "json"
		});
		
		request.done(function( msg ) {
			$.updateGroups();
			if ( msg.status ) {
				modalGroupDelete.dialog("close");
				tableGroups.flexReload();
			} else {
				alert(msg.error);
			}
		});

		request.fail(function( jqXHR, textStatus ) {
			
		});

		request.always(function() {
			$(buttonDomElement).attr('disabled', false).removeClass('ui-state-disabled');
			$('#loading').hide();
			$('#imgLoading').hide();
		});					
	}
	
	buttonModalGroupDelete[language.cancel] = function() {
		$(this).dialog("close");
	};
			
	modalGroupDelete.dialog({
		autoOpen : false,
		resizable : false,
		width : 400,
		modal : true,
		open : function() {
			var countSelectGroups = 1;
			$("#modal_groups_delete p#selected").html("Selected:");
			$('.trSelected', tableGroups).each(function() {
				var name = $(this).children('td:nth-child(1)').text();
				$("#modal_groups_delete p#selected").append("</br>" + countSelectGroups + " : " + name);
				countSelectGroups = countSelectGroups + 1;
			});
		},
		buttons :buttonModalGroupDelete,
		Cancel : function() {
			modalGroupDelete.dialog("close");
		}
	});

	$.editarModal = function(id, clonar) {

		if (clonar) {
			var id_set = "clone";
		} else {
			var id_set = id;
		};

		modalGroupEdit.load(modalGroupEditUrlForm, {
			IDSelect : id
		}, // omit this param object to issue a GET request instead a POST request, otherwise you may provide
		// post parameters within the object
		function(responseText, textStatus, XMLHttpRequest) {

			modalGroupEditForm.refresh();

			// remove the loading class
			modalGroupEdit.removeClass('loading');

			if (clonar) {
				var name1 = modalGroupEditForm.get('group_name').val();
				modalGroupEditForm.get('group_name').val(name1 + '_1');
				modalGroupEditForm.get('group_items').show();
			}

			modalGroupEdit.dialog("open");
		});

		modalGroupEdit.dialog({
			autoOpen : false,
			resizable : false,
			height : 'auto',
			width : 400,
			modal : false,
			closeOnEscape : true,
			draggable : true,
			open : function() {
				$(this).find('.ui-dialog-titlebar-close').blur();
			},
			buttons : {
				"Guardar" : function() {

					var bValid = true;

					modalGroupEditForm.all.removeClass("ui-state-error");
					modalGroupEditForm.all.css("color", "#5A698B");

					if (bValid) {

						jQuery('#loading').show();
						jQuery('#imgLoading').show();

						$.ajax({
							type : "POST",
							url : modalGroupNewUrl,
							data : "id=" + id_set + "&idClone=" + id + "&" + modalGroupEditForm.form.serialize(),
							dataType : "json",
							success : function(data) {
								if (data.status) {
									modalGroupEdit.dialog("close");
									$.updateGroups();
									tableGroups.flexReload();
								} else {
									modalGroupEditForm.tipsBox("Error al crear registro", "alert");
								}
							},
							error : function() {
								modalGroupEditForm.tipsBox("Error connecting to server", "alert");
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
				modalGroupEditForm.tips.text('Todo los elementos del formulario son requeridos');
				modalGroupEditForm.all.removeClass("ui-state-error").css("color", "#5A698B");
			}
		});
	};
});

function editar(id, clone) {
	$.editarModal(id, clone);
}