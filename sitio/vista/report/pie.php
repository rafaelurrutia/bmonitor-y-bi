<script type="text/javascript">	
$(function () {

    $( "#FECHAHORA_START" ).datepicker({
      defaultDate: "+1w",
      changeMonth: true,
      changeYear: true,
      dateFormat: 'yy-mm-dd',
      onClose: function( selectedDate ) {
        $( "#FECHAHORA_END" ).datepicker( "option", "minDate", selectedDate );
      }
    });
    $( "#FECHAHORA_END" ).datepicker({
      defaultDate: "+1w",
      changeMonth: true,
      changeYear: true,
      dateFormat: 'yy-mm-dd',
      onClose: function( selectedDate ) {
        $( "#FECHAHORA_START" ).datepicker( "option", "maxDate", selectedDate );
      }
    });
    
    var chart = new Highcharts.Chart({
        chart: {
			renderTo: 'container',
            plotShadow: false
        },
        title: {
            text: '{graphTitle}'
        },
        tooltip: {
        	pointFormat: '{series.name}: <b>{point.y}</b>',
        	percentageDecimals: 2,
        	valueDecimals: 0
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                showInLegend: {showInLegend},
                dataLabels: {
                    enabled: {showLabels_enabled},
                    color: '#000000',
                    connectorColor: '#000000',
                    formatter: function() {
                       {showLabels_detail}
                    }
                }
            }
        },
        series: [{series}]
    });

});
</script>
<div id="container" style="min-width: 400px; height: 400px; margin: 0 auto"></div>
<div id="containerFilter" class="ui-widget-header ui-corner-all" style="display: none"><form id="containerFilterForm">{filter}<button id="search">Search</button> <button id="exportar">Exportar</button></form></div>