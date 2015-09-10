<?php

use Ghunti\HighchartsPHP\HighchartJsExpr;

function GraphHour($chart,$id_chart,$id_plan,$id_item, $tag, $from, &$hgram,&$low,&$mid,&$max,&$dir)
{    
  	$offset=getOffset();
  	setTimezone();
  
  	$ldata=loadRAWData($from, $id_plan, $id_item);
	$unit=$ldata['unit'];
	$div=$ldata['div'];
	$percentile=calculatePercentile($ldata['allData']);
	$low=0;
  	$mid=0;
  	$max=0;
	if(isset($ldata['hostList']))
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
			$tk=date('H',$row['clock'])*1;
			if (!isset($hourAvg[$tk])) $hourAvg[$tk] = array($tk,0,0);
			if (!isset($hourQoE[$tk])) $hourQoE[$tk] = array($tk,0,0);
			$skip=false;
			if (isset($_SESSION['filter']['P95']) && $row['value'] > $percentile['P95']) $skip=true; 
			if (isset($_SESSION['filter']['P5']) && $row['value'] < $percentile['P5']) $skip=true; 
			if (!$skip) {
				$filteredData[]=array(round($row['value']/$div,2));
				if ($warning <=100) {
		 			if ($row['value'] >= $nominal*$warning/100) $max++;
					if ($row['value'] <  $nominal*$warning/100 && $row['value'] >= $nominal*$critical/100) $mid++;
					if ($row['value'] <  $nominal*$critical/100) $low++;
				}
				else {
		 			if ($row['value'] <= $nominal*$warning/100) $max++;
					if ($row['value'] >  $nominal*$warning/100 && $row['value'] <= $nominal*$critical/100) $mid++;
					if ($row['value'] >  $nominal*$critical/100) $low++;
				}
				$sum=$hourAvg[$tk][1];
				$cnt=$hourAvg[$tk][2];
				$x=$sum+$row['value'];
				$y=$cnt+1;
				$hourAvg[$tk] = array($tk,$x,$y);		
							
				if ($row['value'] > $nacD) $row['value'] = $nacD;
				$sum=$hourQoE[$tk][1];
				$cnt=$hourQoE[$tk][2];
				$x=$sum+$row['value'];
				$y=$cnt+1;
				$hourQoE[$tk] = array($tk,$x,$y);
			}
		}	 		
 	}

	$offset=0;
 	unset($dataQoE);
 	unset($dataAvg);
	unset($data3);
 	unset($data4);
 	unset($data5);

 	if (isset($hourQoE)) {
		aasort($hourQoE, 0);	
		unset($k);
		for ($h=0;$h<24;$h++) {
			if (isset($hourQoE[$h])) {
	 			$dataQoE[] = ColorBar($h,$nominal,$warning,$critical,$hourQoE[$h][1]/$hourQoE[$h][2],$div);	
				$data3[] = array('x'=>$h,'y'=>round($nominal/$div,2));
				$data4[] = array('x'=>$h,'y'=>round($nominal/$div*$warning/100,2));
				$data5[] = array('x'=>$h,'y'=>round($nominal/$div*$critical/100,2));
			}
			else {
				$dataQoE[] = null;	
				$data3[] = array('x'=>$h,'y'=>round($nominal/$div,2));
				$data4[] = array('x'=>$h,'y'=>round($nominal/$div*$warning/100,2));
				$data5[] = array('x'=>$h,'y'=>round($nominal/$div*$critical/100,2));
			}
		}

		aasort($hourAvg, 0);
		for ($h=0;$h<24;$h++) {
			if (isset($hourQoE[$h])) {
				$dataAvg[] = array('x'=> $h,'y'=>round($hourQoE[$h][1]/$hourQoE[$h][2]/$div, 2),'color'=>'silver');	
				$category[] = str_pad($h . ":00", 5, "0", STR_PAD_LEFT);				
			}
			else {
				$dataAvg[] = null;
				$category[] = str_pad($h . ":00", 5, "0", STR_PAD_LEFT);								
			}
		}
		for ($i=0;$i<count($dataAvg);$i++) {
			$dataAvg[$i]['y'] =round($dataAvg[$i]['y'] - $dataQoE[$i]['y'],2);
		}
  	}	
	if (!isset($filteredData)) {
		$filteredData=null;
		$nominal=0;
		$category=null;
		$data3=null;
		$data4=null;
		$data5=null;
		$warning=0;
		$critical=0;
		$dataAvg=null;
		$dataQoE=null;
	}
	$hgram=createHistogram($filteredData, 0, round($nominal/$div*2,0), round($nominal/$div/6,0)+1,0);
	
	$title='Average by Hour for "' . $ldata['item'] . '" on plan "' . $ldata['plan'] . '"';
  	$subtitle='From ' . $ldata['from']  . ' to ' . $ldata['to'];
  	$chart=chartHeader($chart, $id_chart, $title, $subtitle, $ldata['unit'], $category);
  	
    $chart->plotOptions->series->pointPadding=-0.2; 
    if ($ldata['peak'] > $ldata['offpeak'])
		$chart->xAxis->plotBands=array(array('label'=>array('text'=>'Peak Hour','x'=>2,'style'=>array('fontSize'=>'8px','color'=>'red')),'from'=>$ldata['peak']-0.5,'to'=>24-1.5,'color'=>'#FCFFC5'),
									   array('label'=>array('text'=>'Peak Hour','x'=>2,'style'=>array('fontSize'=>'8px','color'=>'red')),'from'=>-0.5,'to'=>$ldata['offpeak']+1.5,'color'=>'#FCFFC5'));
	else
		$chart->xAxis->plotBands=array('label'=>array('text'=>'Peak Hour','x'=>2,'style'=>array('fontSize'=>'8px','color'=>'red')),'from'=>$ldata['peak']-0.5,'to'=>$ldata['offpeak']+0.5,'color'=>'#FCFFC5');	
	$chart->series[0]->type ='column';
	$chart->title->style->fontSize='14px';
	$chart->subtitle->style->fontSize='10px';
	$chart->series[0]->data=$dataAvg;
	$chart->series[0]->stack =0;
	$chart->series[0]->tooltip->valueSuffix = ' ' . $ldata['unit'];
	$chart->series[0]->name = 'Sample average for ' . $ldata['plan'];
	$chart->series[0]->dataLabels->enabled = true;
	$chart->series[0]->dataLabels->crop = true;
	$chart->series[0]->dataLabels->overflow ='none';
	$chart->series[0]->dataLabels->align = 'center';
	$chart->series[0]->dataLabels->y = -8;
	$chart->series[0]->dataLabels->color = 'black'; //'#000000';
	$chart->series[0]->dataLabels->style->fontSize= '10px';
	$chart->series[0]->dataLabels->style->fontFamily='Verdana, sans-serif';
	$chart->series[0]->dataLabels->formatter = new HighchartJsExpr("function() { return Highcharts.numberFormat(this.point.stackTotal, 2, '.');}");
	
	$chart->series[1]->type ='column';
	$chart->series[1]->data=$dataQoE;
	$chart->series[1]->stack =0;
	$chart->series[1]->tooltip->valueSuffix = ' ' . $ldata['unit'];;
	$chart->series[1]->name = 'Real average for ' . $ldata['plan'];
	$chart->series[1]->dataLabels->enabled = true;
	$chart->series[1]->dataLabels->rotation = -90;
	$chart->series[1]->dataLabels->crop = false;
	$chart->series[1]->dataLabels->align = 'center';
	$chart->series[1]->dataLabels->color = '#FFFFFF';
	$chart->series[1]->dataLabels->style->fontSize= '13px';
	$chart->series[1]->dataLabels->style->fontFamily='Verdana, sans-serif';
	$chart->series[1]->dataLabels->formatter = new HighchartJsExpr("function() { return Highcharts.numberFormat(this.y, 2, '.');}");
	
	$chart->series[2]->type ='spline';
	$chart->series[2]->data=$data3;
	$chart->series[2]->stack =1;
	$chart->series[2]->tooltip->valueSuffix = ' ' . $ldata['unit'];;
	$chart->series[2]->name = 'Nominal at 100%';
	$chart->series[2]->dataLabels->crop = false;
	$chart->series[2]->dataLabels->enabled = false;
	$chart->series[2]->dataLabels->rotation = -90;
	$chart->series[2]->dataLabels->align = 'right';
	$chart->series[2]->dataLabels->x = 4;
	$chart->series[2]->dataLabels->y = 10;
	$chart->series[2]->dataLabels->color = '#FFFFFF';
	$chart->series[2]->dataLabels->style->fontSize= '13px';
	$chart->series[2]->dataLabels->style->fontFamily='Verdana, sans-serif';
	$chart->series[2]->lineWidth = 2;
	$chart->series[2]->color = 'green';
	$chart->series[2]->marker->fillColor = 'green';
	$chart->series[2]->marker->radius = 2;
	$chart->series[2]->dataLabels->formatter = new HighchartJsExpr("function() { return Highcharts.numberFormat(this.y, 2, '.');}");
	
	$chart->series[3]->type ='spline';
	$chart->series[3]->data=$data4;
	$chart->series[3]->stack =2;
	$chart->series[3]->tooltip->valueSuffix = ' ' . $ldata['unit'];;
	$chart->series[3]->dataLabels->crop = false;
	$chart->series[3]->name = 'Warning at ' . $warning . '% of the threashold';
	$chart->series[3]->dataLabels->enabled = false;
	$chart->series[3]->dataLabels->rotation = -90;
	$chart->series[3]->dataLabels->align = 'right';
	$chart->series[3]->dataLabels->x = 4;
	$chart->series[3]->dataLabels->y = 10;
	$chart->series[3]->dataLabels->color = '#FFFFFF';
	$chart->series[3]->dataLabels->style->fontSize= '10px';
	$chart->series[3]->dataLabels->style->fontFamily='Verdana, sans-serif';
	$chart->series[3]->lineWidth = 2;
	$chart->series[3]->color = '#F79E03';
	$chart->series[3]->marker->fillColor = '#F79E03';
	$chart->series[3]->marker->radius = 2;
	$chart->series[3]->dataLabels->formatter = new HighchartJsExpr("function() { return Highcharts.numberFormat(this.y, 2, '.');}");
	
	$chart->series[4]->type ='line';
	$chart->series[4]->data=$data5;
	$chart->series[4]->stack =3;
	$chart->series[4]->tooltip->valueSuffix = ' ' . $ldata['unit'];;
	$chart->series[4]->dataLabels->crop = false;
	$chart->series[4]->name = 'Critical at ' . $critical . '% of the threashold';
	$chart->series[4]->dataLabels->enabled = false;
	$chart->series[4]->dataLabels->rotation = -90;
	$chart->series[4]->dataLabels->align = 'right';
	$chart->series[4]->dataLabels->x = 4;
	$chart->series[4]->dataLabels->y = 10;
	$chart->series[4]->dataLabels->color = '#FFFFFF';
	$chart->series[4]->dataLabels->style->fontSize= '10px';
	$chart->series[4]->dataLabels->style->fontFamily='Verdana, sans-serif';
	$chart->series[4]->lineWidth = 2;
	$chart->series[4]->marker->radius = 2;
	$chart->series[4]->color = 'red';
	$chart->series[4]->marker->fillColor = 'red';
	$chart->series[4]->dataLabels->formatter = new HighchartJsExpr("function() { return Highcharts.numberFormat(this.y, 2, '.');}"); 
    
  	return $chart;
}

