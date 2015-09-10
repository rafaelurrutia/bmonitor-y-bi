<script type="text/javascript" src="{url_base}sitio/js/view.graphFilter.js?ver=1"></script>
<style>
	#targetLineBar {
		width: 99%;
		height: 30px;
		padding: 5px 0px 5px 15px;
		background-color: #f3f3f3;
		border-bottom: 1px solid #c3c3c3;
	}
	#targetContainer {
		float: left;
		height: 30px;
		width: 260px;
		padding: 0 0 0 0;
		margin: 0 0 0 0;
	}
	#targetLabel {
		font-family: Georgia;
		font-weight: bold;
		height: 100%;
		margin: 5px 5px 0 0;
		padding: 0 0 0 0;
		float: left;
	}
	#targetTextBox {
		width: 60px;
		padding: 0 0 0 3px;
		margin: 5px 0 0 0;
		height: 18px;
		float: left;
	}
	#targetButton {
		margin: 4px 0 0 5px;
		float: left;
		height: 22px;
	}

	#chkBoxContainer {
		float: left;
		height: 30px;
		padding: 0 10px 0 10px;
		margin: 0 0 0 0;
	}
	#avgChkBox {
		margin: 10px 0 0 0;
		padding: 0px 0 0 0;
	}
	#avgLabel {
		margin: 0 0 0 0;
		padding: 0 0 0 0;
		font-family: Arial;
		font-size: 10pt;
	}
	#details {
		padding: 5px 10px 0 40px;
	}
	#containerGraphDetail *{
		padding: 5px;
	}
	
</style>

<div id="loading"></div>
<div id="imgLoading">
	<img widht="32" height="32" src="/sitio/img/ajax-loader.gif" alt="loading" title="loading" />
</div>
<form id="singleFilterGraph2">
	<div class="paneles">

		<fieldset>
			<div id="row" class="short">
				<label for="limit" accesskey="g">{OPTION_LIMIT}</label>
				<select name="limit" id="limit">
					<option value="0">{SELECT_BY_DEFAULT}</option>
					<option value="5000">1 {MONTH}</option>
					<option value="15000">3 {MONTH}</option>
					<option value="25000">6 {MONTH}</option>
					<option value="60000">1 {YEAR}</option>
				</select>
			</div>
			<div id="row" class="short">
				<label for="dataGrouping" accesskey="g">{GROUPING_DATA}</label>
				<select name="dataGrouping" id="dataGrouping">
					<option value="1">Auto</option>
					<option value="hour">{HOUR}</option>
					<option value="day">{DAY}</option>
					<option value="week">{WEEK}</option>
					<option value="month">{MONTH}</option>
					<option value="0">{DISABLED}</option>
				</select>
			</div>
			<div id="row" class="short">
				<label for="groupid" accesskey="g">{GROUP}</label>
				<select name="groupid" id="groupid">
					{option_groups}
				</select>
			</div>
			<div id="row" class="short">
				<label for="filterid" accesskey="g">{FILTER}</label>
				<select name="filterid" id="filterid">
					{option_filter}
				</select>
			</div>
			<div class="planidLabel short" style="display: none" id="row">
				<label for="planid" accesskey="g">{PLAN}</label>
				<select name="planid" id="planid">
					{option_plan}
				</select>
			</div>
			<div id="row">
				<label for="hostid" accesskey="e">{AGENT}</label>
				<select name="hostid" id="hostid">
					{option_equipos}
				</select>
			</div>
			<div id="row">
				<label for="categoriesid" accesskey="a">{PROBE}</label>
				<select name="categoriesid" id="categoriesid">
					{option_monitor}
				</select>
			</div>
			<div style="max-width: 300px" id="row">
				<label for="monitorid" accesskey="a">{MONITOR}</label>
				<select name="monitorid" id="monitorid">
					{option_monitor}
				</select>
			</div>
			<div style="display: none" id="row" class="none refresh">
				<button id="refresh">{REFRESH}</button>
			</div>
		</fieldset>

	</div>
</form>
<div style="min-width: 500px" id="graph2_ultimafecha_index"></div>