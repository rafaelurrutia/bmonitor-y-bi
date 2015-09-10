<script type="text/javascript">
	$("#tableHistory").flexigrid({
		url: '{url_base}history/getTable',
		dataType: 'json',
		title: 'Item: {title}',
		colModel : [
			{display: '{DATE}', name : 'clock', classTH : 'pum' , width : 200, sortable : true, align: 'left'},
			{display: '{VALUE}', name : 'value', width : 200, sortable : true, align: 'left'}
			],
		useRp: false,
		rp: 50,
		showTableToggleBtn: false,
		resizable: true,
		onSubmit : function(){
        	$('#tableHistory').flexOptions({params: [{name:'idItem', value:'{idItem}'},{name:'host', value:'{host}'},{name:'type', value:'{type}'}]});
       		return true;
    	},
        usepager: true,
		singleSelect: true
	});
</script>

<div class="tables">
	<table id="tableHistory"></table>
</div>