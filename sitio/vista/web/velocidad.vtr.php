<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
		<meta http-equiv="X-UA-Compatible" content="IE=8" />
		<title>Speedtest.net - Resultados</title>
		<script type="text/javascript" src="http://c.speedtest.net/javascript/swfobject.js?v=2.2"></script>
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
	</head>

	<body>
	<script type="text/javascript">
			function test_completed(array) {
				console.log(array);
				download_rate = Math.round((array.download / 8) * 10) / 10;
				upload_rate = Math.round((array.upload / 8) * 10) / 10;
				var curdate = new Date();
				var speed = document.getElementById('speed');
				if (speed) {
					if (array.latency) {
						if (array.jitter > -1) {
							if (array.packetloss < 100) {
								speed.innerHTML = "<strong>Last Result:</strong><br/>" + "Download Speed: <strong>" + array.download + "</strong> kbps (" + download_rate + " KB/sec transfer rate)<br/>" + "Upload Speed: <strong>" + array.upload + "</strong> kbps (" + upload_rate + " KB/sec transfer rate)<br/>" + "Latency: <strong>" + array.latency + "</strong> ms<br/>" + "Jitter: <strong>" + array.jitter + "</strong> ms<br/>" + "Packet Loss: <strong>" + array.packetloss + "</strong>%";
							} else {
								speed.innerHTML = "<strong>Last Result:</strong><br/>" + "Download Speed: <strong>" + array.download + "</strong> kbps (" + download_rate + " KB/sec transfer rate)<br/>" + "Upload Speed: <strong>" + array.upload + "</strong> kbps (" + upload_rate + " KB/sec transfer rate)<br/>" + "Latency: <strong>" + array.latency + "</strong> ms<br/>" + "Jitter: <strong>" + array.jitter + "</strong> ms";
							}
						} else {
							speed.innerHTML = "<strong>Last Result:</strong><br/>" + "Download Speed: <strong>" + array.download + "</strong> kbps (" + download_rate + " KB/sec transfer rate)<br/>" + "Upload Speed: <strong>" + array.upload + "</strong> kbps (" + upload_rate + " KB/sec transfer rate)<br/>" + "Latency: <strong>" + array.latency + "</strong> ms";
						}
					} else {
						speed.innerHTML = "<strong>Last Result:</strong><br/>" + "Download Speed: <strong>" + array.download + "</strong> kbps (" + download_rate + " KB/sec transfer rate)<br/>" + "Upload Speed: <strong>" + array.upload + "</strong> kbps (" + upload_rate + " KB/sec transfer rate)";
					}

					speed.innerHTML = speed.innerHTML + "<br/>" + curdate.toLocaleString() + "<br/>";
				}

				var abovebefore = document.getElementById('abovebefore');
				if (abovebefore) {
					abovebefore.style.display = "none";
				}
				var belowbefore = document.getElementById('belowbefore');
				if (belowbefore) {
					belowbefore.style.display = "none";
				}
				var aboveafter = document.getElementById('aboveafter');
				if (aboveafter) {
					aboveafter.style.display = "block";
				}
				var belowafter = document.getElementById('belowafter');
				if (belowafter) {
					belowafter.style.display = "block";
				}

				var downMbps = Math.round(array.download / 10) / 100;
				var expDown = document.getElementById("targetDown").value;
				var afterDiv = document.getElementById("aboveafter");
				if (downMbps < expDown) {

					afterDiv.innerHTML = "Your download rate of " + downMbps + " Mbps was slower than the expected rate of " + expDown + " Mbps. <br/>Click <a href='http://d.pr/GFDw' target='_blank'>here</a> to be connected with a support representative.";
				} else if (downMbps > expDown) {

					afterDiv.innerHTML = "Your download rate of " + downMbps + " Mbps was better than expected! <br/>Need an even faster connection? Click <a href='http://d.pr/q826' target='_blank'>here</a> to upgrade your service.";
				}

			}

			function test_component_complete(testval) {
				var statDiv = document.getElementById("status");
				statDiv.innerHTML = "Current status: " + testval + " test completed successfully."
			}

			function processData(allText) {
				var allTextLines = allText.split(/\r\n|\n/);
				var headers = allTextLines[0].split(',');
				var lines = [];

				for (var i = 1; i < allTextLines.length; i++) {
					var data = allTextLines[i].split(',');
					if (data.length == headers.length) {

						var tarr = [];
						for (var j = 0; j < headers.length; j++) {
							tarr.push(headers[j] + ":" + data[j]);
						}
						lines.push(tarr);
					}
				}
				$("#statusResult").html(lines);
			}

			function test_completed2(array) {
				download_rate = Math.round((array.download / 8) * 10) / 10;
				upload_rate = Math.round((array.upload / 8) * 10) / 10;
				var curdate = new Date();
				console.log("par5:" + download_rate);
				console.log("par6:" + upload_rate);
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

				console.log("par7:" + obj);

			}

			function test_started(test_count, server_id) {
				console.log("mono2");
			}

			function toJava(jsmethod, args) {
				var e = document.getElementById('VoipApplet');
				e.fromJS(jsmethod, args);
			}

			function fromJava(jsmethod, args) {
				setTimeout("flashCall(\'" + jsmethod + "\', \'" + args + "\')", 100);
			}

			function flashCall(jsmethod, args) {
				var e = document.getElementById('flashtest');
				e.fromJS(jsmethod, args);
			}

			var flashvars = {
				configExtension : "php"
			};
			var params = {
				quality : "high",
				bgcolor : "#ffffff",
				allowScriptAccess : "always"
			};
			var attributes = {
				id : "flashtest",
				name : "flashtest"
			};
			swfobject.embedSWF("http://qoe.velocidad.vtr.net/netgauge.swf?v=3.0", "flashcontent", "620", "420", "10.0.0", false, flashvars, params, attributes);

		</script>
		<div id="status">
			Current status: Waiting
		</div>
		<br />

		<div id="abovebefore">
			Expected download speed:
			<input id="targetDown" type=
			"number" />
			Mbps
			<br />
		</div>
		<div id="speed">

		</div>
		<div id="aboveafter" style="display: none;"></div>
		<br />

		<table border="0">
			<tr>
				<td>
				<div id="flashcontainer">
					<div id="flashcontent">
						This text is replaced by the Flash movie.
					</div>
				</div>
				<br />
				</td>
			</tr>

			<tr>
				<td><!-- The following div element, javadiv, must be present for the packetloss
				and firewall tests to function properly.
				--><div id="javadiv"></div></td>
			</tr>
		</table>
	</body>
</html>