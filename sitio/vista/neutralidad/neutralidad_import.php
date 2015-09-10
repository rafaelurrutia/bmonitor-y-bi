<script language="JavaScript">
	function ajaxFileUpload()
	{
		$("#loading").ajaxStart(function(){
			$('#loading').show();
			$('#imgLoading').show();
		}).ajaxComplete(function(){
			$('#loading').hide();
			$('#imgLoading').hide();
		});
		
		var grupo = $("#fmGrupo_import").val();
		var mes = $("#fmMeses_import").val();
		var ano = $("#fmAno_import").val();
		var separador = $("#neutralidad_export_separador").val();
		

		$.ajaxFileUpload
		(
			{
				url:'/neutralidad/setImport',
				secureuri:false,
				fileElementId:'import_file',
				dataType: 'json',
				data:{grupo:grupo, mes:mes, ano:ano, separador:separador},
				success: function (data, status)
				{
					if(typeof(data.error) != 'undefined')
					{
						if(data.error != '')
						{
							alert(data.error);
						}else
						{
							$('#tablePesos').show();
							for (var i=0; i < data.rows.length; i++) {
								$('#'+data.rows[i].name).text(data.rows[i].value);
							};
						}
					}
				},
				error: function (data, status, e)
				{
					alert(e);
				}
			}
		)
		
		return false;
	}
	
	$( "#buttonUpload" ).button();
</script>
<div id="loading"></div>
<div id="imgLoading">
	<img widht="32" height="32" src="/sitio/img/ajax-loader.gif" alt="loading" title="loading" />
</div>
	
<div class="tables">
			
	<form id="fmFilter_import">
	<div id="tableResumenConsFDT" class="ui-grid ui-widget ui-widget-content ui-corner-all">
		<div class="ui-grid-header ui-widget-header ui-corner-top">Import {NEUTRALITY}</div>
		<table id="tableResumenConsFDT_int" class="ui-grid-content ui-widget-content">
			<thead>
				<tr>
					<th class="ui-state-default">{FIELD}</th>
					<th class="ui-state-default">{SELECTION}</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td class="ui-widget-content"><label for="fmGrupo_import" accesskey="g">{GROUP}</label></td>
					<td class="ui-widget-content">
						<select name="fmGrupo_import" id="fmGrupo_import">
							{option_groups}
						</select></td>
				</tr>
				<tr>
					<td class="ui-widget-content"><label for="fmMeses_import" accesskey="m">{MONTH}</label></td>
					<td class="ui-widget-content">						
						<select name="fmMeses_import" id="fmMeses_import">
							{option_meses}
						</select>
					</td>
				</tr>
				<tr>
					<td class="ui-widget-content"><label for="fmAno_import" accesskey="a">{YEAR}</label></td>
					<td class="ui-widget-content">						
						<select name="fmAno_import" id="fmAno_import">
							{option_anos}
						</select>
					</td>
				</tr>
				<tr>
					<td class="ui-widget-content"><label for="neutralidad_export_separador" accesskey="a">{SEPARATOR}:</label></td>
					<td class="ui-widget-content">						
						<select name="neutralidad_export_separador" id="neutralidad_export_separador">
							<option value=',' selected>,</option>
							<option value=';'>;</option>
						</select>
					</td>
				</form>
				</tr>
				<tr>
					<td colspan="2" class="ui-widget-content"><input id="import_file" type="file" size="45" name="import_file" class="input"></td>
				</tr>
				<tr>
					<td colspan="2" class="ui-widget-content"><button class="button" id="buttonUpload" onclick="return ajaxFileUpload();">{FILE_UPLOAD}</button></td>
				</tr>
			</tbody>
		</table>
	</div>
