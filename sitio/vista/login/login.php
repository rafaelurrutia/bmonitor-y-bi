{header}
<script type="text/javascript">
	jQuery(function( $ ) {
		$("#username").focus();
	}); 
</script>

<style type="text/css">
	.alert0 {
		min-width: 358px;
		min-height: 75px;
		position: relative;
	}
	.alert1 {
		min-width: 358px;
		min-height: 55px;
		position: relative;
	}
</style>

</head>

<body>

	<div style="opacity: 1" class="container ui-widget-overlay"> 
		{menu}
		<div style="margin-top: 40px" class="row">
			<form  class="form-horizontal" id="formLogin" method="post" action="/login/access" accept-charset="UTF-8">
				<div id="login" style="width: 400px;margin: auto;" class=" well">
					<fieldset>
						<legend style="text-align: center">
							<img src="/sitio/img/main-logo.png" alt="Baking" width="200"/>
						</legend>

						<div id="errorSection" class="ui-widget">
							<div class="ui-state-error ui-corner-all" style="margin: 6px 0px; padding: 0 .7em;">
								<p>
									<span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
									Login username/password incorrect.
								</p>
							</div>
						</div>

						<label class="block clearfix"> <span class="block input-icon input-icon-right">
								<input type="text" id="username" class="form-control" name="username" placeholder="{USER_LOGIN}"/>
								<i class="glyphicon glyphicon-user"></i> </span> </label>

						<label class="block clearfix"> <span class="block input-icon input-icon-right">
								<input type="password" id="password" class="form-control" name="password" placeholder="{USER_PASS}"/>
								<i class="glyphicon glyphicon-lock"></i> </span> </label>

						<div class="input-group input-group-sm" style="padding: 0px 0px 4px 0px;">
							<span class="input-group-addon">{USER_LANG}</span>
							<select class="form-control" name="lang" id="lang" style="
							border: 1px solid #ccc;
							outline: none;
							-webkit-appearance: none;
							">
								<option value="false">Default</option>
								<option value="es">{SPANISH}</option>
								<option value="en">{ENGLISH}</option>
								<option value="pt">{PORTUGUESE}</option>
							</select>
						</div>
						<button style="width: 100%" type="submit" name="btnLogin" id="btnLogin" class="btn btn-primary btn-block">
							{USER_BUTTON}
						</button>
					</fieldset>

				</div>
			</form>
		</div>
	</div>
	{footer}
	<script type="text/javascript" src="{url_base}sitio/js/view.login.js"></script>
</body>
</html>