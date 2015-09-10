<?php
include_once 'api.php';
include_once "include/dashboard/graphHour.php";
include_once "include/dashboard/graphBar.php";
include_once "include/dashboard/graphSonda.php";
include_once "include/dashboard/graphGroup.php";
include_once "include/dashboard/graphPlan.php";
include_once "include/dashboard/dashboard.php";

include_once "plugins/Highchart.php";
include_once "plugins/HighchartOption.php";
include_once "plugins/HighchartJsExpr.php";
include_once "plugins/HighchartOptionRenderer.php";
use Ghunti\HighchartsPHP\Highchart;
use Ghunti\HighchartsPHP\HighchartJsExpr;
use Ghunti\HighchartsPHP\HighchartOptionRenderer;
use Ghunti\HighchartsPHP\HighchartOption;
  
include_once 'include/graphCompare/graphCompare.php'; 

echo '<form method="POST" name="form" id="form" action="index.php?route=home">';
$min=0;
$max=10000000000; 

if (isset($_POST['min'])) $min=$_POST['min'];
if (isset($_POST['max'])) $max=$_POST['max'];
$date1=gmdate("m/d/Y H:i",time()-60*60*24*7);
$date2=gmdate("m/d/Y H:i",time());
if (isset($_POST['date1'])) $date1=$_POST['date1'];
if (isset($_POST['date2'])) $date2=$_POST['date2'];
$_POST['date1']=$date1;
$_POST['date2']=$date2;

echo '<div class="row">';
$chart1 = new Highchart();
$chart2 = new Highchart();
$chart3 = new Highchart();
$chart1=graphCompare($chart1, "item1", $min, $max, "chart1");
$chart2=graphCompare($chart2, "item2", $min, $max, "chart2");
$chart3=graphCompare($chart3, "item3", $min, $max, "chart3");

echo '<div class="col-xs-2">';
echo '<select class="form-control" name="avg" id="avg">';
if (!isset($_POST['avg'])) $_POST['avg']=='4 Hours';
if ($_POST['avg']=='None')  echo '<option selected value="None">None</option>'; else echo '<option value="None">None</option>'; 
if ($_POST['avg']=='10 Minutes')  echo '<option selected value="10 Minutes">10 Minutes</option>'; else echo '<option value="10 Minutes">10 Minutes</option>';
if ($_POST['avg']=='15 Minutes')  echo '<option selected value="15 Minutes">15 Minutes</option>'; else echo '<option value="15 Minutes">15 Minutes</option>';
if ($_POST['avg']=='20 Minutes')  echo '<option selected value="20 Minutes">20 Minutes</option>'; else echo '<option value="20 Minutes">20 Minutes</option>';
if ($_POST['avg']=='Hour')  echo '<option selected value="Hour">Hour</option>'; else echo '<option value="Hour">Hour</option>';
if ($_POST['avg']=='4 Hours')  echo '<option selected value="4 Hours">4 Hours</option>'; else echo '<option value="4 Hours">4 Hours</option>';
if ($_POST['avg']=='6 Hours')  echo '<option selected value="6 Hours">6 Hours</option>'; else echo '<option value="6 Hours">6 Hours</option>';
if ($_POST['avg']=='Half Day')  echo '<option selected value="Half Day">Half day</option>'; else echo '<option value="Half Day">Half Day</option>';
if ($_POST['avg']=='Day')  echo '<option selected value="Day">Day</option>'; else echo '<option value="Day">Day</option>';
if ($_POST['avg']=='Week')  echo '<option selected value="Week">Week</option>'; else  echo '<option value="Week">Week</option>';
if ($_POST['avg']=='Month')  echo '<option selected value="Month">Month</option>'; else  echo '<option value="Month">Month</option>';
echo '</select>';
echo '</div>';

echo '<script type="text/javascript"> $(function(){
            $("#row-date1").datepicker();
            $("#row-date2").datepicker();
    });</script>';

echo '<div class="col-xs-2"><div class="input-group date" id="row-date1" data-date="'.date('Y/m/d', strtotime($date1)).'" data-date-format="yyyy/mm/dd">
                <input class="form-control" id="date1" name="date1" size="16" type="text" value="'.date('Y/m/d', strtotime($date1)).'" readonly>
                <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
              </div></div>';
echo '<div class="col-xs-2"><div class="input-group date" id="row-date2" data-date="'.date('Y/m/d', strtotime($date2)).'" data-date-format="yyyy/mm/dd">
                <input class="form-control" size="16" id="date2" name="date2" type="text" value="'.date('Y/m/d', strtotime($date2)).'" readonly>
                <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
              </div></div>';


echo '<div class="col-xs-2">';
echo '<input class="btn btn-default" type="submit" value="Go" id="go">';
echo '</div></div>';
echo '</form>';

?>
<div class="row" id="c_item1"></div>
<div class="row" id="c_item2"></div>
<div class="row" id="c_item3"></div>
<?php
     if (isset($_POST['item1']) && $_POST['item1'] != "Seleccione" && $_POST['item1'] != '')
     //if (true)
     { 
        foreach ($chart1->getScripts() as $script) {
          echo '<script type="text/javascript" src="' . $script . '"></script>';
	}
	echo '<script type="text/javascript">' . $chart1->render("chart1") . '</script>';
     } 
     if (isset($_POST['item2']) && $_POST['item2'] != "Seleccione" && $_POST['item2'] != '')
     { 
        foreach ($chart2->getScripts() as $script) {
          echo '<script type="text/javascript" src="' . $script . '"></script>';
	}
	echo '<script type="text/javascript">' . $chart2->render("chart2") . '</script>';
     } 
     if (isset($_POST['item3']) && $_POST['item3'] != "Seleccione" && $_POST['item3'] != '')
     { 
        foreach ($chart3->getScripts() as $script) {
          echo '<script type="text/javascript" src="' . $script . '"></script>';
	}
	echo '<script type="text/javascript">' . $chart3->render("chart3") . '</script>';
     } 
     mysql_close();
?>
