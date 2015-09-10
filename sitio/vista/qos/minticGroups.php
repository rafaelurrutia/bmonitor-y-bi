<script type="text/javascript">
	jQuery(function( ) {

		var tableReportMinticGroups = $("#tableReportMinticGroups");

		fmFilterReportQoeMinticGroups = new $.formVar( 'fmFilterReportQoeMinticGroups' );

		fmFilterReportQoeMinticGroups.get("fromMinticGroups").datepicker({
			changeMonth : true,
			changeYear : true,
			numberOfMonths : 1,
			dateFormat : "yy-mm-dd",
			onClose : function( selectedDate ) {
				fmFilterReportQoeMinticGroups.get("toMinticGroups").datepicker("option", "minDate", selectedDate);
				tableReportMinticGroups.flexReload();
			}

		});
		
		fmFilterReportQoeMinticGroups.get("toMinticGroups").datepicker({
			changeMonth : true,
			changeYear : true,
			numberOfMonths : 1,
			dateFormat : "yy-mm-dd",
			minDate : "{firstDay}",
			onClose : function( selectedDate ) {
				fmFilterReportQoeMinticGroups.get("fromMinticGroups").datepicker("option", "maxDate", selectedDate);
				tableReportMinticGroups.flexReload();
			}

		});

		tableReportMinticGroups.flexigrid({
			url : '{url_base}qoe/getTableMinticGroups',
			title : '{AGENTS}',
			dataType : 'json',
			colModel : [{
				display : '{CITY}',
				name : 'groups',
				width : '100',
				sortable : true,
				align : 'left'
			}, {
				display : '{SAMPLES_PERFORMED}',
				name : 'mEESperada',
				width : '100',
				sortable : true,
				align : 'left'
			}, {
				display : '{SAMPLES_DOWN_OK}',
				name : 'mDownloadOk',
				width : '100',
				sortable : true,
				align : 'left'
			}, {
				display : '{SAMPLES_DOWN_FAIL}',
				name : 'mDownloadFallidas',
				width : '100',
				sortable : false,
				align : 'left'
			}, {
				display : '{SAMPLES_UP_OK}',
				name : 'mUploadOk',
				width : '100',
				sortable : false,
				align : 'left'
			}, {
				display : '{SAMPLES_UP_FAIL}',
				name : 'mUploadFallidas',
				width : '100',
				sortable : true,
				align : 'left'
			}, {
				display : '{AVERAGE_SPEED_DOWN}',
				name : 'vavgdown',
				width : '100',
				sortable : true,
				align : 'left'
			}, {
                display : '{AVERAGE_SPEED_UP}',
                name : 'vavgupload',
                width : '100',
                sortable : true,
                align : 'left'
            }, {
                display : '{ACCOMPLISHMENT_SAMPLES_DOWN}',
                name : 'cumplimientoDown',
                width : '100',
                sortable : true,
                align : 'left'
            }, {
                display : '{ACCOMPLISHMENT_SAMPLES_UP}',
                name : 'cumplimientoUpload',
                width : '100',
                sortable : true,
                align : 'center'
            }],
			usepager : true,
			useRp : true,
			rp : 20,
			height : 'auto',
			sortname : "testDate",
			sortorder : "desc",
			showTableToggleBtn : true,
            buttons : [{
                name : "{EXPORT}",
                bclass : 'new',
                onpress : buttonMinticGroups
            }],
			resizable : true,
			onSuccess: flexi_colorGridRows,
			singleSelect: true,
			onSubmit : function( ) {
				tableReportMinticGroups.flexOptions({
					params : [{
						name : 'callId',
						value : 'sondas'
					}].concat(fmFilterReportQoeMinticGroups.form.serializeArray())
				});
				return true;
			}
		});

		function flexi_colorGridRows(){
		    $("tr").each(function() {
		        var st = $(this).find("td:nth(8)").text()
		        if (st < 100) {
		            //$(this).attr("class",$(this).attr("class") == "erow" ? "forestgreen" : "forestgreen" );
		            $(this).find("td:nth(8)").attr("class","forestgreen");
		            $(this).find("td:nth(9)").attr("class","forestgreen");
		        } 
		        else if (st == 0) {
		            $(this).find("td:nth(8)").attr("class","darkred");
		            $(this).find("td:nth(9)").attr("class","darkred");
		        }
		    });
		};

        function buttonMinticGroups( com, grid )
        {
            if ( com == "{EXPORT}" ) {
                
                $('#loading').show();
                $('#imgLoading').show();
            
                var request = $.ajax({
                    type : "POST",
                    url : "/qoe/exportReportMinticGroups/csv",
                    dataType : "json",
                    data : fmFilterReportQoeMinticGroups.form.serialize()
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

		fmFilterReportQoeMinticGroups.get("grupo").change(function( ) {

            $('#loading').show();
            $('#imgLoading').show();

            var request = $.ajax({
                type : "POST",
                url : "/qoe/getServerSpeed/"+fmFilterReportQoeMinticGroups.get("grupo").val(),
                dataType : "json"
            });

            request.done(function( msg ) {
                if ( msg.status ) {
                    fmFilterReportQoeMinticGroups.get("server").html(msg.option);
                }
            });

            request.fail(function( jqXHR, textStatus ) {

            });

            request.always(function( ) {
                $('#loading').hide();
                $('#imgLoading').hide();
                tableReportMinticGroups.flexReload();
            });			

			
		});

		fmFilterReportQoeMinticGroups.get("fromMinticGroups").change(function( ) {
			tableReportMinticGroups.flexReload();
		});

		fmFilterReportQoeMinticGroups.get("toMinticGroups").change(function( ) {
			tableReportMinticGroups.flexReload();
		});
		
		fmFilterReportQoeMinticGroups.get("grupingData").change(function( ) {
			tableReportMinticGroups.flexReload();
			var grupingData = $("#grupingData :selected").text();
			$('th[abbr="groups"] div').text(grupingData);
		});
		
		fmFilterReportQoeMinticGroups.get("server").change(function( ) {
			tableReportMinticGroups.flexReload();
		});
		
		var grupingData = $("#grupingData :selected").text();
		$('th[abbr="groups"] div').text(grupingData);

		
	}); 
</script>
<div id="loading"></div>
<div id="imgLoading">
	<img widht="32" height="32" src="/sitio/img/ajax-loader.gif" alt="loading" title="loading" />
</div>
<form id="fmFilterReportQoeMinticGroups">
	<div class="paneles">
		<fieldset>
			<div id="row">
				<label for="grupo" accesskey="g">{GROUP}</label>
				<select name="grupo" id="grupo">
					{option_groups}
				</select>
			</div>
			<div id="row">
				<label for="server" accesskey="g">{SERVER}</label>
				<select name="server" id="server">
					{option_speedServer}
				</select>
			</div>
			<div id="row" class="none datepicker">
				<label for="fromMinticGroups" accesskey="m">{FROM}</label>
				<input type="text" id="fromMinticGroups" name="from" value="{firstDay}">
			</div>
			<div id="row" class="none datepicker">
				<label for="toMinticGroups" accesskey="a">{TO}</label>
				<input type="text" id="toMinticGroups" name="to" value="{dateNow}">
			</div>
			<div id="row" class="none">
				<label for="percentStartDownload" accesskey="p">({FILTER}){COMPLIANCE_RANGE} {SPEED_DOWNLOAD} (%)</label>
				<input style="width: 22%; box-shadow: none;text-align: center;" type="text" size="3" value="95" id="percentStartDownload" name="percentStartDownload">
				<input style="width: 50%; box-shadow: none;text-align: center;" type="text" size="3" value=">= & <=" name="label" disabled>
				<input style="width: 22%; box-shadow: none;text-align: center;" type="text" size="3" value="120" id="percentEndDownload" name="percentEndDownload">
			</div>
            <div id="row" class="none">
                <label for="percentStartUpload" accesskey="p">({FILTER}){COMPLIANCE_RANGE} {SPEED_UPLOAD} (%)</label>
                <input style="width: 22%; box-shadow: none;text-align: center;" type="text" size="3" value="95" id="percentStartUpload" name="percentStartUpload">
                <input style="width: 50%; box-shadow: none;text-align: center;" type="text" size="3" value=">= & <=" name="label" disabled>
                <input style="width: 22%; box-shadow: none;text-align: center;" type="text" size="3" value="120" id="percentEndUpload" name="percentEndUpload">
            </div>
			<div id="row" class="none short">
				<label for="percentValidDownload" accesskey="p">(%){COMPLIANCE} {SPEED_DOWNLOAD} (>=)</label>
				<input style=" box-shadow: none;text-align: center;" type="text" size="3" value="100" id="percentValidDownload" name="percentValidDownload">
				
			</div>
            <div id="row" class="none short">
                <label for="percentValidUpload" accesskey="p">(%){COMPLIANCE} {SPEED_UPLOAD} (>=)</label>
                <input style=" box-shadow: none;text-align: center;" type="text" size="3" value="100" id="percentValidUpload" name="percentValidUpload">
            </div>
			<div id="row">
				<label for="grupingData" accesskey="g">{GROUPING_DATA}</label>
				<select name="grupingData" id="grupingData">	
					<option value="city">Ciudad</option>
					<option value="agent">{AGENT}</option>
				</select>
			</div>
		</fieldset>
	</div>
</form>
<table id='tableReportMinticGroups' cellpadding="0" cellspacing="0"></table>