<div id="tablePesos"  style="display: none;">
		<table id="tableResumenConsFDT_int" class="tableBSW ui-grid-content ui-widget-content">
			<tr>
				<td>&nbsp;</td>
                <th class="ui-state-default">{SUNDAY}</th>
                <th class="ui-state-default">{MONDAY}</th>
                <th class="ui-state-default">{TUESDAY}</th>
                <th class="ui-state-default">{WEDNESDAY}</th>
                <th class="ui-state-default">{THURSDAY}</th>
                <th class="ui-state-default">{FRIDAY}</th>
                <th class="ui-state-default">{SATURDAY}</th>
			</tr>
			<tr>
				<th>0:00</th>
				<td><span id="H00_D0" /></td>
				<td><span id="H00_D1" /></td>
				<td><span id="H00_D2" /></td>
				<td><span id="H00_D3" /></td>
				<td><span id="H00_D4" /></td>
				<td><span id="H00_D5" /></td>
				<td><span id="H00_D6" /></td>
			</tr>
			<tr>
				<th>1:00</th>
				<td><span id="H01_D0" /></td>
				<td><span id="H01_D1" /></td>
				<td><span id="H01_D2" /></td>
				<td><span id="H01_D3" /></td>
				<td><span id="H01_D4" /></td>
				<td><span id="H01_D5" /></td>
				<td><span id="H01_D6" /></td>
			</tr>			
			<tr>
				<th>2:00</th>
				<td><span id="H02_D0" /></td>
				<td><span id="H02_D1" /></td>
				<td><span id="H02_D2" /></td>
				<td><span id="H02_D3" /></td>
				<td><span id="H02_D4" /></td>
				<td><span id="H02_D5" /></td>
				<td><span id="H02_D6" /></td>
			</tr>			
			<tr>
				<th>3:00</th>
				<td><span id="H03_D0" /></td>
				<td><span id="H03_D1" /></td>
				<td><span id="H03_D2" /></td>
				<td><span id="H03_D3" /></td>
				<td><span id="H03_D4" /></td>
				<td><span id="H03_D5" /></td>
				<td><span id="H03_D6" /></td>
			</tr>			
			<tr>
				<th>4:00</th>
				<td><span id="H04_D0" /></td>
				<td><span id="H04_D1" /></td>
				<td><span id="H04_D2" /></td>
				<td><span id="H04_D3" /></td>
				<td><span id="H04_D4" /></td>
				<td><span id="H04_D5" /></td>
				<td><span id="H04_D6" /></td>
			</tr>
			<tr>
				<th>5:00</th>
				<td><span id="H05_D0" /></td>
				<td><span id="H05_D1" /></td>
				<td><span id="H05_D2" /></td>
				<td><span id="H05_D3" /></td>
				<td><span id="H05_D4" /></td>
				<td><span id="H05_D5" /></td>
				<td><span id="H05_D6" /></td>
			</tr>
			<tr>
				<th>6:00</th>
				<td><span id="H06_D0" /></td>
				<td><span id="H06_D1" /></td>
				<td><span id="H06_D2" /></td>
				<td><span id="H06_D3" /></td>
				<td><span id="H06_D4" /></td>
				<td><span id="H06_D5" /></td>
				<td><span id="H06_D6" /></td>
			</tr>
			<tr>
				<th>7:00</th>
				<td><span id="H07_D0" /></td>
				<td><span id="H07_D1" /></td>
				<td><span id="H07_D2" /></td>
				<td><span id="H07_D3" /></td>
				<td><span id="H07_D4" /></td>
				<td><span id="H07_D5" /></td>
				<td><span id="H07_D6" /></td>
			</tr>
			<tr>
				<th>8:00</th>
				<td><span id="H08_D0" /></td>
				<td><span id="H08_D1" /></td>
				<td><span id="H08_D2" /></td>
				<td><span id="H08_D3" /></td>
				<td><span id="H08_D4" /></td>
				<td><span id="H08_D5" /></td>
				<td><span id="H08_D6" /></td>
			</tr>
			<tr>
				<th>9:00</th>
				<td><span id="H09_D0" /></td>
				<td><span id="H09_D1" /></td>
				<td><span id="H09_D2" /></td>
				<td><span id="H09_D3" /></td>
				<td><span id="H09_D4" /></td>
				<td><span id="H09_D5" /></td>
				<td><span id="H09_D6" /></td>
			</tr>
			<tr>
				<th>10:00</th>
				<td><span id="H10_D0" /></td>
				<td><span id="H10_D1" /></td>
				<td><span id="H10_D2" /></td>
				<td><span id="H10_D3" /></td>
				<td><span id="H10_D4" /></td>
				<td><span id="H10_D5" /></td>
				<td><span id="H10_D6" /></td>
			</tr>
			<tr>
				<th>11:00</th>
				<td><span id="H11_D0" /></td>
				<td><span id="H11_D1" /></td>
				<td><span id="H11_D2" /></td>
				<td><span id="H11_D3" /></td>
				<td><span id="H11_D4" /></td>
				<td><span id="H11_D5" /></td>
				<td><span id="H11_D6" /></td>
			</tr>
			<tr>
				<th>12:00</th>
				<td><span id="H12_D0" /></td>
				<td><span id="H12_D1" /></td>
				<td><span id="H12_D2" /></td>
				<td><span id="H12_D3" /></td>
				<td><span id="H12_D4" /></td>
				<td><span id="H12_D5" /></td>
				<td><span id="H12_D6" /></td>
			</tr>
			<tr>
				<th>13:00</th>
				<td><span id="H13_D0" /></td>
				<td><span id="H13_D1" /></td>
				<td><span id="H13_D2" /></td>
				<td><span id="H13_D3" /></td>
				<td><span id="H13_D4" /></td>
				<td><span id="H13_D5" /></td>
				<td><span id="H13_D6" /></td>
			</tr>
			<tr>
				<th>14:00</th>
				<td><span id="H14_D0" /></td>
				<td><span id="H14_D1" /></td>
				<td><span id="H14_D2" /></td>
				<td><span id="H14_D3" /></td>
				<td><span id="H14_D4" /></td>
				<td><span id="H14_D5" /></td>
				<td><span id="H14_D6" /></td>
			</tr>
			<tr>
				<th>15:00</th>
				<td><span id="H15_D0" /></td>
				<td><span id="H15_D1" /></td>
				<td><span id="H15_D2" /></td>
				<td><span id="H15_D3" /></td>
				<td><span id="H15_D4" /></td>
				<td><span id="H15_D5" /></td>
				<td><span id="H15_D6" /></td>
			</tr>
			<tr>
				<th>16:00</th>
				<td><span id="H16_D0" /></td>
				<td><span id="H16_D1" /></td>
				<td><span id="H16_D2" /></td>
				<td><span id="H16_D3" /></td>
				<td><span id="H16_D4" /></td>
				<td><span id="H16_D5" /></td>
				<td><span id="H16_D6" /></td>
			</tr>
			<tr>
				<th>17:00</th>
				<td><span id="H17_D0" /></td>
				<td><span id="H17_D1" /></td>
				<td><span id="H17_D2" /></td>
				<td><span id="H17_D3" /></td>
				<td><span id="H17_D4" /></td>
				<td><span id="H17_D5" /></td>
				<td><span id="H17_D6" /></td>
			</tr>
			<tr>
				<th>18:00</th>
				<td><span id="H18_D0" /></td>
				<td><span id="H18_D1" /></td>
				<td><span id="H18_D2" /></td>
				<td><span id="H18_D3" /></td>
				<td><span id="H18_D4" /></td>
				<td><span id="H18_D5" /></td>
				<td><span id="H18_D6" /></td>
			</tr>
			<tr>
				<th>19:00</th>
				<td><span id="H19_D0" /></td>
				<td><span id="H19_D1" /></td>
				<td><span id="H19_D2" /></td>
				<td><span id="H19_D3" /></td>
				<td><span id="H19_D4" /></td>
				<td><span id="H19_D5" /></td>
				<td><span id="H19_D6" /></td>
			</tr>
			<tr>
				<th>20:00</th>
				<td><span id="H20_D0" /></td>
				<td><span id="H20_D1" /></td>
				<td><span id="H20_D2" /></td>
				<td><span id="H20_D3" /></td>
				<td><span id="H20_D4" /></td>
				<td><span id="H20_D5" /></td>
				<td><span id="H20_D6" /></td>
			</tr>
			<tr>
				<th>21:00</th>
				<td><span id="H21_D0" /></td>
				<td><span id="H21_D1" /></td>
				<td><span id="H21_D2" /></td>
				<td><span id="H21_D3" /></td>
				<td><span id="H21_D4" /></td>
				<td><span id="H21_D5" /></td>
				<td><span id="H21_D6" /></td>
			</tr>
			<tr>
				<th>22:00</th>
				<td><span id="H22_D0" /></td>
				<td><span id="H22_D1" /></td>
				<td><span id="H22_D2" /></td>
				<td><span id="H22_D3" /></td>
				<td><span id="H22_D4" /></td>
				<td><span id="H22_D5" /></td>
				<td><span id="H22_D6" /></td>
			</tr>
			<tr>
				<th>23:00</th>
				<td><span id="H23_D0" /></td>
				<td><span id="H23_D1" /></td>
				<td><span id="H23_D2" /></td>
				<td><span id="H23_D3" /></td>
				<td><span id="H23_D4" /></td>
				<td><span id="H23_D5" /></td>
				<td><span id="H23_D6" /></td>
			</tr>
		</table>	
	</div>
	</form>
</div>