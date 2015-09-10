$(document).ready(function( )
{
	$("#seguridad_button").click(function( )	{

		var password_active = $("#user_passwd_active"), password = $('#user_passwd'), password_valid = $('#user_passwd_valid'), allFields_edit = $([]).add(password_active).add(password).add(password_valid), tips_edit = $("#pass_validateTips");

		var form_data = {
			password_active : password_active.val(),
			password : password.val()
		};

		var bValid = true;

		bValid = bValid && $.checkLength(tips_edit, password_active, "Contraseña Actual", 3, 100);
		bValid = bValid && $.checkLength(tips_edit, password, "Nueva Contraseña", 3, 100);
		bValid = bValid && $.checkLength(tips_edit, password_valid, "Confirmación", 3, 100);

		bValid = bValid && $.checkPass2(tips_edit, password, "Nueva Contraseña", password_valid, "Confirmación");

		allFields_edit.removeClass("ui-state-error");
		allFields_edit.css("color", "#5A698B");

		if ( bValid ) {

			$.ajax({
				type : "POST",
				url : '/login/new_pass',
				data : form_data,
				dataType : "json",
				success : function( response )
				{
					if ( response.valid ) {

						alert(response.msg);
						tips_edit.text('Todo los elementos del formulario son requeridos');
						allFields_edit.val("").removeClass("ui-state-error");
						allFields_edit.css("color", "#5A698B");

					} else {
						alert(response.msg);
					}

				}

			});

		}
		return false;
	});

	$(".column").sortable({
		connectWith : ".column"
	});

	$(".portlet").addClass("ui-widget ui-widget-content ui-helper-clearfix ui-corner-all").find(".portlet-header").addClass("ui-widget-header ui-corner-all").prepend("<span class='ui-icon ui-icon-minusthick'></span>").end().find(".portlet-content");

	$(".portlet-header .ui-icon").click(function( )
	{
		$(this).toggleClass("ui-icon-minusthick").toggleClass("ui-icon-plusthick");
		$(this).parents(".portlet:first").find(".portlet-content").toggle();
	});

	//$(".column").disableSelection();    

	$("#general_button").button();
	$("#seguridad_button").button();

	$("#switcher").themeswitcher({
		imgpath : "/sitio/img/",
		loadTheme : "ui-lightness",
		buttonheight : 25,
		width : 276,
		height : 250
	});
}); 