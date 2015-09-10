jQuery(function( $ )
{
	modalQoeLocationNew = $('#modalQoeLocationNew');
	modalQoeLocationNewUrl = '/qoe/dbLocation';
	modalQoeLocationNewForm = new $.formVar( 'modalFormNewQoeLocation' );

	modalQoeLocationEdit = $('#modalQoeLocationEdit');
	modalQoeLocationEditUrl = '/qoe/dbLocation';
	modalQoeLocationEditUrlForm = '/qoe/getLocationForm';
	modalQoeLocationEditForm = new $.formVar( 'modalFormEditQoeLocation' );
		
	modalQoeLocationDelete = $('#modalQoeLocationDelete');
	modalQoeLocationDeleteUrl = '/qoe/deleteLocation';	

	$.locationSelect(modalQoeLocationNewForm);
		
	var buttonModalQoeLocationNew = {};

	buttonModalQoeLocationNew[language.accept] = function(evt) {
		var buttonDomElement = evt.target;
		
		var bValid = true;

		bValid = bValid && modalQoeLocationNewForm.checkLength('city', "auto", 3, 200,language.code);

		if ( bValid ) {

			if($(buttonDomElement).attr('disabled')) {
				return true;
			} else {
				$(buttonDomElement).attr('disabled', true).addClass('ui-state-disabled');
			}
			
			$('#loading').show();
			$('#imgLoading').show();

			var request = $.ajax({
				type : "POST",
				url : modalQoeLocationNewUrl,
				data : modalQoeLocationNewForm.form.serialize(),
				dataType : "json"
			});

			request.done(function( msg ) {
				if ( msg.status ) {
					modalQoeLocationNew.dialog("close");
					tableQoeLocation.flexReload();
				} else {
					modalQoeLocationNewForm.tipsBox(msg.error, 'alert');
				}
			});

			request.fail(function( jqXHR, textStatus ) {
				modalQoeLocationNewForm.errorSystem(language.code);
			});

			request.always(function() {
				$(buttonDomElement).attr('disabled', false).removeClass('ui-state-disabled');
			});
					
			$('#loading').hide();
			$('#imgLoading').hide();
		}
	}

	buttonModalQoeLocationNew[language.cancel] = function( ) {
		$(this).dialog("close");
	};
	
	modalQoeLocationNew.dialog({
		autoOpen : false,
		resizable : true,
		width : 650,
		modal : true,
		zIndex : 20000,
		closeOnEscape : true,
		draggable : true,
		open : function( ) {
			$(this).find('.ui-dialog-titlebar-close').blur();
		},
		buttons : buttonModalQoeLocationNew,
		close : function( ) {
			modalQoeLocationNewForm.get('continent').val(0);
			modalQoeLocationNewForm.get('country').html('<option selected value="0">' + language.SELECT + '</option>');
			modalQoeLocationNewForm.get('state').html('<option selected value="0">' + language.SELECT + '</option>');		
			modalQoeLocationNewForm.get('city').val("");
			modalQoeLocationNewForm.get('latitude').val("");
			modalQoeLocationNewForm.get('longitude').val("");
			modalQoeLocationNewForm.get('minTest').val("");
		}
	});

	var buttonModalQoeLocationDelete = {};

	buttonModalQoeLocationDelete[language.delete] = function(evt) {
		var select = new Array( );
		var buttonDomElement = evt.target;
		count = 0;
		$('.trSelected', tableQoeLocation).each(function( ) {
			var id = $(this).attr('id').substr(3);
			if ( $.isNumeric(id) ) {
				select[count] = id;
				count = count + 1;
			}
		});

		$('#loading').show();
		$('#imgLoading').show();

		if($(buttonDomElement).attr('disabled')) {
			return true;
		} else {
			$(buttonDomElement).attr('disabled', true).addClass('ui-state-disabled');
		}
			
		var request = $.ajax({
			type : "POST",
			url : modalQoeLocationDeleteUrl,
			data : {
				id : select
			},	
			dataType : "json"
		});

		request.done(function( data ) {
			if ( data.status ) {
				modalQoeLocationDelete.dialog("close");
				tableQoeLocation.flexReload();
			} else {
				alert(data.error);
			}
		});
			
		request.fail(function( jqXHR, textStatus ) {
			alert("Error connecting to server");			
		});
		
		request.always(function() {
			$(buttonDomElement).attr('disabled', false).removeClass('ui-state-disabled');
		});
			
		$('#loading').hide();
		$('#imgLoading').hide();		
	}

	buttonModalQoeLocationDelete[language.cancel] = function( ) {
		$(this).dialog("close");
	};
		
	modalQoeLocationDelete.dialog({
		autoOpen : false,
		resizable : true,
		height : 'auto',
		width : 600,
		modal : true,
		zIndex : 20000,
		closeOnEscape : true,
		draggable : true,
		open : function( ) {
			$(this).find('.ui-dialog-titlebar-close').blur();
			var countSelect = 1;
			$("p#selected", modalQoeLocationDelete).html(language.SELECTION+":");
			$('.trSelected', tableQoeLocation).each(function( ) {
				var name = $(this).children('td:nth-child(2)').text();
				$("p#selected", modalQoeLocationDelete).append("</br>" + countSelect + " : " + name);
				countSelect = countSelect + 1;
			});
		},
		buttons : buttonModalQoeLocationDelete
	});
	
	$.functionQoeLocationEdit = function(idLocation) {

		modalQoeLocationEdit.load(modalQoeLocationEditUrlForm, {
			idLocation : idLocation
		}, function(responseText, textStatus, XMLHttpRequest) {
			modalQoeLocationEditForm.refresh();
			modalQoeLocationEdit.removeClass('loading');		
			modalQoeLocationEdit.dialog("open");
		});
		
		var buttonModalQoeLocationEdit = {};

		buttonModalQoeLocationEdit[language.accept] = function(evt) {
			var buttonDomElement = evt.target;
			
			var bValid = true;
	
			bValid = bValid && modalQoeLocationEditForm.checkLength('city', "auto", 3, 200,language.code);
	
			if ( bValid ) {
	
				if($(buttonDomElement).attr('disabled')) {
					return true;
				} else {
					$(buttonDomElement).attr('disabled', true).addClass('ui-state-disabled');
				}
				
				$('#loading').show();
				$('#imgLoading').show();
	
				var request = $.ajax({
					type : "POST",
					url : modalQoeLocationEditUrl+'/'+idLocation,
					data : modalQoeLocationEditForm.form.serialize(),
					dataType : "json"
				});
	
				request.done(function( msg ) {
					if ( msg.status ) {
						modalQoeLocationEdit.dialog("close");
						tableQoeLocation.flexReload();
					} else {
						modalQoeLocationEditForm.tipsBox(msg.error, 'alert');
					}
				});
	
				request.fail(function( jqXHR, textStatus ) {
					modalQoeLocationEditForm.errorSystem(language.code);
				});
	
				request.always(function() {
					$(buttonDomElement).attr('disabled', false).removeClass('ui-state-disabled');
				});
						
				$('#loading').hide();
				$('#imgLoading').hide();
			}
		}
	
		buttonModalQoeLocationEdit[language.cancel] = function( ) {
			$(this).dialog("close");
		};
	
		modalQoeLocationEdit.dialog({
			autoOpen : false,
			resizable : true,
			height : 'auto',
			width : 600,
			modal : true,
			zIndex : 20000,
			closeOnEscape : true,
			draggable : true,
			open : function( ) {
				$(this).find('.ui-dialog-titlebar-close').blur();
			},
			buttons : buttonModalQoeLocationEdit
		});
			
	}
});