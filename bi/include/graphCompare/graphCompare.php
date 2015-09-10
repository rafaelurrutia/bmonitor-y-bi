<?php
use Ghunti\HighchartsPHP\Highchart;
use Ghunti\HighchartsPHP\HighchartJsExpr;
use Ghunti\HighchartsPHP\HighchartOptionRenderer;
use Ghunti\HighchartsPHP\HighchartOption;

function graphCompare($chart, $item, $min, $max, $ch) {
	echo '<div class="col-xs-2"><select class="form-control" name="' . $item . '" id="' . $item . '">';
	if (!isset($_POST[$item])) {
		$_POST[$item] = '';
		echo '<option selected value="Seleccione">Select</option>';
	} else {
		echo '<option value="Seleccione">Select</option>';
	}
	$itemsSelected = '';
	$monitors=readMonitors();
	foreach ($monitors as $items) {
		if (isset($_POST) && $_POST[$item] == $items['id_item']) {
			$itemsSelected = $items['descriptionLong'];
			echo '<option selected value="' . $items['id_item'] . '">' . $items['descriptionLong'] . '</option>';
		} else {
			echo '<option value="' . $items['id_item'] . '">' . $items['descriptionLong'] . '</option>';
		}
		$itemsArry[$items['id_item']] = array("unit" => $items['unit'], "name" =>  $items['descriptionLong']);
	}
	echo '</select></div>';
	
	$index = 0;

	if (isset($_POST['date1'])) $days = Days($_POST['date1'], $_POST['date2']);
	if (!isset($_POST['avg'])) $_POST['avg'] = '4 Hours';
	$divide=getAverage($_POST['avg']);
	$divide=$divide[1];
	/*
	$days = 1;
	$days = Days($_POST['date1'], $_POST['date2']);
	
	if ($days > 7 * 1) $format = '%Y/%m/%d %H:00:00';
	if ($days > 7 * 2) $divide = 3600 * 4;
	if ($days > 7 * 4) $divide = 3600 * 6;
	if ($days > 7 * 5) $format = '%Y/%m/%d';
	if ($days > 7 * 8) $divide = 3600 * 24 * 7;
	if ($days > 7 * 16) $format = '%Y/%m';
	 */
	$col = 0;
	$timeStart  = strtotime($_POST['date1'].' 00:00:00');
	$timeFinish = strtotime($_POST['date2'].' 23:59:59');
	
	$hosts = readHosts(0);
	
	foreach ($hosts as $r) {
		$unit = getUnit($_POST[$item]);
		$div = 1;
		if ($unit == 'bps') {
			$div=1024;  ///BUGS BGS009
			$unit = 'Mbps';
		}
		$sonda = $r['host'];
		$id_host = $r['id_host'];
		$id_item = $_POST[$item];
		if ($id_item > 0) {
			$rawData=loadRAWData($_POST['date1'], 0, $id_item, $_POST['date2'], $id_host);
			$history=$rawData['historyList'][$id_host];
			unset($finalData);
			unset($data2);
			if (isset($history)) {
				foreach ($history as $ddata) {
					$tk=round($ddata['clock']/$divide,0)*$divide;
					if (!isset($finalData[$tk])) $finalData[$tk] = array('clock'=>$tk,'sum'=>0,'cnt'=>0);
					$sum=$finalData[$tk]['sum'];
					$cnt=$finalData[$tk]['cnt'];
					$x=$sum+$ddata['value'];
					$y=$cnt+1;
					$finalData[$tk] = array('clock'=>$tk,'sum'=>$x,'cnt'=>$y);	
				}
				$data2[] = array($timeStart*1000,null);
				foreach ($finalData as $row) {
					if ($row['cnt'] != 0) $v=round($row['sum']/$row['cnt'] * 1 / $div, 2);
					$data2[] = array($row['clock'] * 1000,$v * 1 / $div, 2);						
					if (!isset($minClock)) {
						$minClock = $row['clock'];
						$maxClock = $row['clock'];
						$maxClockIndex = $index;
					} else {
						if ($minClock > $row['clock']){
							$minClock = $row['clock'];
						}
								
						if ($maxClock < $row['clock']){
							$maxClock = $row['clock'];
							$maxClockIndex = $index;
						}
								
					}
				}
			}
					
			if (isset($data2)) {
				$data2[] = array($timeFinish * 1000,null);			
				$chart->yAxis->title->text = $unit;
				$chart->series[]->name = $sonda;
				$chart->series[$index]->type = 'spline';
				$chart->series[$index]->data = $data2;
				$chart->series[$index]->color = getColor($col);
				$index++;
			}
			unset($rawData);
			unset($history);
			$col++;
		}
	}
	
	if (!isset($maxClock)) {
		$maxClock=0;
		$minClock=0;
	}
	if (!isset($unit)) $unit="";
	$title = getItem($_POST[$item]);
	$subtitle='From ' . $_POST['date1']  . ' to ' . $_POST['date2'];
	$chart=chartHeader($chart, 'c_' . $item,$title, $subtitle, $unit, null);
	$chart->xAxis->type = 'datetime';
	//$chart->plotOptions->series->animation->complete = new HighchartJsExpr(" function () {redraw" . $ch . "();}");
	$chart->tooltip->formatter = new HighchartJsExpr("
            function() {
                var s = '<b>'+ Highcharts.dateFormat('%e. %b %H:%M',this.x) +'</b>';    
                $.each(this.points, function(i, point) {
                    s += '<br/><span style=\"color:'+point.series.color+'\">'+ point.series.name +': '+ point.y +' " . $unit . "</span><b>';
                });
                return s;
            }");
	//echo "\n" . '<script> function redraw' . $ch . '() { ' . $ch . '.xAxis['.$maxClockIndex.'].setExtremes(' . $minClock* 1000 . ', ' . $maxClock* 1000 . ');}  </script>';
	return $chart;
}
?>
