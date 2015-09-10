<?php
use Ghunti\HighchartsPHP\Highchart;
use Ghunti\HighchartsPHP\HighchartJsExpr;
use Ghunti\HighchartsPHP\HighchartOptionRenderer;
use Ghunti\HighchartsPHP\HighchartOption;

function groupedGraph($chart, $item, $min, $max) {
		
	global $cmd;
	
	echo '<div class="col-xs-2"><select class="form-control" name="' . $item . '" id="' . $item . '">';
	if (!isset($_POST[$item])) {
		$_POST[$item] = '';
		echo '<option selected value="Select">Select</option>';
	} else {
		echo '<option value="Select">Select</option>';
	}
	
	
	$itemSelected = '';
	$graphs=readGraphs();
	foreach($graphs as $graph) {
		if (isset($_POST) && $_POST[$item] == $graph['id_graph']) {
			echo '<option selected value="' . $graph['id_graph'] . '">' . $graph['name'] . '</option>';
			$itemSelected = $graph['name'];
		} else {
			echo '<option value="' . $graph['id_graph'] . '">' . $graph['name'] . '</option>';
		}		
	}
	
	echo '</select></div>';
	echo '<div class="col-xs-2"><select class="form-control" name="host" id="host">';
	if (!isset($_POST['host'])) {
		$_POST['host'] = '';
		echo '<option selected value="Select">Select</option>';
	} else {
		echo '<option value="Select">Select</option>';
	}
	
    $hostSelected = '';
	$hosts=readHosts(0);
	foreach ($hosts as $host) {
		$thehost = $host['host'];

		if (isset($_POST['host']) && $_POST['host'] == $host['id_host']) {
			$hostSelected = $thehost;
			echo '<option selected value="' . $host['id_host'] . '">' . $thehost . '</option>';
		} else {
			echo '<option value="' . $host['id_host'] . '">' . $thehost . '</option>';
		}
	}        

	echo '</select></div>';
	$index = 0;
				
	$days = Days($_POST['date1'], $_POST['date2']);
	$divide = 1;
	$divide=getAverage($_POST['avg']);
	$divide=$divide[1];
	
	if ($_POST['avg'] == "None") {
		$divideslot = 60 * 30;
	}
	else{
		$divideslot = $divide;
	}
		
	if (is_numeric($_POST['host'])) {
		$id_host = $_POST['host'];
		$col = 0;
		$monitors=readMonitorsForGraph($_POST[$item]);
	
		foreach ($monitors as $monitor) {
			$id_item = $monitor['id_item'];
			$rawData=loadRAWData($_POST['date1'], 0, $id_item,$_POST['date2'],$id_host);
			unset($data2);
			$prev = 0;
			$next = 0;
			$history=$rawData['historyList'][$id_host];
			if (isset($history)){

				$unit = $monitor['unit'];
				$div = 1;
				if ($unit == 'bps') {
					$div=1024;  //BGS011
					$unit = 'Mbps';
				}
				unset($finalData);
				foreach ($history as $ddata) {
					$tk=round($ddata['clock']/$divide,0)*$divide;
					if (!isset($finalData[$tk])) $finalData[$tk] = array('clock'=>$tk,'sum'=>0,'cnt'=>0);
					$sum=$finalData[$tk]['sum'];
					$cnt=$finalData[$tk]['cnt'];
					$x=$sum+$ddata['value'];
					$y=$cnt+1;
					$finalData[$tk] = array('clock'=>$tk,'sum'=>$x,'cnt'=>$y);	
				}
				foreach ($finalData as $row) {
					if (!isset($minClock)) {
						$minClock = $row['clock'] * 1000;
						$maxClock = $row['clock'] * 1000;
					} else {
						if ($minClock > $row['clock'] * 1000)
							$minClock = $row['clock'] * 1000;
						if ($maxClock < $row['clock'] * 1000)
							$maxClock = $row['clock'] * 1000;
					}

					$next = $row['clock'];
					if ($prev > 0 && $next > 0 && ($next - $prev) > $divideslot * 1.2) {
						$data2[] = array(
							($prev + 1000) * 1000,
							null
						);

					}
					$prev = $next;
					$v=null;
					if ($row['cnt'] != 0) $v=round($row['sum']/$row['cnt'] * 1 / $div, 2);
					$data2[] = array($row['clock'] * 1000,$v * 1 / $div, 2);
				}
				$chart->series[]->name = $monitor['descriptionLong'];
				$chart->series[$index]->type = 'spline';
				$chart->yAxis->title->text = $unit;
				$chart->series[$index]->color = getColor($col);

				$chart->series[$index]->data = $data2;
				$index++;
				$col++;
			}
		}
	}
	if (!isset($unit)) $unit="";
	$title = $hostSelected . ":" . $itemSelected;
	$subtitle='From ' . $_POST['date1']  . ' to ' . $_POST['date2'];
	$chart=chartHeader($chart, 'c_' . $item,$title, $subtitle, $unit, null);
	$chart->xAxis->type = 'datetime';
	$chart->plotOptions->series->animation->complete = new HighchartJsExpr(" function () {redraw();}");

    if (!isset($minClock)) {
    	$minClock=0;
		$maxClock=0;
    }
	if (!isset($unit)) $unit="";
	$chart->tooltip->formatter = new HighchartJsExpr("
            function() {
                var s = '<b>'+ Highcharts.dateFormat('%e. %b %H:%M',this.x) +'</b>';    
                $.each(this.points, function(i, point) {
                    s += '<br/><span style=\"color:'+point.series.color+'\">'+ point.series.name +': '+ point.y +' " . $unit . "</span><b>';
                });
                return s;
            }");
	//return array( 0 => $chart, 1 => $minClock , 2 => $maxClock);
	echo "\n" . '<script> function redraw() { chart1.xAxis[0].setExtremes(' . ($minClock - 60000) . ', ' . ($maxClock + 60000 * 5) . ');}  </script>';

	return $chart;

}
?>