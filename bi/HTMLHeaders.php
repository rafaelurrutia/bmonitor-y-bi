<?php
require_once "api.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="la" lang="la">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo printName(); ?></title>
        <link rel="stylesheet" type="text/css" href="theme/css/bootstrap.org.min.css"/>
        <link rel="stylesheet" type="text/css" href="theme/css/bootstrap-baking.css"/>
        <link rel="stylesheet" type="text/css" href="theme/css/font-awesome.min.css">
        <link rel="stylesheet" type="text/css" href="theme/css/datepicker.css"/>
        <link rel="stylesheet" type="text/css" href="theme/css/bootstrap-datetimepicker.min.css"/>
        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->
        <script src="theme/js/jquery-1.11.1.min.js"></script>
        <script src="theme/js/moment.min.js"></script>
        <script src="theme/js/en-ca.js"></script>
        <script src="theme/js/bootstrap.min.js"></script>
        <script src="theme/js/bootstrap-datepicker.js"></script>
        <script src="theme/js/bootstrap-datetimepicker.js"></script>
        <script src="theme/js/multifilter.min.js"></script>
        <link rel="icon" type="image/x-icon" href="favicon.ico" />
        <script type="text/javascript">
        	
            function preloader(){
               // document.getElementById("loading").style.display = "block";
               // document.getElementById("imgLoading").style.display = "block";
            }

           window.onload = preloader;

            $(window).load(function(){
               // $("#loading").fadeOut("slow");
               // $("#imgLoading").fadeOut("slow");
               // $('#loading').hide();
               // $('#imgLoading').hide();
                
            });
            
            $(document).ready(function() {
                //$('#loading').show();
                //$('#imgLoading').show();
            });
          
        </script>   
            
        <script>
       
			$(function() {
			    try {
				Highcharts.setOptions({
					global : {
						useUTC : false
					}
				});
			}  catch(err) {
			    
			}	
			});
			

        </script>
    </head>
    <body>
        <div id="loading"></div>
        <div id="imgLoading">
            <img widht="32" height="32" src="" alt="loading" title="loading" />        
        </div>
