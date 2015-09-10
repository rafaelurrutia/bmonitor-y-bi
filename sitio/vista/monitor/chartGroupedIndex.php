<script type="text/javascript" src="{url_base}sitio/js/view.chartGroupedIndex.js?ver=1"></script>
<style type="text/css">
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
	#containerGraphGroupsDetail *{
		padding: 5px;
	}
</style>

<div id="loading"></div>
<div id="imgLoading">
	<img widht="32" height="32" src="/sitio/img/ajax-loader.gif" alt="loading" title="loading" />
</div>
<form id="singleFilterChartGrouped">
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
			<div id="row">
				<label for="hostid" accesskey="e">{AGENT}</label>
				<select name="hostid" id="hostid">
					{option_equipos}
				</select>
			</div>
			<div id="row">
				<label for="graphid" accesskey="a">{OPTION_CHART}</label>
				<select name="graphid" id="graphid">
					{option_graph}
				</select>
			</div>
			<div style="display: none"  id="row" class="none refresh">
				<button id="refresh">{REFRESH}</button>
			</div>
		</fieldset>

	</div>
</form>
<div style="min-width: 500px" id="chartGroupedIndex"></div>