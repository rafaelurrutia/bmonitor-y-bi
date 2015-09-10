<?php
include_once 'calendar/classes/tc_calendar.php';

if (defined('URL_BASE_FULL')) {
	$urlBase = explode('/', URL_BASE_FULL);

	$urlBase = $urlBase[0];
	
	$urlBase1 = str_replace("baking", "bsw", $urlBase);
	$urlBase2 = str_replace("bsw", "baking", $urlBase);
	
} else {
	$urlBase = 'none';
}

if (isset($_GET['PEAK'])) {

	if (isset($_POST['PEAK1'])) {
		$PEAK_HOUR_START = $_POST['PEAK1'];
		$valid = $cmd->conexion->query("REPLACE INTO `Parametros` ( `nombre`, `descripcion`, `type`, `valor`, `visible`)
			VALUES
				( 'PEAK_HOUR_START', 'Peak hour', 'string', '$PEAK_HOUR_START', 'true')");
	}

	if (isset($_POST['PEAK2'])) {
		$PEAK_HOUR_END = $_POST['PEAK2'];
		$valid = $cmd->conexion->query("REPLACE INTO `Parametros` ( `nombre`, `descripcion`, `type`, `valor`, `visible`)
		VALUES
			( 'PEAK_HOUR_END', 'Peak hour', 'string', '$PEAK_HOUR_END', 'true')");
	}

	exit ;
}

if (isset($_GET['BACKTO'])) {
	//One time execution
	if (isset($_POST['BACKTO1'])) {
		$BACKTO1 = $_POST['BACKTO1'];
		$valid = $cmd->conexion->query("REPLACE INTO `Parametros` ( `nombre`, `descripcion`, `type`, `valor`, `visible`)
			VALUES
				( 'BACKTO_ONE_TIME_EXECUTION', 'Bacto one time execution', 'int', '$BACKTO1', 'true')");
	}

	if (isset($_POST['BACKTO2'])) {
		$BACKTO2 = $_POST['BACKTO2'];
		$valid = $cmd->conexion->query("UPDATE `bsw_bi`.`bi_server` SET `backto` = '$BACKTO2' WHERE `domain` IN ('$urlBase1','$urlBase2')");
	}

	exit ;
}

if (isset($_GET['MAIL'])) {
	
	if (isset($_POST['MAILALERT'])) {
		$MAILALERT = $_POST['MAILALERT'];
		$valid = $cmd->conexion->query("UPDATE `bsw_bi`.`bi_server` SET `mailAlert` = '$MAILALERT' WHERE `domain` IN ('$urlBase1','$urlBase2')");
	}

	exit ;
}

if (isset($_GET['TIMEZONE'])) {
	
	if (isset($_POST['TZ_OPTION'])) {
		$TZ_OPTION = $_POST['TZ_OPTION'];
		$valid = $cmd->conexion->query("UPDATE `bsw_bi`.`bi_server` SET `timezone` = '$TZ_OPTION' WHERE `domain` IN ('$urlBase1','$urlBase2')");
	}

	exit ;
}

//Get Param

$getQuery = "SELECT `backto`, `mailAlert`,`timezone` FROM  `bsw_bi`.`bi_server` WHERE  `domain` IN ('$urlBase1','$urlBase2')";
$BACKTO_DEAFULT_RESULT = $cmd->conexion->queryFetch($getQuery);

if ($BACKTO_DEAFULT_RESULT) {
	$BACKTO_DEAFULT = $BACKTO_DEAFULT_RESULT[0]['backto'];
	$MAIL_ALERT = $BACKTO_DEAFULT_RESULT[0]['mailAlert'];
	$TIMEZONE = $BACKTO_DEAFULT_RESULT[0]['timezone'];
} else {
	$BACKTO_DEAFULT = '';
	$MAIL_ALERT = '';
	$TIMEZONE = '';
}

$BACKTO_ONE_TIME_EXECUTION = $cmd->parametro->get("BACKTO_ONE_TIME_EXECUTION", '');

$PEAK_HOUR_START = $cmd->parametro->get("PEAK_HOUR_START", '16:00');
$PEAK_HOUR_END = $cmd->parametro->get("PEAK_HOUR_END", '23:00');
?>

<script type="text/javascript">
	$(document).ready(function() {
		/*
		$("#datetimepicker1").datetimepicker({
			pickDate : false
		});

		$("#datetimepicker2").datetimepicker({
			pickDate : false
		});
		*/
            		
		$("#savePeak").click(function() {
			
			var PEAK1 = $("#PEAK1").val();
				PEAK1 = PEAK1.split(':');
				PEAK1 = PEAK1[0];
			var PEAK2 = $("#PEAK2").val();   
				PEAK2 = PEAK2.split(':');
				PEAK2 = PEAK2[0];
        	if(parseInt(PEAK1) > parseInt(PEAK2)) {
        		alert("Error: Wrong range");
        		return false;
        	}
        	
			$.ajax({
				type : "POST",
				url : "/bi/index.php?route=homec&PEAK=1",
				data : $("#formPeak").serialize(),
				success : function(data) {
					//Valid
					alert("Save OK");
				}
			});
		});

		$("#saveBackto").click(function() {
			$.ajax({
				type : "POST",
				url : "/bi/index.php?route=homec&BACKTO=1",
				data : $("#formBackto").serialize(),
				success : function(data) {
					//Valid
					alert("Save OK");
				}
			});
		});
		
		$("#saveMailAlert").click(function() {
			$.ajax({
				type : "POST",
				url : "/bi/index.php?route=homec&MAIL=1",
				data : $("#formMailAlert").serialize(),
				success : function(data) {
					//Valid
					alert("Save OK");
				}
			});
		});

		$("#saveTimezone").click(function() {
			$.ajax({
				type : "POST",
				url : "/bi/index.php?route=homec&TIMEZONE=1",
				data : $("#formTimezone").serialize(),
				success : function(data) {
					//Valid
					alert("Save OK");
				}
			});
		});

	}); 
</script>
<div class="row">
	<div class="col-md-6">
		<div class="panel panel-default">
			<div class="panel-heading">
				Peak hour
			</div>
			<div class="panel-body">
				<form id="formPeak" class="form-search">
					<div class="row">
						<div class="col-md-4">
							<div class="form-group">
								<div id="datetimepicker1" class="input-group date">
									<!--<input id="PEAK1" name="PEAK1" type="datetime" value="<?php echo $PEAK_HOUR_START ?>" class="form-control">-->
									<select id="PEAK1" name="PEAK1" class="form-control">
										<option value="00" <?php echo ("00" == "$PEAK_HOUR_START") ? "selected='selected'" : ""; ?>>00:00</option>
										<option value="01" <?php echo ("01" == "$PEAK_HOUR_START") ? "selected='selected'" : ""; ?>>01:00</option>
										<option value="02" <?php echo ("02" == "$PEAK_HOUR_START") ? "selected='selected'" : ""; ?>>02:00</option>
										<option value="03" <?php echo ("03" == "$PEAK_HOUR_START") ? "selected='selected'" : ""; ?>>03:00</option>
										<option value="04" <?php echo ("04" == "$PEAK_HOUR_START") ? "selected='selected'" : ""; ?>>04:00</option>
										<option value="05" <?php echo ("05" == "$PEAK_HOUR_START") ? "selected='selected'" : ""; ?>>05:00</option>
										<option value="06" <?php echo ("06" == "$PEAK_HOUR_START") ? "selected='selected'" : ""; ?>>06:00</option>
										<option value="07" <?php echo ("07" == "$PEAK_HOUR_START") ? "selected='selected'" : ""; ?>>07:00</option>
										<option value="08" <?php echo ("08" == "$PEAK_HOUR_START") ? "selected='selected'" : ""; ?>>08:00</option>
										<option value="09" <?php echo ("09" == "$PEAK_HOUR_START") ? "selected='selected'" : ""; ?>>09:00</option>
										<option value="10" <?php echo ("10" == "$PEAK_HOUR_START") ? "selected='selected'" : ""; ?>>10:00</option>
										<option value="11" <?php echo ("11" == "$PEAK_HOUR_START") ? "selected='selected'" : ""; ?>>11:00</option>
										<option value="12" <?php echo ("12" == "$PEAK_HOUR_START") ? "selected='selected'" : ""; ?>>12:00</option>
										<option value="13" <?php echo ("13" == "$PEAK_HOUR_START") ? "selected='selected'" : ""; ?>>13:00</option>
										<option value="14" <?php echo ("14" == "$PEAK_HOUR_START") ? "selected='selected'" : ""; ?>>14:00</option>
										<option value="15" <?php echo ("15" == "$PEAK_HOUR_START") ? "selected='selected'" : ""; ?>>15:00</option>
										<option value="16" <?php echo ("16" == "$PEAK_HOUR_START") ? "selected='selected'" : ""; ?>>16:00</option>
										<option value="17" <?php echo ("17" == "$PEAK_HOUR_START") ? "selected='selected'" : ""; ?>>17:00</option>
										<option value="18" <?php echo ("18" == "$PEAK_HOUR_START") ? "selected='selected'" : ""; ?>>18:00</option>
										<option value="19" <?php echo ("19" == "$PEAK_HOUR_START") ? "selected='selected'" : ""; ?>>19:00</option>
										<option value="20" <?php echo ("20" == "$PEAK_HOUR_START") ? "selected='selected'" : ""; ?>>20:00</option>
										<option value="21" <?php echo ("21" == "$PEAK_HOUR_START") ? "selected='selected'" : ""; ?>>21:00</option>
										<option value="22" <?php echo ("22" == "$PEAK_HOUR_START") ? "selected='selected'" : ""; ?>>22:00</option>
										<option value="23" <?php echo ("23" == "$PEAK_HOUR_START") ? "selected='selected'" : ""; ?>>23:00</option>
									</select>
									<span class="input-group-btn add-on">
										<button type="button" class="btn btn-search">
											<span class="glyphicon glyphicon-time"></span>
										</button> </span>
								</div>

							</div>
						</div>

						<div class="col-md-4">
							<div class="form-group">
								<div  id="datetimepicker2" class="input-group dat">
									<!--<input id="PEAK2" name="PEAK2" type="datetime" value="<?php echo $PEAK_HOUR_END ?>" class="form-control">-->
									<select id="PEAK2" name="PEAK2" class="form-control">
										<option value="00" <?php echo ("00" == "$PEAK_HOUR_END") ? "selected='selected'" : ""; ?>>00:00</option>
										<option value="01" <?php echo ("01" == "$PEAK_HOUR_END") ? "selected='selected'" : ""; ?>>01:00</option>
										<option value="02" <?php echo ("02" == "$PEAK_HOUR_END") ? "selected='selected'" : ""; ?>>02:00</option>
										<option value="03" <?php echo ("03" == "$PEAK_HOUR_END") ? "selected='selected'" : ""; ?>>03:00</option>
										<option value="04" <?php echo ("04" == "$PEAK_HOUR_END") ? "selected='selected'" : ""; ?>>04:00</option>
										<option value="05" <?php echo ("05" == "$PEAK_HOUR_END") ? "selected='selected'" : ""; ?>>05:00</option>
										<option value="06" <?php echo ("06" == "$PEAK_HOUR_END") ? "selected='selected'" : ""; ?>>06:00</option>
										<option value="07" <?php echo ("07" == "$PEAK_HOUR_END") ? "selected='selected'" : ""; ?>>07:00</option>
										<option value="08" <?php echo ("08" == "$PEAK_HOUR_END") ? "selected='selected'" : ""; ?>>08:00</option>
										<option value="09" <?php echo ("09" == "$PEAK_HOUR_END") ? "selected='selected'" : ""; ?>>09:00</option>
										<option value="10" <?php echo ("10" == "$PEAK_HOUR_END") ? "selected='selected'" : ""; ?>>10:00</option>
										<option value="11" <?php echo ("11" == "$PEAK_HOUR_END") ? "selected='selected'" : ""; ?>>11:00</option>
										<option value="12" <?php echo ("12" == "$PEAK_HOUR_END") ? "selected='selected'" : ""; ?>>12:00</option>
										<option value="13" <?php echo ("13" == "$PEAK_HOUR_END") ? "selected='selected'" : ""; ?>>13:00</option>
										<option value="14" <?php echo ("14" == "$PEAK_HOUR_END") ? "selected='selected'" : ""; ?>>14:00</option>
										<option value="15" <?php echo ("15" == "$PEAK_HOUR_END") ? "selected='selected'" : ""; ?>>15:00</option>
										<option value="16" <?php echo ("16" == "$PEAK_HOUR_END") ? "selected='selected'" : ""; ?>>16:00</option>
										<option value="17" <?php echo ("17" == "$PEAK_HOUR_END") ? "selected='selected'" : ""; ?>>17:00</option>
										<option value="18" <?php echo ("18" == "$PEAK_HOUR_END") ? "selected='selected'" : ""; ?>>18:00</option>
										<option value="19" <?php echo ("19" == "$PEAK_HOUR_END") ? "selected='selected'" : ""; ?>>19:00</option>
										<option value="20" <?php echo ("20" == "$PEAK_HOUR_END") ? "selected='selected'" : ""; ?>>20:00</option>
										<option value="21" <?php echo ("21" == "$PEAK_HOUR_END") ? "selected='selected'" : ""; ?>>21:00</option>
										<option value="22" <?php echo ("22" == "$PEAK_HOUR_END") ? "selected='selected'" : ""; ?>>22:00</option>
										<option value="23" <?php echo ("23" == "$PEAK_HOUR_END") ? "selected='selected'" : ""; ?>>23:00</option>
									</select>
									<span class="input-group-btn add-on">
										<button type="button" class="btn btn-search">
											<span class="glyphicon glyphicon-time"></span>
										</button> </span>
								</div>
							</div>
						</div>

						<div class="col-md-2">
							<button id="savePeak" type="button" class="btn btn-default">
								Save
							</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>

	<div class="col-md-6">
		<!--<div class="panel panel-default">
			<div class="panel-heading">
				Days to process
			</div>
			<div class="panel-body">
				<form id="formBackto" class="form-search">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<div id="backToContainer1" class="input-group date">
									<input id="BACKTO1" name="BACKTO1" type="datetime" value="<?php echo $BACKTO_ONE_TIME_EXECUTION ?>" class="form-control">
									<span class="input-group-addon">One time execution</span>
								</div>

							</div>
						</div>

						<div class="col-md-4">
							<div class="form-group">
								<div  id="backToContainer2" class="input-group dat">
									<input id="BACKTO2" name="BACKTO2" type="datetime" value="<?php echo $BACKTO_DEAFULT ?>" class="form-control">
									<span class="input-group-addon">Default</span>
								</div>
							</div>
						</div>

						<div class="col-md-1">
							<button id="saveBackto" type="button" class="btn btn-default">
								Save
							</button>
						</div>
					</div>
				</form>
			</div>
		</div>-->
		
		<div class="panel panel-default">
			<div class="panel-heading">
				Setup email alerts
			</div>
			<div class="panel-body">
				<form id="formMailAlert" class="form-search">
					<div class="row">
						<div class="col-md-9">
							<div class="form-group">
								<div id="backToContainer1" class="input-group date">
									<input id="MAILALERT" name="MAILALERT" type="email" value="<?php echo $MAIL_ALERT ?>" class="form-control">
									<span class="input-group-addon">Mail</span>
								</div>

							</div>
						</div>
						<div class="col-md-2">
							<button id="saveMailAlert" type="button" class="btn btn-default">
								Save
							</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-6">
		<!--<div class="panel panel-default">
			<div class="panel-heading">
				Setup email alerts
			</div>
			<div class="panel-body">
				<form id="formMailAlert" class="form-search">
					<div class="row">
						<div class="col-md-9">
							<div class="form-group">
								<div id="backToContainer1" class="input-group date">
									<input id="MAILALERT" name="MAILALERT" type="email" value="<?php echo $MAIL_ALERT ?>" class="form-control">
									<span class="input-group-addon">Mail</span>
								</div>

							</div>
						</div>
						<div class="col-md-2">
							<button id="saveMailAlert" type="button" class="btn btn-default">
								Save
							</button>
						</div>
					</div>
				</form>
			</div>
		</div>-->
		
		<div class="panel panel-default">
			<div class="panel-heading">
				Time Zone
			</div>
			<div class="panel-body">
				<form id="formTimezone" class="form-search">
					<div class="row">
						<div class="col-md-9">
							<div class="form-group">
								<?php get_tz_options($TIMEZONE); ?>
							</div>
						</div>
						<div class="col-md-2">
							<button id="saveTimezone" type="button" class="btn btn-default">
								Save
							</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<!--<div class="panel panel-default">
			<div class="panel-heading">
				Time Zone
			</div>
			<div class="panel-body">
				<form id="formTimezone" class="form-search">
					<div class="row">
						<div class="col-md-9">
							<div class="form-group">
								<?php get_tz_options($TIMEZONE); ?>
							</div>
						</div>
						<div class="col-md-2">
							<button id="saveTimezone" type="button" class="btn btn-default">
								Save
							</button>
						</div>
					</div>
				</form>
			</div>
		</div>-->
	</div>
</div>