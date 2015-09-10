<script type="text/javascript" src="{url_base}sitio/js/ui.wizard.js"></script>
<script type="text/javascript" src="{url_base}sitio/js/progress.js"></script>

<link rel="stylesheet" type="text/css" href="{url_base}sitio/css/style.php?css=jWizard.base">
<link rel="stylesheet" type="text/css" href="{url_base}sitio/css/style.php?css=ui.progress-bar">

<style type="text/css">
    .ui-progressbar {
        position: relative;
    }
    .ui-progressbar .ui-progressbar-value {
        background-image: url(/sitio/img/pbar-ani.gif);
    }
    .pblabel {
        position: absolute;
        width: 100%;
        text-align: center;
        line-height: 1.9em;
        padding: 0px
    }
</style>

<script type="text/javascript">
	jQuery(function($) {

		function refresh() {

			$('#progress_bar').html('<div class="ui-progress" style="width: 1%;"><span class="ui-label" style="display:none;">Buscando <b class="value">1%</b></span></div>');

			// Hide the label at start
			$('#progress_bar .ui-progress .ui-label').hide();
			// Set initial value
			$('#progress_bar .ui-progress').css('width', '7%');

			$.ajax({
				type : "POST",
				url : '/configsec/getMac',
				dataType : "json",
				beforeSend : function(xhr) {
					$('#progress_bar .ui-progress').animateProgress(45);
				},
				success : function(data) {
					if (data.status) {
						$('#progress_bar .ui-progress').animateProgress(100);
						$('#main_content').slideDown();
						$('#fork_me').fadeIn();
						$('#msg').html(data.msg);
						$("#msg").removeClass("ui-state-error").addClass("ui-state-highlight");
						$("#config_valid").val(1);
					} else {
						$('#progress_bar .ui-progress').animateProgress(100);
						$('#main_content').slideDown();
						$('#fork_me').fadeIn();
						$('#msg').html(data.msg);
						if (data.manual) {
							$('#msg2').show();
							$("#validMAC").button();
						}
						$("#msg").removeClass("ui-state-highlight").addClass("ui-state-error");
						$("#refresh").button();
						$("#config_valid").val(0);
					}
				},
				error : function() {
					alert("Error");
				}
			});

		}

		refresh();

		$("#refresh").on("click", function() {
			$('#main_content').hide();
			refresh();
		})

		$("#validMAC").on("click", function() {

			var config_mac_valid = $('#config_mac_valid').val();

			$.ajax({
				type : "POST",
				url : '/configsec/manualValidMAC/' + config_mac_valid,
				dataType : "json",
				success : function(data) {
					if (data.status) {
						$('#ResultManual').show();
						$('#ResultManual').html(data.msg);
						$("#ResultManual").removeClass("ui-state-error").addClass("ui-state-highlight");
						$("#config_valid").val(1);
					} else {
						$('#ResultManual').show();
						$('#ResultManual').html(data.msg);
						$("#ResultManual").removeClass("ui-state-highlight").addClass("ui-state-error");
						$("#config_valid").val(0);
					}
				},
				error : function() {
					alert("Error");
				}
			});

		})
		var w = $("#config_fdt");

		w.jWizard({
			buttons : {
				cancelHide : true, // Determines whether or not to generate/use a cancel <button>
				previousText : "Regresar", // Determines the text of the previous <button>
				nextText : "Siguiente", // Determines the text of the next <button>
				finishText : "Guardar" // Determines the text of the finish <button>
			},
			cancel : function(event, ui) {
				w.jWizard("firstStep");
			},
			finish : function(event, ui) {

				var bValid = true;

				bValid = bValid && $.checkSelect($('#wizard_validateTips'), $('#config_group'), "Grupo", 0);
				bValid = bValid && $.checkSelect($('#wizard_validateTips'), $('#config_region'), "Region", 0);
				bValid = bValid && $.checkSelect($('#wizard_validateTips'), $('#config_school'), "Establecimiento", 0);
				bValid = bValid && $.checkSelect($('#wizard_validateTips'), $('#config_plan'), "Plan", 0);

				//maldito serialize que no funciono

				MAC = $('#config_mac').val();
				IP = $('#config_ip').val();

				groupid = $('#config_group').val();
				region = $('#config_region').val();
				school = $('#config_school').val();
				cparental = $('#config_cparental').val();
				wifi = $('#config_wifi').val();
				ssid = $('#config_ssid').val();
				wifikey = $('#config_wifi_key').val();
				lanip = $('#config_lan_ip').val();
				mascara = $('#config_mascara').val();
				planid = $('#config_plan').val();

				if (bValid) {
					$.ajax({
						url : "/configsec/createSonda",
						type : "POST",
						data : "mac=" + MAC + "&ip=" + IP + "&groupid=" + groupid + "&region=" + region + "&groupid=" + groupid + "&idschool=" + school + "&cparental=" + cparental + "&wifi=" + wifi + "&ssid=" + ssid + "&wifikey=" + wifikey + "&lanip=" + lanip + "&mascara=" + mascara + "&planid=" + planid,
						dataType : "json",
						error : function() {
							alert("Error al crear sonda");
							return false;
						},
						success : function(j) {
							if (j.status) {
								alert(j.msg);
							} else {
								alert(j.msg);
							}
						}
					});
				}

			},
			next : function(event, ui) {

			},
		}).bind("jwizardchangestep", function(event, ui) {

			if (ui.currentStepIndex == 0) {
				var valid = $("#config_valid").val();

				if (valid == 0) {
					$("#main_content").parent().effect("pulsate");
					return false;
				} else {
					$('#step-school').load('/configsec/getFormConfig', function(response, status, xhr) {

						$(".jw-button-finish").removeClass("ui-state-highlight");

						$("#config_school").combobox({
							selected : function(event, ui) {
								var idschool = $("#config_school").val();
								var groupid = $("#config_group").val();
								$.ajax({
									url : "/configsec/getPlanes",
									type : "POST",
									data : {
										idschool : idschool,
										groupid : groupid
									},
									dataType : "json",
									error : function() {
										alert("Error al consultar planes");
										return false;
									},
									success : function(j) {
										if (j.status) {
											$("#config_plan").html(j.select);
										}
									}
								});
							}
						});

						$("#config_region").change(function() {

							var region = $("#config_region").val();

							$.ajax({
								url : "/configsec/getSchool",
								type : "POST",
								data : {
									region : region
								},
								dataType : "json",
								error : function() {
									alert("hay un error en el servicio de datos");
									return false;
								},
								success : function(j) {
									$("#config_school").html(j);
								}
							});
						});

						$("#config_group").change(function() {
							var idschool = $("#config_school").val();
							var groupid = $("#config_group").val();
							$.ajax({
								url : "/configsec/getPlanes",
								type : "POST",
								data : {
									idschool : idschool,
									groupid : groupid
								},
								dataType : "json",
								error : function() {
									alert("Error al consultar planes");
									return false;
								},
								success : function(j) {
									if (j.status) {
										$("#config_plan").html(j.select);
									}
								}
							});
						});

						$("#config_school").change(function() {
							var idschool = $("#config_school").val();
							var groupid = $("#config_group").val();
							$.ajax({
								url : "/configsec/getPlanes",
								type : "POST",
								data : {
									idschool : idschool,
									groupid : groupid
								},
								dataType : "json",
								error : function() {
									alert("Error al consultar planes");
									return false;
								},
								success : function(j) {
									if (j.status) {
										$("#config_plan").html(j.select);
									}
								}
							});
						});

					});
				}
			}
		})
	});

