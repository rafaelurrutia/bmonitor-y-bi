<div id="header">
	<div id="toppanel">
		<div id="tabs_menu" style="height: 46px" class="tabs_menu-bottom ui-tabs ui-widget ui-widget-content ui-corner-all">
			{menu_inicio}
			<ul style="float: left" class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-corner-bottom">
				{menu_permitido}
			</ul>

			<ul style="float: right" class="ui-tabs-nav ui-helper-reset ui-helper-clearfix  ui-corner-all">
				<li style="float: right;">
					<a class="ui-state-default ui-corner-all" href="#" onclick="logoutClick()" id="logout">{EXIT}</a>
				</li>
				<li style="float: right;">
					<a class="ui-state-default ui-corner-all" href="{url_base}login/perfil">{PROFILE}</a>
				</li>
				{bmonitorLink}
				<li style="float: right;">
					<a class="ui-state-default ui-corner-all" href="#">{WELCOME} {name_user}</a>
				</li>
			</ul>
			{menu_fin}
		</div>
	</div>
</div>
