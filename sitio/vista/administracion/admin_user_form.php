<form id="{formID}" class="formUI">
	<div id="{formID}_validateTips" class="validateTips ui-widget">
		<div class="ui-state-highlight ui-corner-all">
			<p>
				<span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
				{FORM_ALL_PARAM_REQUIRED}
			</p>
		</div>
	</div>

	<fieldset>
        <div class="title"><span>{DATA}</span></div>
		<div class="row">
			<label for="user_name" class="col1">{NAME}</label>
			<span class="col2 set pmedium">
				<input type="text" name="user_name" id="user_name" value="{user_name}" class="input ui-widget-content ui-corner-all" />
			</span>
		</div>

		<div class="row">
			<label for="user_email" class="col1">Email</label>
			<span class="col2 set pmedium">
				<input type="text" name="user_email" id="user_email" value="{user_email}" class="input ui-widget-content ui-corner-all" />
			</span>
		</div>
	</fieldset>

	<fieldset>
	    <div class="title"><span>{ACCESS}</span></div>
        <div class="row">
            <label for="user_user" class="col1">{USER}</label>
            <span class="col2 set pmedium">
                <input type="text" name="user_user" id="user_user" value="{user_user}" class="input ui-widget-content ui-corner-all" />
            </span>
        </div>

        <div class="row">
            <label for="user_passwd" class="col1">{USER_PASS}</label>
            <span class="col2 set pmedium">
                <input type="password" name="user_passwd" id="user_passwd"  onkeyup="$.passwordStrength(this.value)" class="input ui-widget-content ui-corner-all" />
            </span>
        </div>
               
        <div class="row">
            <label for="user_passwd_valid" class="col1">{CONFIRMATION}</label>
            <span class="col2 set pmedium">
                <input type="password" name="user_passwd_valid" id="user_passwd_valid"  class="input ui-widget-content ui-corner-all" />
            </span>
        </div>
	</fieldset>

	<fieldset>
	    <div class="title"><span>{SECURITY}</span></div>

        <div class="row" style="padding: 3px 7px">
            <label for="passwordStrength">{PASSWORD_STRENGTH}</label></br>
        </div>

        <div class="row" style="padding: 3px 7px">
            <div id="passwordDescription">
                Password field is empty
            </div>
            <div id="passwordStrength" class="strength0"></div>
        </div>
	</fieldset>
	<fieldset>
	    <div class="title"><span>{PERMISSIONS}</span></div>

        <div class="row">
            <label for="user_id_group" class="col1">{GROUP}</label>
            <span class="col2 set pmedium">
                <select name="user_id_group" id="user_id_group" class="select ui-widget-content ui-corner-all">
                    {optionGroups}
                </select>
            </span>
        </div>
	</fieldset>
</form>