<script type="text/javascript">
	jQuery(function( ) {

		var tableReportMintic = $("#tableReportMintic");

		fmFilterReportQoeMintic = new $.formVar( 'fmFilterReportQoeMintic' );

		fmFilterReportQoeMintic.get("fromSpeedtest").datepicker({
			changeMonth : true,
			numberOfMonths : 1,
			dateFormat : "yy-mm-dd",
			onClose : function( selectedDate ) {
				fmFilterReportQoeMintic.get("toSpeedtest").datepicker("option", "minDate", selectedDate);
				tableReportMintic.flexReload();
			}

		});
		
		fmFilterReportQoeMintic.get("toSpeedtest").datepicker({
			changeMonth : true,
			numberOfMonths : 1,
			dateFormat : "yy-mm-dd",
			minDate : "{dateNow}",
			onClose : function( selectedDate ) {
				fmFilterReportQoeMintic.get("fromSpeedtest").datepicker("option", "maxDate", selectedDate);
				tableReportMintic.flexReload();
			}

		});

		tableReportMintic.flexigrid({
			url : '{url_base}qoe/getTableMintic',
			title : '{AGENTS}',
			dataType : 'json',
			colModel : [{
				display : 'NUMEROCONTRATO',
				name : 'NUMEROCONTRATO',
				width : '88',
				sortable : true,
				align : 'left'
			}, {
				display : 'ANO',
				name : 'ANO',
				width : '69',
				sortable : true,
				align : 'left'
			}, {
				display : 'NUMEROREFERENCIAPAGO',
				name : 'NUMEROREFERENCIAPAGO',
				width : '54',
				sortable : true,
				align : 'left'
			}, {
				display : 'IDBENEFECIARIO',
				name : 'IDBENEFECIARIO',
				width : '54',
				sortable : false,
				align : 'left'
			}, {
				display : 'VELOCIDADBAJADA',
				name : 'VELOCIDADBAJADA',
				width : '57',
				sortable : false,
				align : 'left'
			}, {
				display : 'VELOCIDADSUBIDA',
				name : 'VELOCIDADSUBIDA',
				width : '65',
				sortable : true,
				align : 'left'
			}, {
				display : 'FECHAMEDICION',
				name : 'FECHAMEDICION',
				width : '70',
				sortable : true,
				align : 'left'
			}, {
                display : 'DANEDEPARTAMENTO',
                name : 'DANEDEPARTAMENTO',
                width : '109',
                sortable : true,
                align : 'left'
            }, {
                display : 'DANEMUNICIPIO',
                name : 'DANEMUNICIPIO',
                width : '93',
                sortable : true,
                align : 'left'
            }, {
                display : 'DANECODIGOCENTROPOBLADO',
                name : 'DANECODIGOCENTROPOBLADO',
                width : '55',
                sortable : true,
                align : 'center'
            }, {
                display : 'MARCATIEMPO',
                name : 'MARCATIEMPO',
                width : '48',
                sortable : true,
                align : 'center'
            }, {
                display : 'MARCATIEMPO2',
                name : 'MARCATIEMPO2',
                width : '40',
                sortable : true,
                align : 'center'
            }, {
                display : 'CUENTA',
                name : 'CUENTA',
                width : '95',
                sortable : true,
                align : 'center'
            }],
			usepager : true,
			useRp : true,
			rp : 20,
			height : 'auto',
			striped : false,
			sortname : "testDate",
			sortorder : "desc",
			showTableToggleBtn : true,
            buttons : [{
                name : "{EXPORT}",
                bclass : 'new',
                onpress : buttonMintic
            }],
			resizable : true,
			onSubmit : function( ) {
				tableReportMintic.flexOptions({
					params : [{
						name : 'callId',
						value : 'sondas'
					}].concat(fmFilterReportQoeMintic.form.serializeArray())
				});
				return true;
			}
		});


        function buttonMintic( com, grid )
        {
            if ( com == "{EXPORT}" ) {
                
                $('#loading').show();
                $('#imgLoading').show();
            
                var request = $.ajax({
                    type : "POST",
                    url : "/qoe/exportReportMintic/csv",
                    dataType : "json",
                    data : fmFilterReportQoeMintic.form.serialize()
                });
    
                request.done(function( msg ) {
                    if ( msg.status ) {
                        window.location.href = "/report/download/" + msg.file;
                    }
                });
    
                request.fail(function( jqXHR, textStatus ) {
    
                });
    
                request.always(function( ) {
                    $('#loading').hide();
                    $('#imgLoading').hide();
                });
            }
        }

		fmFilterReportQoeMintic.get("grupo").change(function( ) {
			tableReportMintic.flexReload();
		});

		fmFilterReportQoeMintic.get("fromSpeedtest").change(function( ) {
			tableReportMintic.flexReload();
		});

		fmFilterReportQoeMintic.get("toSpeedtest").change(function( ) {
			tableReportMintic.flexReload();
		});
	}); 
</script>
<div id="loading"></div>
<div id="imgLoading">
	<img widht="32" height="32" src="/sitio/img/ajax-loader.gif" alt="loading" title="loading" />
</div>
<form id="fmFilterReportQoeMintic">
	<div class="paneles">
		<fieldset>
			<div id="row">
				<label for="grupo" accesskey="g">{GROUP}</label>
				<select name="grupo" id="grupo">
					{option_groups}
				</select>
			</div>
			<div id="row" class="none">
				<label for="fromSpeedtest" accesskey="m">{FROM}</label>
				<input type="text" id="fromSpeedtest" name="from" value="{firstDay}">
			</div>
			<div id="row" class="none">
				<label for="toSpeedtest" accesskey="a">{TO}</label>
				<input type="text" id="toSpeedtest" name="to" value="{dateNow}">
			</div>
			<div id="row" class="none">
				<label for="percentStartDownload" accesskey="p">({FILTER}){COMPLIANCE_RANGE} {SPEED_DOWNLOAD} (%)</label>
				<input style="width: 48%; box-shadow: none;text-align: center;" type="text" size="3" value="95" id="percentStartDownload" name="percentStartDownload">
				<input style="width: 48%; box-shadow: none;text-align: center;" type="text" size="3" value="120" id="percentEndDownload" name="percentEndDownload">
			</div>
            <div id="row" class="none">
                <label for="percentStartUpload" accesskey="p">({FILTER}){COMPLIANCE_RANGE} {SPEED_UPLOAD} (%)</label>
                <input style="width: 48%; box-shadow: none;text-align: center;" type="text" size="3" value="95" id="percentStartUpload" name="percentStartUpload">
                <input style="width: 48%; box-shadow: none;text-align: center;" type="text" size="3" value="120" id="percentEndUpload" name="percentEndUpload">
            </div>
		</fieldset>
	</div>
</form>
<table id='tableReportMintic' cellpadding="0" cellspacing="0"></table>