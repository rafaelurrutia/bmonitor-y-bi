<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
        <meta http-equiv="X-UA-Compatible" content="IE=8" />
        <title>Speedtest.net - Resultados</title>
    </head>
    <body>
        <div id="st-flash">
            <script type="text/javascript" src="http://c.speedtest.net/javascript/swfobject.js?v=2.2"></script>
            <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
            <!--        <script type="text/javascript" src="http://c.speedtest.net/javascript/swfmacmousewheel2.js?v=1.0"></script> 
            <script type="text/javascript" src="http://c.speedtest.net/javascript/extMouseWheel.js"></script>-->
            <div id="speed" result_up="0" result_dn="0" result_png="0"></div>
            <script type="text/javascript">
				function test_completed(download_speed, upload_speed, latency, server_id) {
				    console.log("par1:"+download_speed);
				    console.log("par2:"+upload_speed);
				    console.log("par3:"+latency);
				    console.log("par4:"+server_id);
				    
				    test_completed2(download_speed);
				    
                    download_rate = Math.round((array.download/8)*10)/10;
                    upload_rate = Math.round((array.upload/8)*10)/10;
                    
                    console.log(upload_rate);
                    
                    var curdate = new Date();
                    var speed = document.getElementById('speed');
				}

                function test_completed2(array) {
                    download_rate = Math.round((array.download/8)*10)/10;
                    upload_rate = Math.round((array.upload/8)*10)/10;
                    var curdate = new Date();
                    console.log(download_rate);
                    console.log(upload_rate);
                }
                
				function promo_completed(download, upload, ping) {

				}

				function test_error() {
					window.location.reload();
				}

				function loading() {
					$("#resultFrame").attr("src", "http://www.speedtest.net/results.php");
				}

                function getResult() {
                    
                   var obj = swfobject.getObjectById("speedtest"); 
                   
                   console.log(obj);
                 
                }
                
                function test_started(test_count, server_id) {
                   console.log("mono2");
                }

                
				var flashvars = {
					//ad : "banner",
					//adurl : "",
					//showsurvey : "1",
					threads : "4",
					//cdnconfig : "prod",
					//promo : "",
					upload_extension: "php",
					apiurl: "http://bmonitoe.com/mono.php",
					//hitdefaultapi: "false",
					//apireporting: "true",
					configExtension: "php"
				};
				var params = {
                    quality: "high",
                    bgcolor: "#ffffff",
                    allowScriptAccess: "always",
                    wmode: "transparent"
				};
				var attributes = {
					id : 'flashtest',
					name : 'flashtest'
				};

				swfobject.embedSWF("http://nacional.grupogtd.com/netgauge.swf?v=3.0", "flashcontent", "496", "305", "10.0.0", "flash/expressInstall.swf", flashvars, params, attributes);
				//swfmacmousewheel.registerObject(attributes.id);

            </script>
            <div id="flashcontent"></div>
           <!-- <div id="mydiv">
                <iframe id="resultFrame" src="" width="100px" height="100px"></iframe>
            </div>
            <button onclick="loading()">
                Cargar
            </button>
            <button onclick="getResult()">
                Resultado
            </button> -->
        </div>
    </body>
</html>