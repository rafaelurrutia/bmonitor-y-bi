<form id="{formID}" class="formUI"  method='POST' enctype='multipart/form-data'>
    <p id="{formID}_validateTips" class="validateTips">
        {FORM_ALL_PARAM_REQUIRED}.
    </p>
    <fieldset class="ui-widget-content ui-corner-all">
        <div class="row">
            <label for="probe" class="col1">{PROBE}</label>
            <span class="col2 ">
				<div style="padding: 6px" id="radioProbe2">
				    <input type="radio" value="1" id="probe3" name="probe" checked="checked"><label for="probe3">{YES}</label>
				    <input type="radio" value="0" id="probe4" name="probe" ><label for="probe4">{NO}</label>
				</div>
            </span>
        </div> 
        <div class="row">
            <label for="fileXML" class="col1">{CONFIGURATION_FILE}</label>
            <span class="col2 ">
            	<div style="padding: 6px" id="fileContainer">
            		<button id="uploadfile" name="uploadfile">{BROWSE}</button>
				    <input type="file" id="fileXML" name="fileXML" style="display: none">
				</div>
            </span>
        </div>
        <div class="row">
            <div style="margin: 9px auto; text-align: center" id="fileName"></div>
        </div> 
    </fieldset>
</form>
