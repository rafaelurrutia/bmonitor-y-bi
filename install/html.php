<!--
You are free to copy and use this sample in accordance with the terms of the
Apache license (http://www.apache.org/licenses/LICENSE-2.0.html)
-->

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <title>YouTube Player API Sample</title>
    <style type="text/css">
      #videoDiv {
        margin-right: 3px;
      }
      #videoInfo {
        margin-left: 3px;
      }
    </style>
    <script src="http://www.google.com/jsapi" type="text/javascript"></script>
    <script type="text/javascript">
      google.load("swfobject", "2.1");
    </script>    
    <script type="text/javascript">
      /*
       * Polling the player for information
       */
      
      // Update a particular HTML element with a new value
      function updateHTML(elmId, value) {
        document.getElementById(elmId).innerHTML = value;
      }
      
      // This function is called when an error is thrown by the player
      function onPlayerError(errorCode) {
        // alert("An error occured of type:" + errorCode);
      }
      
      // This function is called when the player changes state
      function onPlayerStateChange(newState) {
        updateHTML("playerState", newState);
      }
      
      // Display information about the current state of the player
      function updatePlayerInfo() {
        // Also check that at least one function exists since when IE unloads the
        // page, it will destroy the SWF before clearing the interval.
        if(ytplayer && ytplayer.getDuration) {
          updateHTML("videoDuration", ytplayer.getDuration());
          updateHTML("videoCurrentTime", ytplayer.getCurrentTime());
          updateHTML("bytesTotal", ytplayer.getVideoBytesTotal());
          updateHTML("bytesLoaded", ytplayer.getVideoBytesLoaded());
        }
      }
      
      // This function is automatically called by the player once it loads
      function onYouTubePlayerReady(playerId) {
        ytplayer = document.getElementById("ytPlayer");
        ytplayer.setPlaybackQuality("<?php echo $_GET['q'] ?>");
		// This causes the updatePlayerInfo function to be called every 250ms to
        // get fresh data from the player
        setInterval(updatePlayerInfo, 250);
        updatePlayerInfo();
        ytplayer.addEventListener("onStateChange", "onPlayerStateChange");
        ytplayer.addEventListener("onError", "onPlayerError");
		ytplayer.playVideo();
		
      }
      
      // The "main method" of this sample. Called when someone clicks "Run".
      function loadPlayer() {
        // The video to load
        var videoID ="<?php echo $_GET['id'] ?>"
        // Lets Flash from another domain call JavaScript
        var params = { allowScriptAccess: "always" };
        // The element id of the Flash embed
        var atts = { id: "ytPlayer" };
        // All of the magic handled by SWFObject (http://code.google.com/p/swfobject/)

		
        swfobject.embedSWF("http://www.youtube.com/v/" + videoID +
                           "?version=3&enablejsapi=1&playerapiid=player1&cc_load_policy=1",
                           "videoDiv", "700", "500", "9", null, null, params, atts);
	   	
      }
      function _run() {
        loadPlayer();
      }
      google.setOnLoadCallback(_run);
    </script>
  </head>
  <body style="font-family: Arial;border: 0 none;">
    <div id="videoDiv">Loading...</div>
    
      <div id="videoInfo" style="display: none" >
        <p>Player state: <span id="playerState">--</span></p>
        <p>Current Time: <span id="videoCurrentTime">--:--</span> | Duration: <span id="videoDuration">--:--</span></p>
        <p>Bytes Total: <span id="bytesTotal">--</span> | Bytes Loaded: <span id="bytesLoaded">--</span></p>
      </div>
    
  </body>
</html>

