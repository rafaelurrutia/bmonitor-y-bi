var tableUsers = $('#usuarios');

var modalNewUser = $('#modal_user_new');
var modalNewUserUrl = '/admin/createUser';
var modalNewUserForm = new $.formVar( 'form_user_new' );

var modalDeleteUser = $('#modal_user_delete');
var modalDeleteUserUrl = '/admin/deleteUser';

var modalEditUser = $('#modal_use_edit');
var modalEditUserUrl = '/admin/getFormUser';
var modalEditUserForm = '#form_use_edit';

var modalEditPassUserForm = new $.formVar( 'form_user_new_pass' );

var modalEditPassUserUrl = '/admin/editPassUser';

function statusUser( id, status ) {
	if ( status ) {
		status_value = 1;
	} else {
		status_value = 0;
	}

	var request = $.ajax({
		type : "POST",
		url : "/admin/statusUser",
		data : {
			status : status_value,
			id : id
		},
		dataType : "json"
	});

	request.done(function( msg ) {
		tableUsers.flexReload();
	});

	request.fail(function( jqXHR, textStatus ) {
		alert("Error connecting to server");
	});
}

jQuery(function( $ ) {

	$.userSchedule = function(idUser) {
		alert("Funcion no disponible");
	};
	
	//var idUser = null;
	$.userPass = function(iduser) {
		idUser = iduser;
		$("#modal_user_edit_pass").dialog("open");
	};

	var buttonModalEditPassUser = {};

	buttonModalEditPassUser[language.accept] = function( ) {
		var bValid = true;

		bValid = bValid && modalEditPassUserForm.checkLength('password', 'auto', 5, 16, language.code);

		bValid = bValid && modalEditPassUserForm.checkEqual('password', 'confirmation', language.code);

		if ( bValid ) {

			jQuery('#loading').show();
			jQuery('#imgLoading').show();

			var request = $.ajax({
				type : "POST",
				url : modalEditPassUserUrl+"/"+idUser,
				data : modalEditPassUserForm.form.serialize(),
				dataType : "json"
			});

			request.done(function( data ) {
				if ( data.status ) {
					$("#modal_user_edit_pass").dialog("close");
					tableUsers.flexReload();
				} else {
					modalEditPassUserForm.tipsBox(data.msg, 'alert');
				}
			});

			request.fail(function( jqXHR, textStatus ) {
				modalEditPassUserForm.errorSystem(language.code);
			});

			jQuery('#loading').hide();
			jQuery('#imgLoading').hide();

		}
	};

	buttonModalEditPassUser[language.cancel] = function( ) {
		$(this).dialog("close");
	};

	$("#modal_user_edit_pass").dialog({
		autoOpen : false,
		resizable : false,
		height : 'auto',
		width : 450,
		modal : false,
		zIndex : 20000,
		closeOnEscape : true,
		draggable : true,
		buttons : buttonModalEditPassUser,
		close : function( ) {
			modalEditPassUserForm.get('password').val("");
			modalEditPassUserForm.get('confirmation').val("");
		}
	});

	var buttonModalNewUser = {};

	buttonModalNewUser[language.accept] = function( ) {

		var bValid = true;

		bValid = bValid && modalNewUserForm.checkLength('user_name', 'auto', 3, 200, language.code);
		bValid = bValid && modalNewUserForm.checkLength('user_email', 'auto', 6, 200, language.code);

		bValid = bValid && modalNewUserForm.checkRegexp('user_email', /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,4})+$/, "auto", language.code);

		bValid = bValid && modalNewUserForm.checkLength('user_user', 'auto', 3, 30, language.code);

		//bValid = bValid && modalNewUserForm.checkRegexp('user_user', /^[a-z]([0-9a-z_])+$/i, "The User
		// field only allows : a-z 0-9.");

		bValid = bValid && modalNewUserForm.checkLength('user_passwd', 'auto', 5, 16, language.code);

		bValid = bValid && modalNewUserForm.checkEqual('user_passwd', 'user_passwd_valid', language.code);

		bValid = bValid && modalNewUserForm.checkSelect('user_id_group', "auto", 0);

		if ( bValid ) {

			jQuery('#loading').show();
			jQuery('#imgLoading').show();

			var request = $.ajax({
				type : "POST",
				url : modalNewUserUrl,
				data : "id=new" + "&" + modalNewUserForm.form.serialize(),
				dataType : "json"
			});

			request.done(function( data ) {
				if ( data.status ) {
					$('#modal_user_new').dialog("close");
					$("form#form_user_new input").each(function(){
						$(this).val("");
					});
					$("form#form_user_new select#user_id_group option[value=0]").attr("selected", "selected");
					tableUsers.flexReload();
				} else {
					modalNewUserForm.tipsBox(data.msg, 'alert');
				}
			});

			request.fail(function( jqXHR, textStatus ) {
				modalNewUserForm.errorSystem(language.code);
			});
			
			request.always(function() {
				jQuery('#loading').hide();
				jQuery('#imgLoading').hide();
			});
		}
	};

	buttonModalNewUser[language.cancel] = function( ) {
		$(this).dialog("close");
		$("form#form_user_new input").each(function(){
			$(this).val("");
		});
		$("form#form_user_new select#user_id_group option[value=0]").attr("selected", "selected");
	};

	modalNewUser.dialog({
		autoOpen : false,
		resizable : false,
		height : 650,
		width : 450,
		modal : true,
		open : function( ) {
			modalNewUser.find('.ui-dialog-titlebar-close').blur();
		},
		buttons : buttonModalNewUser
	});

	var buttonModalDeleteUser = {};

	buttonModalDeleteUser[language.accept] = function( ) {

		jQuery('#loading').show();
		jQuery('#imgLoading').show();

		var select = new Array( );
		count = 1;
		$('.trSelected', tableUsers).each(function( ) {
			var id = $(this).attr('id');
			id = id.substring(id.lastIndexOf('row') + 3);
			if ( $.isNumeric(id) ) {
				select[count] = id;
				count = count + 1;
			}
		});

		var request = $.ajax({
			type : "POST",
			url : modalDeleteUserUrl,
			data : {
				idSelect : select
			},
			dataType : "json"
		});
		
		request.done(function( data ) {
			if ( data.status ) {
				tableUsers.flexReload();
				modalDeleteUser.dialog("close");
			} else {
				alert(data.error);
			}
		});

		request.fail(function( jqXHR, textStatus ) {
			alert("Error de conexion");
		});
		
		request.always(function() {
			jQuery('#loading').hide();
			jQuery('#imgLoading').hide();
		});
	};

	buttonModalDeleteUser[language.cancel] = function( ) {
		$(this).dialog("close");
	};

	modalDeleteUser.dialog({
		autoOpen : false,
		resizable : false,
		height : "auto",
		width : 400,
		modal : true,
		buttons : buttonModalDeleteUser,
		open: function(event, ui){
			var aUserDel = new Array();
			
			$(".trSelected", tableUsers).each(function(){
				usertext = $("td", this).eq(1).text();
				aUserDel.push(usertext);
			});
			
			$("div#modal_user_delete span#usersdel").empty().append(aUserDel.join(", "));
		}
	});

	$.editarModal = function( id, clonar ) {

		if ( clonar ) {
			var id_set = "clone";
		} else {
			var id_set = id;
		}

		modalEditUser.load(modalEditUserUrl, {
			IDSelect : id
		}, // omit this param object to issue a GET request instead a POST request, otherwise you may provide
		// post parameters within the object
		function( responseText, textStatus, XMLHttpRequest ) {
			// remove the loading class
			modalEditUser.removeClass('loading');

			if ( clonar ) {
				var name1 = user_name_edit.val();
				user_name_edit.val(name1 + '_1');
			}

			modalEditUser.dialog("open");
		});

		$(modalEditUser).dialog({
			autoOpen : false,
			resizable : false,
			height : 'auto',
			width : 1000,
			modal : true,
			closeOnEscape : true,
			draggable : true,
			open : function( ) {
				$(this).find('.ui-dialog-titlebar-close').blur();
			},
			buttons : {
				"Save" : function( ) {

					allFields_edit.removeClass("ui-state-error");
					allFields_edit.css("color", "#5A698B");

					bValid = bValid && $.checkLength(tips_edit, user_name_edit, "Name", 3, 100);
					bValid = bValid && $.checkLength(tips_edit, user_email_edit, "Email", 6, 80);

					bValid = bValid && $.checkRegexp(tips_edit, user_email_edit, /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,4})+$/, "Email incorrecto ej. soporte@bsw.cl");

					bValid = bValid && $.checkLength(tips_edit, user_user_edit, "User", 3, 30);

					bValid = bValid && $.checkRegexp(tips_edit, user_user_edit, /^[a-z]([0-9a-z_])+$/i, "Username may consist of a-z, 0-9, underscores, begin with a letter.");

					bValid = bValid && $.checkLength(tips_edit, user_passwd_edit, "password", 5, 16);

					bValid = bValid && $.checkRegexp(tips_edit, user_passwd_edit, /^([0-9a-zA-Z])+$/, "The Password field only allows : a-z 0-9");

					bValid = bValid && $.checkPass(tips_edit, user_passwd_edit, user_passwd_valid_edit);

					bValid = bValid && $.checkSelect(tips_edit, user_id_group_edit, "Group", 0);

					if ( bValid ) {

						jQuery('#loading').show();
						jQuery('#imgLoading').show();

						$.ajax({
							type : "POST",
							url : modalNewUserUrl,
							data : "id=" + id_set + "&" + $(modalEditUserForm).serialize(),
							dataType : "json",
							success : function( data ) {
								if ( data.status ) {
									$(modalEditUser).dialog("close");
									$(tableUsers).flexReload();
								} else {
									$.updateTips(tips_edit, "Error al crear registro");
								}
							},
							error : function( ) {
								$.updateTips(tips_edit, "Error connecting to server");
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
				tips_edit.text('All form elements are required');
				allFields_edit.val("").removeClass("ui-state-error");
				allFields_edit.css("color", "#5A698B");
			}

		});
	};
});

function userEdit( id, clone ) {
	$.editarModal(id, clone);
}