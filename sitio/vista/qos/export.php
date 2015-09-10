<script type="text/javascript">
jQuery(function() {
	fmFilterExportItemQoe = new $.formVar( 'fmFilterExportItemQoe' );	
	fmFilterExportItemQoe.get("fromExportQoe").datepicker({
		changeMonth : true,
		numberOfMonths : 1,
		dateFormat : "yy-mm-dd",
		onClose : function( selectedDate ) {
			fmFilterExportItemQoe.get("toExportQoe").datepicker("option", "minDate", selectedDate);
		}
	});
	
	fmFilterExportItemQoe.get("toExportQoe").datepicker({
		changeMonth : true,
		numberOfMonths : 1,
		dateFormat : "yy-mm-dd",
		minDate : "{dateNow}",
		onClose : function( selectedDate ) {
			fmFilterExportItemQoe.get("fromExportQoe").datepicker("option", "maxDate", selectedDate);
		}
	});
	
	fmFilterExportItemQoe.get("groupid").change(function( ) {

        $('#loading').show();
        $('#imgLoading').show();

        var request = $.ajax({
            type : "POST",
            url : "/qoe/getExportItemsOption/"+fmFilterExportItemQoe.get("groupid").val(),
            dataType : "json"
        });

        request.done(function( msg ) {
            if ( msg.status ) {
                fmFilterExportItemQoe.get("measurement").html(msg.optionMeasurement);
                fmFilterExportItemQoe.get("plan").html(msg.optionPlanes);
                fmFilterExportItemQoe.get("agent").html(msg.optionSondas);
            }
        });

        request.fail(function( jqXHR, textStatus ) {

        });

        request.always(function( ) {
            $('#loading').hide();
            $('#imgLoading').hide();
        });			

	});
	
	fmFilterExportItemQoe.get("exportDownload").on("click", function() {
		$('#loading').show();
        $('#imgLoading').show();
        
        var request = $.ajax({
            type : "POST",
            url : "/qoe/getExportItems/csv",
            dataType : "json",
            data: fmFilterExportItemQoe.form.serialize()
        });

        request.done(function( msg ) {
            if ( msg.status ) {
            	window.location.href = "/report/download/"+msg.file;
            }
        });

        request.fail(function( jqXHR, textStatus ) {
			alert("Error memory");
        });

        request.always(function( ) {
            $('#loading').hide();
            $('#imgLoading').hide();
        });
	});
	
	fmFilterExportItemQoe.get("exportDownload").button();
});
</script>
<div id="loading"></div>
<div id="imgLoading">
	<img widht="32" height="32" src="/sitio/img/ajax-loader.gif" alt="loading" title="loading" />
</div>

<div id="exportItemQoe" class="ui-grid ui-widget ui-widget-content ui-corner-all">
	<form id="fmFilterExportItemQoe">
	<div class="ui-grid-header ui-widget-header ui-corner-top">{EXPORT}</div>
	<p  class="validateTips">{FORM_ALL_PARAM_REQUIRED}</p>
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
				<td class="ui-widget-content"><label for="measurement" accesskey="a">{MEASUREMENT}:</label></td>
				<td class="ui-widget-content">
					<select name="measurement" id="measurement">
						{option_measurement}
					</select></td>
			</tr>
			<tr>
				<td class="ui-widget-content"><label for="agent" accesskey="a">{AGENT}:</label></td>
				<td class="ui-widget-content">						
					<select name="agent" id="agent">
						{option_sondas}
					</select>
				</td>
			</tr>
			<tr>
				<td class="ui-widget-content"><label for="agent" accesskey="a">{DATE}:</label></td>
				<td class="ui-widget-content">						
					<label for="fromExportQoe" accesskey="m">{FROM}</label>
					<input type="text" id="fromExportQoe" name="from" value="{dateNow}">
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
