<form id="{formID}" class="formUI">
    <p id="{formID}_validateTips" class="validateTips">
        {FORM_ALL_PARAM_REQUIRED}
    </p>

    <fieldset class="ui-widget-content ui-corner-all">
    	{company}
        <div class="row">
            <label for="name" class="col1">{NAME}</label>
            <span class="col2">
                <input type="text" name="groupName" id="groupName" value="{groupName}" class="input input-long ui-widget-content ui-corner-all" />
            </span>
        </div>
        <div class="row">
            <label for="default" class="col1">{DEFAULT_GROUP}</label>
            <span class="col2">
                <input type="checkbox" id="default" name="default" value="{default}">
            </span>
        </div>
        <div class="row">
            <label for="grouplist" class="col1">{GROUPS}: ({DRAG_ITEMS_LEFT_TO_ALLOCATE})</label>
        </div>
        <div class="row multiselectContainer ui-helper-clearfix">
            <select id="grouplist" class="multiselect" multiple="multiple" name="grouplist[]">
                {hostGroupsList}
            </select>
        </div>    
    </fieldset>

</form>