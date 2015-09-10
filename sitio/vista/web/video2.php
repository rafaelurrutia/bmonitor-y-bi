<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
        <meta http-equiv="X-UA-Compatible" content="IE=8" />
        <title>Video - Resultados</title>
        <script type="text/javascript" src="/sitio/js/swfobject.js"></script>
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
        
        <script type="text/javascript">
            var countPlaying = 0;
            var countBuffering = 0;
            
			function updateHTML(elmId, value) {
				if (document.getElementById(elmId)) {
					document.getElementById(elmId).innerHTML = value;
				}
			}

			function roundNumber(number, decimalPlaces) {
				decimalPlaces = (!decimalPlaces ? 2 : decimalPlaces);
				return Math.round(number * Math.pow(10, decimalPlaces)) / Math.pow(10, decimalPlaces);
			}

			function getVolume() {
				return player.getVolume();
			}

			function getDuration() {
				return player.getDuration();
			}

			function getCurrentTime() {
				var currentTime = player.getCurrentTime();
				return roundNumber(currentTime, 3);
			}

			function getPlayerState() {
				if (player) {
					var playerState = player.getPlayerState();
					switch (playerState) {
						case 5:
							return 'video cued';
						case 3:
							countBuffering++;
							return 'buffering';
						case 2:
							return 'paused';
						case 1:
						    countPlaying++;
							return 'playing';
						case 0:
							return 'ended';
						case -1:
							return 'unstarted';
						default:
							return 'Status uncertain';
					}
				}
			}

			function getBytesTotal() {
				return player.getVideoBytesTotal();
			}

			function getStartBytes() {
				return player.getVideoStartBytes();
			}

			function getVideoLoadedFraction() {
				return player.getVideoLoadedFraction();
			}

			function getPlaybackRate() {
				return player.getPlaybackRate() || '';
			}

			function getAvailablePlaybackRates() {
				return player.getAvailablePlaybackRates();
			}

			function getBytesLoaded() {
				return player.getVideoBytesLoaded();
			}

			function getQuality() {
				var quality = player.getPlaybackQuality();
				if (!quality) {
					return '';
				}
				return quality;
			}

			function getQualityLevels() {
				return player.getAvailableQualityLevels();
			}

			function isMuted() {
				if (!player.isMuted()) {
					return 'on';
				}
				return 'off';
			}

			function updateytplayerInfo() {
				player = document.getElementById("myytplayer");

                try {
                  player.playVideo();  
                  player.setPlaybackQuality("{QUALITY}");
                  player.setVolume(0); 
                } catch(err) {
                    
                }
				
                if (player) {
                    updateHTML('volume', getVolume());

                    updateHTML('videoDuration', getDuration());
                    updateHTML('videoCurrentTime', getCurrentTime());
                    document.title="Video: "+getCurrentTime();
                    updateHTML('playerState', getPlayerState());
                    updateHTML('perState', getPlayerPerState());

                    updateHTML('bytesTotal', getBytesTotal());
                    updateHTML('startbytes', getStartBytes());
                    
					updateHTML('percentloaded', getVideoLoadedFraction());
					updateHTML('playbackrate', getPlaybackRate());
					updateHTML('availableplaybackrates', getAvailablePlaybackRates());
                    
                    updateHTML('bytesLoaded', getBytesLoaded());

                    updateHTML('playbackquality', getQuality());
                    updateHTML('availablelevels', getQualityLevels());
                    updateHTML('ismuted', isMuted());

                }
			}
			
			function getPlayerPerState(){
				return ((countPlaying / (countPlaying + countBuffering)) * 100);
			}
			
			function getCountPlaying(){
				return countPlaying;
			}

			function loadVideo(videoid) {
				var params = {
					allowScriptAccess : "always",
					allowFullScreen : "true"
				};
				var atts = {
					id : "myytplayer"
				};
				swfobject.embedSWF("http://www.youtube.com/v/{VIDEOID}?enablejsapi=1&playerapiid=ytplayer&version=3", "ytapiplayer", "640", "360", "8", null, null, params, atts,alertStatus);
			}

            function alertStatus(e) {
                   // updateHTML('console', "e.success = " + e.success +"\ne.id = "+ e.id );
            }

			loadVideo("YE7VzlLtp-4");

			window.setInterval(function() {
				updateytplayerInfo();
			}, 1000);

        </script>
    </head>
    <body>
        
        <div id="ytapiplayer"></div>
 <div class="player-demo-group-options" style="display: block">
            Duration: <span id="videoDuration">1344</span>,
            Current time: <span id="videoCurrentTime">0</span>,
            Player state: <span id="playerState">-1</span>,
            % state: <span id="perState">0</span>
            <br>
            <span id="bytesdisplay"> Start bytes: <span id="startbytes">0</span>,
                <div id="percent-video-loaded" style="display: block;">
                    Percentage of video loaded: <span id="percentloaded">0</span>
                </div> </span>
            <br>
            <span id="quality"> Quality level: <span id="playbackquality">small</span>,
                Available levels: <span id="availablelevels"></span>,
                Volume (on/off): <span id="ismuted">on</span> </span>
            <br>
            <span id="playbackrate-statistics" style="display: block;"> Playback rate: <span id="playbackrate">1</span>,
                Available rates: <span id="availableplaybackrates">1</span> </span>
            <br>
            <span id="bytesdisplay-deprecated"> <span style="color:maroon">Deprecated: </span> Bytes loaded: <span id="bytesLoaded">0</span>,
                Total bytes: <span id="bytesTotal">0</span> </span>
            <div id="playlist-statistics" style="display: none;">
                <br>
                Number of videos in playlist: <span id="playlistcount"> </span>
                <br>
                Position of current video ('0' = first video): <span id="currentplaylistvideo"> </span>
                <br>
                <table style="border:0">
                    <tbody>
                        <tr>
                            <td style="border:0; padding:0; vertical-align:top">Videos in playlist:</td>
                            <td style="border:0; padding:0;"><span id="playlistvideos"> </span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div id="console"></div>
    </body>
</html>