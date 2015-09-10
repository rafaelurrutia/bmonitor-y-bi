<form id="{formID}" class="formUI">
    <p id="{formID}_validateTips" class="validateTips">
        {FORM_ALL_PARAM_REQUIRED}.
    </p>
    <fieldset class="ui-widget-content ui-corner-all">
        <input type="hidden" name="type" id="type" value="result"/>
        <div class="row">
            <label for="name" class="col1">{CODE}</label>
            <span class="col2 ">
                <input type="text" name="name" id="name" value="{name}" class="input input-long ui-widget-content ui-corner-all" />
            </span>
        </div>
        <div class="row">
            <label for="display" class="col1">{DESCRIPTION}</label>
            <span class="col2 ">
                <input type="text" name="display" id="display" value="{display}" class="input input-long ui-widget-content ui-corner-all" />
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
            <span class="col2 ">
                <input type="text" name="default" id="default" value="{default}" class="input input-long ui-widget-content ui-corner-all" />
            </span>
        </div>
        <div class="row">
            <label for="unit" class="col1">{UNIT}</label>
            <span class="col2 ">
                <input type="text" name="unit" id="unit" value="{unit}" class="input input-long ui-widget-content ui-corner-all" />
            </span>
        </div>
        <div style="display: none" class="row">
            <label class="col1">{REPORT} QoS</label>
            <span id="profileReport" class="col2" style="padding: 7px;width : 105px">
                <input type="checkbox" name="report" value="1" {checkedReport}>
            </span> 
        </div>
        <div class="row">
            <label class="col1">{SAVE} IP</label>
            <span id="profileSaveip" class="col2" style="padding: 7px;width : 105px">
                <input type="checkbox" name="saveip" value="1" {saveip}>
            </span> 
        </div>
        <div class="row">
            <label class="col1">Alertar</label>
            <span id="profileAlert" class="col2" style="padding: 7px;width : 105px">
                <input type="checkbox" name="thold" value="1" {checkedThold}>
            </span> 
        </div>
        <div class="row">
            <label class="col1">Cycles</label>
             <span class="col2 ">
                <input type="text" name="cycles" id="cycles" value="{cycles}" class="input input-long ui-widget-content ui-corner-all" />
            </span>
        </div>
        <div class="row">
            <label class="col1">Valor Minimo</label>
             <span class="col2 ">
                <input type="text" name="minThold" id="minThold" value="{minThold}" class="input input-long ui-widget-content ui-corner-all" />
            </span> 
        </div>
        <div class="row">
            <label class="col1">Valor Maximo</label>
             <span class="col2 ">
                <input type="text" name="maxThold" id="maxThold" value="{maxThold}" class="input input-long ui-widget-content ui-corner-all" />
            </span>
        </div>   
    </fieldset>
</form>
