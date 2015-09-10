<script type="text/javascript" src="{url_base}sitio/js/view.profile.js"></script>
<style>
	.column {
		width: 300px;
		float: left;
		padding-bottom: 100px;
	}
	.portlet {
		margin: 0 1em 1em 0;
	}
	.portlet-header {
		margin: 0.3em;
		padding-bottom: 4px;
		padding-left: 0.2em;
	}
	.portlet-header .ui-icon {
		float: right;
	}
	.portlet-content {
		padding: 0.4em;
	}
	.ui-sortable-placeholder {
		border: 1px dotted black;
		visibility: visible !important;
		height: 150px !important;
	}
	.ui-sortable-placeholder * {
		visibility: hidden;
	}
</style>
<form id="form_user_new" style=" height: 328px; ">
	<div class="column">
<!--		<div class="portlet">
			<div class="portlet-header">
				{GENERAL}
			</div>
			<div class="portlet-content">
				<table id="tableProfileUSer" style="width: 100%" class="ui-grid-content ui-widget-content">
					<tbody>
						<tr>
							<td class="ui-widget-content"><label for="user_name">{NAME}</label></td>
							<td class="ui-widget-content">
							<input type="text" name="user_name" id="user_name" value="{user_name}" />
							</td>
						</tr>
						<tr>
							<td class="ui-widget-content"><label for="user_email">Email</label></td>
							<td class="ui-widget-content">
							<input type="text" name="user_email" id="user_email" value="{user_email}" />
							</td>
						</tr>
						<tr>
							<td colspan="2" class="ui-widget-content">
							<button type='button' id='general_button'>
								{SAVE}
							</button></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>  
-->
	<div class="portlet">
		<div class="portlet-header">
			{CHANGE_PASSWORD}
		</div>
		<div class="portlet-content">
			<table id="tableProfileUSer" style="width: 100%" class="ui-grid-content ui-widget-content">
				<tbody>
					<tr>
						<td class="ui-widget-content"><label for="user_passwd_active" value="">{CURRENT_PASSWORD}</label></td>
						<td class="ui-widget-content">
						<input type="password" name="user_passwd_active" id="user_passwd_active" value="" />   
						</td>
					</tr>
					<tr>
						<td class="ui-widget-content"><label for="user_passwd">{NEW_PASSWORD}</label></td>
						<td class="ui-widget-content">
						<input type="password" name="user_passwd" id="user_passwd" value="{user_passwd}" />
						</td>
					</tr>
					<tr>
						<td class="ui-widget-content"><label for="user_passwd_valid">{CONFIRMATION}</label></td>
						<td class="ui-widget-content">
						<input type="password" name="user_passwd_valid" id="user_passwd_valid" value="{user_passwd_valid}"  />
						</td>
					</tr>
					<tr>
						<td colspan="2" class="ui-widget-content">
						<button type='button' id='seguridad_button'>
							{SAVE}
						</button></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	</div>
<!--	<div class="column"> 
	<div class="portlet">
	    <div class="portlet-header">{TEMPLATE_LANG}</div>
		<div class="portlet-content"><div id="switcher"></div></div>
	</div>
	</div>
-->
</form>