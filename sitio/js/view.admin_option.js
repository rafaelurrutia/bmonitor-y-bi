$(function() {

	function deleteCache(option) {
		$.ajax({
			type : "POST",
			url : "/admin/deleteCache",
			data : "option=" + option,
			dataType : "json",
			success : function(data) {
				console.log("OK");
			},
			error : function() {
				alert("Error");
			}
		});
	}


	$("#deleteAllCache").button().click(function(event) {
		deleteCache("all");
	});
	$("#deleteTemplateCache").button().click(function(event) {
		deleteCache("template");
	});
	$("#deleteGraphCache").button().click(function(event) {
		deleteCache("graph");
	});
});
