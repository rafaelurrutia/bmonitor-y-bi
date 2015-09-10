<script language="JavaScript">
	jQuery(function( ) {

        ReportQoeGeneralForm = new $.formVar( 'fmFilterReportQoeGeneral' );
        
		$("#exportReport").buttonset();

		$("#exportReportCSV").click(function( event ) {
			event.preventDefault();

			$('#loading').show();
			$('#imgLoading').show();

			var request = $.ajax({
				type : "POST",
				url : "/qoe/exportReportGeneral/csv",
				dataType : "json",
				data: ReportQoeGeneralForm.form.serialize()
			});

			request.done(function( msg ) {
                if(msg.status){
                    window.location.href = "/report/download/"+msg.file;
                }
			});

			request.fail(function( jqXHR, textStatus ) {

			});

			request.always(function( ) {
				$('#loading').hide();
				$('#imgLoading').hide();
			});
		});

		$("#exportReportEXCEL").click(function( event ) {
			event.preventDefault();

			$('#loading').show();
			$('#imgLoading').show();

			var request = $.ajax({
				type : "POST",
				url : "/qoe/exportReportGeneral/excel",
				dataType : "json",
				data: ReportQoeGeneralForm.form.serialize()
			});

			request.done(function( msg ) {
                window.location.href = "/report/download/"+msg.file;
			});

			request.fail(function( jqXHR, textStatus ) {

			});

			request.always(function( ) {
				$('#loading').hide();
				$('#imgLoading').hide();
			});
		});

		function genTableNeutralidad( ) {
			$('#loading').show();
			$('#imgLoading').show();

			var request = $.ajax({
				type : "POST",
				url : "/qoe/getTableReport",
				data : ReportQoeGeneralForm.form.serialize(),
				dataType : "json"
			});

			request.done(function( data ) {
				if ( data.status ) {

					$("#container_tables_center_1").html(data.tablas);

					$(".portlet").addClass("ui-widget ui-widget-content ui-helper-clearfix ui-corner-all").find(".portlet-header").addClass("ui-widget-header ui-corner-all").prepend("<span class='ui-icon ui-icon-plusthick'></span>").end().find(".portlet-content").parents(".portlet").find(".portlet-content").toggle();

					$(".portlet-header").click(function( ) {
						$(this).toggleClass("ui-icon-minusthick").toggleClass("ui-icon-plusthick");
						$(this).parents(".portlet:first").find(".portlet-content").toggle();
					});

					$("#tableResumenConsFDT_int .sorting").click(function( ) {
						var image = $(this).find("img");
						var imageId = image.attr('id');
						if ( image.attr("src") == '/sitio/img/details_open.png' ) {
							image.attr("src", '/sitio/img/details_close.png');
						} else {
							image.attr("src", '/sitio/img/details_open.png');
						}
						// $(this).parents(".portlet-content:first").find("#mono").toggle();
						$(this).parents("tbody:first").find("td#" + imageId).toggle();
					});

				} else {
					alert("error");
				}
			});

			request.fail(function( jqXHR, textStatus ) {
				alert("Error");
			});

			request.always(function( ) {
				$('#loading').hide();
				$('#imgLoading').hide();
			});

		}

		genTableNeutralidad();

		function refresh( ) {
			genTableNeutralidad();
		}

		ReportQoeGeneralForm.get("escala").change(function( ) {
			refresh();
		});

		ReportQoeGeneralForm.get("grupo").change(function( ) {
			refresh();
		});

		ReportQoeGeneralForm.get("meses").change(function( ) {
			refresh();
		});

		ReportQoeGeneralForm.get("ano").change(function( ) {
			refresh();
		});
	}); 
</script>
<style>
	.column {
		padding-top: 14px;
		padding-bottom: 100px;
	}
	.portlet {
		margin: 0 1em 1em 0;
	}
	.portlet-header {
		margin: 0.3em;
		padding-bottom: 4px;
		padding-left: 0.2em;
	}
	.portlet-header .ui-icon {
		float: right;
	}
	.portlet-content {
		padding: 0.4em;
	}
	.ui-sortable-placeholder {
		border: 1px dotted black;
		visibility: visible !important;
		height: 50px !important;
	}
	.ui-sortable-placeholder * {
		visibility: hidden;
	}

	td.details {
		background-color: #d1cfd0;
		border: 2px solid #A19B9E;
	}

</style>
<div id="loading"></div>
<div id="imgLoading">
	<img widht="32" height="32" src="/sitio/img/ajax-loader.gif" alt="loading" title="loading" />
</div>
<form id="fmFilterReportQoeGeneral">
	<div class="paneles">
		<fieldset>
			<div id="row">
				<label for="escala" accesskey="g">{SCALE}</label>
				<select name="escala" id="escala">
					{option_escalas}
				</select>
			</div>
			<div id="row">
				<label for="grupo" accesskey="g">{GROUP}</label>
				<select name="grupo" id="grupo">
					{option_groups}
				</select>
			</div>
			<div id="row">
				<label for="meses" accesskey="m">{MONTH}</label>
				<select name="meses" id="meses">
					{option_meses}
				</select>
			</div>
			<div id="row">
				<label for="ano" accesskey="a">{YEAR}</label>
				<select name="ano" id="ano">
					{option_anos}
				</select>
			</div>
			<div id="row" style="width: 110px" class="none">
				<label for="fmAno_neutralidad" accesskey="a">{EXPORT}</label>
				<span class="ui-widget-header ui-corner-bottom" style="padding: 2px 2px;display: inline-block;"> <span id="exportReport">
						<!--<button id="exportReportCSV">
							csv
						</button> -->
						<button id="exportReportEXCEL">
							excel
						</button> </span> </span>
			</div>
		</fieldset>
	</div>
</form>
<div id='container_tables_center_1'></div>