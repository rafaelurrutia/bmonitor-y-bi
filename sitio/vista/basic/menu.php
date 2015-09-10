<div id="header">
	<div id="toppanel">
		<div class="tab">
			{menu_inicio}
			<img class="logo_bsw" alt="Logo" src="{url_base}sitio/img/logobmonitor.png" />
			<ul id="menuIndex" class="menu" >
				<li class="left"/>
				{menu_permitido}
				<li class="right"/>
			</ul>
			<ul class="login">
				<li class="left"/>
				<li>{welcome} {name_user}</li>
				<li class="sep">|</li>
				<li class="toggle"> 
					<a  href="{url_base}login/perfil">Perfil</a>
				</li>
				<li class="sep">|</li>
				<li class="toggle"> 
					<a href="#" onclick="logoutClick()" id="logout">Salida</a>
				</li>
				<li class="right"/>
			</ul>
			{menu_fin}
		</div>
	</div>
</div>