jQuery(function( $ )
{

	var groupFormNew = new $.formVar( 'groupFormNew' );
	var groupFormEdit = new $.formVar( 'groupFormEdit' );
	
	var modalNewGroup = $("#modalNewGroup");

	$("#fmGroup").change(function(){
		groupsTable = $("#grupos");
		groupsTable.flexReload();
	});

	$("#dialog:ui-dialog").dialog("destroy");

	modalNewGroup.dialog({
		autoOpen : false,
		resizable : false,
		height : 'auto',
		width : '640',
		modal : true,
		open : function( )
		{
			modalNewGroup.find('.ui-dialog-titlebar-close').blur();
			groupFormNew.get('grouplist').multiselect();
		},
		buttons : {
			"Accept" : function( )
			{
				var bValid = true;

				bValid = bValid && groupFormNew.checkLength('groupName', "auto", 3, 200,language.code);

				if ( bValid ) {

					$.ajax({
						type : "POST",
						url : "/admin/createGroups",
						data : groupFormNew.form.serialize(),
						dataType : "json",
						async: false,
						success : function( data )
						{
							if ( data.status ) {
								modalNewGroup.dialog("close");
								$("#grupos").flexReload();
							} else {
								groupFormNew.tipsBox(data.error, 'alert');
							}
						},
						error : function() {
							groupFormNew.errorSystem(language.code);
						}

					});

				}
			},
			Cancel : function( )
			{
				$(this).dialog("close");
			}

		},
		close : function( )
		{
			groupFormNew.get('groupName').val("");
		}

	});

	var groups;

	$("#groups-delete").dialog({
		autoOpen : false,
		resizable : false,
		width : 500,
		modal : true,
		open : function( ) {
			var countSelectGroups = 1;
			$("#groups-delete p#selected").html("Selected:");
			$('.trSelected', $('#table_planes')).each(function( ) {
				var name = $(this).children('td:nth-child(2)').text();
				$("#groups-delete p#selected").append("</br>" + countSelectGroups + " : " + name);
				countSelectGroups = countSelectGroups + 1;
			});
		},
		buttons : {
			"Accept" : function( )
			{
				$("#groups-delete").dialog("close");

				var groupsSelect = new Array( );
				count = 1;
				grid = $('#grupos');
				$('.trSelected',grid).each(function( )
				{
					var id = $(this).attr('id');
					id = id.substring(id.lastIndexOf('row') + 3);
					groupsSelect[count] = id;
					count = count + 1;
				});

				$.ajax({
					type : "POST",
					url : "/admin/deleteGroups",
					data : {
						'idGroups' : groupsSelect
					},
					dataType : "json",
					success : function( data )
					{
						if ( data.status ) {
							$("#grupos").flexReload();
						} else {
							alert("Acceso Denegado");
						}
					}

				});
			},
			Cancel : function( )
			{
				$("#groups-delete").dialog("close");
			}

		}
	});

	$.cargaPerm = function( idgroups,name )
	{

		$("#modalGroupsPerm").attr('title', name + 'group permissions');

		$("#modalGroupsPerm").load("/admin/perm", {
			id : idgroups
		}, function( responseText,textStatus,XMLHttpRequest )
		{
			$("#modalGroupsPerm").dialog("open");
		});

		var dialogWidth = 800;

		$("#modalGroupsPerm").dialog({
			resizable : false,
			height : 'auto',
			width : dialogWidth,
			position : [( $(window).width() / 2 ) - ( dialogWidth / 2 ),50],
			zIndex : 20000,
			modal : true,
			buttons : {
				"Accept" : function( )
				{
					$.post("/admin/setperm",$("#Permissions").serialize());
					$("#modalGroupsPerm").dialog("close");
				},
				Cancel : function( )
				{
					$(this).dialog("close");
				}

			}
		});

		return true;

	};

	$.editGroup = function( groupid )
	{

		$("#modalEditGroup").load("/admin/getFormEditGroup", {
			groupid : groupid
		}, function( responseText,textStatus,XMLHttpRequest )
		{
			groupFormEdit.refresh();
			$("#modalEditGroup").dialog("open");
		});

		$("#modalEditGroup").dialog({
			autoOpen : false,
			resizable : false,
			height : 'auto',
			width : 640,
			modal : true,
			open : function( )
			{
				$("#modalEditGroup").find('.ui-dialog-titlebar-close').blur();
				groupFormEdit.get('grouplist').multiselect();
			},
			buttons : {
				"Accept" : function( )
				{
					var bValid = true;

					bValid = bValid && groupFormEdit.checkLength('groupName', "auto", 3, 200,language.code);

					if ( bValid ) {

						$.ajax({
							type : "POST",
							url : "/admin/createGroups",
							data : "gorupid=" + groupid + "&" + groupFormEdit.form.serialize(),
							dataType : "json",
							success : function( data )
							{
								if ( data.status ) {
									$("#modalEditGroup").dialog("close");
									$("#grupos").flexReload();
								} else {
									updateTips("Error al crear grupo");
								}
							}

						});

					}
				},
				Cancel : function( )
				{
					$(this).dialog("close");
				}

			},
			close : function( )
			{
				
			}

		});

	};
});

function permisosGroups( idgroups,name )
{
	$.carga("/admin/perm?id=" + idgroups,"Group Permissions " + name,idgroups,name);
}