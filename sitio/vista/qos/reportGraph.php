<script type="text/javascript">	
jQuery(function($){
	
	var reportActive;
	
	$('#navigationMenu').accordion({
        collapsible: true,
        navigation: true,
        clearStyle: true
	});
	
	$('.single-menu-item').unbind('click');
	
	$("#navigationMenu ul li a").click(function () {
		var href = $(this).attr('href').replace(/#/, "");
		
		if(reportActive != href) {
		    
		    $( "#report_display" ).html('<div id="imgLoading2"><img widht="32" height="32" src="{url_base}sitio/img/ajax-loader.gif" alt="loading" title="loading" /></div>');

			$.ajax({
				type: "POST",
				url: "/report/getReport/"+href,
				data:  "type=table",
				dataType: "json",
				success: function(data){
					$( "#report_display" ).html(data.html);
					$( "#containerFilter" ).show();
					
					$( "#search" ).button({
						text: false,
						icons: {
							primary: "ui-icon-search"
						}
					});
					
					$( "#exportar" ).button();
					
				    $("#exportar").on("click", function() {
                        $('#exportar').button({ disabled: true });
						$.ajax({
							type: "POST",
							url: "/report/export",
							dataType: "json",
							success: function(data){
								if(data.status) {
									window.location.href = "/report/download/"+data.excel_file;
									
								} else {
									alert(data.msg);
								}
							},
							error: function() {
								alert("Error");
							}
						});
						$('#exportar').button({ disabled: false });
						return false;
					})

					$("#search").on("click", function() {
						var data = $("#containerFilterForm").serialize();
						getGraph(href,data);
						return false;
					})
						
					reportActive = href;
				},
				error: function() {
					
				}
			});
			
		}
		
		return false;
    });
    
    function getGraph(id,dataS){

		$.ajax({
			type: "POST",
			url: "/report/getReport/"+id+"/TRUE",
			data:  dataS,
			dataType: "json",
			success: function(data){
				$( "#container" ).html(data.html); 
			}
		});
    }
});
</script>
<style>

	#container_report {
		width: 100%;
		height: 437px;
	}
	
	#menu_report {
		width: 300px;
		float: left;
	}
	
	#report_display {
		width: 800px;
		height: 100%;
		float: left;
	}
	
	#navigationMenu{ 
		width: 270px; 
	}
	.single-menu-item .ui-icon {
		display: none !important;
	}
	.single-menu-container {
		display: none !important;
	}
	
	#containerFilter {
		padding: 4px;
	}
	/* support: IE7 */
 	*+html #containerFilter {
		display: inline;
	}
	
	#containerFilter select {
		padding: 7px;
		line-height: 1;
		border: 0;
		height: 26px;
		-webkit-appearance: none;
		margin-right: 5px;
	}
	
    #containerFilter input {
        line-height: 1;
        border: 0;
        height: 24px;
        text-align: center;
        -webkit-appearance: none;
        margin-right: 5px;
    }

	#containerFilter label {
		padding: 7px;
	} 

	#containerFilterForm {
		margin: 0px;
		padding: 0px;
	}
</style>
<div id="loading"></div>
<div id="imgLoading">
    <img widht="32" height="32" src="{url_base}sitio/img/ajax-loader.gif" alt="loading" title="loading" />
</div>
<div id="container_report">
	<div id="menu_report">
		<div id="navigationMenu">
			
			<h3 class="single-menu-item"> <a href="#"> {REPORT} </a> </h3>
			<div class="single-menu-container"> </div>
			{menu}
			<div class="single-menu-container"></div>
		</div>
	</div>
	<div id="report_display" class="ui-helper-reset ui-widget-content ui-corner-all">
		
	</div>
</div>