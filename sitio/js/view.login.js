$(document).ready(function() {
	
	$("#errorSection").hide();
	
	// Call the button widget method on the login button to format it. 
	$("#btnLogin").button().bind("click", function() {
	
		var action = $("#formLogin").attr('action');
		var form_data = {
			username: $("#username").val(),
			password: $('#password').val(),
			lang: $('#lang').val(),
			is_ajax: 1
		};
		
		$.ajax({
			type: "POST",
			url: action,
			data: form_data,
			dataType: "json",
			success: function(response)
			{
				if(response.status){
					location.href = response.redirect;
				}
				else {
					$("#errorSection").effect("shake", {} , 500);
					$("#errorSection").show();
					$(".ui-state-error p").html('<span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>'+response.msg);	
				}
					
			}
		});
		
		return false;
	});
	
});