<script language="JavaScript">
jQuery(function() {
	table_pesos();
	function table_pesos(){	
		$.post('{url_base}neutralidad/getPesosTable',
			$("#fmFilter_neutralidad_pesos").serialize(),
			function(responseText){
				if(responseText.status) {
					for (var i=0; i < responseText.rows.length; i++) {
						$('input:text[name='+responseText.rows[i].name+']').val(responseText.rows[i].value);
					};	
				} else {
					$('input:text').val(0);
				}
			},
			"json"
		);
	}
	$("#fmGrupo_neutralidad_pesos").change(function(){
		table_pesos();
	});

	$("#fmMeses_neutralidad_pesos").change(function(){
		table_pesos();
	});

	$("#fmAno_neutralidad_pesos").change(function(){
		table_pesos();
	});
	
	$( "#save_pesos" ).button();
});

function table_pesos_save(){	
	$.post('{url_base}neutralidad/putPesosTable',
		$(":input").serialize(),
		function(responseText){
			if(responseText.status) {
				alert('{SAVED_DATA_SUCCESSFULLY}');
			} else {
				alert('{ITERNAL_ERROR}');
			}
		},
		"json"
	);
}
</script>
<div>
	<div id="loading"></div>
	<div id="imgLoading">
		<img widht="32" height="32" src="/sitio/img/ajax-loader.gif" alt="loading" title="loading" />
	</div>
	
	<div class="paneles ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
	<form id="fmFilter_neutralidad_pesos">
	
	<label for="fmGrupo_neutralidad_pesos" accesskey="g">{GROUP}</label>
	<select name="fmGrupo_neutralidad_pesos" id="fmGrupo_neutralidad_pesos">
	{option_groups}
	</select>
	
	<label for="fmMeses_neutralidad_pesos" accesskey="m">{MONTH}</label>
	<select name="fmMeses_neutralidad_pesos" id="fmMeses_neutralidad_pesos">
	{option_meses}
	</select>
	
	<label for="fmAno_neutralidad_pesos" accesskey="a">{YEAR}</label>
	<select name="fmAno_neutralidad_pesos" id="fmAno_neutralidad_pesos">
	{option_anos}
	</select>
	</form>
	</div>
	
	<div id="tableResumenConsFDT" style="width:660px" class="ui-grid ui-widget ui-widget-content ui-corner-all">
		<div class="ui-grid-header ui-widget-header ui-corner-top">{WEIGHING}</div>
		<form id="neutralidad_pesos_form" >
		<table class="ui-grid-content ui-widget-content">
			<tr>
				<td class="ui-grid-content">&nbsp;</td>
				<th class="ui-state-default">{SUNDAY}</th>
				<th class="ui-state-default">{MONDAY}</th>
				<th class="ui-state-default">{TUESDAY}</th>
				<th class="ui-state-default">{WEDNESDAY}</th>
				<th class="ui-state-default">{THURSDAY}</th>
				<th class="ui-state-default">{FRIDAY}</th>
				<th class="ui-state-default">{SATURDAY}</th>
			</tr>
			<tr>
				<th class="ui-state-default">0:00</th>
				<td class="ui-grid-content"><input type="text" name="H00_D0" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H00_D1" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H00_D2" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H00_D3" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H00_D4" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H00_D5" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H00_D6" size="10" value="0"></td>
			</tr>
			<tr>
				<th class="ui-state-default">1:00</th>
				<td class="ui-grid-content"><input type="text" name="H01_D0" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H01_D1" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H01_D2" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H01_D3" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H01_D4" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H01_D5" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H01_D6" size="10" value="0"></td>
			</tr>			
			<tr>
				<th class="ui-state-default">2:00</th>
				<td class="ui-grid-content"><input type="text" name="H02_D0" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H02_D1" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H02_D2" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H02_D3" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H02_D4" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H02_D5" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H02_D6" size="10" value="0"></td>
			</tr>			
			<tr>
				<th class="ui-state-default">3:00</th>
				<td class="ui-grid-content"><input type="text" name="H03_D0" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H03_D1" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H03_D2" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H03_D3" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H03_D4" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H03_D5" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H03_D6" size="10" value="0"></td>
			</tr>			
			<tr>
				<th class="ui-state-default">4:00</th>
				<td class="ui-grid-content"><input type="text" name="H04_D0" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H04_D1" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H04_D2" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H04_D3" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H04_D4" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H04_D5" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H04_D6" size="10" value="0"></td>
			</tr>
			<tr>
				<th class="ui-state-default">5:00</th>
				<td class="ui-grid-content"><input type="text" name="H05_D0" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H05_D1" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H05_D2" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H05_D3" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H05_D4" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H05_D5" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H05_D6" size="10" value="0"></td>
			</tr>
			<tr>
				<th class="ui-state-default">6:00</th>
				<td class="ui-grid-content"><input type="text" name="H06_D0" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H06_D1" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H06_D2" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H06_D3" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H06_D4" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H06_D5" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H06_D6" size="10" value="0"></td>
			</tr>
			<tr>
				<th class="ui-state-default">7:00</th>
				<td class="ui-grid-content"><input type="text" name="H07_D0" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H07_D1" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H07_D2" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H07_D3" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H07_D4" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H07_D5" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H07_D6" size="10" value="0"></td>
			</tr>
			<tr>
				<th class="ui-state-default">8:00</th>
				<td class="ui-grid-content"><input type="text" name="H08_D0" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H08_D1" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H08_D2" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H08_D3" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H08_D4" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H08_D5" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H08_D6" size="10" value="0"></td>
			</tr>
			<tr>
				<th class="ui-state-default">9:00</th>
				<td class="ui-grid-content"><input type="text" name="H09_D0" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H09_D1" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H09_D2" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H09_D3" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H09_D4" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H09_D5" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H09_D6" size="10" value="0"></td>
			</tr>
			<tr>
				<th class="ui-state-default">10:00</th>
				<td class="ui-grid-content"><input type="text" name="H10_D0" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H10_D1" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H10_D2" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H10_D3" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H10_D4" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H10_D5" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H10_D6" size="10" value="0"></td>
			</tr>
			<tr>
				<th class="ui-state-default">11:00</th>
				<td class="ui-grid-content"><input type="text" name="H11_D0" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H11_D1" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H11_D2" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H11_D3" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H11_D4" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H11_D5" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H11_D6" size="10" value="0"></td>
			</tr>
			<tr>
				<th class="ui-state-default">12:00</th>
				<td class="ui-grid-content"><input type="text" name="H12_D0" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H12_D1" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H12_D2" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H12_D3" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H12_D4" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H12_D5" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H12_D6" size="10" value="0"></td>
			</tr>
			<tr>
				<th class="ui-state-default">13:00</th>
				<td class="ui-grid-content"><input type="text" name="H13_D0" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H13_D1" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H13_D2" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H13_D3" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H13_D4" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H13_D5" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H13_D6" size="10" value="0"></td>
			</tr>
			<tr>
				<th class="ui-state-default">14:00</th>
				<td class="ui-grid-content"><input type="text" name="H14_D0" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H14_D1" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H14_D2" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H14_D3" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H14_D4" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H14_D5" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H14_D6" size="10" value="0"></td>
			</tr>
			<tr>
				<th class="ui-state-default">15:00</th>
				<td class="ui-grid-content"><input type="text" name="H15_D0" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H15_D1" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H15_D2" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H15_D3" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H15_D4" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H15_D5" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H15_D6" size="10" value="0"></td>
			</tr>
			<tr>
				<th class="ui-state-default">16:00</th>
				<td class="ui-grid-content"><input type="text" name="H16_D0" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H16_D1" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H16_D2" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H16_D3" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H16_D4" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H16_D5" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H16_D6" size="10" value="0"></td>
			</tr>
			<tr>
				<th class="ui-state-default">17:00</th>
				<td class="ui-grid-content"><input type="text" name="H17_D0" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H17_D1" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H17_D2" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H17_D3" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H17_D4" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H17_D5" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H17_D6" size="10" value="0"></td>
			</tr>
			<tr>
				<th class="ui-state-default">18:00</th>
				<td class="ui-grid-content"><input type="text" name="H18_D0" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H18_D1" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H18_D2" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H18_D3" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H18_D4" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H18_D5" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H18_D6" size="10" value="0"></td>
			</tr>
			<tr>
				<th class="ui-state-default">19:00</th>
				<td class="ui-grid-content"><input type="text" name="H19_D0" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H19_D1" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H19_D2" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H19_D3" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H19_D4" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H19_D5" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H19_D6" size="10" value="0"></td>
			</tr>
			<tr>
				<th class="ui-state-default">20:00</th>
				<td class="ui-grid-content"><input type="text" name="H20_D0" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H20_D1" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H20_D2" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H20_D3" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H20_D4" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H20_D5" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H20_D6" size="10" value="0"></td>
			</tr>
			<tr>
				<th class="ui-state-default">21:00</th>
				<td class="ui-grid-content"><input type="text" name="H21_D0" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H21_D1" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H21_D2" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H21_D3" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H21_D4" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H21_D5" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H21_D6" size="10" value="0"></td>
			</tr>
			<tr>
				<th class="ui-state-default">22:00</th>
				<td class="ui-grid-content"><input type="text" name="H22_D0" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H22_D1" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H22_D2" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H22_D3" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H22_D4" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H22_D5" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H22_D6" size="10" value="0"></td>
			</tr>
			<tr>
				<th class="ui-state-default">23:00</th>
				<td class="ui-grid-content"><input type="text" name="H23_D0" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H23_D1" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H23_D2" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H23_D3" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H23_D4" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H23_D5" size="10" value="0"></td>
				<td class="ui-grid-content"><input type="text" name="H23_D6" size="10" value="0"></td>
			</tr>
		</table>
		<div class="ui-toolbar ui-widget-header ui-corner-bl ui-corner-br ui-helper-clearfix">
			<div style="width: 50%; float: left;"><input type="button" value="{SAVE}" name="save_pesos" id="save_pesos" onClick="table_pesos_save()" /></div>	
		</div>
		
		</form>
	</div>
</div>