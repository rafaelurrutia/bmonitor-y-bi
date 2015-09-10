<form id="{formID}" class="formUI">
    <p id="{formID}_validateTips" class="validateTips">
        {FORM_ALL_PARAM_REQUIRED}.
    </p>

    <fieldset class="ui-widget-content ui-corner-all">
        <input type="hidden" name="type" id="type" value="param"/>
        <div class="row">
            <label for="name" class="col1">{CODE}</label>
            <span class="col2">
                <input type="text" name="name" id="name" value="{name}" class="input input-long ui-widget-content ui-corner-all" />
            </span>
        </div>
        <div class="row">
            <label for="display" class="col1">{DISPLAY}</label>
            <span class="col2">
                <input type="text" name="display" id="display" value="{display}" class="input input-long ui-widget-content ui-corner-all" />
            </span>
        </div>
        <div class="row">
            <label for="description" class="col1">{DESCRIPTION}</label>
            <span class="col2">
                <input type="text" name="description" id="description" value="{description}" class="input input-long ui-widget-content ui-corner-all" />
            </span>
        </div>
        <div class="row">
            <label for="typeData" class="col1">{DATATYPE}</label>
            <span class="col2">
                <select name="typeData" id="typeData" class="select">
                    {optionTypeData}
                </select></span>
        </div>
        <div class="row">
            <label for="default" class="col1">{DEFAULT}</label>
            <span class="col2">
                <input type="text" name="default" id="default" value="{default}" class="input input-long ui-widget-content ui-corner-all" />
            </span>
        </div>
    </fieldset>

</form>
