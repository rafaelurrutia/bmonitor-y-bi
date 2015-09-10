<form id="{formID}" class="formUI">
    
    <div id="{formID}_validateTips" class="validateTips ui-widget">
        <div class="ui-state-highlight ui-corner-all">
            <p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
            {FORM_ALL_PARAM_REQUIRED}.</p>
        </div>
    </div>


    <fieldset class="ui-widget-content ui-corner-all">
        <div class="row">
            <label for="name" class="col1">{NAME}</label>
            <span class="col2 set pmedium">
                <input type="text" name="name" id="name" value="{name}" class="input ui-widget-content ui-corner-all" />
            </span>
        </div>
        <div class="row">
            <label for="groupid" class="col1">{GROUPS}: ({DRAG_ITEMS_LEFT_TO_ALLOCATE})</label>     
        </div>
        <div class="row multiselectContainer ui-helper-clearfix">
            <select id="groupid" class="multiselect" multiple="multiple" name="groupid[]">
                {groupidList}
            </select>
        </div>
    </fieldset>

</form>
