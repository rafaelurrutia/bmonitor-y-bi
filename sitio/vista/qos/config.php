<script type="text/javascript" src="{url_base}sitio/js/view.qoe_config.js"></script>
<style type="text/css">
    .containerper {
        width: 100%;
        height: 66px;
        background-color: #eeffee;
    }

    div.flexigrid {

        width: 100%;
    }

    .title {
        width: 100%;
        text-align: center;
        float: left;
    }

    .optionPer {
        width: 170px;
        float: left;
        padding: 7px;
    }

    .optionPer .header {
        text-align: center;
        height: 15px;
    }

    .optionPer .content, .optionPer .content select {
        text-align: center;
        border: none;
    }
    
    #radioPercentil {
        float: left;
        padding: 9px 30px 0px 13px;
    }

    .percentilCreate {
        float: left;
        padding-top: 9px;
    }

    .percentilCreate  a {
        float: left;
    }

    #percentilActive {
        margin-left: 10px;
        min-width: 100px;
    }
</style>
<div class="containerper" id="percentil">
    <div class="ui-widget-header ui-corner-top title">
        {PERCENTILE}
    </div>
	<form id="fmFilterConfigQoe" style="float: left">
	<div class="paneles">
		<fieldset>
			<div style="width: 130px" id="row">
				<label style="border-radius: 0px" for="groupid" accesskey="g">{GROUP}</label>
				<select name="groupid" id="groupid">
					{optionGroups}
				</select>
			</div>
		</fieldset>
	</div>
	</form>
    <div id="radioPercentil">
        <input type="radio" id="radio1" value="0" {checked1} name="percentilType" />
        <label for="radio1">Inclusive</label>
        <input type="radio" id="radio2" value="1" {checked2} name="percentilType" />
        <label for="radio2">Exclusive</label>
    </div>
            
    <div class="percentilCreate">

        <label for="percentilNew">{PERCENTILE}: </label>
        <input size="3" id="percentilNew" name="percentilNew" />
        <button id="addPercentil">
            {ADD}
        </button>

        <span id="percentilActive">
            {buttonPercentile}
         </span>
    </div>
</div>
<div id="modalQoeConfigNew" title="{ASSIGN_NEW_MONITOR}">
{formNewMonitor}
</div>
<div id="modalQoeConfigEdit" title="{EDIT_MONITOR_REPORT}"></div>
<div id="modalQoeConfigDelete" title="{DELETE_ITEM}">
    <p>
        <span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>{MSG_DELETE}
    </p>
    <p style="float:left; margin:4px 0px 0px 0px;" id="selected">{SELECTED}: </p>
</div>

<div style="float: left;" id="qoeMonitorsReportTable">
    No data
</div>