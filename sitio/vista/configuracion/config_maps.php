<div id="map" style="width: 800px; height: 500px"></div>
<script type="text/javascript">

function load() {
	var map = new google.maps.Map(document.getElementById("map"), {
		center: new google.maps.LatLng(-33.46912, -70.641997),
		zoom: 13,
		mapTypeId: 'roadmap'
	});
  
	var infoWindow = new google.maps.InfoWindow;

	$.ajax({
		type: 'GET',
		url: '/config/getMapsXml',
		cache: false,
		dataType: (browser.msie) ? 'text' : 'xml', // Reconocemos el browser.
		success: function(data){
			var xml;
			if(typeof data == 'string'){
				xml = new
				ActiveXObject('Microsoft.XMLDOM');
				xml.async = false;
				xml.loadXML(data);
		    } else {
				xml = data;
		    }
		
			$(xml).find('marker').each(function(){
				var name = $(this).attr('name');
				var address = $(this).attr('address');
				var type = $(this).attr('type');
				var point = new google.maps.LatLng(
					parseFloat($(this).attr('lat')),
					parseFloat($(this).attr('lng')));
					
				var html = "<b>" + name + "</b> <br/>" + address;
				var marker = new google.maps.Marker({
					map: map,
					position: point
				});
				bindInfoWindow(marker, map, infoWindow, html);
			});
		}
	});
}

function bindInfoWindow(marker, map, infoWindow, html) {
	google.maps.event.addListener(marker, 'click', function() {
		infoWindow.setContent(html);
		infoWindow.open(map, marker);
	});
}
    
load();
</script>
    