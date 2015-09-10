<script type="text/javascript">
jQuery(function() {
	fmFilterExportMintic = new $.formVar( 'fmFilterExportMintic' );	
	fmFilterExportMintic.get("fromExportQoe").datepicker({
		changeMonth : true,
		numberOfMonths : 1,
		dateFormat : "yy-mm-dd",
		onClose : function( selectedDate ) {
			fmFilterExportMintic.get("toExportQoe").datepicker("option", "minDate", selectedDate);
		}
	});
	
	fmFilterExportMintic.get("toExportQoe").datepicker({
		changeMonth : true,
		numberOfMonths : 1,
		dateFormat : "yy-mm-dd",
		minDate : "{firstDay}",
		onClose : function( selectedDate ) {
			fmFilterExportMintic.get("fromExportQoe").datepicker("option", "maxDate", selectedDate);
		}
	});
		
	fmFilterExportMintic.get("exportDownload").on("click", function() {
		$('#loading').show();
        $('#imgLoading').show();
        
        var request = $.ajax({
            type : "POST",
            url : "/qoe/getExportMintic/csv",
            dataType : "json",
            data: fmFilterExportMintic.form.serialize()
        });

        request.done(function( msg ) {
            if ( msg.status ) {
            	window.location.href = "/report/download/"+msg.file;
            }
        });

        request.fail(function( jqXHR, textStatus ) {
			alert("Método en mantenimiento");
        });

        request.always(function( ) {
            $('#loading').hide();
            $('#imgLoading').hide();
        });
	});
	
	fmFilterExportMintic.get("exportDownload").button();
});
</script>
<div id="loading"></div>
<div id="imgLoading">
	<img widht="32" height="32" src="/sitio/img/ajax-loader.gif" alt="loading" title="loading" />
</div>

<div style="max-width: 630px;" id="exportMintic" class="ui-grid ui-widget ui-widget-content ui-corner-all">
	<form id="fmFilterExportMintic">
	<div class="ui-grid-header ui-widget-header ui-corner-top">{EXPORT}</div>
	<table class="ui-grid ui-component ui-component-content" cellpadding="0" cellspacing="0" width="100%" height="100%">
		<thead>
			<tr>
				<th class="ui-state-default">{FIELD}</th>
				<th class="ui-state-default">{SELECTION}</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class="ui-widget-content"><label for="groupid" accesskey="a">{GROUP}:</label></td>
				<td class="ui-widget-content">
					<select name="groupid" id="groupid">
						{option_groups}
					</select></td>
			</tr>
			<tr>
				<td class="ui-widget-content"><label for="percentStartDownload" accesskey="a">{COMPLIANCE_RANGE} {SPEED_DOWNLOAD} (%):</label></td>
				<td class="ui-widget-content">
					<input style="width: 40px; box-shadow: none;text-align: center;" type="text" size="3" value="95" id="percentStartDownload" name="percentStartDownload">
					<input style="width: 100px; box-shadow: none;text-align: center;" type="text" size="3" value=">= & <=" name="label" disabled>
					<input style="width: 40px; box-shadow: none;text-align: center;" type="text" size="3" value="120" id="percentEndDownload" name="percentEndDownload">
				</td>
			</tr>
			<tr>
				<td class="ui-widget-content"><label for="percentStartUpload" accesskey="a">{COMPLIANCE_RANGE} {SPEED_UPLOAD} (%):</label></td>
				<td class="ui-widget-content">						
                	<input style="width: 40px; box-shadow: none;text-align: center;" type="text" size="3" value="95" id="percentStartUpload" name="percentStartUpload">
                	<input style="width: 100px; box-shadow: none;text-align: center;" type="text" size="3" value=">= & <=" name="label" disabled>
                	<input style="width: 40px; box-shadow: none;text-align: center;" type="text" size="3" value="120" id="percentEndUpload" name="percentEndUpload">
				</td>
			</tr>
			<tr>
				<td class="ui-widget-content"><label for="agent" accesskey="a">{DATE}:</label></td>
				<td class="ui-widget-content">						
					<label for="fromExportQoe" accesskey="m">{FROM}</label>
					<input type="text" id="fromExportQoe" name="from" value="{firstDay}">
					<label for="toExportQoe" accesskey="a">{TO}</label>
					<input type="text" id="toExportQoe" name="to" value="{dateNow}">
				</td>
			</tr>
			<tr>
				<td colspan="2"><button type='button' id='exportDownload'>{DOWNLOAD}</button></td>
			</tr>
		</tbody>
	</table>
	</form>
</div>
