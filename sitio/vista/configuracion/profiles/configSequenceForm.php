<style>
	#sortable1, #sortable2 {
		list-style-type: none;
		margin: 0;
		padding: 0;
		float: left;
		margin-right: 10px;
		background: #eee;
		padding: 5px;
		width: 400px;
		min-height: 298px;
	}

	#sortable1 li, #sortable2 li {
		margin: 0 3px 3px 3px;
		padding: 0.4em;
		padding-left: 1.5em;
		font-size: 12px;
		height: 36px;
	}

	#sortable1 li span, #sortable2 li span {
		position: absolute;
		margin-left: -1.3em;
	}

	#titleSequence {
		width: 810px;
		padding: 0.4em;
	}

	#containerSequence {
		height: 300px;
	}

</style>
<script>
	$(function( ) {
		$("#sortable1, #sortable2").sortable({
			connectWith : "ul",
			placeholder : "ui-state-highlight",
			items : "li:not(.ui-state-disabled)"
		}).disableSelection();

		$("#sequenceSave").button();
		$("#sequenceClose").button();

		$("#sequenceClose").click(function( ) {
			$('#dialogGenerator').html("");
			$('#tableProfile').parent( "div.bDiv" ).parent( "div.flexigrid" ).show();
		});

		$("#sequenceSave").click(function( ) {
			var activeArray = [];
			$('#sortable1 li').each(function( i ) {
				if ( typeof $(this).attr('title') != 'undefined' ) {
					activeArray[i] = $(this).attr('title');
				}
			});

			$.ajax({
				type : "POST",
				url : "/profiles2/changeSequence/{idprofile}",
				dataType : "json",
				data : {
					activeArray : activeArray
				},
				success : function( data ) {
					if(data.status) {
					    alert("{SAVE_OK}");
					    $('#tableProfile').parent( "div.bDiv" ).parent( "div.flexigrid" ).show();
					    $('#dialogGenerator').html("");
					} else {
					    alert("{SAVE_NOK}");
					}
				}

			});

		});
	}); 
</script>
<div id="titleSequence" class="ui-state-default">
	{SEQUENCE_OF_TESTS}
</div>
<div id="containerSequence">
	<ul id="sortable1" class="droptrue">
		<li class="ui-state-default ui-state-disabled">
			<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>{ACTIVE}
		</li>
		{activeForm}
	</ul>

	<ul id="sortable2" class="dropfalse">
		<li class="ui-state-default ui-state-disabled">
			<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>{INACTIVE}
		</li>
		{inactiveForm}
	</ul>
</div>
<div id="titleSequence" class="ui-widget-header ui-corner-all">
	<button id="sequenceSave">
		{SAVE}
	</button>
	<button id="sequenceClose">
		{CLOSE}
	</button>
</div>