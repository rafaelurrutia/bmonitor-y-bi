<script type="text/javascript">
	$(function() {

		$("#qoeThresholdTable").flexigrid({
			url : '{url_base}qoe/getThreshold',
			title : 'Threshold',
			dataType : 'json',
			colModel : [{
				display : 'ID',
				name : 'idThreshold',
				width : 30,
				sortable : true,
				align : 'center'
			}, {
				display : '{MONITOR}',
				name : 'item',
				width : 300,
				sortable : true,
				align : 'left'
			}, {
				display : '{NOMINAL}',
				name : 'nominal',
				width : 100,
				sortable : true,
				align : 'center'
			}, {
				display : 'Warning',
				name : 'warning',
				width : 70,
				sortable : true,
				align : 'center'
			}, {
                display : 'Critical',
                name : 'critical',
                width : 70,
                sortable : true,
                align : 'center'
            }],
			buttons : [{
				name : '{NEW}',
				bclass : 'add',
				onpress : buttonSelect
			},{
				name : '{DELETE}',
				bclass : 'delete',
				onpress : buttonSelect
			}, {
				separator : true
			}],
			usepager : true,
			useRp : true,
			rp : 40,
			sortname : "idqos",
			sortorder : "asc",
			showTableToggleBtn : true,
			resizable : true
		});

		function buttonSelect(com, grid) {
			if (com == '{NEW}') {
				$("#modal_user_new").dialog("open");
			} else if (com == '{DELETE}') {
				lengthSelect = $('.trSelected', grid).length;
				if (lengthSelect > 0) {
					$("#modal_user_delete").dialog("open");
				}
			}
		}

	}); 
</script>
<style type="text/css">
    .containerper {
        width: 100%;
        height: 66px;
        background-color: #eeffee;
    }

    div.flexigrid {

        width: 100%;
    }

    .title {
        width: 100%;
        text-align: center;
        float: left;
    }
</style>
<div style="float: left;" id="qoeThresholdTable">
    none
</div>