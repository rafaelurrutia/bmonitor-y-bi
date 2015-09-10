<?php
use Ghunti\HighchartsPHP\HighchartJsExpr;

function GraphGroup($chart, $id_chart, $item,$itemName, $from, $avg, $type)
{
  $offset=getOffset();
  setTimezone();
      
  $dt=getDates($from);
  $from=$dt[0];
  $to=$dt[1];
    
  $chart->chart->renderTo = $id_chart;
  $chart->chart->zoomType = 'x';
  $chart->chart->spacingRight = 20;
  $chart->xAxis->type='datetime';
  $chart->xAxis->lineWidth=0;
  $chart->xAxis->tickWidth=1;
  $chart->chart->shadow =true;
  $chart->chart->plotShadow =true;
  
  $chart->yAxis->startOnTick = false;
  $chart->yAxis->showFirstLabel = false;
  $chart->yAxis->min=0;
  $chart->xAxis->gridLineWidth= 1;
  $chart->tooltip->shared = true;
  $chart->legend->enabled = true;
  $chart->subtitle->text= 'From ' . $from . ' to ' .$to;
        
  $index=0;
  $thezero='-1';
  $average=getAverage($avg);
  $format=$average[0];
  $divide=$average[1];
  $filterHost='';
  if ($_GET['host'] != '') $filterHost=' and host="' . $_GET['host'] . '" ';
  switch ($type)
  {
      case 'YouTube': 
        $list='%video youtube%AVG%';
        $txt='Youtube Average for Plan ' . $_GET['plan'];
        if ($_GET['host'] != '') $txt .= ' for Sonda ' . $_GET['host'];
        $chart->title->text = $txt;  
        break; 
      case 'File': 
        $list='file download% speed';
        $txt='File Download Average for Plan ' . $_GET['plan'];
        if ($_GET['host'] != '') $txt .= ' for Sonda ' . $_GET['host'];
        $chart->title->text = $txt;  
        break; 
      case 'Video': 
        $list='Video %experience%';
        $txt='Video Experience for Plan ' . $_GET['plan'];
        if ($_GET['host'] != '') $txt .= ' for Sonda ' . $_GET['host'];
        $chart->title->text = $txt;  
        break; 
      case 'Ping': 
        $list='ping%avg%';
        $txt='Ping Average for Plan ' . $_GET['plan'];
        if ($_GET['host'] != '') $txt .= ' for Sonda ' . $_GET['host'];
        $chart->title->text = $txt;  
        break; 
  }
 
  $thezero='0'; 
  $q='select distinct descriptionLong from bm_items where descriptionLong like "' . $list . '" order by 1';
  $q1=mysql_query($q);
  
  while ($r = mysql_fetch_array($q1, MYSQL_ASSOC)) {
     $desc=$r['descriptionLong'];    
     $query = "SELECT descriptionLong,unix_timestamp(date_format(from_unixtime(round(clock/" . $divide . ",0)*" . $divide . "),'" . $format . "')) as clk,
              round(avg(value),0) as value,
              max(unit) as unit 
              FROM  bm_host,bm_items,bm_history,bm_plan,bm_host_groups
              WHERE bm_host.id_host=bm_history.id_host and bm_history.id_item=bm_items.id_item and  descriptionLong = '" . $desc . "'
                and bm_host.id_plan=bm_plan.id_plan 
                and clock between unix_timestamp('" . $from . "') and unix_timestamp('" . $to . " 23:59:59')+3600*24
                and value > " . $thezero  . "
                and plan='" . $_GET['plan'] . "' " . $filterHost . " 
                and bm_host_groups.groupid=bm_host.groupid
                and bm_host_groups.groupid=" . $_SESSION['groupid'] . "
              GROUP BY descriptionLong,unit,unix_timestamp(date_format(from_unixtime(round(clock/" . $divide . ",0)*" . $divide . "),'" . $format . "'))
              ORDER BY 1";
     //var_dump($query);
     $result = mysql_query($query) or die("SQL Error 1: " . mysql_error());
     unset($data2);
     $unit='';
     if (mysql_num_rows($result) > 0)
     {
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) 
        {
             $unit=$row['unit']; 
             $div=1;  
             if ($unit == 'bps') 
             {
                 $div=(1024*1024);   
                 $unit='Mbps';           
             }
             $data2[] = array($row['clk']*1000,round($row['value']*1/$div,2));
        }
    
        $chart->yAxis->title->text=$unit;
        //$chart->tooltip->formatter = new HighchartJsExpr("function() { return '<b>'+ this.series.name +'</b><br/>'+ Highcharts.dateFormat('%e. %b %H:%M', this.x) +': '+ Highcharts.numberFormat(this.y, 2, ',') +' " . $unit . "';}");
        $chart->series[]->name =$desc;
        $chart->series[$index]->type ='spline';
        $chart->series[$index]->data=$data2;
        $index++;
     }
  }
  return $chart;
}

?>