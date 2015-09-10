<form id="{formID}" class="formUI">
    <p id="{formID}_validateTips" class="validateTips">
        {FORM_ALL_PARAM_REQUIRED}.
    </p>
    <fieldset class="ui-widget-content ui-corner-all">
    	<!--
        <div class="row">
            <label for="structure" class="col1">{STRUCTURE}</label>
            <span class="col2 ">
            	<div style="padding: 6px" id="radioStructure1">
                 	<input type="radio" value="1" id="structure1" name="structure" checked="checked"><label for="structure1">{YES}</label>
					<input type="radio" value="0" id="structure2" name="structure" ><label for="structure2">{NO}</label>
				</div>
            </span>
        </div>
        <div class="row">
            <label for="probe" class="col1">{PROBE}</label>
            <span class="col2 ">
				<div style="padding: 6px" id="radioProbe1">
				    <input type="radio" value="1" id="probe1" name="probe" checked="checked"><label for="probe1">{YES}</label>
				    <input type="radio" value="0" id="probe2" name="probe" ><label for="probe2">{NO}</label>
				</div>
            </span>
        </div>-->
		<div class="row">
            <label for="typeFile" class="col1">{TYPE}</label>
            <span class="col2 ">
				<div style="padding: 6px" id="radioType">
				    <input type="radio" value="0" id="xml" name="typeFile" checked="checked"><label for="xml">XML</label>
				    <input type="radio" value="1" id="excel" name="typeFile" ><label for="excel">EXCEL</label>
				</div>
            </span>
        </div> 
    </fieldset>
</form>
