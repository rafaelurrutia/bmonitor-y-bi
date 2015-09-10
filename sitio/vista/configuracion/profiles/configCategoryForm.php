<form id="{formID}" class="formUI">
    <p id="{formID}_validateTips" class="validateTips">
        {FORM_ALL_PARAM_REQUIRED}.
    </p>

    <fieldset class="ui-widget-content ui-corner-all">
        <div class="row">
            <label for="name" class="col1">{NAME}</label>
            <span class="col2">
                <input type="text" name="name" id="name" value="{name}" class="input input-long ui-widget-content ui-corner-all" />
            </span>
        </div>
        <div class="row">
            <label for="class" class="col1">{CLASS}</label>
            <span class="col2">
                <input type="text" name="class" id="class" value="{class}" class="input input-long ui-widget-content ui-corner-all" />
            </span>
        </div>
        <div class="row">
            <label for="codeSequence" class="col1">{CODE_SEQUENCE}</label>
            <span class="col2">
                <input type="text" name="codeSequence" id="codeSequence" value="{codeSequence}" class="input input-long ui-widget-content ui-corner-all" />
            </span>
        </div>
    </fieldset>

</form>
