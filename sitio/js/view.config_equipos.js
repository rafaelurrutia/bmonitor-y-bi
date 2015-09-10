jQuery(function( $ )
{
	var formNewAgent;
	var formEdit;

	formNewAgent = new $.formVar( 'form_new_sonda' );
	
	formEdit = new $.formVar( 'form_edit_sonda' );
	
	// Formularios

	var form_new = "form_new_sonda";
	var form_edit = "form_edit_sonda";

	var urlGetSondaForm = "/config/getSondaForm";
	var urlCreateSonda = "/config/createSonda";
	var urlEditSonda = "/config/editSonda";

	//Set box
	$("#dialog:ui-dialog").dialog("destroy");
	
	var buttons = [,];
	buttons['accept'] = language.accept;
	buttons['cancel'] = language.cancel;

	var TabsSonda_new = $("#tabs_" + form_new).tabs();
	
	TabsSonda_new.tabs("option","disabled",[1]);
	

	// Param Global

	formNewAgent.get('sonda_mac_wan').mask("**:**:**:**:**:**");
	formNewAgent.get('sonda_mac_lan').mask("**:**:**:**:**:**");
	formNewAgent.get('sonda_ip_lan').ipAddress();
	formNewAgent.get('sonda_netmask_lan').ipAddress();
	formNewAgent.get('sonda_ip_wan').ipAddress();

	formNewAgent.get('secc_lanconfig','div#').hide();

	var profile_status = 0;
	var plan_status = 0;
	var auth_metod = 'MAC';

	//sampleTags = ['speedtest','youtube','dns'];

	formNewAgent.get('sonda_tags').tagit({
		availableTags : sampleTags
	});

	formNewAgent.get('sonda_mac_wan').keyup(function( e )
	{
		formNewAgent.get('sonda_dns').val("BSW" + this.value.toUpperCase().replace(new RegExp( ':|_','g' ),""));
		return false;
	});

	function getPlan( form )
	{
		if ( form.get('sonda_group').val() > 0 ) {

			$.ajax({
				type : "POST",
				url : "/config/getPlanOption",
				data : {
					groupid : form.get('sonda_group').val()
				},
				dataType : "json",
				success : function( data )
				{
					if ( data.status ) {
						form.get('sonda_plan').html(data.datos);
					} else {
						alert("Server Error");
					}
				},
				error : function( )
				{
					alert("Connection Error");
				}

			});
			
			return false;

		} else {
			form.get('sonda_plan').html('<option selected value="0">Select a group</option>');
			return false;
		}
	}

	function getProfile( form )
	{
		if ( form.get('sonda_group').val() > 0 ) {

			$.ajax({
				type : "POST",
				url : "/config/getProfileOption",
				data : {
					groupid : form.get('sonda_group').val()
				},
				dataType : "json",
				success : function( data )
				{
					if ( data.status ) {
						form.get('sonda_profile').html(data.datos);
					} else {
						alert("Server Error");
					}
				},
				error : function( )
				{
					alert("Connection Error");
				}

			});

		} else {
			form.get('sonda_profile').html('<option selected value="0">Select a group</option>');
		}
		return false;
	}

	$.profileDefault = function( idvalue,value )
	{
		$("#" + idvalue).val( value);
		var color = $("#" + idvalue).css( "color", '#99CCCC' );
		return false;
	};

	function tabProfile( form,tabs,idsonda )
	{

		var tab = tabs.find(".ui-tabs-nav li:eq(3)").remove();

		var panelId = tab.attr("aria-controls");

		$("#" + panelId).remove();

		tabs.tabs("refresh");

		var tab_title_input = 'Perfil';

		tabs.find(".ui-tabs-nav").append("<li><a href='#tabs-4'>" + tab_title_input + "</a></li>");
		
		if(idsonda != false){
			sendForm = "profileid=" + form.get('sonda_profile').val() + "&idsonda=" + idsonda;
		} else {
			sendForm = "profileid=" + form.get('sonda_profile').val();
		}

		$.ajax({
			type : "POST",
			async: false,
			url : "/config/getFormProfile",
			data : sendForm,
			dataType : "json",
			success : function( data )
			{
				if ( data.status ) {
					
					form.form.append("<div id='tabs-4' style='display: none;'><p>" + data.datos + "</p></div>");

					//form.get('profileDefault')
					$("form #profileDefault").button().click(function( event )
					{
						return false;
					});
					
					$("form #profileDefault").parent('span').keyup(function() {
						var color = $( 'input', this ).css( "color", '#000000' );
					});
					
				} else {
					form.form.append("<div id='tabs-4' style='display: none;'><p>" + data.error + "</p></div>");
				}
			},
			error : function( )
			{
				form.form.append("<div id='tabs-4' style='display: none;'><p>Connection Error</p></div>");
			}

		});

		tabs.tabs("refresh");
		form.refresh();
		form.get('accordion').accordion({heightStyle: "content", collapsible: true, active : false});
		return false;
	}

	function tabProfileParam( form,tabs,idsonda )
	{

		var tab = tabs.find(".ui-tabs-nav li:eq(4)").remove();

		var panelId = tab.attr("aria-controls");

		$("#" + panelId).remove();

		tabs.tabs("refresh");

		var tab_title_input = 'Perfil - Param';

		tabs.find(".ui-tabs-nav").append("<li><a href='#tabs-5'>" + tab_title_input + "</a></li>");
		
		if(idsonda != false){
			sendForm = "profileid=" + form.get('sonda_profile').val() + "&idsonda=" + idsonda;
		} else {
			sendForm = "profileid=" + form.get('sonda_profile').val();
		}

		$.ajax({
			type : "POST",
			async: false,
			url : "/config/getFormProfileParam",
			data : sendForm,
			dataType : "json",
			success : function( data )
			{
				if ( data.status ) {
					form.form.append("<div id='tabs-5' style='display: none;'><p>" + data.datos + "</p></div>");

					$("form #profileDefault").button().click(function( event )
					{
						return false;
					});

					$("form #profileDefault").parent('span').keyup(function() {
							var color = $( 'input', this ).css( "color", '#000000' );
					});
					
				} else {
					form.form.append("<div id='tabs-5' style='display: none;'><p>" + data.error + "</p></div>");
				}
			},
			error : function( )
			{
				form.form.append("<div id='tabs-5' style='display: none;'><p>Connection Error</p></div>");
			}

		});

		tabs.tabs("refresh");
		form.refresh();
		form.get('accordion').accordion({heightStyle: "content", collapsible: true, active : false});
		return false;
	}

	formNewAgent.get('sonda_group').change(function( )
	{
		
		var tab = TabsSonda_new.find(".ui-tabs-nav li:eq(4)").remove();
		var tab = TabsSonda_new.find(".ui-tabs-nav li:eq(3)").remove();
		var tab = TabsSonda_new.find(".ui-tabs-nav li:eq(2)").remove();

		formNewAgent.get('sonda_profile').eq(0).prop('selected',true);

		var panelId = tab.attr("aria-controls");
		
		$("#" + panelId).remove();

		TabsSonda_new.tabs("refresh");

		var tab_title_input = formNewAgent.get("sonda_group").find("option:selected").text();

		TabsSonda_new.find(".ui-tabs-nav").append("<li><a href='#tabs-3'>General</a></li>");

		var profile = 0;
		var plan = 1;
		var method_auth = 'MAC';
		
		var request = $.ajax({
			type : "POST",
			url : "/config/getFormFeature",
			data : "groupid=" + formNewAgent.get('sonda_group').val(),
			dataType : "json",
			async : false
		});

		request.done(function( data ) {
			if ( data.status ) {
				formNewAgent.form.append("<div id='tabs-3' style='display: none;'><p>" + data.datos + "</p></div>");

				if ( data.profile == 1 ) {
					formNewAgent.get('secc_profile').show();
					getProfile(formNewAgent);
				} else {
					formNewAgent.get('secc_profile').hide();
				}

				if ( data.method_auth == 'IDENTIFICATIONID' ) {
					formNewAgent.get('secc_identificator').show();
				} else {
					formNewAgent.get('secc_identificator').hide();
				}

				if ( data.configTab == 'true' ) {
					TabsSonda_new.tabs("enable",1);
				} else {
					TabsSonda_new.tabs("option","disabled",[1]);
				}

				if ( data.lanConfig == 'true' ) {
					formNewAgent.get('secc_lanconfig','div#').show();
				} else {
					formNewAgent.get('secc_lanconfig','div#').hide();
				}

				if ( data.plan == 1 ) {
					formNewAgent.get('secc_plan').show();
					getPlan(formNewAgent);
				} else {
					formNewAgent.get('secc_plan').hide();
					TabsSonda_new.tabs("refresh");
				}
				
				formNewAgent.locationSelect();
				
			} else {
				formNewAgent.form.append("<div id='tabs-3' style='display: none;'><p>" + data.error + "</p></div>");
			}
		});

		request.fail(function( jqXHR, textStatus ) {
			formNewAgent.form.append("<div id='tabs-3' style='display: none;'><p>Connection Error</p></div>");
		});
								
		TabsSonda_new.tabs("refresh");
		return true;
	});

	formNewAgent.get('sonda_profile').change(function( )
	{
		tabProfile(formNewAgent,TabsSonda_new,false);
		tabProfileParam(formNewAgent,TabsSonda_new,false);
		return false;
	});

	var buttonModalAgentForm = {};
	
	buttonModalAgentForm[language.accept] = function() { 

		var bValid = true;
	
		formNewAgent.refresh();
	
		bValid = bValid && formNewAgent.checkLength('sonda_name', "auto", 3, 200,language.code);
		bValid = bValid && formNewAgent.checkSelect('sonda_group', "auto", 0,language.code);

		if ( formNewAgent.get('secc_profile').css('display') !== 'none' ) {
			bValid = bValid && formNewAgent.checkSelect('sonda_profile', "auto", 0,language.code);
		}
		
		if ( formNewAgent.get('secc_plan').css('display') !== 'none' ) {
			bValid = bValid && formNewAgent.checkSelect('sonda_plan', "auto", 0,language.code);
		}

		//bValid = bValid && formNewAgent.checkLength('sonda_dns', "auto", 3, 15,language.code);
		bValid = bValid && formNewAgent.checkLength('sonda_ip_wan', "auto", 6, 15,language.code);
		bValid = bValid && formNewAgent.checkLength('sonda_mac_wan', "auto", 17, 17,language.code);
		
	
		if ( bValid ) {
	
			jQuery('#loading').show();
			jQuery('#imgLoading').show();	
			cadena = formNewAgent.form.serialize() + '&sonda_dns=' + formNewAgent.get('sonda_dns').val();	
			$.ajax({
				type : "POST",
				url : urlCreateSonda,
				data : cadena,
				dataType : "json",
				success : function( data )
				{
					if ( data.status ) {
						$("#modal_sonda_form").dialog("close");
						formNewAgent.get('fmGrupo').val(formNewAgent.get('sonda_group').val());
						$(".sondas").flexReload();
						return true;
					} else {
						formNewAgent.tipsBox(data.msg, 'alert');
						return false;
					}
				},
				error : function( )
				{
					formNewAgent.tipsBox("Error connecting to server", 'alert');
					return false;
				}
	
			});
			jQuery('#loading').hide();
			jQuery('#imgLoading').hide();
		}
	};

	buttonModalAgentForm[language.cancel] = function() {
		$(this).dialog("close");
	};

	$("#modal_sonda_form").dialog({
		autoOpen : false,
		resizable : false,
		height : 'auto',
		width : 660,
		modal : false,
		zIndex : 20000,
		closeOnEscape : true,
		draggable : true,
		open : function( )
		{
			$(this).find('.ui-dialog-titlebar-close').blur();
		},
		buttons : buttonModalAgentForm,
		close : function( )
		{
			formNewAgent.get('sonda_group').val(0);
			formNewAgent.get('sonda_plan').val(0);
			formNewAgent.get('sonda_dns').val("");
			formNewAgent.get('sonda_mac_wan').val("00:00:00:00:00:00");
			formNewAgent.get('sonda_ip_wan').val("___.___.___.___");
			formNewAgent.get('sonda_ip_lan').val("192.168.1.1");
			formNewAgent.get('sonda_netmask_lan').val("255.255.255.0");
			formNewAgent.get('secc_plan').hide();
			formNewAgent.get('secc_profile').hide();
			formNewAgent.get('secc_identificator').hide();
			var tab = TabsSonda_new.find(".ui-tabs-nav li:eq(2)").remove();
			var panelId = tab.attr("aria-controls");
			$("#" + panelId).remove();
			var tab = TabsSonda_new.find(".ui-tabs-nav li:eq(3)").remove();
			var panelId = tab.attr("aria-controls");
			$("#" + panelId).remove();
			var tab = TabsSonda_new.find(".ui-tabs-nav li:eq(4)").remove();
			var panelId = tab.attr("aria-controls");
			$("#" + panelId).remove();
			$("tabs").tabs("refresh");
		}

	});

	$("#sondas-delete").dialog({
		autoOpen : false,
		resizable : false,
		height : 'auto',
		width : 384,
		modal : false,
		zIndex : 20000,
		open : function( )
		{

			var grid = $('.sondas');
			var countSelect = 1;
			$("#sondas-delete #selected").html("Selected:");
			$('.trSelected',grid).each(function( )
			{
				var name = $(this).children('td:first').text();
				$("#sondas-delete #selected").append("</br>" + countSelect + " : " + name);
				countSelect = countSelect + 1;
			});

		},
		buttons : {
			"Accept" : function( )
			{

				jQuery('#loading').show();
				jQuery('#imgLoading').show();

				var sondasSelect = new Array( );
				var sondasSelectName = new Array( );
				count = 1;

				grid = $('.sondas');

				$('.trSelected',grid).each(function( )
				{
					var id = $(this).attr('id');

					var name = $(this).children('td:first').text();

					id = id.substring(id.lastIndexOf('row') + 3);
					if ( $.isNumeric(id) ) {
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
					success : function( data )
					{
						if ( data.status ) {
							$(".sondas").flexReload();
							return true;
						} else {
							alert("Access denied");
							return false;
						}
					},
					error : function( )
					{
						alert("Connection Error");
						$("#sondas-delete").dialog("close");
					}

				});
				jQuery('#loading').hide();
				jQuery('#imgLoading').hide();
				$("#sondas-delete").dialog("close");
			},
			Cancel : function( )
			{
				$("#sondas-delete").dialog("close");
			}

		}
	});
	
	var buttonModalConfigForm = {};
	
	buttonModalConfigForm[language.accept] = function() { 
		jQuery('#loading').show();
		jQuery('#imgLoading').show();

		var sondasSelect = new Array( );
		count = 1;
		grid = $('.sondas');
		$('.trSelected',grid).each(function( )
		{
			var id = $(this).attr('id');
			id = id.substring(id.lastIndexOf('row') + 3);
			if ( $.isNumeric(id) ) {
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
			success : function( data )
			{
				if ( data.status ) {
					$(".sondas").flexReload();
					return true;
				} else {
					alert("Access denied");
					return false;
				}
			},
			error : function( )
			{
				alert("Connection Error");
				$("#sondas-delete").dialog("close");
			}

		});
		jQuery('#loading').hide();
		jQuery('#imgLoading').hide();
		$("#sonda-config").dialog("close");
		return false;
	};

	buttonModalConfigForm[language.cancel] = function() {
		$(this).dialog("close");
	};

	$("#sonda-config").dialog({
		autoOpen : false,
		resizable : false,
		height : 150,
		width : 400,
		zIndex : 20000,
		modal : false,
		buttons : buttonModalConfigForm
	});

	var buttonModalUpgradeForm = {};
	
	buttonModalUpgradeForm[language.accept] = function() { 
		jQuery('#loading').show();
		jQuery('#imgLoading').show();

		var sondasSelect = new Array( );
		count = 1;
		grid = $('.sondas');
		$('.trSelected',grid).each(function( )
		{
			var id = $(this).attr('id');
			id = id.substring(id.lastIndexOf('row') + 3);
			if ( $.isNumeric(id) ) {
				sondasSelect[count] = id;
				count = count + 1;
			}
		});

		$.ajax({
			type : "POST",
			url : "/config/setUpgradeSonda",
			data : {
				idSonda : sondasSelect
			},
			dataType : "json",
			success : function( data )
			{
				if ( data.status ) {
					$(".sondas").flexReload();
					return true;
				} else {
					alert("Access denied");
					return false;
				}
			},
			error : function( )
			{
				alert("Connection Error");
			}

		});
		jQuery('#loading').hide();
		jQuery('#imgLoading').hide();
		$("#sonda-update").dialog("close");
		return false;
	};

	buttonModalUpgradeForm[language.cancel] = function() {
		$(this).dialog("close");
	};

	$("#sonda-update").dialog({
		autoOpen : false,
		resizable : false,
		height : 150,
		width : 400,
		zIndex : 20000,
		modal : false,
		buttons : buttonModalUpgradeForm
	});
	
	$.editarSonda = function( id,clonar )
	{

		var dialog = $('<div style="display:none" id="editSondaDialog" class="loading"></div>').appendTo('body');

		if ( clonar ) {
			var id_set = "clone";
			var type = 'clone';
			var url_send = urlCreateSonda;
		} else {
			var type = 'edit';
			var id_set = id;
			var url_send = urlEditSonda;
		}

		$('#loading').show();
		$('#imgLoading').show();
			
		$("#modal_sonda_edit").load(urlGetSondaForm, {
			sondaID : id,
			type : type
		}, function( responseText,textStatus,XMLHttpRequest )
		{


			
			formEdit.refresh();
						
			formEdit.get('sonda_tags').tagit({
				availableTags : sampleTags
			});

			if ( clonar ) {
				var name1 = formEdit.get('sonda_name').val();
				formEdit.get('sonda_name').val(name1 + '_1');
			}

			$("#modal_sonda_edit").removeClass('loading');

			formEdit.get('sonda_mac_wan').mask("**:**:**:**:**:**");
			formEdit.get('sonda_mac_lan').mask("**:**:**:**:**:**");
			formEdit.get('sonda_ip_lan').ipAddress();
			formEdit.get('sonda_netmask_lan').ipAddress();
			formEdit.get('sonda_ip_wan').ipAddress();

			formEdit.get('secc_lanconfig','div#').hide();

			formEdit.get('sonda_mac_wan').keyup(function( e )
			{
				formEdit.get('sonda_dns').val("BSW" + this.value.toUpperCase().replace(new RegExp( ':|_','g' ),""));
			});

			var TabsSonda_edit = $("#tabs_" + form_edit).tabs();

			var tab_title_input = formEdit.get("sonda_group").find("option:selected").text();

			TabsSonda_edit.find(".ui-tabs-nav").append("<li><a href='#tabs-3'>General</a></li>");

			if(formEdit.get('sonda_opc_region').val() == '-1'){
				formEdit.get('sonda_opc_city').combobox({
			          select: function( event, ui ) {
						updateLatAndLong(formEdit.get('sonda_opc_city').val());
			          }
				});
			}
			
			var request =  $.ajax({
				type : "POST",
				async:  false,
				url : "/config/getFormFeature",
				data : {
					groupid : formEdit.get('sonda_group').val(),
					id : id_set
				},
				dataType : "json"
			});

			request.fail(function( jqXHR, textStatus ) {
				formEdit.form.append("<div id='tabs-3' style='display: none;'><p>Connection Error</p></div>");
			});
			
			request.done(function( data ) {
				if ( data.status ) {

					formEdit.form.append("<div id='tabs-3' style='display: none;'><p>" + data.datos + "</p></div>");

					if ( data.profile == 1 ) {
						formEdit.get('secc_profile').show();
						tabProfile(formEdit,TabsSonda_edit,id_set);
						tabProfileParam(formEdit,TabsSonda_edit,id_set);
					} else {
						formEdit.get('secc_profile').hide();
					}

					if ( data.method_auth == 'IDENTIFICATIONID' ) {
						formEdit.get('secc_identificator').show();
					} else {
						formEdit.get('secc_identificator').hide();
					}

					if ( data.configTab == 'true' ) {
						TabsSonda_edit.tabs("enable",1);
					} else {
						TabsSonda_edit.tabs("option","disabled",[1]);
					}

					if ( data.lanConfig == 'true' ) {
						formEdit.get('secc_lanconfig','div#').show();
					} else {
						formEdit.get('secc_lanconfig','div#').hide();
					}

					if ( data.plan == 1 ) {
						formEdit.get('secc_plan').show();
						//getPlan(formEdit);
					} else {
						formEdit.get('secc_plan').hide();
						TabsSonda_edit.tabs("refresh");
					}

				} else {
					formEdit.form.append("<div id='tabs-3' style='display: none;'><p>" + data.error + "</p></div>");
				}
			});
			
			TabsSonda_edit.tabs("refresh");

			formEdit.get("sonda_group").change(function( )
			{

				var tab = TabsSonda_edit.find(".ui-tabs-nav li:eq(2)").remove();
				var panelId = tab.attr("aria-controls");
				$("#" + panelId).remove();
				$("tabs").tabs("refresh");

				//TabsSonda_edit.tabs( "remove", 2 );

				var tabsSelect = $(sonda_group_edit);
				var tabsSelectText = $(sonda_group_edit + " option:selected").text();
				tab_title_input = tabsSelectText;

				TabsSonda_edit.tabs("add","#tabs-3",tab_title_input);

				if ( tabsSelect.val() > 0 ) {
					var request =  $.ajax({
						type : "POST",
						url : "/config/getPlanOption",
						data : {
							groupid : tabsSelect.val()
						},
						dataType : "json"
					});
					
					request.done(function( data ) {
						if ( data.status ) {
							$(sonda_plan_edit).html(data.datos);
						} else {
							alert("Server Error");
						}
					});
					
					request.fail(function( jqXHR, textStatus ) {
						alert("Connection Error");
					});
			
				} else {
					$(sonda_plan_edit).html('<option selected value="0">Select a group</option>');
				}
			});

			formEdit.get('sonda_profile').change(function( )
			{
				tabProfile(formEdit,TabsSonda_edit,id_set);
				tabProfileParam(formEdit,TabsSonda_edit,id_set);
			});
			
			formEdit.locationSelect();
			
			$("#modal_sonda_edit").dialog("open");
			$('#loading').hide();
			$('#imgLoading').hide();
		});

		var buttonModalAgentFormEdit = {};

		buttonModalAgentFormEdit[language.accept] = function() {
			var bValid = true;

			jQuery('#loading').show();
			jQuery('#imgLoading').show();
			
			bValid = bValid && formEdit.checkLength('sonda_name', "auto", 3, 200,language.code);
			bValid = bValid && formEdit.checkSelect('sonda_group', "auto", 0,language.code);
	
			if ( formEdit.get('secc_profile').css('display') !== 'none' ) {
				bValid = bValid && formEdit.checkSelect('sonda_profile', "auto", 0,language.code);
			}
			
			if ( formEdit.get('secc_plan').css('display') !== 'none' ) {
				bValid = bValid && formEdit.checkSelect('sonda_plan', "auto", 0,language.code);
			}
	
			bValid = bValid && formEdit.checkLength('sonda_ip_wan', "auto", 6, 15,language.code);
			bValid = bValid && formEdit.checkLength('sonda_mac_wan', "auto", 17, 17,language.code);

			if ( bValid ) {
				$.ajax({
					type : "POST",
					url : url_send,
					dataType : "json",
					data : "id=" + id_set + "&" + formEdit.form.serialize() + '&sonda_dns=' + formEdit.get('sonda_dns').val(),
					success : function( data )
					{
						if ( data.status ) {
							$("#modal_sonda_edit").dialog("close");
							$(".sondas").flexReload();
						} else {
							formEdit.tipsBox(data.msg, 'alert');
						}
					},
					error : function( )
					{
						formEdit.tipsBox("Error connecting to server", 'alert');
					}

				});

			};
			
			jQuery('#loading').hide();
			jQuery('#imgLoading').hide();
		};

		buttonModalAgentFormEdit[language.cancel] = function() {
			$(this).dialog("close");
		};
		
		$("#modal_sonda_edit").dialog({
			resizable : false,
			height : 'auto',
			autoOpen: false,
			width : 660,
			zIndex : 20000,
			modal : false,
			cache : false,
			position : ['middle',20],
			buttons : buttonModalAgentFormEdit
		});

	};
});

function statusSonda( id,status )
{

	if ( status ) {
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
		success : function( data )
		{
			if ( data.status ) {
				$(".sondas").flexReload();
			} else {
				alert("Error interno");
			}
		},
		error : function( )
		{
			alert("Connection Error con el servidor");
		}

	});
}

function editSonda( id,clonar )
{
	$.editarSonda(id,clonar);
	return false;
}

var sendIDButton = '';

$("#modal_sonda_trigger").dialog({
	autoOpen : false,
	resizable : false,
	height : 200,
	width : 450,
	modal : false,
	buttons : {
		"Accept" : function( )
		{

			var bValid = true;

			jQuery('#loading').show();
			jQuery('#imgLoading').show();

			bValid = bValid && $.checkLength($('#config_equipos_trigger_validateTips'),$('#trigger_responsable'),"Responsable",3,40);
			bValid = bValid && $.checkSelect($('#config_equipos_trigger_validateTips'),$('#trigger_id'),"Trigger",0);

			if ( bValid ) {
				$.ajax({
					type : "POST",
					url : "/config/setTriggerSonda",
					dataType : "json",
					data : "idSonda=" + sendIDButton + "&" + $('#config_equipos_trigger').serialize(),
					success : function( data )
					{
						if ( data.status ) {
							$("#modal_sonda_trigger").dialog("close");
						} else {
							alert("Access denied");
						}
					},
					error : function( )
					{
						alert("Connection Error");
					}

				});
			}
			jQuery('#loading').hide();
			jQuery('#imgLoading').hide();

		},
		Cancel : function( )
		{
			$("#modal_sonda_trigger").dialog("close");
		}

	}

});

function triggerSonda( id )
{
	sendIDButton = id;
	$("#modal_sonda_trigger").dialog("open");
}

function configSonda( id )
{

	var sondasSelect = new Array( );
	sondasSelect[0] = id;

	$.ajax({
		type : "POST",
		url : "/config/setConfigSonda",
		data : {
			idSonda : sondasSelect
		},
		dataType : "json",
		success : function( data )
		{
			if ( data.status ) {
				alert("informed settings");
			} else {
				alert("Access denied");
			}
		},
		error : function( )
		{
			alert("Connection Error");
			$("#sondas-delete").dialog("close");
		}

	});
}