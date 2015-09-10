<script type="text/javascript">
	jQuery(function( ) {

		var tableReportSpeedtest = $("#tableReportSpeedtest");

		fmFilterReportQoeSpeedtest = new $.formVar( 'fmFilterReportQoeSpeedtest' );

		fmFilterReportQoeSpeedtest.get("from").datepicker({
			changeMonth : true,
			changeYear : true,
			numberOfMonths : 1,
			dateFormat : "yy-mm-dd",
			onClose : function( selectedDate ) {
				fmFilterReportQoeSpeedtest.get("to").datepicker("option", "minDate", selectedDate);
				tableReportSpeedtest.flexReload();
			}

		});
		
		fmFilterReportQoeSpeedtest.get("to").datepicker({
			changeMonth : true,
			changeYear : true,
			numberOfMonths : 1,
			dateFormat : "yy-mm-dd",
			minDate : "{firstDay}",
			onClose : function( selectedDate ) {
				fmFilterReportQoeSpeedtest.get("from").datepicker("option", "maxDate", selectedDate);
				tableReportSpeedtest.flexReload();
			}

		});

		tableReportSpeedtest.flexigrid({
			url : '{url_base}qoe/getTableSpeedtest',
			title : '{AGENTS}',
			dataType : 'json',
			colModel : [{
				display : 'IP',
				name : 'ip',
				width : '88',
				sortable : true,
				align : 'left'
			}, {
				display : '{CITY}',
				name : 'city',
				width : '69',
				sortable : true,
				align : 'left'
			}, {
				display : 'Region',
				name : 'region',
				width : '54',
				sortable : true,
				align : 'left'
			}, {
				display : '{COUNTRY}',
				name : 'country',
				width : '54',
				sortable : false,
				align : 'left'
			}, {
				display : 'ISP',
				name : 'isp',
				width : '57',
				sortable : false,
				align : 'left'
			}, {
				display : '{LATITUDE}',
				name : 'latitude',
				width : '65',
				sortable : true,
				align : 'left'
			}, {
				display : '{LONGITUDE}',
				name : 'longitude',
				width : '70',
				sortable : true,
				align : 'left'
			}, {
                display : '{MEASUREMENT_DATE}',
                name : 'testDate',
                width : '109',
                sortable : true,
                align : 'left'
            }, {
                display : '{NAME}',
                name : 'serverName',
                width : '93',
                sortable : true,
                align : 'left'
            }, {
                display : '{SPEED_DOWNLOAD}',
                name : 'download',
                width : '55',
                sortable : true,
                align : 'center'
            }, {
                display : '{SPEED_UPLOAD}',
                name : 'upload',
                width : '48',
                sortable : true,
                align : 'center'
            }, {
                display : 'Delay',
                name : 'latency',
                width : '40',
                sortable : true,
                align : 'center'
            }, {
                display : '{PERCENT} {SPEED_DOWNLOAD}',
                name : 'pdownload',
                width : '95',
                sortable : true,
                align : 'center'
            }, {
                display : '{PERCENT} {SPEED_UPLOAD}',
                name : 'pupload',
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
                onpress : buttonSpeedtest
            }],
			resizable : true,
			onSubmit : function( ) {
				tableReportSpeedtest.flexOptions({
					params : [{
						name : 'callId',
						value : 'sondas'
					}].concat(fmFilterReportQoeSpeedtest.form.serializeArray())
				});
				return true;
			}
		});


        function buttonSpeedtest( com, grid )
        {
            if ( com == "{EXPORT}" ) {
                
                $('#loading').show();
                $('#imgLoading').show();
            
                var request = $.ajax({
                    type : "POST",
                    url : "/qoe/exportReportSpeedtest/csv",
                    dataType : "json",
                    data : fmFilterReportQoeSpeedtest.form.serialize()
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

		fmFilterReportQoeSpeedtest.get("grupo").change(function( ) {
			tableReportSpeedtest.flexReload();
		});

		fmFilterReportQoeSpeedtest.get("from").change(function( ) {
			tableReportSpeedtest.flexReload();
		});

		fmFilterReportQoeSpeedtest.get("to").change(function( ) {
			tableReportSpeedtest.flexReload();
		});
	}); 
</script>
<div id="loading"></div>
<div id="imgLoading">
	<img widht="32" height="32" src="/sitio/img/ajax-loader.gif" alt="loading" title="loading" />
</div>
<form id="fmFilterReportQoeSpeedtest">
	<div class="paneles">
		<fieldset>
			<div id="row">
				<label for="grupo" accesskey="g">{GROUP}</label>
				<select name="grupo" id="grupo">
					{option_groups}
				</select>
			</div>
			<div id="row" class="none datepicker">
				<label for="from" accesskey="m">{FROM}</label>
				<input type="text" id="from" name="from" value="{firstDay}">
			</div>
			<div id="row" class="none datepicker">
				<label for="to" accesskey="a">{TO}</label>
				<input type="text" id="to" name="to" value="{dateNow}">
			</div>
			<div id="row" class="none">
				<label for="percentStartDownload" accesskey="p">{COMPLIANCE_RANGE} {SPEED_DOWNLOAD} (%)</label>
				<input style="width: 50%; box-shadow: none;text-align: center;border-radius: 0px 0px 0px 5px;float:left" type="text" size="3" value="95" id="percentStartDownload" name="percentStartDownload">
				<input style="width: 50%; box-shadow: none;text-align: center;border-radius: 0px 0px 5px 0px;float:left" type="text" size="3" value="120" id="percentEndDownload" name="percentEndDownload">
			</div>
            <div id="row" class="none">
                <label for="percentStartUpload" accesskey="p">{COMPLIANCE_RANGE} {SPEED_UPLOAD} (%)</label>
                <input style="width: 50%; box-shadow: none;text-align: center;border-radius: 0px 0px 0px 5px;float:left" type="text" size="3" value="95" id="percentStartUpload" name="percentStartUpload">
                <input style="width: 50%; box-shadow: none;text-align: center;border-radius: 0px 0px 5px 0px;float:left" type="text" size="3" value="120" id="percentEndUpload" name="percentEndUpload">
            </div>
		</fieldset>
	</div>
</form>
<table id='tableReportSpeedtest' cellpadding="0" cellspacing="0"></table>