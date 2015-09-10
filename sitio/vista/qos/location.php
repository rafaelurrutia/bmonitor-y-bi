<script type="text/javascript">
	var tableQoeLocation = $("#tableQoeLocation");

	fmFilterLocationQoe = new $.formVar( 'fmFilterLocationQoe' );
		
	tableQoeLocation.flexigrid({
		url : '{url_base}qoe/getTableLocation',
		title : '{LOCATIONS}',
		dataType : 'json',
		colModel : [{
			display : 'ID',
			name : 'idLocation',
			width : '50',
			sortable : true,
			align : 'center'
		}, {
			display : '{STATE_PROVINCE}',
			name : 'idState',
			width : '190',
			sortable : true,
			align : 'center'
		}, {
			display : '{MUNICIPALITY}',
			name : 'city',
			width : '100',
			sortable : true,
			align : 'center'
		}, {
			display : '{OPTIONS}',
			name : 'option',
			width : '80',
			sortable : false,
			align : 'center'
		}],
		usepager : true,
		useRp : true,
		rp : 20,
		height : 'auto',
		striped : false,
		sortname : "idLocation",
		sortorder : "asc",
		showTableToggleBtn : true,
        buttons : [{
            name : "{NEW}",
            bclass : 'new',
            onpress : buttonLocationQoe
        },{
            name : "{DELETE}",
            bclass : 'delete',
            onpress : buttonLocationQoe
        }],
		resizable : true,
		onSuccess : function() {
			$("#tableQoeLocation button").button().on("click", function( ){
				idLocation = $(this).attr('id');
				$.functionQoeLocationEdit(idLocation);
			});
		},
		onSubmit : function( ) {
			tableQoeLocation.flexOptions({
				params : [{
					name : 'callId',
					value : 'idLocation'
				}].concat(fmFilterLocationQoe.form.serializeArray())
			});
			return true;
		}
	});

    function buttonLocationQoe(com, grid) {
        if (com == '{NEW}') {
            $( "#modalQoeLocationNew" ).dialog( "open" );
        } else if (com == '{DELETE}') {
            lengthSelect = $('.trSelected', grid).length;
            if(lengthSelect > 0) {
                $( "#modalQoeLocationDelete" ).dialog( "open" );
            }
        }
    }
    	
	fmFilterLocationQoe.get("city").keyup(function( ) {
		tableQoeLocation.flexReload();
	});
</script>
<script type="text/javascript" src="{url_base}sitio/js/view.qoe_location.js"></script>
<div id="modalQoeLocationNew" title="{NEW}">{qoeLocationNewForm}</div>
<div id="modalQoeLocationEdit" title="{EDIT_REGISTRY}"></div>
<div id="modalQoeLocationDelete" title="{DELETE_ITEM}">
	<p>
		<span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>{MSG_DELETE}
	</p>
	<p style="float:left; margin:4px 0px 0px 0px;" id="selected">
		{SELECTED}:
	</p>
</div>
<form id="fmFilterLocationQoe">
	<div class="paneles">
		<fieldset>
			<div id="row" class="none">
				<label for="city" accesskey="m">{CITY}</label>
				<input type="text" id="city" name="city" value="">
			</div>
		</fieldset>
	</div>
</form>
<div style="float: left;" id="tableQoeLocation">
	No data
</div>