function GraphHistogram($chart, $id_chart, $id_plan, $id_item, $tag, $from, $hgram)
{
  	$dt=getDates($from);
  	$from=$dt[0];
  	$to=$dt[1];
	$plan=getPlan($id_plan);
	$item=getItem($id_item);
	$unit=getUnit($id_item);
	if (strtolower($unit) == 'bps')	$unit="Mbps";
	$index=0;
	if (isset($hgram))
	foreach($hgram as $key => $val) {
		$category[$index] = $key . ' ' . $unit;
		$data0[$index] = $val;  // ColorBar($h,$nominal,$warning,$critical,$weekQoEPeak[$key][1]/$weekQoEPeak[$key][2],$div);
		$index++;
	}
	if (!isset($category)) {
		$category=null;
		$data0=null;
	}
	$title='Histogram for "' . $item. '" on plan "' . $plan . '"';
  	$subtitle='From ' . $from  . ' to ' . $to;
  	$chart=chartHeader($chart, $id_chart, $title, $subtitle, 'Quantity', $category);
	$chart->xAxis->plotBands=array(array('label'=>array('text'=>'No data should be here','rotation'=>-90,'y'=>141,'x'=>-10,'style'=>array('fontSize'=>'10x','color'=>'red')),'from'=>-0.5,'to'=>0.5,'color'=>'#FFA6A6'),
							       array('label'=>array('text'=>'No data should be here','rotation'=>-90,'y'=>141,'x'=>+10,'style'=>array('fontSize'=>'10x','color'=>'red')),'from'=>count($hgram)-1.5,'to'=>count($hgram),'color'=>'#FFA6A6'));
	
	$chart->legend->enabled = false;
    $chart->plotOptions->series->pointPadding=-0.333333;
	
    $chart->series[0]->type ='areaspline';
    $chart->series[0]->data=$data0;
    $chart->series[0]->stack =0;
	$chart->series[0]->tooltip->valueSuffix = ' time(s)';
    $chart->series[0]->name = 'Number of occurencies for ' . $plan;
    $chart->series[0]->dataLabels->enabled = true;
    $chart->series[0]->dataLabels->crop = true;
    $chart->series[0]->dataLabels->align = 'center';
    $chart->series[0]->dataLabels->y = -6;
    $chart->series[0]->dataLabels->color = 'black'; //'#000000';
    $chart->series[0]->dataLabels->style->fontSize= '10px';
    $chart->series[0]->dataLabels->style->fontFamily='Verdana, sans-serif';
    $chart->series[0]->dataLabels->formatter = new HighchartJsExpr("function() { return Highcharts.numberFormat(this.y, 0, '.');}");
        
    $chart->series[1]->type ='spline';
    $chart->series[1]->data=$data0;
    $chart->series[1]->stack =0;
	$chart->series[1]->tooltip->valueSuffix = ' time(s)';
    $chart->series[1]->name = 'Number of occurencies for ' . $plan;
    $chart->series[1]->dataLabels->enabled = false;
    $chart->series[1]->dataLabels->rotation = -90;
    $chart->series[1]->dataLabels->crop = false;
    $chart->series[1]->dataLabels->align = 'right';
    $chart->series[1]->dataLabels->x = 4;
    $chart->series[1]->dataLabels->y = 3;
    $chart->series[1]->dataLabels->color = '#FFFFFF';
    $chart->series[1]->dataLabels->style->fontSize= '13px';
    $chart->series[1]->dataLabels->style->fontFamily='Verdana, sans-serif';
	
    return $chart;
}

