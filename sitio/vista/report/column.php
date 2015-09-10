<script type="text/javascript">	
$(function () {
    
    
    Highcharts.setOptions({
        lang: {
            decimalPoint: ',',
            thousandsSep: '.'
        }
    });
    

    var chart = new Highcharts.Chart({
        chart: {
            renderTo: 'container',
            type: 'column'
        },
        title: {
            text: '{graphTitle}'
        },
        xAxis: {
            categories: [
               {category}
            ]
        },
        yAxis: {
            min: 0,
            title: {
                text: '{yAxis_title}'
            }
        },
        lang: {
            decimalPoint: ',',
            thousandsSep: '.'
        },
        tooltip: {
            yDecimals: 0,
            formatter: function() {
                //return ''+this.x +': '+ this.y +' '+ {tooltips};
                return ''+this.x +': '+ Highcharts.numberFormat(this.y,0) +' '+ {tooltips};
            }
        },
        plotOptions: {
            column: {
                pointPadding: 0.2,
                borderWidth: 0
            }
        },
        series: [{series}]
    });

});
</script>
<div id="container" style="min-width: 400px; height: 400px; margin: 0 auto"></div>
<div id="containerFilter" class="ui-widget-header ui-corner-all" style="display: none"><form id="containerFilterForm">{filter}<button id="search">Search</button> <button id="exportar">Exportar</button></form></div>