</script>
<form style="width: 864px" id="config_fdt">
    <div id="step-find" title="Buscando Sonda!">

        <div id="progress_bar" class="ui-progress-bar ui-container">
            <div class="ui-progress" style="width: 79%;">
                <span class="ui-label" style="display:none;">Buscando <b class="value">1%</b></span>
            </div>
        </div>
        <div class="ui-widget"  id="main_content" style="display: none;">
            <div id="msg" class="ui-corner-all" style="margin-top: 20px; padding: 0 .7em;">

            </div>
            <div id="msg2" class="ui-corner-all ui-state-highlight" style="margin-top: 20px; padding: 0 .7em; display: none">

                <p>
                    <span id="msg"> Asignacion Manual:
                        <br/>
                        <label for="config_mac_valid" accesskey="m">MAC:</label>
                        <input type="text" name="config_mac_valid" id="config_mac_valid" value="{config_mac}" />
                    </span>
                    <button type="button" id="validMAC">
                        Validar
                    </button>
                </p>

            </div>

            <div id="ResultManual" class="ui-corner-all ui-state-highlight" style="margin-top: 20px; padding: 0 .7em; display: none"></div>
        </div>

    </div>
    <input type="hidden" value="0" name="config_valid" id="config_valid" />
    <div id="step-school" title="Asignando Colegio!">

    </div>
</form>