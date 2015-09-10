<?php
include_once "plugins/Highchart.php";
include_once "plugins/HighchartOption.php";
include_once "plugins/HighchartJsExpr.php";
include_once "plugins/HighchartOptionRenderer.php";

use Ghunti\HighchartsPHP\Highchart;
use Ghunti\HighchartsPHP\HighchartJsExpr;
use Ghunti\HighchartsPHP\HighchartOptionRenderer;
use Ghunti\HighchartsPHP\HighchartOption;

function GraphSonda($chart, $id_chart, $id_plan, $id_item, $from, $avg, $tag)
{
  $offset=getOffset();
  // setTimezone();
  $dt=getDates($from);
           
  $index=0;
  $average=getAverage($avg);
  $format=$average[0];
  $divide=$average[1];
  if ($divide == 60 * 60 * 6) $offset = -60*60*2;
  if ($divide == 60 * 60 * 12) $offset = +60*60*4;
  if ($divide == 60 * 60 * 24) $offset = +60*60*4;
  $colorIndex=0;
  $ldata=loadRAWData($from, $id_plan, $id_item);
  $percentile=calculatePercentile($ldata['allData']);
  $div=$ldata['div'];
  if (isset($ldata['hostList']))
  foreach ($ldata['hostList'] as $host) {
  	$id_host=$host['id_host'];	
	$hostName=$host['host'];								
	$nacD=$host['nacD'];
	$nacU=$host['nacU'];
	$critical=$host['critical'];
	$warning=$host['warning'];
	$nominal=$host['nominal'];
	if ($warning >100) $dir=1; else $dir=0;
	if ($nominal == -1) $nominal = $nacD;
	if ($nominal == -2) $nominal = $nacU;
	
	unset($theSondaAvg);
	unset($theSondaQoE);
	unset($dataAvg);
	unset($dataQoE);
	if (isset($ldata['historyList'][$id_host]))
	foreach ($ldata['historyList'][$id_host] as $key=>$row) {
		$tk=round($row['clock']/$divide,0)*$divide;
		$skip=false;
		if (isset($_SESSION['filter']['P95']) && $row['value'] > $percentile['P95']) $skip=true; 
		if (isset($_SESSION['filter']['P5']) && $row['value'] < $percentile['P5']) $skip=true; 
		if (!$skip)	{
			if (!isset($theSondaAvg[$tk])) $theSondaAvg[$tk] = array($tk,0,0);
			if (!isset($theSondaQoE[$tk])) $theSondaQoE[$tk] = array($tk,0,0);
			$sum=$theSondaAvg[$tk][1];
			$cnt=$theSondaAvg[$tk][2];
			$x=$sum+$row['value'];
			$y=$cnt+1;
			$theSondaAvg[$tk] = array($tk,$x,$y);		
			
			if ($row['value'] > $nominal) $row['value'] = $nominal;
			$sum=$theSondaQoE[$tk][1];
			$cnt=$theSondaQoE[$tk][2];
			$x=$sum+$row['value'];
			$y=$cnt+1;
			$theSondaQoE[$tk] = array($tk,$x,$y);			
		}
	}	
	if (isset($theSondaAvg)) {
		aasort($theSondaAvg, 0);	
		$next = 0;
		$prev = 0;
    	
		foreach ($theSondaAvg as $tvalue) {				
			$next = $tvalue[0] + $offset;	
			if ($prev > 0 && $next > 0 && ($next - $prev) > ($divide*2)) {
				$dataAvg[] = array(($prev + 1000) * 1000,null);	
			}
			$dataAvg[] = array($next * 1000,round($tvalue[1]/$tvalue[2] / $div, 2));	
					
			$prev = $next;
		}		
		aasort($theSondaQoE, 0);	
		$next = 0;
		$prev = 0;
    	
		foreach ($theSondaQoE as $tvalue) {				
			$next = $tvalue[0] + $offset;	
			if ($prev > 0 && $next > 0 && ($next - $prev) > ($divide*2)) {
				$dataQoE[] = array(($prev + 1000) * 1000,null);	
			}
			$dataQoE[] = array($next * 1000,round($tvalue[1]/$tvalue[2] / $div, 2));	
					
			$prev = $next;
		}								
	}	
	 
     if (isset($dataAvg)) {
     	if (!isset($minClock))
		{
			$minClock=$dataAvg[0][0];
			$maxClock=$dataAvg[count($dataAvg)-1][0];
		}
		else {
			if ($dataAvg[0][0] < $minClock) $minClock=$dataAvg[0][0];
			if ($dataAvg[count($dataAvg)-2][0] > $maxClock) $maxClock = $dataAvg[count($dataAvg)-2][0];
		}	

        if ($_GET['qoe'] == 'Average On' || $_GET['qoe'] == 'Both')
		{
          $chart->series[]->name =$hostName . "-AVG";
          $chart->series[$index]->type ='spline';
          $chart->series[$index]->dashStyle = 'spline';
		  $chart->series[$index]->data=$dataAvg;
          $chart->series[$index]->color= getColor($colorIndex);
		  $chart->series[$index]->lineWidth = 1;
		  $chart->series[$index]->marker->radius = 2;
          $index++;
		}
		if ($_GET['qoe'] == 'QoE On' || $_GET['qoe'] == 'Both')
		{
		  $chart->series[]->name =$hostName . '-QoE';
		  $chart->series[$index]->type ='shortdot';
		  $chart->series[$index]->color= getColor($colorIndex);
		  $chart->series[$index]->data=$dataQoE;
		  $chart->series[$index]->lineWidth = 1;
		  $chart->series[$index]->marker->radius = 2;
          $index++;
		}
        $colorIndex++;
     }
  }
  $title='Sondas for "' . $ldata['item'] . '" on plan "' . $ldata['plan'] . '"';
  $subtitle='From ' . $ldata['from']  . ' to ' . $ldata['to'];
  $chart=chartHeader($chart, $id_chart, $title, $subtitle, $ldata['unit'], null);
  if (!isset($minClock)) {
  	$minClock=0;
	  $maxClock=0;
  }
  $chart->xAxis->type = 'datetime';
  $chart->tooltip->formatter = new HighchartJsExpr("
            function() {
                var s = '<b>'+ Highcharts.dateFormat('%e. %b %H:%M',this.x) +'</b>';    
                $.each(this.points, function(i, point) {
                    s += '<br/><span style=\"color:'+point.series.color+'\">'+ point.series.name +': '+ point.y +' " .  $ldata['unit'] . "</span><b>';
                });
                return s;
            }");
  echo "\n".'<script> function redraw() { chart1.xAxis[0].setExtremes('.$minClock.', '. (round($maxClock/(3600*24),0)*3600*24+$divide*1000) .');}  </script>';
  //var_dump($chart->chart);
  return $chart;
}
?>