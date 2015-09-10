<script type="text/javascript" src="{url_base}sitio/js/view.graph_screen.js"></script>
<style type="text/css">
	.column {
		float: left;
		padding-bottom: 100px;
	}
	.portlet {
		margin: 0 1em 1em 0;
		padding: 0.3em;
	}
	.portlet-header {
		padding: 0.2em 0.3em;
		margin-bottom: 0.5em;
		position: relative;
	}
	.portlet-toggle {
		position: absolute;
		top: 50%;
		right: 0;
		margin-top: -8px;
	}
	.portlet-content {
		padding: 0.4em;
	}
	.portlet-placeholder {
		border: 1px dotted black;
		margin: 0 1em 1em 0;
		height: 50px;
	}
</style>
<div class="paneles">
	<form id="screen_fmFilter">
		<fieldset>
			<div id="row">
				<label for="screen_fmGrupo" accesskey="g">{GROUP}</label>
				<select name="screen_fmGrupo" id="screen_fmGrupo">
					{select_group}
				</select>
			</div>
			<div id="row">
				<label for="screen_fmScreen" accesskey="g">{SCREEN}</label>
				<select name="screen_fmScreen" id="screen_fmScreen">
					{select_screen}
				</select>
			</div>
			<div id="row">
				<label for="screen_fmEquipo" accesskey="g">{AGENT}</label>
				<select name="screen_fmEquipo" id="screen_fmEquipo">
					<option>{SELECT_A_GROUP}</option>
					{select_equipo}
				</select>
			</div>
		</fieldset>
	</form>
</div>
<div id="contenedor" style="height: 900px;">
	<div id="screen_display">

	</div>
</div>