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

				    $("#status").html("terminated");
				    $(document).attr('title', "terminated");
				    document.title="terminated";
				    
				   // test_completed2(download_speed);
				    
                    //download_rate = Math.round((array.download/8)*10)/10;
                    //upload_rate = Math.round((array.upload/8)*10)/10;
                    
                    //console.log(upload_rate);
                    
                    var curdate = new Date();
                    var speed = document.getElementById('speedtest');
                    console.log(speed);
                   
                  	$.ajax({
				        type: "GET",
				        url: "http://speedtest.net/csv.php?csv=1&ria=0&s=0",
				        dataType: "text",
				        success: function(data) {
				        	processData(data)
				        }
				    });
     
				}

				function processData(allText) {
				    var allTextLines = allText.split(/\r\n|\n/);
				    var headers = allTextLines[0].split(',');
				    var lines = [];
				
				    for (var i=1; i<allTextLines.length; i++) {
				        var data = allTextLines[i].split(',');
				        if (data.length == headers.length) {
				
				            var tarr = [];
				            for (var j=0; j<headers.length; j++) {
				                tarr.push(headers[j]+":"+data[j]);
				            }
				            lines.push(tarr);
				        }
				    }
				    $("#statusResult").html(lines);
				}

                function test_completed2(array) {
                    download_rate = Math.round((array.download/8)*10)/10;
                    upload_rate = Math.round((array.upload/8)*10)/10;
                    var curdate = new Date();
                    console.log("par5:"+download_rate);
                    console.log("par6:"+upload_rate);
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
                   
                   console.log("par7:"+obj);
                 
                }
                
                function test_started(test_count, server_id) {
                   console.log("mono2");
                }

                
				var flashvars = {
                        ad: "banner",
                        adurl: "",
                        showsurvey: "1",
                        threads: "4",
                        cdnconfig: "prod",
                        promo: ""
				};
				var params = {
					bgcolor : "#ffffff",
					quality : "high",
					menu : "false",
					allowScriptAccess : "always",
					scale : 'default',
					salign : 'lt',
					wmode : 'transparent'
				};
				var attributes = {
					id : 'speedtest',
					name : 'speedtest'
				};
                //http://c.speedtest.net/flash/speedtest.swf?v=339384
                
				swfobject.embedSWF("http://qoe.velocidad.vtr.net/netgauge.swf?v=3.0", "flashcontent", "728", "450", "10.0.0", "flash/expressInstall.swf", flashvars, params, attributes);
				//swfobject.embedSWF("http://c.speedtest.net/flash/speedtest-long.swf?v=335591", "flashcontent", "728", "450", "10.0.0", "flash/expressInstall.swf", flashvars, params, attributes);
				//swfmacmousewheel.registerObject(attributes.id);

            </script>
            <div id="flashcontent"></div>
            <div id="status">stop</div>
            <div id="statusResult"></div>
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