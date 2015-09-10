<form id="{config_planes_id_form}" class="formUI">	
    <div id="{config_planes_id_form}_validateTips" class="ui-widget">
        <div class="ui-state-highlight ui-corner-all">
            <p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
            {FORM_ALL_PARAM_REQUIRED}.</p>
        </div>
    </div>
	<fieldset class="ui-widget-content ui-corner-all">
		<div class="row">
			<label for="plan_plan" class="col1">{NAME}</label>
			<span class="col2"><input type="text" name="plan_plan" id="plan_plan" value="{plan_plan}" class="input ui-widget-content ui-corner-all" /></span>
		</div>
		<div class="row">
			<label for="plan_plandesc" class="col1">{DESCRIPTION}</label>
			<span class="col2"><input type="text" name="plan_plandesc" id="plan_plandesc" value="{plan_plandesc}"  class="input ui-widget-content ui-corner-all" /></span>
		</div>
		<div class="row">
			<label for="plan_planname" class="col1">{SHORT_DESCRIPTION}</label>
			<span class="col2"><input type="text" name="plan_planname" id="plan_planname" value="{plan_planname}"  class="input ui-widget-content ui-corner-all" /></span>
		</div>
		
        <div class="row" id="otherDisplay">
            <label for="plan_codedown" class="col1">{DOWNLOAD_PLAN_CODE}</label>
            <span class="col2"><input type="text" name="plan_codedown" id="plan_codedown" value="{plan_codedown}" class="input ui-widget-content ui-corner-all" /></span>
        </div>
        <div class="row" id="otherDisplay">
            <label for="plan_codeuplo" class="col1">{UPLOAD_PLAN_CODE}</label>
            <span class="col2"><input type="text" name="plan_codeuplo" id="plan_codeuplo" value="{plan_codeuplo}" class="input ui-widget-content ui-corner-all" /></span>
        </div>
        <div class="row" id="otherDisplay">
            <label for="plan_codclass" class="col1">{CLASS_CODE}</label>
            <span class="col2"><input type="text" name="plan_codclass" id="plan_codclass" value="{plan_codclass}" class="input ui-widget-content ui-corner-all" /></span>
        </div>
        
		<div class="row">
		    <label for="{planes_id_menu}" class="col1">{GROUPS}: ({DRAG_ITEMS_LEFT_TO_ALLOCATE})</label>		
		</div>
		<div class="row multiselectContainer ui-helper-clearfix">
			<select id="{planes_id_menu}" class="multiselect" multiple="multiple" name="plan_groupid[]">
				{menu_groups_inactive}
			</select>
		</div>
		<div class="row">
			<div id="tableConfigPlanFDT" style="width:100%" class="bsw_table ui-grid ui-widget ui-widget-content ui-corner-all">
			<div class="ui-grid-header ui-widget-header ui-corner-top">{MENU_CONFIGURATION}</div>
			<table class="ui-grid-content ui-widget-content">
				<tr>
					<th class="ui-state-default">{DESTINATION}</th>
					<th class="ui-state-default">{SPEED} (kbps)</th>
					<th class="ui-state-default">{SESSIONS}</th>
					<th class="ui-state-default">{TIME_SEC}</th>
				</tr>
				<tr>
					<th class="ui-state-default">NAC {SPEED_DOWNLOAD}</th>
					<td class="ui-grid-content"><input type="text" name="plan_nacD" id="plan_nacD" value="{plan_nacD}" class="input ui-widget-content ui-corner-all" /></td>
					<td class="ui-grid-content"><input type="text" name="plan_nacDS" id="plan_nacDS" value="{plan_nacDS}" class="input ui-widget-content ui-corner-all" /></td>
					<td class="ui-grid-content"><input type="text" name="plan_nacDT" id="plan_nacDT" value="{plan_nacDT}" class="input ui-widget-content ui-corner-all" /></td>
				</tr>
				<tr>
					<th class="ui-state-default">NAC {SPEED_UPLOAD}</th>
					<td class="ui-grid-content"><input type="text" name="plan_nacU" id="plan_nacU" value="{plan_nacU}" class="input ui-widget-content ui-corner-all" /></td>
					<td class="ui-grid-content"><input type="text" name="plan_nacUS" id="plan_nacUS" value="{plan_nacUS}" class="input ui-widget-content ui-corner-all" /></td>
					<td class="ui-grid-content"><input type="text" name="plan_nacUT" id="plan_nacUT" value="{plan_nacUT}" class="input ui-widget-content ui-corner-all" /></td>
				</tr>
				<tr id="otherDisplay">
					<th class="ui-state-default">LOC {SPEED_DOWNLOAD}</th>
					<td class="ui-grid-content"><input type="text" name="plan_locD" id="plan_locD" value="{plan_locD}" class="input ui-widget-content ui-corner-all" /></td>
					<td class="ui-grid-content"><input type="text" name="plan_locDS" id="plan_locDS" value="{plan_locDS}" class="input ui-widget-content ui-corner-all" /></td>
					<td class="ui-grid-content"><input type="text" name="plan_locDT" id="plan_locDT" value="{plan_locDT}" class="input ui-widget-content ui-corner-all" /></td>
				</tr>
				<tr id="otherDisplay">
					<th class="ui-state-default">LOC {SPEED_UPLOAD}</th>
					<td class="ui-grid-content"><input type="text" name="plan_locU" id="plan_locU" value="{plan_locU}" class="input ui-widget-content ui-corner-all" /></td>
					<td class="ui-grid-content"><input type="text" name="plan_locUS" id="plan_locUS" value="{plan_locUS}" class="input ui-widget-content ui-corner-all" /></td>
					<td class="ui-grid-content"><input type="text" name="plan_locUT" id="plan_locUT" value="{plan_locUT}" class="input ui-widget-content ui-corner-all" /></td>
				</tr>
				<tr id="otherDisplay">
					<th class="ui-state-default">INT {SPEED_DOWNLOAD}</th>
					<td class="ui-grid-content"><input type="text" name="plan_intD" id="plan_intD" value="{plan_intD}" class="input ui-widget-content ui-corner-all" /></td>
					<td class="ui-grid-content"><input type="text" name="plan_intDS" id="plan_intDS" value="{plan_intDS}" class="input ui-widget-content ui-corner-all" /></td>
					<td class="ui-grid-content"><input type="text" name="plan_intDT" id="plan_intDT" value="{plan_intDT}" class="input ui-widget-content ui-corner-all" /></td>
				</tr>
				<tr id="otherDisplay">
					<th class="ui-state-default">INT {SPEED_UPLOAD}</th>
					<td class="ui-grid-content"><input type="text" name="plan_intU" id="plan_intU" value="{plan_intU}" class="input ui-widget-content ui-corner-all" /></td>
					<td class="ui-grid-content"><input type="text" name="plan_intUS" id="plan_intUS" value="{plan_intUS}" class="input ui-widget-content ui-corner-all" /></td>
					<td class="ui-grid-content"><input type="text" name="plan_intUT" id="plan_intUT" value="{plan_intUT}" class="input ui-widget-content ui-corner-all" /></td>
				</tr>
			</table>
			</div>
		</div>
		<div class="row" id="otherDisplay">
			<div id="tableConfigPlanFDT" style="width:100%" class="bsw_table ui-grid ui-widget ui-widget-content ui-corner-all">
			<div class="ui-grid-header ui-widget-header ui-corner-top"><label for="plan_sysctl">{CONFIGURATION_FILE} - sysctl</label></div>
			<textarea name="plan_sysctl" id="plan_sysctl" rows="30" class="textarea ui-corner-all" {plan_sysctl_disable}>{plan_sysctl}</textarea>
			</div>	
		</div>
		<div class="row" id="otherDisplay">
			<div id="tableConfigPlanFDT" style="width:100%" class="bsw_table ui-grid ui-widget ui-widget-content ui-corner-all">
			<div class="ui-grid-header ui-widget-header ui-corner-top"><label for="plan_ppp">{CONFIGURATION_FILE} - ppp</label></div>
			<textarea name="plan_ppp" id="plan_ppp" rows="30" class="textarea ui-corner-all" {plan_ppp_disable}>{plan_ppp}</textarea>
			</div>	
		</div>
	</fieldset>
</form>