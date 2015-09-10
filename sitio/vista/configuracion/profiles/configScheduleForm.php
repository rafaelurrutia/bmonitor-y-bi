<script type="text/javascript">
	$(function( ) {

		$("#ScheduleSave").button();
		$("#ScheduleClose").button();

		$("#ScheduleClose").click(function( ) {
			$('#dialogGenerator').html("");
			$('#tableProfile').parent("div.bDiv").parent("div.flexigrid").show();
		});

        formSchedule = new $.formVar( 'formSchedule' );
        
		$("#ScheduleSave").click(function( ) {
			var request = $.ajax({
				type : "POST",
				url : "/profiles2/changeSchedule/{idprofile}",
				dataType : "json",
				data : formSchedule.form.serialize()
			});

            request.done(function( data ) {
                if ( data.status ) {
                    alert("{SAVE_OK}");
                    $('#tableProfile').parent("div.bDiv").parent("div.flexigrid").show();
                    $('#dialogGenerator').html("");
                } else {
                    alert("{SAVE_NOK}");
                }
            });
		});

		$.widget("ui.timespinner", $.ui.spinner, {
		    widgetEventPrefix: "spin",
			options : {
				max : '60',
				min : '0',
				step: '10'
			},

			_create : function( ) {

				this._super();

				this.uiSpinner.addClass('ui-spinner-alpha').find('.ui-spinner-up').addClass('ui-corner-tl').end().find('.ui-spinner-down').addClass('ui-corner-bl');

			}
		});

		$("#hourStart").timespinner({ 
		  max: 23, 
		  min: 0,
		  step: 1
		});
		$("#secondStart").timespinner();
		
        $("#hourEnd").timespinner({ 
          max: 23, 
          min: 0,
          step: 1
        });
        $("#secondEnd").timespinner();
	}); 
</script>
<style type="text/css">
	#titleSchedule {
		width: 400px;
		padding: 0.4em;
	}

	#containerSchedule {
		height: 160px;
		padding-top: 10px;
		padding-left: 100px;
	}

	.ui-spinner-alpha .ui-spinner-input {
		margin: 0;
		margin-top: 10px;
		margin-bottom: 10px;
		text-align: center;
	}

	.ui-spinner-alpha .ui-spinner-button {
		height: 10px;
		left: 0px;
		width: 100%;
	}

	.ui-spinner-alpha a.ui-spinner-button {
		border: none;
	}

	.ui-spinner-alpha .ui-icon {
		margin-left: -7px;
		top: 5px;
		left: 50%;
	}

</style>
<div id="titleSchedule" class="ui-state-default">
	{PLANNIN_TIME_TESTING_SET}
</div>
<div id="containerSchedule" class="paneles">
	<form id="formSchedule">
		<fieldset>
			<label style="width: 80px;" for="hourStart">{TIME_START}:</label>
			<input size="5" id="hourStart" name="hourStart" value="{hourStart}">
			<input size="5" id="secondStart" name="secondStart" value="{secondStart}">
		</fieldset>
        <fieldset style="margin-top: 10px">
            <label style="width: 80px;" for="hourEnd">{TIME_END}:</label>
            <input size="5" id="hourEnd" name="hourEnd" value="{hourEnd}">
            <input size="5" id="secondEnd" name="secondEnd" value="{secondEnd}">
        </fieldset>
		<fieldset style="margin-top: 20px">
			{weekName}
		</fieldset>
	</form>
</div>
<div id="titleSchedule" class="ui-widget-header ui-corner-all">
	<button id="ScheduleSave">
		{SAVE}
	</button>
	<button id="ScheduleClose">
		{CLOSE}
	</button>
</div>