<?php

use Ghunti\HighchartsPHP\HighchartJsExpr;

function GraphBar($chart,$id_chart,$id_plan,$id_item,$tag,$from)
{
  $offset=getOffset();
  setTimezone();
  
  $dt=getDates($from);
    
  $ldata=loadRAWData($from, $id_plan, $id_item);
  $unit=$ldata['unit'];
  $div=$ldata['div'];
  $percentile=calculatePercentile($ldata['allData']);
  if (isset($ldata['hostList']))			
  foreach ($ldata['hostList'] as $host) {
	$nacD=$host['nacD'];
	$nacU=$host['nacU'];
	$critical=$host['critical'];
	$warning=$host['warning'];
	$nominal=$host['nominal'];
	if ($nominal == -1) $nominal = $nacD;
	if ($nominal == -2) $nominal = $nacU;

	$id_host=$host['id_host'];
	if (isset($ldata['historyList'][$id_host]))
	foreach ($ldata['historyList'][$id_host] as $key=>$row) {
		$tk=date('Y/m/d',$row['clock']);
		if (!isset($weekAvgPeak[$tk])) $weekAvgPeak[$tk] = array($tk,0,0);
		if (!isset($weekAvgOffPeak[$tk])) $weekAvgOffPeak[$tk] = array($tk,0,0);
		if (!isset($weekQoEPeak[$tk])) $weekQoEPeak[$tk] = array($tk,0,0);
		if (!isset($weekQoEOffPeak[$tk])) $weekQoEOffPeak[$tk] = array($tk,0,0);
		$skip=false;
		if (isset($_SESSION['filter']['P95']) && $row['value'] > $percentile['P95']) $skip=true; 
		if (isset($_SESSION['filter']['P5']) && $row['value'] < $percentile['P5']) $skip=true; 
		
		if (!$skip)	{
			if ($row['t'] == 'P') {
				$sum=$weekAvgPeak[$tk][1];
				$cnt=$weekAvgPeak[$tk][2];
				$x=$sum+$row['value'];
				$y=$cnt+1;
				$weekAvgPeak[$tk] = array($tk,$x,$y);	
			}
			if ($row['t'] == 'O') {
				$sum=$weekAvgOffPeak[$tk][1];
				$cnt=$weekAvgOffPeak[$tk][2];
				$x=$sum+$row['value'];
				$y=$cnt+1;
				$weekAvgOffPeak[$tk] = array($tk,$x,$y);	
			}
		
			if ($row['value'] > $nacD) $row['value'] = $nominal;
			if ($row['t'] == 'P') {
				$sum=$weekQoEPeak[$tk][1];
				$cnt=$weekQoEPeak[$tk][2];
				$x=$sum+$row['value'];
				$y=$cnt+1;
				$weekQoEPeak[$tk] = array($tk,$x,$y);	
			}
			if ($row['t'] == 'O') {	
				$sum=$weekQoEOffPeak[$tk][1];
				$cnt=$weekQoEOffPeak[$tk][2];
				$x=$sum+$row['value'];
				$y=$cnt+1;
				$weekQoEOffPeak[$tk] = array($tk,$x,$y);		
			}
		}
  	}
  } 

 if (isset($weekQoEPeak)) {
	aasort($weekQoEPeak, 0);	
	aasort($weekQoEOffPeak, 0);
	aasort($weekAvgPeak, 0);
	aasort($weekAvgOffPeak, 0);
    
	$h=-100;
	foreach ($weekQoEPeak as $tvalue) {
		$key = $tvalue[0];
		if ($weekQoEPeak[$key][2] >0) 
			$dataQoEPeak[]  = ColorBar($h,$nominal,$warning,$critical,$weekQoEPeak[$key][1]/$weekQoEPeak[$key][2],$div);	
		else 
			$dataQoEPeak[]  = ColorBar($h,$nominal,$warning,$critical,0,$div);		  // ColorBar($x,$nominal,$warning,$critical,$value,$div)	
		if ($weekQoEOffPeak[$key][2] >0) 
			$dataQoEOffPeak[] = ColorBar($h,$nominal,$warning,$critical,$weekQoEOffPeak[$key][1]/$weekQoEOffPeak[$key][2],$div);
		else
			$dataQoEOffPeak[] = ColorBar($h,$nominal,$warning,$critical,0,$div);
		if ($weekAvgPeak[$key][2] >0) 
			$dataAvgPeak[]= array('x'=>$h,'y'=>round($weekAvgPeak[$key][1]/$weekAvgPeak[$key][2]/$div, 2),'color'=>'silver');	
		else 
			$dataAvgPeak[]= array('x'=>$h,'y'=>0,'color'=>'silver');	
		if ($weekAvgOffPeak[$key][2] >0) 
			$dataAvgOffPeak[] = array('x'=>$h,'y'=>round($weekAvgOffPeak[$key][1]/$weekAvgOffPeak[$key][2]/$div, 2),'color'=>'silver');		
		else
			$dataAvgOffPeak[] = array('x'=>$h,'y'=>0,'color'=>'silver');	
		$dataNominal[]    = array('x'=>$h,'y'=>round($nominal/$div,2));
		$dataWarning[]    = array('x'=>$h,'y'=>round($nominal/$div*$warning/100,2));
		$dataCritical[]   = array('x'=>$h,'y'=>round($nominal/$div*$critical/100,2));
		$category[$h] = $key;	
		$h++;
	}

	for ($i=0;$i<count($dataAvgPeak);$i++) {
		$dataAvgPeak[$i]['y']    -=$dataQoEPeak[$i]['y'];
		$dataAvgOffPeak[$i]['y'] -=$dataQoEOffPeak[$i]['y'];
	}
  }	
  if (!isset($category)) {
  	$category=null;
	$dataAvgPeak=null;
	$dataQoEPeak=null;
	$dataAvgOffPeak=null;
	$dataQoEOffPeak=null;
	$dataNominal=null;
	$dataWarning=null;
	$dataCritical=null;
	$warning=0;
	$critical=0;
  }
  $title='Average by Hour for "' . $ldata['item'] . '" on plan "' . $ldata['plan'] . '"';
  $subtitle='From ' . $ldata['from']  . ' to ' . $ldata['to'];
  $chart=chartHeader($chart, $id_chart, $title, $subtitle, $ldata['unit'], $category);
  $fontSize='10px';
  
  if (count($category) > 15) {
  	$fontSize='6px';
  	$chart->plotOptions->series->pointPadding=-1/300;
  }  
  $chart -> xAxis->type = 'datetime';
  $chart->xAxis->labels->rotation = -90;
  $chart->xAxis->labels->style->color = "#000000";
  $chart->legend->enabled = false;
  
 /*
  $chart->option3d->enabled = true;
  $chart->option3d->alpha = 45;
  $chart->option3d->beta = 0;
  $chart->option3d->depth = 50;
  $chart->option3d->viewDistance = 25;
  $chart->plotOptions->column->depth = 25;
  $chart->option3d->beta = 0;
 
*/
  $chart -> series[0] -> type = 'column';
  $chart -> series[0] -> stack = 0;
  $chart -> series[0] -> data = $dataAvgPeak;
  $chart -> series[0] -> tooltip -> valueSuffix = ' ' . $unit;
  $chart -> series[0] -> name = 'Sample Average PEAK';
  $chart -> series[0] -> dataLabels -> enabled = true;
  $chart -> series[0] -> dataLabels -> rotation = 0;
  $chart -> series[0] -> dataLabels -> rotation = 0;
  $chart -> series[0] -> dataLabels -> crop = false;
  $chart -> series[0] -> dataLabels -> y = -10;
  $chart -> series[0] -> dataLabels -> zIndexy = 12;
  $chart -> series[0] -> dataLabels -> color = '#000000';
  $chart -> series[0] -> dataLabels -> style -> fontSize = $fontSize;
  $chart -> series[0] -> dataLabels -> style -> fontFamily = 'Verdana, sans-serif';
  $chart -> series[0] -> dataLabels -> formatter = new HighchartJsExpr("function() { return  Highcharts.numberFormat(this.point.stackTotal, 2, '.');}");

  $chart -> series[1] -> type = 'column';
  $chart -> series[1] -> stack = 0;
  $chart -> series[1] -> data = $dataQoEPeak;
  $chart -> series[1] -> name = 'Sample Average PEAK';
  $chart -> series[1] -> dataLabels -> enabled = true;
  $chart -> series[1] -> dataLabels -> crop = false;
  $chart -> series[1] -> dataLabels -> rotation = -90;
  $chart -> series[1] -> dataLabels -> align = 'left';
  $chart -> series[1] -> tooltip -> valueSuffix = ' ' . $unit;
  $chart -> series[1] -> dataLabels -> x = 2;
  $chart -> series[1] -> dataLabels -> color = '#FFFFFF';
  $chart -> series[1] -> dataLabels -> style -> fontSize = $fontSize;
  $chart -> series[1] -> dataLabels -> style -> fontFamily = 'Verdana, sans-serif';
  $chart -> series[1] -> dataLabels -> formatter = new HighchartJsExpr("function() { return 'PEAK: ' + Highcharts.numberFormat(this.y, 2, '.');}");

  $chart -> series[2] -> type = 'column';
  $chart -> series[2] -> stack = 1;
  $chart -> series[2] -> data = $dataAvgOffPeak;
  $chart -> series[2] -> name = 'Real Average OFFPEAK';
  $chart -> series[2] -> tooltip -> valueSuffix = ' ' . $unit;
  $chart -> series[2] -> dataLabels -> crop = false;
  $chart -> series[2] -> dataLabels -> enabled = true;
  $chart -> series[2] -> dataLabels -> rotation = 0;
  $chart -> series[2] -> dataLabels -> align = 'center';
  $chart -> series[2] -> dataLabels -> y = -10;
  $chart -> series[2] -> dataLabels -> zIndexy = 12;
  $chart -> series[2] -> dataLabels -> color = '#000000';
  $chart -> series[2] -> dataLabels -> style -> fontSize = $fontSize;
  $chart -> series[2] -> dataLabels -> style -> fontFamily = 'Verdana, sans-serif';
  $chart -> series[2] -> dataLabels -> formatter = new HighchartJsExpr("function() { return Highcharts.numberFormat(this.point.stackTotal, 2, '.');}");

  $chart -> series[3] -> type = 'column';
  $chart -> series[3] -> stack = 1;
  $chart -> series[3] -> data = $dataQoEOffPeak;
  $chart -> series[3] -> tooltip -> valueSuffix = ' ' . $unit;
  $chart -> series[3] -> name = 'Real Average OFFPEAK';
  $chart -> series[3] -> dataLabels -> crop = false;
  $chart -> series[3] -> dataLabels -> enabled = true;
  $chart -> series[3] -> dataLabels -> rotation = -90;
  $chart -> series[3] -> dataLabels -> align = 'left';
  $chart -> series[3] -> dataLabels -> x = 2;
  $chart -> series[3] -> dataLabels -> color = '#FFFFFF';
  $chart -> series[3] -> dataLabels -> style -> fontSize = $fontSize;
  $chart -> series[3] -> dataLabels -> style -> fontFamily = 'Verdana, sans-serif';
  $chart -> series[3] -> dataLabels -> formatter = new HighchartJsExpr("function() { return 'OFFPEAK: ' +Highcharts.numberFormat(this.y, 2, '.');}");

  $chart -> series[4] -> type = 'spline';
  $chart -> series[4] -> data = $dataNominal;
  $chart -> series[4] -> stack = 2;
  $chart -> series[4] -> tooltip -> valueSuffix = ' ' . $unit;
  $chart -> series[4] -> dataLabels -> crop = false;
  $chart -> series[4] -> name = 'Nominal PEAK';
  $chart -> series[4] -> dataLabels -> enabled = false;
  $chart -> series[4] -> dataLabels -> rotation = -90;
  $chart -> series[4] -> dataLabels -> align = 'right';
  $chart -> series[4] -> dataLabels -> x = 4;
  $chart -> series[4] -> dataLabels -> y = 10;
  $chart -> series[4] -> dataLabels -> color = '#FFFFFF';
  $chart -> series[4] -> dataLabels -> style -> fontSize =$fontSize;
  $chart -> series[4] -> dataLabels -> style -> fontFamily = 'Verdana, sans-serif';
  $chart -> series[4] -> lineWidth = 2;
  $chart -> series[4] -> color = 'green';
  $chart -> series[4] -> marker -> fillColor = 'green';
  $chart -> series[4] -> marker -> radius = 2;
  $chart -> series[4] -> dataLabels -> formatter = new HighchartJsExpr("function() { return Highcharts.numberFormat(this.y, 2, '.');}");

  $chart -> series[5] -> type = 'spline';
  $chart -> series[5] -> data = $dataWarning;
  $chart -> series[5] -> stack = 2;
  $chart -> series[5] -> tooltip -> valueSuffix = ' ' . $unit;
  $chart -> series[5] -> dataLabels -> crop = false;
  $chart -> series[5] -> name = $warning . '% of the plan';
  $chart -> series[5] -> dataLabels -> enabled = false;
  $chart -> series[5] -> dataLabels -> rotation = -90;
  $chart -> series[5] -> dataLabels -> align = 'right';
  $chart -> series[5] -> dataLabels -> x = 4;
  $chart -> series[5] -> dataLabels -> y = 10;
  $chart -> series[5] -> dataLabels -> color = '#FFFFFF';
  $chart -> series[5] -> dataLabels -> style -> fontSize = $fontSize;
  $chart -> series[5] -> dataLabels -> style -> fontFamily = 'Verdana, sans-serif';
  $chart -> series[5] -> lineWidth = 2;
  $chart -> series[5] -> color = '#F79E03';
  $chart -> series[5] -> marker -> fillColor = '#F79E03';
  $chart -> series[5] -> marker -> radius = 2;
  $chart -> series[5] -> dataLabels -> formatter = new HighchartJsExpr("function() { return Highcharts.numberFormat(this.y, 2, '.');}");

  $chart -> series[6] -> type = 'spline';
  $chart -> series[6] -> data = $dataCritical;
  $chart -> series[6] -> stack = 2;
  $chart -> series[6] -> tooltip -> valueSuffix = ' ' . $unit;
  $chart -> series[6] -> dataLabels -> crop = false;
  $chart -> series[6] -> name = $critical . '% of the plan';
  $chart -> series[6] -> dataLabels -> enabled = false;
  $chart -> series[6] -> dataLabels -> rotation = -90;
  $chart -> series[6] -> dataLabels -> align = 'right';
  $chart -> series[6] -> dataLabels -> x = 4;
  $chart -> series[6] -> dataLabels -> y = 10;
  $chart -> series[6] -> dataLabels -> color = '#FFFFFF';
  $chart -> series[6] -> dataLabels -> style -> fontSize = $fontSize;
  $chart -> series[6] -> dataLabels -> style -> fontFamily = 'Verdana, sans-serif';
  $chart -> series[6] -> lineWidth = 2;
  $chart -> series[6] -> color = 'red';
  $chart -> series[6] -> marker -> fillColor = 'red';
  $chart -> series[6] -> marker -> radius = 2;
  $chart -> series[6] -> dataLabels -> formatter = new HighchartJsExpr("function() { return Highcharts.numberFormat(this.y, 2, '.');}");

  return $chart;
}
?>