function GraphPie($chart,$id_chart,$id_plan,$id_item, $tag, $from, $low,$mid,$max,$dir)
{
  	$dt=getDates($from);
	$plan=getPlan($id_plan);
	$item=getItem($id_item);
	$unit=getUnit($id_item);
	$from=$dt[0];
  	$to=$dt[1];
  	
	$sum=$low+$mid+$max;
	if ($sum==0) $sum=999999999999999999;
	
	if ($dir==0) {
		$data0[]=array('name'=>"Not acceptable measures",'y'=>round($low/$sum*100,2),'color'=>'red');
		$data0[]=array('name'=>"Below acceptable measures",'y'=>round($mid/$sum*100,2),'color'=>'#F79E03');
		$data0[]=array('name'=>"Acceptable measures",'y'=>round($max/$sum*100,2),'color'=>'green');
	}
	else {
		$data0[]=array('name'=>"Not acceptable measures",'y'=>round($low/$sum*100,2),'color'=>'red');
		$data0[]=array('name'=>"Below acceptable measures",'y'=>round($mid/$sum*100,2),'color'=>'#F79E03');
		$data0[]=array('name'=>"Acceptable measures",'y'=>round($max/$sum*100,2),'color'=>'green');		
	}	
	$title='% de results for "' . $item. '" on plan "' . $plan . '"';
  	$subtitle='From ' . $from  . ' to ' . $to;
  	$chart=chartHeader($chart, $id_chart, $title, $subtitle, 'Quantity', null);
		
    $chart->plotOptions->series->pointPadding=-0.333333;
    $chart->series[0]->type ='pie';
    $chart->series[0]->data=$data0;
    $chart->series[0]->tooltip->valueSuffix = '%';
    $chart->series[0]->name = 'Number of occurencies';
    $chart->series[0]->dataLabels->enabled = true;
    $chart->series[0]->dataLabels->crop = false;
    $chart->series[0]->dataLabels->align = 'center';
    $chart->series[0]->dataLabels->y = -6;
    $chart->series[0]->dataLabels->color = 'black'; //'#000000';
    $chart->series[0]->dataLabels->style->fontSize= '13px';
    $chart->series[0]->dataLabels->style->fontFamily='Verdana, sans-serif';
    $chart->series[0]->dataLabels->formatter = new HighchartJsExpr("function() { return Highcharts.numberFormat(this.y, 2, '.') + '%';}");
    
    return $chart;
}
?>