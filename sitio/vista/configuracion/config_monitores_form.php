<form id="{form_id}" accept-charset="utf-8" method="post" class="formUI">
    <div id="{form_id}_validateTips" class="ui-widget">
        <div class="ui-state-highlight ui-corner-all">
            <p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
            {FORM_ALL_PARAM_REQUIRED}.</p>
        </div>
    </div>
    
	<fieldset class="ui-widget-content ui-corner-all">
		<div class="row">
			<label for="descriptionLong" class="col1">{DESCRIPTIVE_NAME}</label>
			<span class="col2"><input type="text" name="descriptionLong" id="descriptionLong" value="{monitor_descriptionLong}" class="input ui-widget-content ui-corner-all" /></span>
		</div>
		<div class="row">
			<label for="description" class="col1">Monitor</label>
			<span class="col2"><input type="text" name="description" id="description" value="{monitor_description}"  class="input ui-widget-content ui-corner-all" /></span>
		</div>
		<div class="row">
			<label for="type" class="col1">{TYPE_MONITORS}</label>
			<span class="col2"><select name="type" id="type" class="select">
				{option_type_monitor}
			</select></span>
		</div>

		<div class="row">
			<label for="type_item" class="col1">{DATATYPE}</label>
			<span class="col2"><select name="type_item" id="type_item" class="select">
				{option_type_item}
			</select></span>
		</div>
				
		<div id="snmp_1" class="row">
			<label for="snmp_community" class="col1">{SNMP_COMMUNITY}</label>
			<span class="col2"><input type="text" name="snmp_community" value="{monitor_snmp_community}" id="snmp_community"  class="input ui-widget-content ui-corner-all" /></span>
		</div>
		
		<div id="snmp_2" class="row">
			<label for="snmp_oid" class="col1">SNMP OID</label>
			<span class="col2"><input type="text" name="snmp_oid" id="snmp_oid" value="{monitor_snmp_oid}" class="input ui-widget-content ui-corner-all" /></span>
		</div>
		
		<div id="snmp_3" class="row">
			<label for="snmp_port" class="col1">{SNMP_PORT}</label>
			<span class="col2"><input type="text" name="snmp_port" value="{monitor_snmp_port}" id="snmp_port" class="input ui-widget-content ui-corner-all" /></span>
		</div>

		<div class="row">
			<label for="unit" class="col1">{UNIT}</label>
			<span class="col2"><input type="text" name="unit" id="unit" value="{monitor_unit}" class="input ui-widget-content ui-corner-all" /></span>
		</div>
		<div class="row">
			<label for="delay" class="col1">{REFRESH_INTERVAL_SECOND}</label>
			<span class="col2"><input type="text" name="delay" id="delay" value="{monitor_delay}" class="input ui-widget-content ui-corner-all" /></span>
		</div>
		<div class="row">
			<label for="history" class="col1">{PRESERVE_HISTORIC_FOR_DAY}</label>
			<span class="col2"><input type="text" name="history" id="history" value="{monitor_history}" class="input ui-widget-content ui-corner-all" value="365" /></span>
		</div>
		<div class="row">
			<label for="trend" class="col1">{KEEP_TRENDS_OVER_DAYS}</label>
			<span class="col2"><input type="text" name="trend" id="trend" value="{monitor_trend}" class="input ui-widget-content ui-corner-all" value="365" /></span>
		</div>
		<div class="row">
			<label for="status" class="col1">{STATUS}</label>
			<span class="col2"><select name="status" id="status" class="select">
				<option selected value="1">{ENABLED}</option>
				<option value="2">{DISABLED}</option>
			</select></span>
		</div>
        <div class="row">
            <label for="tags" class="col1">Tags</label>
            <span class="col2"><input name="tags" id="tags" value="{monitor_tags}"></span>
        </div>
		<div class="row">
		    <label for="groupid" class="col1">{GROUPS}: ({DRAG_ITEMS_LEFT_TO_ALLOCATE})</label>
			
		</div>
		<div class="row multiselectContainer ui-helper-clearfix">
			<select id="groupid" class="multiselect" multiple="multiple" name="groupid[]">
				{menu_groupid_list}
			</select>
		</div>
	</fieldset>
</form>