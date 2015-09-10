<style>
	#sortable1, #sortable2 {
		list-style-type: none;
		margin: 0;
		padding: 0;
		float: left;
		margin-right: 10px;
		background: #eee;
		padding: 5px;
		width: 143px;
	}
	#sortable1 li, #sortable2 li {
		margin: 5px;
		padding: 5px;
		font-size: 1.2em;
		width: 120px;
	}
</style>
<script>
	$(function( ) {
		$("ul.droptrue").sortable({
			connectWith : "ul"
		});

		$("ul.dropfalse").sortable({
			connectWith : "ul",
			dropOnEmpty : false
		});

		$("#sortable1, #sortable2").disableSelection();
	}); 
</script>
<ul id="sortable1" class="droptrue">
	<li class="ui-state-default">
		<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>Item 5
	</li>
	<li class="ui-state-default">
		<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>Item 6
	</li>
	<li class="ui-state-default">
		<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>Item 7
	</li>
	<li class="ui-state-default">
		<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>Item 8
	</li>
	<li class="ui-state-default">
		<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>Item 9
	</li>
</ul>

<ul id="sortable2" class="dropfalse">
	<li class="ui-state-default">
		<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>Item 1
	</li>
	<li class="ui-state-default">
		<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>Item 2
	</li>
	<li class="ui-state-default">
		<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>Item 3
	</li>
	<li class="ui-state-default">
		<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>Item 4
	</li>
</ul>