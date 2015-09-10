jQuery(function( $ )
{
	modalQoeConfigNew = $('#modalQoeConfigNew');
	modalQoeConfigNewUrl = '/qoe/threshold/add';
	modalQoeConfigNewForm = new $.formVar( 'modalQoeConfigForm' );

	modalQoeConfigEdit = $('#modalQoeConfigEdit');
	modalQoeConfigEditUrl = '/qoe/threshold/edit';
	modalQoeConfigEditUrlForm = '/qoe/threshold/form';
	modalQoeConfigEditForm = new $.formVar( 'modalQoeConfigFormEdit' );
		
	modalQoeConfigDelete = $('#modalQoeConfigDelete');
	modalQoeConfigUrl = '/qoe/threshold/delete';

	percetilTypeChangeUrl = '/qoe/percetilTypeChange';
	
	percetilNumberChangeUrl = '/qoe/percetilNumberChange';
	
	$("#radioPercentil").buttonset();

	var numberPercentil = $("#percentilNew").spinner({
      min: 1,
      max: 100,
      start: 1,
    });

	var addPercentil = $("#addPercentil").button();

	var percentilActive = $("#percentilActive button").button({
		icons : {
			secondary : "ui-icon-close"
		}
	});

	$("#qoeReport input", modalQoeConfigNewForm.form).switchButton();
	$("#qoeAlert input", modalQoeConfigNewForm.form).switchButton();

		
	$('#addPercentil').on("click", function(event){
		percentil = $("#percentilNew").val();
		if($.isNumeric(percentil) == false) {
			return false;
		} else {
			if(percentil < 1 || percentil > 100){
				return false;
			}
		}
		
		$.ajax({
			type : "POST",
			url : percetilNumberChangeUrl+'/add',
			data : {
				percetilID : percentil
			},
			dataType : "json",
			success : function( data )
			{
				if ( data.status ) {
					$("#percentilActive").append("<button id='"+percentil+"'>P"+percentil+"</button>");
					var percentilActive = $("#percentilActive button").button({
						icons : {
							secondary : "ui-icon-close"
						}
					});
				} else {
					return false;
					      
				}
			},
			error : function( )
			{
				return false;
			}
		});
	});
	
	$("#percentilActive").on("click", "button", function( )
	{
		percetilSelected = $(this).closest("button");
		percetilID = percetilSelected.attr('id');

		$.ajax({
			type : "POST",
			url : percetilNumberChangeUrl+'/delete',
			data : {
				percetilID : percetilID
			},
			dataType : "json",
			success : function( data )
			{
				if ( data.status ) {
					percetilSelected.remove();
				} else {
					return false;      
				}
			},
			error : function( )
			{
				return false;
			}
		});
	});

	$("button[name='editThreshold']").on("click", function( ){
		idThreshold = $(this).attr('id');

		console.log(idThreshold);
	});
		
	$('#radio1').on("change", function(event){
		percetilChangeType(0);
    	return false;
	});

	$('#radio2').on("change", function(event){
		percetilChangeType(1);
    	return false;    	
	});
	
	function percetilChangeType(percetilChecked) {
		$.ajax({
			type : "POST",
			url : percetilTypeChangeUrl,
			data : {
				percetilChecked : percetilChecked
			},
			dataType : "json",
			success : function( data )
			{
				if ( data.status ) {
					return true;
				} else {
					return false;
					      
				}
			},
			error : function( )
			{
				return false;
			}
		});
					
  	};

	$.changethresholdTypeForm = function(form,reset) {
		thresholdType = form.get('thresholdType').val();
		if(thresholdType == 0) {
			if(reset == true) {			
				form.get('nominal').val("").attr("placeholder", "number").effect("highlight", {}, 3000);
				form.get('warning').val("").attr("placeholder", "number").effect("highlight", {}, 3000);
				form.get('critical').val("").attr("placeholder", "number").effect("highlight", {}, 3000);
			} else {
				form.get('nominal').attr("placeholder", "number").effect("highlight", {}, 3000);
				form.get('warning').attr("placeholder", "number").effect("highlight", {}, 3000);
				form.get('critical').attr("placeholder", "number").effect("highlight", {}, 3000);
			}
		} else if (thresholdType == 1) {
			form.get('nominal').hide();
			form.get('nominal').val("-1");
			if(reset == true) {	
				form.get('warning').val("").attr("placeholder", "percentage").effect("highlight", {}, 3000);
				form.get('critical').val("").attr("placeholder", "percentage").effect("highlight", {}, 3000);
			}
		}  else if (thresholdType == 2) {
			form.get('nominal').hide();
			form.get('nominal').val("-2");
			if(reset == true) {	
				form.get('warning').val("").attr("placeholder", "percentage").effect("highlight", {}, 3000);
				form.get('critical').val("").attr("placeholder", "percentage").effect("highlight", {}, 3000);
			}
		}	
	}
	
	modalQoeConfigNewForm.get('groupid').on("change", function(event){
		refreshGroup(modalQoeConfigNewForm);
		
	});
	
	function refreshGroup(form) {
		$.ajax({
			type : "POST",
			url : "/qoe/getConfigItems/"+form.get('groupid').val(),
			dataType : "json",
			async : false,
			success : function( data )
			{
				if ( data.status ) {
					form.get('item').html(data.result);
				} else {
					return false;
					      
				}
			},
			error : function( )
			{
				alert("Error");
			}
		});		
	}

	modalQoeConfigNewForm.get('thresholdType').on("change", function(event){
		$.changethresholdTypeForm(modalQoeConfigNewForm,true);
	});
	var fmFilterConfigQoe = new $.formVar( 'fmFilterConfigQoe' );
	
	fmFilterConfigQoe.get('groupid').on("change", function(event){
		$("#qoeMonitorsReportTable").flexReload();
	});
	
	$("#qoeMonitorsReportTable").flexigrid({
		url : '/qoe/getThreshold',
		title : language.THRESHOLD,
		dataType : 'json',
		colModel : [{
			display : 'ID',
			name : 'id_item',
			width : 30,
			sortable : true,
			align : 'center'
		}, {
			display : language.MONITOR,
			name : 'descriptionLong',
			width : 363,
			sortable : true,
			align : 'left'
		}, {
			display : language.nominal,
			name : 'nominal',
			width : 100,
			sortable : true,
			align : 'center'
		}, {
			display : language.warning,
			name : 'warning',
			width : 70,
			sortable : true,
			align : 'center'
		}, {
            display : language.critical,
            name : 'critical',
            width : 70,
            sortable : true,
            align : 'center'
        },/* {
            display : 'Status',
            name : 'status',
            width : 70,
            sortable : false,
            align : 'center'
        },*/ {
            display : language.options,
            name : 'option',
            width : 70,
            sortable : false,
            align : 'center'
        }],
		buttons : [{
			name : language.new,
			bclass : 'add',
			onpress : buttonSelect
		}, {
			name : language.delete,
			bclass : 'delete',
			onpress : buttonSelect
		}, {
			separator : true
		}],
		usepager : true,
		useRp : true,
		rp : 40,
		sortname : "id",
		sortorder : "asc",
		showTableToggleBtn : true,
		resizable : true,
		onSubmit : function() {
			$("#qoeMonitorsReportTable").flexOptions({
                params : [].concat(fmFilterConfigQoe.form.serializeArray())
			});
			return true;
		},
		onSuccess : function() {
			$("#qoeMonitorsReportTable button").button();
			$("button[name='editThreshold']").on("click", function( ){
				idThreshold = $(this).attr('id');
				$.functionQoeConfigEdit(idThreshold);
			});
		}
	});

	function buttonSelect( com, grid )
	{
		if ( com == language.new ) {
			$("#modalQoeConfigNew").dialog("open");
		} else if ( com == language.delete ) {
			lengthSelect = $('.trSelected', grid).length;
			if ( lengthSelect > 0 ) {
				$("#modalQoeConfigDelete").dialog("open");
			}
		}
	}


	modalQoeConfigNew.dialog({
		autoOpen : false,
		resizable : false,
		height : 'auto',
		width : 600,
		modal : false,
		zIndex : 20000,
		closeOnEscape : true,
		draggable : true,
		open : function( )
		{
			$(this).find('.ui-dialog-titlebar-close').blur();
		},
		buttons : {
			"Accept" : function( )
			{
				var bValid = true;

				modalQoeConfigNewForm.refresh();
				
				bValid = bValid && modalQoeConfigNewForm.checkLength('nominal', "auto", 1, 200);
				bValid = bValid && modalQoeConfigNewForm.checkLength('warning', "auto", 1, 200);
				bValid = bValid && modalQoeConfigNewForm.checkLength('critical', "auto", 1, 200);

				if ( bValid ) {

					jQuery('#loading').show();
					jQuery('#imgLoading').show();

					$.ajax({
						type : "POST",
						url : modalQoeConfigNewUrl,
						data : modalQoeConfigNewForm.form.serialize(),
						dataType : "json",
						success : function( data )
						{
							if ( data.status ) {
								modalQoeConfigNew.dialog("close");
								$("#qoeMonitorsReportTable").flexReload();
							} else {
								modalQoeConfigNewForm.tipsBox(data.error , "alert");
							}
						},
						error : function( )
						{
							modalQoeConfigNewForm.tipsBox("Error connecting to server" , "alert");
						}

					});

					jQuery('#loading').hide();
					jQuery('#imgLoading').hide();

				}
			},
			Cancel : function( )
			{
				$(this).dialog("close");
			}

		},
		close : function( )
		{
			
		}

	});

	modalQoeConfigDelete.dialog({
		autoOpen : false,
		resizable : true,
		height : 'auto',
		width : 600,
		modal : true,
		zIndex : 20000,
		closeOnEscape : true,
		draggable : true,
		open : function() {
			$(this).find('.ui-dialog-titlebar-close').blur();
			var countSelect = 1;
			$("p#selected", modalQoeConfigDelete).html("Selected:");
			$('.trSelected', $("#qoeMonitorsReportTable")).each(function() {
				var name = $(this).children('td:nth-child(2)').text();
				$("p#selected", modalQoeConfigDelete).append("</br>" + countSelect + " : " + name);
				countSelect = countSelect + 1;
			});
		},
		buttons : {
			"Delete" : function() {

				var select = new Array();
				count = 0;
				$('.trSelected', $("#qoeMonitorsReportTable")).each(function() {
					var id = $(this).attr('id').substr(3);
					if ($.isNumeric(id)) {
						select[count] = id;
						count = count + 1;
					}
				});

				$('#loading').show();
				$('#imgLoading').show();

				$.ajax({
					type : "POST",
					url : modalQoeConfigUrl,
					data : {
						idThreshold : select
					},
					dataType : "json",
					success : function(data) {
						if (data.status) {
							modalQoeConfigDelete.dialog("close");
							$("#qoeMonitorsReportTable").flexReload();
						} else {
							modalQoeConfigNewForm.tipsBox(data.error , "alert");
						}
					},
					error : function() {
						modalQoeConfigNewForm.tipsBox("Error connecting to server" , "alert");
					}
				});

				$('#loading').hide();
				$('#imgLoading').hide();

			},
			Cancel : function() {
				$(this).dialog("close");
			}
		},
		close : function() {

		}
	});
	
	function statusThreshold( idThreshold, status ) {
		//console.log(idThreshold);
	}
		
	$.functionQoeConfigEdit = function(idThreshold) {
		modalQoeConfigEdit.load(modalQoeConfigEditUrlForm, {
			idThreshold : idThreshold
		}, function(responseText, textStatus, XMLHttpRequest) {
			modalQoeConfigEditForm.refresh();
			modalQoeConfigEdit.removeClass('loading');
			$("#qoeAlert input", modalQoeConfigEditForm.form).switchButton();
			$("#qoeReport input", modalQoeConfigEditForm.form).switchButton();
			
			$.changethresholdTypeForm(modalQoeConfigEditForm,false);

			modalQoeConfigEditForm.get('thresholdType').on("change", function(event){
				$.changethresholdTypeForm(modalQoeConfigEditForm,false);
			});
			
			modalQoeConfigEditForm.get('groupid').on("change", function(event){
				refreshGroup(modalQoeConfigEditForm);	
			});
			
			modalQoeConfigEdit.dialog("open");
		});
		
		modalQoeConfigEdit.dialog({
			autoOpen : false,
			resizable : true,
			height : 'auto',
			width : 600,
			modal : true,
			zIndex : 20000,
			closeOnEscape : true,
			draggable : true,
			open : function( )
			{
				$(this).find('.ui-dialog-titlebar-close').blur();
			},
			buttons : {
				"Accept" : function( )
				{
					var bValid = true;
	
					modalQoeConfigEditForm.refresh();
					
					bValid = bValid && modalQoeConfigEditForm.checkLength('nominal', "auto", 1, 200);
					bValid = bValid && modalQoeConfigEditForm.checkLength('warning', "auto", 1, 200);
					bValid = bValid && modalQoeConfigEditForm.checkLength('critical', "auto", 1, 200);
	
					if ( bValid ) {
	
						jQuery('#loading').show();
						jQuery('#imgLoading').show();
	
						$.ajax({
							type : "POST",
							url : modalQoeConfigEditUrl,
							data : modalQoeConfigEditForm.form.serialize() + "&idThreshold=" + idThreshold,
							dataType : "json",
							success : function( data )
							{
								if ( data.status ) {
									modalQoeConfigEdit.dialog("close");
									$("#qoeMonitorsReportTable").flexReload();
								} else {
									modalQoeConfigEditForm.tipsBox(data.error , "alert");
								}
							},
							error : function( )
							{
								modalQoeConfigEditForm.tipsBox("Error connecting to server" , "alert");
							}
	
						});
	
						jQuery('#loading').hide();
						jQuery('#imgLoading').hide();
	
					}
				},
				Cancel : function( )
				{
					$(this).dialog("close");
				}
	
			},
			close : function( )
			{
				
			}
	
		});
	}
}); 