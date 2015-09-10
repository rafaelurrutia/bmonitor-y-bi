{DOCTYPE2}
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        {META}
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
        <title>Video - Resultados</title>
        
        <style>
            * {
                margin: 0px;
                padding: 0px;
            }
            
            html {
                height: 400px;
                width: 660px;
            } 
        </style>

        <script type="text/javascript">
			var countPlaying = 0;
			var countBuffering = 0;
			if (typeof console == "undefined"){
				var console = { log: function() {} };
			}
     
            var tag = document.createElement('script');
            tag.src = "http://www.youtube.com/iframe_api";
        
            var firstScriptTag = document.getElementsByTagName('script')[0];
            firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
        
            var player;

            function onYouTubeIframeAPIReady() {
                try {
                    player = new YT.Player('player', {
                        height: '390',
                        width: '640',
                        videoId: '{VIDEOID}',
                        suggestedQuality: "{QUALITY}",
                        playerVars: {
                            html5: {HTML5},
                            controls: 1,
                            autoplay: 1,
                            modestbranding: 1,
                            showinfo: 0,
                            rel: 0,
                            quality:  "{QUALITY}"
                        },
                        events: {
                            'onReady': onPlayerReady,
                            'onStateChange': onPlayerStateChange,
                            'onPlaybackQualityChange': onPlayerPlaybackQualityChange
                        }
                    });
                } catch(err) {
                    console.log(err);
                }
            };

            function onPlayerReady(event) { 
                
                try {
                    event.target.setVolume(0);   
                    
                    event.target.playVideo();  
                    
                    player.setPlaybackQuality("{QUALITY}");
                } catch(err) {
                    console.log(err);
                }
                
                window.setInterval(function() {
                    try {
                        updateytplayerInfo();
                    } catch(err) {
                    	console.log(err);
                    }
                }, 1000);
                  
            }

            function onPlayerPlaybackQualityChange(event) { 
                try {
                    player.setPlaybackQuality("{QUALITY}");
                } catch(err) {
                	console.log(err);
                    return false;
                }
            }    
            
            function onPlayerStateChange(event) {
                //rate.html(player.getPlaybackRate());
            }
              
            function stopVideo() {
                player.stopVideo();
            }
            
            function setPlaybackQuality(quality) {     
                try {
                    player.setPlaybackQuality(quality);
                } catch(err) {
                	console.log(err);
                    return false;
                }
            }
    

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
                try {
                    return player.getVolume();
                } catch(err) {
                    return false;
                }
            }

            function getDuration() {
                try {
                    return player.getDuration();
                } catch(err) {
                    return false;
                }
            }

            function getCurrentTime() {
                try {
                    var currentTime = player.getCurrentTime();
                    return roundNumber(currentTime, 3);
                } catch(err) {
                    return false;
                }
            }

            function getPlayerState() {
                if (player) {
                    try {
                        var playerState = player.getPlayerState();
                    } catch(err) {
                        var playerState =  99;
                    }
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
                try {
                    return player.getVideoBytesTotal();
                } catch(err) {
                    return '';
                }
            }

            function getStartBytes() {
                try {
                return player.getVideoStartBytes();
                } catch(err) {
                    return '';
                }
            }

            function getVideoLoadedFraction() {
                try {
                return player.getVideoLoadedFraction();
                } catch(err) {
                    return '';
                }
            }

            function getPlaybackRate() {
                try {
                return player.getPlaybackRate();
                } catch(err) {
                    return '';
                }
            }

            function getAvailablePlaybackRates() {
                try {
                return player.getAvailablePlaybackRates();
                } catch(err) {
                    return '';
                }
            }

            function getBytesLoaded() {
                try {
                return player.getVideoBytesLoaded();
                } catch(err) {
                    return '';
                }
            }

            function getQuality() {
                try {
                    var quality = player.getPlaybackQuality();
                    return quality;
                } catch(err) {
                    return '';
                }
            }

            function getQualityLevels() {
                try {
                    return player.getAvailableQualityLevels();
                } catch(err) {
                    return '';
                }
            }

            function isMuted() {
                try {
                    if (!player.isMuted()) {
                        return 'on';
                    }
                    return 'off';
                } catch(err) {
                    return '';
                }
            }
            
            function getPlayerPerState(){
            	return ((countPlaying / (countPlaying + countBuffering)) * 100)
            }
            
            function getCountPlaying(){
            	return countPlaying;
            }
                        
             function updateytplayerInfo() {
                if (player) {
                    updateHTML('volume', getVolume());
    
                    updateHTML('videoDuration', getDuration());
                    updateHTML('videoCurrentTime', getCurrentTime());
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

        </script>
    </head>
    <body> 

   <div id="player"></div>
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
    </body>
</html>