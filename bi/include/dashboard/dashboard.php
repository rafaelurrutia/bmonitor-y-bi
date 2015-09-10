<?php

function Dashboard($dashboard,$plan,$tag,$move)
{
  $offset=getOffset();
  setTimezone();

  $r='-7' - $_GET['move'];
  $s='-1' - $_GET['move'];
  $from=date("Y/m/d",strtotime($r . ' days'));
  $to=date("Y/m/d",strtotime($s . ' days'));
    
  $theDate=date('Y/m/d',strtotime($from . ' -1 days'));
  while ($theDate != $to) {
	$theDate = date('Y/m/d',strtotime($theDate . ' +1 days'));
	$category[]=$theDate;
  } 
			
  $dashList=readDashboard($dashboard);
  $peakHour =    str_replace(":","","18");
  $offPeakHour = str_replace(":","","22");
	
  foreach ($dashList as $dash) {
  	$id_item = $dash['id_item'];
	$descriptionLong = $dash['descriptionLong'];
	$planList=readPlan();
	foreach ($planList as $plan) {
		unset($weekAvgPeak);
  		unset($weekAvgOffPeak);
  		unset($weekQoEPeak);
  		unset($weekQoEOffPeak);	
		$id_plan=$plan['id_plan'];
		$ldata=loadRAWData($from, $id_plan, $id_item);
		$unit=$ldata['unit'];
		$div=$ldata['div'];
		$plan=$ldata['plan'];
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
			if (isset($ldata['historyList'][$id_host])) {
				foreach ($ldata['historyList'][$id_host] as $key=>$row) {
					$tk=date('m/d',$row['clock']);
					if (!isset($weekAvgPeak[$tk])) $weekAvgPeak[$tk] = array($tk,0,0);
					if (!isset($weekAvgOffPeak[$tk])) $weekAvgOffPeak[$tk] = array($tk,0,0);
					if (!isset($weekQoEPeak[$tk])) $weekQoEPeak[$tk] = array($tk,0,0);
					if (!isset($weekQoEOffPeak[$tk])) $weekQoEOffPeak[$tk] = array($tk,0,0);
					$skip = false;
					if (isset($_SESSION['filter']['P95']) && $row['value'] > $percentile['P95']) $skip=true; 
					if (isset($_SESSION['filter']['P5']) && $row['value'] < $percentile['P5']) $skip=true; 
					
					if (!$skip) {
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
		}		
		unset($dataPeak);
		unset($dataOffPeak);
		unset($dataQoEPeak);
		unset($dataQoEOffPeak);
		unset($dataNominal);
		unset($dataWarning);
		unset($dataCritical);	
		 
		if (isset($weekQoEPeak)) {
			aasort($weekQoEPeak, 0);	
			aasort($weekQoEOffPeak, 0);
			aasort($weekAvgPeak, 0);
			aasort($weekAvgOffPeak, 0);
		    
			$sumQoEPeak=0;
			$cntQoEPeak=0;
			$sumQoEOffPeak=0;
			$cntQoEOffPeak=0;
			
			foreach (array_reverse($weekQoEPeak) as $tvalue) {
				$key = $tvalue[0];
				$sumQoEPeak+=$weekQoEPeak[$key][1];
				$cntQoEPeak+=$weekQoEPeak[$key][2];
				$sumQoEOffPeak+=$weekQoEOffPeak[$key][1];
				$cntQoEOffPeak+=$weekQoEOffPeak[$key][2];
				
				if ($weekQoEPeak[$key][2] > 0) $dataQoEPeak[$key]    = array('x'=>$key,'y'=>round($weekQoEPeak[$key][1]/$weekQoEPeak[$key][2]/$div,2),'cnt'=>$weekQoEPeak[$key][2]);				  // ColorBar($x,$nominal,$warning,$critical,$value,$div)	
				if ($weekQoEOffPeak[$key][2] > 0) $dataQoEOffPeak[$key] = array('x'=>$key,'y'=>round($weekQoEOffPeak[$key][1]/$weekQoEOffPeak[$key][2]/$div,2),'cnt'=>$weekQoEOffPeak[$key][2]);
				if ($weekAvgPeak[$key][2] > 0) $dataAvgPeak[$key]    = array('x'=>$key,'y'=>round($weekAvgPeak[$key][1]/$weekAvgPeak[$key][2]/$div, 2),'cnt'=>$weekAvgPeak[$key][2]);	
				if ($weekAvgOffPeak[$key][2] >  0) $dataAvgOffPeak[$key] = array('x'=>$key,'y'=>round($weekAvgOffPeak[$key][1]/$weekAvgOffPeak[$key][2]/$div, 2),'cnt'=>$weekAvgOffPeak[$key][2]);	
			}
			
	 		//echo  $items['descriptionLong'] . " " .  $plans['plan']  . " " . $host['host'] ;
			//var_dump($dataQoEPeak);
			//echo "<br>";
			if (!isset($dataAvgOffPeak)) $dataAvgOffPeak=null;
			if (!isset($dataQoEPeak)) $dataQoEPeak=null;
			if (!isset($dataAvgPeak)) $dataAvgPeak=null;	
			if ($cntQoEOffPeak==0) $cntQoEOffPeak=9999999999999999999999;
			if ($cntQoEPeak==0) $cntQoEPeak=99999999999999999999999;
			$dataList[] = array('descriptionLong'=>$descriptionLong,
						    'plan'=>$plan,
						    'id_plan'=>$id_plan,
						    'id_item'=>$id_item,
						    'QoEPeak'=>$dataQoEPeak,
						    'QoEPeakAvg'=>round($sumQoEPeak/$cntQoEPeak/$div,2),
						    'QoEOffPeak'=>$dataQoEOffPeak,
						    'QoEOffPeakAvg'=>round($sumQoEOffPeak/$cntQoEOffPeak/$div,2),
						    'AvgPeak'=>$dataAvgPeak,
						    'AvgOffPeak'=>$dataAvgOffPeak,
						    'nominal'=>round($nominal/$div,2),
						    'warning'=>round($warning,2),
						    'critical'=>round($critical,2),
							'unit'=>$unit,
							'cntQoEPeak'=>$cntQoEPeak,
							'cntQoEOffPeak'=>$cntQoEOffPeak,
							'category'=>$category);
		}	
	} 		
  }		

  echo '<table width="100%" border="0"><tr><td><center>';
  echo '<table class="tooltip-tabla">';
  $img1='<a href="./index.php?route=home0&dashboard=' . $dashboard . '&move=' . ($_GET['move']+1) . '"><span class="glyphicon glyphicon-backward"></span></a>';    
  $img2='<a href="./index.php?route=home0&dashboard=' . $dashboard . '&move=' . ($_GET['move']-1) . '"><span class="glyphicon glyphicon-forward"></a>';    
  $img3='<a href="./index.php?route=home0&dashboard=' . $dashboard . '&move=' . ($_GET['move']+7) . '"><span class="glyphicon glyphicon-fast-backward"></span></a>';    
  $img4='<a href="./index.php?route=home0&dashboard=' . $dashboard . '&move=' . ($_GET['move']-7) . '"><span class="glyphicon glyphicon-fast-forward"></a>';    
  echo '<tbody><tr><th>' . $img1  . "&nbsp;" . $img3 . '</th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th style="text-align: right;">' . $img4 . "&nbsp;" . $img2 . '</th></tr></tbody>';    
                      
  $t='';
  if (isset($dataList))
  foreach ($dataList as $data)
  {
  	$timeSlot='PEAK';
	
  	for ($ij=1;$ij<=2;$ij++) {
 	  	if ($timeSlot == 'PEAK') {
			$QoE='QoEPeak';
			$Avg='QoEPeakAvg';
			$Cnt='cntQoEPeak';
		}
		else {
			$QoE='QoEOffPeak';
			$Avg='QoEOffPeakAvg';
			$Cnt='cntQoEOffPeak';		
		}
	  	if ($t !=  $data['descriptionLong']) {
			$t=$data['descriptionLong'];
			echo '<tbody><tr><th class="success" colspan="12"><center><big>' . $t . '</big></center></th></tr></tbody>';    
	        echo '<tr>';
	        echo '<td>Plan</td>';
	        echo '<td align="center">Left</td>';
	        echo '<td align="center">Nominal</td>';
			//echo '<td align="center">Warning</td>';
			//echo '<td align="center">Critical</td>';
	        echo '<td>Week Average&nbsp;&nbsp;&nbsp;&nbsp;</td>';
			for ($i=0;$i<7;$i++) {
				if (date("w",strtotime($data['category'][$i])) == 0) 
					$sun='<font color="red">'; 
				else 
					$sun='<font color="black">';
				echo '<td align="right">' . $sun . substr($data['category'][$i],5) . '</font>&nbsp;&nbsp;&nbsp;&nbsp;</td>';
			} 
	        echo '</tr>';
		}
	 	$nominal=$data['nominal'];
		$warning=$data['warning'];
		$critical=$data['critical'];
		$unit=$data['unit'];
	        
	    echo '<tr>';
		
	    echo '<td>' . $data['plan'] . '&nbsp;&nbsp;&nbsp;</td>';
	    echo '<td>' . $timeSlot . '&nbsp;&nbsp;&nbsp;</td>';
	    echo '<td align="right">&nbsp;' . number_format($nominal,2) . ' ' . $unit. '&nbsp;</td>';
		//echo '<td align="right">&nbsp;' . number_format($nominal*$warning/100,2) . ' ' . $unit. '&nbsp;</td>';
		//echo '<td align="right">&nbsp;' . number_format($nominal*$critical/100,2) . ' ' . $unit. '&nbsp;</td>';
	    echo '<td>';  // . '&tag=' . $_GET['tag'] 
	    echo '<a href="index.php?route=home0&type=bar&dashboard=' . $dashboard . '&move=' . $_GET['move'] .  '&tag=' . $tag . '&idplan=' . $data['id_plan'] . '&from='  . $from . '&iditem=' . $data['id_item'] .'">';           
	    if (!isset($data[$Avg]) || $data[$Avg] == 0 ) 
	    {
	        echo '<center><img align="center" src="./images/Button-Close-icon.png" title="' . number_format($data[$Avg],2) . ' ' . $unit . "<b>Q:" . '0' .'"></center>' ;   
	    }
	    else
	    { 
	        if ($warning <= 100) {
	        	$T1= $nominal*$warning/100;
				$T2= $nominal*$critical/100;
				if ($data[$Avg] >= $T1)
					$color="./images/Green-Ball-icon.png";
				elseif ($data[$Avg] >= $T2 && $data[$Avg] < $T1)
					$color="./images/Yellow-Ball-icon.png";
				else 
				 	$color="./images/Red-Ball-icon.png";
			}
	        else {
	        	$T1= $nominal*$warning/100;
				$T2= $nominal*$critical/100;
				if ($data[$Avg] < $T1)
					$color="./images/Green-Ball-icon.png";
				elseif ($data[$Avg] >= $T1 && $data[$Avg] < $T2)
					$color="./images/Yellow-Ball-icon.png";
				else 
				 	$color="./images/Red-Ball-icon.png";
	        }
			echo '<center><img align="center" src="' . $color . '" data-toggle="tooltip" title="(' .  number_format($data[$Avg] / $nominal*100,2) . '%) ' . number_format($data[$Avg],2) . ' ' . $unit . "  Q:" . $data[$Cnt] .'"></center>' ; 	
		}
	    echo '</a>';
	    echo '</span></td>'; 
	
		for ($i=0;$i<7;$i++) {
	      $ky=substr($data['category'][$i],5); 	
	      echo '<td>';
		  //echo '(' . $data[$QoE][$ky]['y'] . ')' . '<br>';
	      if (!isset($data[$QoE][$ky]['y'])  || $data[$QoE][$ky]['y'] == 0 ||  $data[$QoE][$ky]['y'] == null)
	      {
	          echo '<center><img align="center" src="./images/Button-Close-icon.png" title="No data"></center>' ; 
	      } 
	      else
	      {
	      	if ($warning <= 100) {
	        	$T1= $nominal*$warning/100;
				$T2= $nominal*$critical/100;
				if ($data[$QoE][$ky]['y'] >= $T1)
					$color="./images/Flag-green-icon.png";
				elseif ($data[$QoE][$ky]['y'] >= $T2 && $data[$QoE][$ky]['y'] < $T1)
					$color="./images/Flag-yellow-icon.png";
				else 
				 	$color="./images/Flag-red-icon.png";
			}
	        else {
	        	$T1= $nominal*$warning/100;
				$T2= $nominal*$critical/100;
				if ($data[$QoE][$ky]['y'] < $T1)
					$color="./images/Flag-green-icon.png";
				elseif ($data[$QoE][$ky]['y'] >= $T1 && $data[$QoE][$ky]['y'] < $T2)
					$color="./images/Flag-yellow-icon.png";
				else 
				 	$color="./images/Flag-red-icon.png";
	        }
	        echo '<center><img align="center" src="' . $color . '" data-toggle="tooltip" title="' . $data[$QoE][$ky]['x'] . ' ' . number_format($data[$QoE][$ky]['y'],2) . ' ' . $unit . "  Q:" . $data[$QoE][$ky]['cnt'] .'"></center>' ; 
	      }
	      echo '</span></td>';
	    }
	    echo '</tr>'; 
	    
		if ( $timeSlot=='PEAK' )
			$timeSlot='OFFPEAK';
		else
			$timeSlot='PEAK';
	  }
  }
  echo '</table>';
  echo '</center></td></tr></table>';
}
?>