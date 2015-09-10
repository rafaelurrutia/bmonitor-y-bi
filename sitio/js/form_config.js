jQuery(function( $ ) {

	$.locationSelect = function(form) {
		form.get("continent").change(function( ) {

			form.get("country").html('<option selected value="0">' + language.SELECT + '</option>');
			form.get("state").html('<option selected value="0">' + language.SELECT + '</option>');
			form.get("region").html('<option selected value="0">' + language.SELECT + '</option>');
			form.get("city").html('<option selected value="0">' + language.SELECT + '</option>');
			
			if ( $(this).val()	 != 0 ) {
				var request = $.ajax({
					type : "POST",
					url :  "/config/getLocation/country/" + $(this).val(),	
					dataType : "json"
				});
				
				request.done(function( data ) {
					if ( data.status ) {
						form.get("country").html(data.data);
					} else {
						alert("Error en el servidor");
					}
				});
					
				request.fail(function( jqXHR, textStatus ) {
					alert("Error connecting to server");			
				});				
			}
		});
		
		form.get("country").change(function( ) {
			
			form.get("state").html('<option selected value="0">' + language.SELECT + '</option>');
			form.get("region").html('<option selected value="0">' + language.SELECT + '</option>');
			form.get("city").html('<option selected value="0">' + language.SELECT + '</option>');
			
			if ( $(this).val()	 != 0 ) {
				var request = $.ajax({
					type : "POST",
					url :  "/config/getLocation/state/" + $(this).val(),	
					dataType : "json"
				});
				
				request.done(function( data ) {
					if ( data.status ) {
						form.get("state").html(data.data);
					} else {
						alert("Error en el servidor");
					}
				});
					
				request.fail(function( jqXHR, textStatus ) {
					alert("Error connecting to server");			
				});				
			}
		});
		
		form.get("state").change(function( ) {
			
			form.get("region").html('<option selected value="0">' + language.SELECT + '</option>');
			form.get("city").html('<option selected value="0">' + language.SELECT + '</option>');
			
			if ( $(this).val()	 != 0 ) {
				var request = $.ajax({
					type : "POST",
					url :  "/config/getLocation/region/" + $(this).val(),	
					dataType : "json"
				});
				
				request.done(function( data ) {
					if ( data.status ) {
						form.get("region").html('<option selected="" value="-1">' + language.others + '</option>' + data.data);
					} else {
						alert("Error en el servidor");
					}
				});
					
				request.fail(function( jqXHR, textStatus ) {
					alert("Error connecting to server");			
				});				
			}
		});
		
		form.get("region").change(function( ) {
			
			form.get("city").html('<option selected value="0">' + language.SELECT + '</option>');
			
			if ( $(this).val() > 0 ) {
				var request = $.ajax({
					type : "POST",
					url :  "/config/getLocation/region/" + $(this).val(),	
					dataType : "json"
				});
				
				request.done(function( data ) {
					try {
						form.get("opc_city").show();
						$(".custom-combobox").hide();
					} catch(err) {

					}

					if ( data.status ) {
						form.get("city").html(data.data);
					} else {
						updateLatAndLong($(this).val());
					}
				});
					
				request.fail(function( jqXHR, textStatus ) {
					alert("Error connecting to server");			
				});				
			} else if ( $(this).val() < 0 ) {
				
				var request = $.ajax({
					type : "POST",
					url :  "/config/getLocation/other/" + form.get("state").val(),	
					dataType : "json",
					async : false
				});
				
				request.done(function( data ) {	
					if ( data.status ) {
						form.get("city").html('<option value=""></option>' + data.data);
						form.get("city").combobox({
				          select: function( event, ui ) {
							updateLatAndLong(form.get("city").val());
				          }
				        });
						$(".custom-combobox").show();
						form.get("opc_city").hide();
					} else {
						updateLatAndLong(form.get("state").val());
					}
				});
				
			} else {
				try {
					form.get("opc_city").show();
					$(".custom-combobox").remove();
				} catch(err) {
	
				}
			}
		});
	};
		
	$("#sonda_opc_continent").change(function( ) {
		var continent = $("#sonda_opc_continent");
		$('#sonda_opc_country').html('<option selected value="0">' + language.SELECT + ' ' + language.CONTINENT + '</option>');
		$('#sonda_opc_state').html('<option selected value="0">' + language.SELECT + ' ' + language.CONTINENT + '</option>');
		$('#sonda_opc_region').html('<option selected value="0">' + language.SELECT + ' ' + language.CONTINENT + '</option>');
		$('#sonda_opc_city').html('<option selected value="0">' + language.SELECT + ' ' + language.CONTINENT + '</option>');
		if ( continent.val() != 0 ) {
			$.ajax({
				type : "POST",
				url : "/config/getLocation/country/" + continent.val(),
				dataType : "json",
				success : function( data ) {
					if ( data.status ) {
						$('#sonda_opc_country').html(data.data);
						$('#sonda_opc_state').html('<option selected value="0">' + language.SELECT + ' ' + language.COUNTRY + '</option>');
						$('#sonda_opc_region').html('<option selected value="0">' + language.SELECT + ' ' + language.COUNTRY + '</option>');
						$('#sonda_opc_city').html('<option selected value="0">' + language.SELECT + ' ' + language.COUNTRY + '</option>');	
					} else {
						alert("Error en el servidor");
					}
				},
				error : function( ) {
					alert("Error de conexion");
				}

			});
		};
		return false;
	});

	$("#sonda_opc_country").change(function( ) {
		var country = $("#sonda_opc_country");
		$('#sonda_opc_state').html('<option selected value="0">' + language.SELECT + ' ' + language.COUNTRY + '</option>');
		$('#sonda_opc_region').html('<option selected value="0">' + language.SELECT + ' ' + language.COUNTRY + '</option>');
		$('#sonda_opc_city').html('<option selected value="0">' + language.SELECT + ' ' + language.COUNTRY + '</option>');
		if ( country.val() != 0 ) {
			$.ajax({
				type : "POST",
				url : "/config/getLocation/state/" + country.val(),
				dataType : "json",
				success : function( data ) {
					if ( data.status ) {
						$('#sonda_opc_state').html(data.data);
						$('#sonda_opc_region').html('<option selected value="0">' + language.SELECT + ' state</option>');
						$('#sonda_opc_city').html('<option selected value="0">' + language.SELECT + ' state</option>');
					} else {
						alert("Error en el servidor");
					}
				},
				error : function( ) {
					alert("Error de conexion");
				}

			});
		}
	});

	$("#sonda_opc_state").change(function( ) {
		var state = $("#sonda_opc_state");
		$('#sonda_opc_region').html('<option selected value="0">' + language.SELECT + ' state</option>');
		$('#sonda_opc_city').html('<option selected value="0">' + language.SELECT + ' state</option>');
		if ( state.val() != 0 ) {
			$.ajax({
				type : "POST",
				url : "/config/getLocation/region/" + state.val(),
				dataType : "json",
				success : function( data ) {
					if ( data.status ) {
						$('#sonda_opc_region').html('<option selected="" value="-1">' + language.others + '</option>' + data.data);
						$('#sonda_opc_city').html('<option selected value="0">' + language.SELECT + ' region</option>');
					} else {
						alert("Error en el servidor");
					}
				},
				error : function( ) {
					alert("Error de conexion");
				}

			});
		}
	});

	$("#sonda_opc_region").change(function( ) {
		var region = $("#sonda_opc_region");
		var state = $("#sonda_opc_state");
		$('#sonda_opc_city').html('<option selected value="0">' + language.SELECT + ' region</option>');
		if ( region.val() > 0 ) {
			$.ajax({
				type : "POST",
				url : "/config/getLocation/city/" + region.val(),
				dataType : "json",
				success : function( data ) {

					try {
						$("#sonda_opc_city").show();
						$(".custom-combobox").hide();
					} catch(err) {

					}

					if ( data.status ) {

						$('#sonda_opc_city').html(data.data);
					} else {
						updateLatAndLong(region.val());
					}
				},
				error : function( ) {
					alert("Error de conexion");
				}

			});
		} else if ( region.val() < 0 ) {
			$.ajax({
				type : "POST",
				url : "/config/getLocation/other/" + state.val(),
				dataType : "json",
				async : false,
				success : function( data ) {
					if ( data.status ) {
						$('#sonda_opc_city').html('<option value=""></option>' + data.data);
						$("#sonda_opc_city").combobox({
				          select: function( event, ui ) {
							updateLatAndLong($("#sonda_opc_city").val());
				          }
				        });
						$(".custom-combobox").show();
						$("#sonda_opc_city").hide();
					} else {
						updateLatAndLong(state.val());
					}
				},
				error : function( ) {
					alert("Error de conexion");
				}

			});
		} else {
			try {
				$("#sonda_opc_city").show();
				$(".custom-combobox").remove();
			} catch(err) {

			}
		}
	});

	$("#sonda_opc_city").change(function( ) {
		var city = $("#sonda_opc_city").val();
		if ( city != 0 ) {
			updateLatAndLong(city);
		}
	});

	function updateLatAndLong( geonameid ) {
		$.ajax({
			type : "POST",
			url : "/config/getLocationValue/" + geonameid,
			dataType : "json",
			success : function( data ) {
				if ( data.status ) {
					$("#sonda_opc_latitud_grados").val(data.latitude.deg);
					$("#sonda_opc_latitud_minutos").val(data.latitude.min);
					$("#sonda_opc_latitud_segudos").val(data.latitude.sec);
					$("#sonda_opc_longitud_grados").val(data.longitude.deg);
					$("#sonda_opc_longitud_minutos").val(data.longitude.min);
					$("#sonda_opc_longitud_segundos").val(data.longitude.sec);
				}
			},
			error : function( ) {
				alert("Error de conexion");
			}

		});
	}

});
