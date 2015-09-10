<?php

use Ghunti\HighchartsPHP\HighchartJsExpr;

function GraphPlan($chart, $id_chart, $id_item, $from, $avg, $tag) {
	//$cache = phpFastCache();

	$dt = getDates($from);
	$item=getItem($id_item);

	$index = 0;
	$thezero = '-1';
	$average = getAverage($avg);
	$format = $average[0];
	$divide = $average[1];
	
	$color = array(
		'#89A54E',
		'none',
		'#4572A7',
		'none',
		'#AA4643',
		'none',
		'#808080',
		'none',
		'darksilver',
		'none',
		'black',
		'none',
		'magenta',
		'none',
		'lighrblue',
		'none'
	);

	$offset = 0;
	$colorIndex = 0;
	$cacheAge=60*30;  // 30 minutes
	if ($divide == 60 * 60 * 6) $offset = -60*60*2;
	if ($divide == 60 * 60 * 12) $offset = +60*60*4;
	if ($divide == 60 * 60 * 24) $offset = +60*60*4;
	
	$planList=readPlan();
	foreach ($planList as $plan) {
		$id_plan = $plan['id_plan'];
		unset($data2);
		unset($data3);
		unset($dataAvg);
		unset($dataQoE);
		unset($planAvg);
		unset($planQoE);
		unset($percentile);
		unset($ldata);
	
		$ldata=loadRAWData($from, $id_plan, $id_item);

		$unit=$ldata['unit'];
		$div=$ldata['div'];
		$percentile=calculatePercentile($ldata['allData']);
		if (isset($ldata['hostList']))
		foreach ($ldata['hostList'] as $host) {
			$id_host=$host['id_host'];									
		 	$nacD=$host['nacD'];
			$nacU=$host['nacU'];
			$critical=$host['critical'];
			$warning=$host['warning'];
			$nominal=$host['nominal'];
			if ($warning >100) $dir=1; else $dir=0;
			if ($nominal == -1) $nominal = $nacD;
			if ($nominal == -2) $nominal = $nacU;
			if (isset($ldata['historyList'][$id_host]))
			foreach ($ldata['historyList'][$id_host] as $key=>$row) {
				$tk=round($row['clock']/$divide,0)*$divide;
				$skip=false;
				if (isset($_SESSION['filter']['P95']) && $row['value'] > $percentile['P95']) $skip=true; 
				if (isset($_SESSION['filter']['P5']) && $row['value'] < $percentile['P5']) $skip=true; 
				if (!$skip) {
					if (!isset($planAvg[$tk]))  $planAvg[$tk] = array($tk,0,0);
					if (!isset($planQoE[$tk]))  $planQoE[$tk] = array($tk,0,0);
					$sum=$planAvg[$tk][1];
					$cnt=$planAvg[$tk][2];
					$x=$sum+$row['value'];
					$y=$cnt+1;
					$planAvg[$tk] = array($tk,$x,$y);	
								
					if ($row['value'] > $nacD) $row['value'] = $nacD;
					$sum=$planQoE[$tk][1];
					$cnt=$planQoE[$tk][2];
					$x=$sum+$row['value'];
					$y=$cnt+1;
					$planQoE[$tk] = array($tk,$x,$y);	
				}
			}	
		}	
		if (isset($planAvg)) {
			$next = 0;
			$prev = 0;
			aasort($planAvg, 0);	
			aasort($planQoE, 0);
			foreach ($planAvg as $tvalue) {	
				$next = $planAvg[$tvalue[0]][0] + $offset;	
				if ($prev > 0 && $next > 0 && ($next - $prev) > ($divide*2)) {
					$dataAvg[] = array(($prev + 1000) * 1000,null);	
					$dataQoE[] = array(($prev + 1000) * 1000,null);	
				}
				$dataAvg[] = array($planAvg[$tvalue[0]][0] * 1000,round($planAvg[$tvalue[0]][1]/$planAvg[$tvalue[0]][2] / $div, 2));	
				$dataQoE[] = array($planQoE[$tvalue[0]][0] * 1000,round($planQoE[$tvalue[0]][1]/$planQoE[$tvalue[0]][2]/ $div, 2));	
				$prev = $next;						
			}
		}	
		if (isset($dataAvg)) {	
			if (!isset($minClock)) {
				$minClock=$dataAvg[0][0];
				$maxClock=$dataAvg[count($dataAvg)-2][0];
			}
			else {
				if ($dataAvg[0][0] < $minClock) $minClock=$dataAvg[0][0];
				if ($dataAvg[count($dataAvg)-2][0] > $maxClock) $maxClock = $dataAvg[count($dataAvg)-2][0];
			}	
			if ($_GET['qoe'] == 'Average On' || $_GET['qoe'] == 'Both')  {
				$chart->series[]->name = $ldata['plan'] . '-AVG';
				$chart->series[$index]->type = 'spline';			
				$chart->series[$index]->color = getColor($colorIndex);
				$chart->series[$index]->data = $dataAvg;
				$chart->series[$index]->lineWidth = 1;
				$chart->series[$index]->marker->radius = 2;
				$index++;
			}
			if ($_GET['qoe'] == 'QoE On' || $_GET['qoe'] == 'Both')  {
				$chart->series[]->name = $ldata['plan'] . '-QoE';
				$chart->series[$index]->type = 'spline';
				$chart->series[$index]->color = getColor($colorIndex);
				$chart->series[$index]->dashStyle = 'shortdot';
				$chart->series[$index]->data = $dataQoE;
				$chart->series[$index]->lineWidth = 1;
			    $chart->series[$index]->marker->radius = 2;
				$index++;
			}
			$colorIndex++;
		}
	}
	
	$title='Average by Plan for "' . $item;
  	$subtitle='From ' . $ldata['from']  . ' to ' . $ldata['to'];
  	$chart=chartHeader($chart, $id_chart,$title, $subtitle, $unit, null);
  	$chart->xAxis->type = 'datetime';
	if (!isset($minClock)) {
		$minclock=0;
		$maxClock=0;
	}
	$chart->tooltip->formatter = new HighchartJsExpr("
            function() {
                var s = '<b>'+ Highcharts.dateFormat('%e. %b %H:%M',this.x) + '</b>';      
                $.each(this.points, function(i, point) {
                    s += '<br/><span style=\"color:'+point.series.color+'\">'+ point.series.name +': '+ point.y +' " . $unit . "</span><b>';
                });
                return s;
            }");
	echo "\n" . '<script> function redraw() { chart1.xAxis[0].setExtremes(' . round($minClock/(60*60),0)*60*60 . ', ' . round(($maxClock+60*60*4*1000)/(60*60),0)*60*60  . ');}  </script>';

	return $chart;
}
?>