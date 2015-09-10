<form id="{formID}" class="formUI">
    <div id="{formID}_validateTips" class="ui-widget">
        <div class="ui-state-highlight ui-corner-all">
            <p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
            {FORM_ALL_PARAM_REQUIRED}.</p>
        </div>
    </div>
    <fieldset class="ui-widget-content ui-corner-all">
        <div class="row">
            <label for="groupid" class="col1">{GROUPS}</label>
            <span class="col2">
                <select name="groupid" id="groupid" class="select">
                    {optionGroups}
                </select>
            </span>
        </div>      
        <div class="row">
            <label for="item" class="col1">{MONITORS}</label>
            <span class="col2">
                <select name="item" id="item" class="select">
                    {optionMonitors}
                </select>
            </span>
        </div>  
        <div class="row">
            <label for="dashboard" class="col1">{CATEGORY}</label>
            <span class="col2">
                <select name="dashboard" id="dashboard" class="select">
                   {optionBiDashboard}
                </select>
            </span>
        </div>       
    </fieldset>
    <fieldset class="ui-widget-content ui-corner-all">
        <div class="title"><span>Threshold</span></div>
        <div class="row">
            <label for="nominal" class="col1">Nominal</label>
            <span class="col2">
                <select name="thresholdType" id="thresholdType" class="select">
                    <option {selectedthresholdType0} value="0">{PERSONALIZED}</option>
                    <option {selectedthresholdType1} value="1">(Plan) Download</option>
                    <option {selectedthresholdType2} value="2">(Plan) Upload</option>
                </select>
                <input type="text" name="nominal" id="nominal" placeholder="number" value="{nominal}" class="input ui-widget-content ui-corner-all" />
            </span>
        </div> 
        <div class="row">
            <label for="warning" class="col1">{WARNING}</label>
            <span class="col2">
                <input type="text" name="warning" id="warning" placeholder="number" value="{warning}" class="input ui-widget-content ui-corner-all" />
            </span>
        </div>
        <div class="row">
            <label for="critical" class="col1">{CRITICAL}</label>
            <span class="col2">
                <input type="text" name="critical" id="critical" placeholder="number" value="{critical}" class="input  ui-widget-content ui-corner-all" />
            </span>
        </div>
        <div class="row">
            <label for="optionalmail" class="col1">{EMAIL_OPTIONAL}</label>
            <span class="col2">
                <input type="text" name="optionalmail" id="optionalmail" placeholder="Email separated by comma" value="{optionalmail}" class="input verylarge ui-widget-content ui-corner-all" />
            </span>
        </div>
        <div class="row">
            <label for="cycle" class="col1">{CYCLE}</label>
            <span class="col2">
                <input type="text" name="cycle" id="cycle" placeholder="number" value="{cycle}" class="input  ui-widget-content ui-corner-all" />
            </span>
        </div>
         <div class="row">
            <label for="qoeAlert" class="col1">{ALERT}</label>
            <span id="qoeAlert" class="col2" style="padding: 7px;width : 120px">
                <input type="checkbox" name="qoeAlert" value="1" {checkedAlert}>
            </span> 
        </div>
        <div class="row">
            <label for="qoeReport" class="col1">{VIEW_THE_REPORT}</label>
            <span id="qoeReport" class="col2" style="padding: 7px;width : 120px">
                <input type="checkbox" name="qoeReport" value="1" {checkedReport}>
            </span> 
        </div>
    </fieldset>  
</form>