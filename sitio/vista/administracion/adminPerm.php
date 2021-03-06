<style type="text/css">
	.column {
		width: 258px;
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
<script type="text/javascript" language="JavaScript">
	$(function( ) {

		var check = true;

		$(".column").sortable({
			connectWith : ".column",
			handle : ".portlet-header",
			cancel : ".portlet-toggle",
			placeholder : "portlet-placeholder ui-corner-all"
		});

		$(".portlet").addClass("ui-widget ui-widget-content ui-helper-clearfix ui-corner-all").find(".portlet-header").addClass("ui-widget-header ui-corner-all").prepend("<span class='ui-icon ui-icon-minusthick portlet-toggle'></span>");

		$(".portlet-toggle").click(function( ) {
			var icon = $(this);
			icon.toggleClass("ui-icon-minusthick ui-icon-plusthick");
			icon.closest(".portlet").find(".portlet-content").toggle();
		});
        $("#select_all").button();
		$("#select_all").on("click", function( ) {
			var checkboxes = $("#Permissions").closest('form').find(':checkbox');
			if ( check ) {
				checkboxes.prop('checked', true);
				check = false;
			} else {
				checkboxes.prop('checked', false);
				check = true;
			}
			return false;
		});

	});

</script>
<form id="Permissions" class="form_bsw">
	<div class="paneles">
		<button id="select_all">{SELECT_ALL}</button>
	</div>
	{columns}
</form>
