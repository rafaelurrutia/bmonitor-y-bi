jQuery(function( $ ) {

	modalFeatureNew = $('#modalFeatureNew');
	modalFeatureNewUrl = '/admin/createFeature';
	modalFeatureNewForm = new $.formVar( 'formFeatureNew' );

	modalFeatureDelete = $('#modalFeatureDelete');
	modalFeatureDeleteUrl = '/admin/deleteFeature';

	var buttonModalFeatureNew = {};

	buttonModalFeatureNew[language.accept] = function(evt) {
		var buttonDomElement = evt.target;
		
		var bValid = true;

		bValid = bValid && modalProfileNewForm.checkLength('feature', "auto", 3, 200,language.code);

		bValid = bValid && modalProfileNewForm.checkSelect('display', "auto", 0,language.code);

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
				url : modalProfileNewUrl,
				data : modalProfileNewForm.form.serialize(),
				dataType : "json"
			});

			request.done(function( msg ) {
				if ( msg.status ) {
					modalProfileNew.dialog("close");
					tableProfile.flexReload();
				} else {
					modalProfileNewForm.tipsBox(msg.error, 'alert');
				}
			});

			request.fail(function( jqXHR, textStatus ) {
				modalProfileNewForm.errorSystem(language.code);
			});

			request.always(function() {
				$(buttonDomElement).attr('disabled', false).removeClass('ui-state-disabled');
			});
					
			$('#loading').hide();
			$('#imgLoading').hide();
		}
	}

	buttonModalFeatureNew[language.cancel] = function( ) {
		$(this).dialog("close");
	};
		
	modalFeatureNew.dialog({
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
		buttons : buttonModalFeatureNew,
		close : function( ) {
			modalProfileNewForm.get('feature').val("");
			modalProfileNewForm.get('display').val("");
		}
	});
	

	var buttonModalFeatureDelete = {};

	buttonModalFeatureDelete[language.delete] = function(evt) {
		var select = new Array( );
		var buttonDomElement = evt.target;
		count = 0;
		$('.trSelected', tableFeature).each(function( ) {
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
			url : modalFeatureDeleteUrl,
			data : {
				id : select
			},	
			dataType : "json"
		});

		request.done(function( data ) {
			if ( data.status ) {
				modalFeatureDelete.dialog("close");
				tableFeature.flexReload();
			} else {
				alert(data.error);
			}
		});
			
		request.fail(function( jqXHR, textStatus ) {
			alert("Error connecting to server");			
		});
		
		request.always(function() {
			$(buttonDomElement).attr('disabled', false).removeClass('ui-state-disabled');
			$('#loading').hide();
			$('#imgLoading').hide();
		});
	}

	buttonModalFeatureDelete[language.cancel] = function( ) {
		$(this).dialog("close");
	};
		
	modalFeatureDelete.dialog({
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
			$("p#selected", modalFeatureDelete).html(language.SELECTION+":");
			$('.trSelected', tableFeature).each(function( ) {
				var name = $(this).children('td:nth-child(3)').text();
				$("p#selected", modalFeatureDelete).append("</br>" + countSelect + " : " + name);
				countSelect = countSelect + 1;
			});
		},
		buttons : buttonModalFeatureDelete
	});


		
});