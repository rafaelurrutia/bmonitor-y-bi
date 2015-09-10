<form id="{config_graph_id_form}" class="formUI">	
    <div id="{config_graph_id_form}_validateTips" class="ui-widget">
        <div class="ui-state-highlight ui-corner-all">
            <p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
            {FORM_ALL_PARAM_REQUIRED}.</p>
        </div>
    </div>	
	<fieldset class="ui-widget-content ui-corner-all">
		<div class="row">
			<label for="name" class="col1">{NAME}</label>
			<span class="col2">
				<input type="text" name="name" id="name" value="{graph_name}" class="input ui-widget-content ui-corner-all" />
			</span>
		</div>
		<div class="row">
			<label for="graphtype" class="col1">{TYPE}</label>
			<span class="col2">
				<select name="graphtype" id="graphtype" class="select">
					<option value="line">Line</option>
				</select>
			</span>
		</div>
        <div class="row">
            <label for="groupid" class="col1">{GROUP}</label>
            <span class="col2">
                <select name="groupid" id="groupid" class="select">
                    {optionGroupid}
                </select>
            </span>
        </div>
		<div class="row">
		    <label for="items" class="col1">Items: {DRAG_ITEMS_LEFT_TO_ALLOCATE}</label>
			
		</div>
		<div class="row multiselectContainer ui-helper-clearfix">
			<select id="items" class="multiselect" multiple="multiple" name="items[]">
				{menu_items_inactive}
			</select>
		</div>
	</fieldset>
</form>