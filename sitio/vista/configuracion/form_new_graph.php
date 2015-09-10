<p class="validateTips">All form fields are requiered.</p>
<form id="form_new_graph" class="form_sonda">		
	<fieldset class="ui-widget-content ui-corner-all">
		<div class="row">
			<label for="graph_name" class="col1">{NAME}</label>
			<span class="col2"><input type="text" name="graph_name" id="graph_name" class="input ui-widget-content ui-corner-all" /></span>
		</div>
		<div class="row">
			<label for="graph_colum" class="col1">{COLUMNS}</label>
			<span class="col2"><input type="text" name="graph_colum" id="graph_colum" class="input ui-widget-content ui-corner-all" /></span>
		</div>
		<div class="row">
			<label for="graph_filas" class="col1">{ROWS}</label>
			<span class="col2"><input type="text" name="graph_filas" id="graph_filas" class="input ui-widget-content ui-corner-all" /></span>
		</div>
		<div class="row">
			<label for="graph_groupid" class="col1">{GROUPS}: (Drag to rigth panel to assign)</label>
		</div>
		<div class="row">
			<div id="demo">
			<ul class="monitor_groupid_1" id="graph_groupid1" class="ui-corner-all">
				{menu_group_inactive}
			</ul>
			<ul class="monitor_groupid_2" id="graph_groupid_2" class="ui-corner-all">
				{menu_group_active}
			</ul>
			</div>
		</div>
	</fieldset>
</form>