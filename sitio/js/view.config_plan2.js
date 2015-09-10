jQuery(function($) {

	formPlanNew = new $.formVar( 'config_planes_form_new' );
	formPlanEdit = new $.formVar( 'config_planes_form_edit' );
	
	var modalPlanNew = $('#modal_plan_new');
	var modalPlanNewUrl = '/config/createPlan';

	var modalPlanDelete = $('#modal_plan_delete');
	var modalPlanDeleteUrl = '/config/deletePlan';

	var modalEdit = $('#modal_plan_edit');
	var modalPlanEditUrl = '/config/getPlanForm';

	modalPlanNew.dialog({
		autoOpen : false,
		resizable : false,
		width : 650,
		modal : true,
		closeOnEscape : true,
		zIndex : 20000,
		draggable : true,
		open : function() {
			modalPlanNew.find('.ui-dialog-titlebar-close').blur();
			formPlanNew.get('planes_groups').multiselect();
			formPlanNew.get('planes_groups').multiselect('locale', lang );
		},
		buttons : {
			"Accept" : function() {

				var bValid = true;

				bValid = bValid && formPlanNew.checkLength('plan_plan', "auto", 3, 200,language.code);
				bValid = bValid && formPlanNew.checkLength('plan_plandesc', "auto", 3, 200,language.code);
				bValid = bValid && formPlanNew.checkLength('plan_planname', "auto", 3, 200,language.code);
				bValid = bValid && formPlanNew.checkSelect('planes_groups', "auto", 0,language.code);
				bValid = bValid && formPlanNew.checkLength('plan_nacD', "Input", 1, 200,language.code);
				bValid = bValid && formPlanNew.checkLength('plan_nacU', "Input", 1, 200,language.code);

				if (bValid) {

					jQuery('#loading').show();
					jQuery('#imgLoading').show();

					$.ajax({
						type : "POST",
						url : modalPlanNewUrl,
						data : "id=new" + "&" + formPlanNew.form.serialize(),
						dataType : "json",
						success : function(data) {
							if (data.status) {
								modalPlanNew.dialog("close");
								tablePlanes.flexReload();
							} else {
								formPlanNew.tipsBox(data.error, 'alert');
							}
						},
						error : function() {
							formPlanNew.errorSystem(language.code);
						}
					});

					jQuery('#loading').hide();
					jQuery('#imgLoading').hide();

				}
			},
			Cancel : function() {
				modalPlanNew.dialog("close");
			}
		},
		close : function() {
			
		}
	});

	modalPlanDelete.dialog({
		autoOpen : false,
		resizable : false,
		zIndex : 20000,
		width : 500,
		modal : true,
		open : function( ) {
			var countSelectPlan = 1;
			$("#modal_plan_delete p#selected").html("Selected:");
			$('.trSelected', tablePlanes).each(function( ) {
				var name = $(this).children('td:nth-child(2)').text();
				$("#modal_plan_delete p#selected").append("</br>" + countSelectPlan + " : " + name);
				countSelectPlan = countSelectPlan + 1;
			});
		},
		buttons : {
			"Accept" : function() {

				jQuery('#loading').show();
				jQuery('#imgLoading').show();

				var select = new Array();
				count = 1;
				$('.trSelected', tablePlanes).each(function() {
					var id = $(this).attr('id');
					id = id.substring(id.lastIndexOf('row') + 3);
					if ($.isNumeric(id)) {
						select[count] = id;
						count = count + 1;
					}
				});

				$.ajax({
					type : "POST",
					url : modalPlanDeleteUrl,
					data : {
						idSelect : select,
						groupID : filterPlans.get('groupid').val()
					},
					dataType : "json",
					success : function(data) {
						if (data.status) {
							tablePlanes.flexReload();
							modalPlanDelete.dialog("close");
						} else {
							alert(data.error);
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
				modalPlanDelete.dialog("close");
			}
		}
	});

	$.editarModal = function(id, clonar) {

		if (clonar) {
			var id_set = "clone";
		} else {
			var id_set = id;
		}

		modalEdit.load(modalPlanEditUrl, {
			IDSelect : id
		}, // omit this param object to issue a GET request instead a POST request,
		// otherwise you may provide post parameters within the object
		function(responseText, textStatus, XMLHttpRequest) {
			// remove the loading class
			modalEdit.removeClass('loading');

			if (clonar) {
				var name1 = formPlanEdit.get("plan_plan").val(); 
				formPlanEdit.get("plan_plan").val(name1 + '_1');
			}

			formPlanEdit.refresh();
			
			modalEdit.dialog("open");
			
			if(completeForm){
				formPlanEdit.get("otherDisplay").show();
			}
		});

		modalEdit.dialog({
			autoOpen : false,
			resizable : false,
			height : 'auto',
			width : 650,
			modal : true,
			closeOnEscape : true,
			draggable : true,
			zIndex : 20000,
			open : function() {
				$(this).find('.ui-dialog-titlebar-close').blur();
				formPlanEdit.get("planes_groups_edit").multiselect();
				formPlanEdit.get("planes_groups_edit").multiselect('locale', lang );
			},
			buttons : {
				"Accept" : function() {

					var bValid = true;


					if (bValid) {

						jQuery('#loading').show();
						jQuery('#imgLoading').show();

						$.ajax({
							type : "POST",
							url : modalPlanNewUrl,
							data : "id=" + id_set + "&" + formPlanEdit.form.serialize(),
							dataType : "json",
							success : function(data) {
								if (data.status) {
									modalEdit.dialog("close");
									tablePlanes.flexReload();
								} else {
									formPlanEdit.tipsBox(data.error, 'alert');
								}
							},
							error : function() {
								formPlanEdit.errorSystem(language.code);
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
				
			}
		});
	};
});

function editar(id, clone) {
	$.editarModal(id, clone);
}

