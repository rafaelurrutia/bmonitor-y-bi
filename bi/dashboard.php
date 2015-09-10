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

function setFilters($filter)
{
	if (isset($_GET[$filter]) && $_GET[$filter] == 'None')
		unset($_SESSION[$filter]);
	else {
		if (isset($_GET[$filter])) {
			if (isset($_SESSION[$filter][$_GET[$filter]]) ) {
				unset($_SESSION[$filter][$_GET[$filter]]);
				unset($_SESSION[$filter]['None']);
			}
			else {
				$_SESSION[$filter][$_GET[$filter]]=true;
				unset($_SESSION[$filter]['None']);
			}
		}
	}
	$isNone=true;
	if (isset($_SESSION[$filter]))
	foreach ($_SESSION[$filter] as $key=>$value) {
		if ($key != "" && $key != "None" && $value) {
			$isNone = false;
		}
	}
	if ($isNone) {
		unset($_SESSION[$filter]);
		$_SESSION[$filter]['None']=true;
	}
	$_GET[$filter]='';
}

if (!isset($_GET['unit'])) $_GET['unit'] = 'Mbps';
if (!isset($_GET['move'])) $_GET['move'] = 0;
if (!isset($_GET['tag'])) $_GET['tag'] ='None';
if (!isset($_GET['move'])) $_POST['move'] = 0; else $_POST['move']=$_GET['move'];
if ($_POST['move'] < 0) $_POST['move']=0;
if (!isset($_GET['plan']))  $_GET['plan'] = '';
if (!isset($_GET['avg']))  $_GET['avg'] = '4 Hours';
if (!isset($_GET['qoe']))  $_GET['qoe'] = 'Average On';
if (isset($_GET['from']) && gmdate("Y/m/d",strtotime('-7 day')) == $_GET['from']) $_GET['from'] = 'Last 7 Days';
if (!isset($_GET['expand'])) $_GET['expand'] = 'Last 7 Days';

$_SESSION['cacheFull'] = true;
$_SESSION['cachePartial'] = false;


echo '<ol class="breadcrumb" style="margin-top: -15px;margin-left: -15px;margin-bottom: 2px;margin-right: -15px">';


$dashboardSection=readDashboardSections();
if (!isset($_GET['dashboard'])) $_GET['dashboard']=$dashboardSection[0]['dashboard'];

foreach ($dashboardSection as $dash)
{
	$d=$dash['dashboard'];
	if ($_GET['dashboard'] == $d)
		echo '<li class="active" style="color:rgb(66, 139, 202)">' . $d . '</li>';
	else
		echo '<li><a style="color:black" href="./index.php?route=home0&dashboard=' . $d . '">' . $d . '</a></li>';
}
echo '</ol>';

setFilters('tag');
setFilters('filter');

if (isset($_GET['from']) && $_GET['from'] != '')
{
  if ($_GET['type'] == 'linehour')
  {
   	  echo '<div class="container-fluid">';
      echo ' <div class="row"><div class="col-md-12"><div id="item1"></div></div></div>';
      echo ' <div class="row"><div class="col-md-6"><div id="item2"></div></div>';
      echo ' <div class="col-md-6"><div id="item3"></div></div></div>';
      echo '</div>';

      $chart1 = new Highchart();
      $chart1 = GraphHour($chart1, "item1", $_GET['idplan'], $_GET['iditem'], $_GET['tag'], $_GET['from'],$hgram,$low,$mid,$max,$dir);
      renderChart($chart1,"item1");
	  $chart2 = new Highchart();
      $chart2 = GraphHistogram($chart2, "item2", $_GET['idplan'],$_GET['iditem'], $_GET['tag'], $_GET['from'],$hgram);
      renderChart($chart2,"item2");
	  $chart3 = new Highchart();
      $chart3 = GraphPie($chart3, "item3", $_GET['idplan'], $_GET['iditem'], $_GET['tag'], $_GET['from'],$low,$mid,$max,$dir);
      renderChart($chart3,"item3");
      addFromLink();
      addPlanLink('','',false,false);
      addTagLink('None');
	  addFilterLink(array('None', 'P5', 'P95',  'Peak Hour', 'Off Peak Hour', '> 0',  '< 1.5x', '< 2.0x',  '< 3.0x'));
      addSpace(5);
      addLink('type','bar',true,$LANG_TEXT_BACK_TO_WEEK_AVERAGE);
      addLink('type','lineSonda',true,$LANG_TEXT_SHOW_DATA);
  }
  if ($_GET['type'] == 'line2')
  {
  	  echo '<div id="item1"></div>';
      $chart1 = new Highchart();
      $chart1 = GraphPlan($chart1, "item1", $_GET['iditem'], $_GET['from'], $_GET['avg'],$_GET['tag']);
      renderChart($chart1,"item1");
      addFromLink();
      addAverageLink();
      addPlanLink('lineSonda', $LANG_TXT_SONDA_BY_PLAN, true, false);
      addQoE(true);
	  addTagLink('None');
	  addFilterLink(array('None', 'P5', 'P95',  'Peak Hour', 'Off Peak Hour', '> 0',  '< 1.5x', '< 2.0x',  '< 3.0x'));
      addSpace(5);
      addLink('type','bar',true,$LANG_TEXT_BACK_TO_WEEK_AVERAGE);
  }
  if ($_GET['type'] == 'group')
  {
  	  echo '<div id="item1"></div>';
      $chart1 = new Highchart();
      /////$chart1 = GraphGroup($chart1, $_GET['iditem'], $_GET['item'], $_GET['from'], $_GET['avg'],$_GET['dashboard']);
      renderChart($chart1);
      addFromLink();
      addAverageLink();
      addHostLink($_GET['id_plan']);
      //echo '<br>';
      resetParameter('host');
      addLink('type','line2',true,$LANG_TEXT_BACK_TO_AVERAGE_BY_PLAN);
  }
  if ($_GET['type'] == 'bar')
  {
  	  echo '<div id="item1"></div>';

      $chart1 = new Highchart();
      $chart1 = GraphBar($chart1, "item1", $_GET['idplan'],$_GET['iditem'], $_GET['tag'], $_GET['from']);
      renderChart($chart1,"item1");
      addFromLink();
      addPlanLink('','',false,false);
	  addTagLink('None');
	  addFilterLink(array('None', 'P5', 'P95', '> 0',  '< 1.5x', '< 2.0x',  '< 3.0x'));
      addSpace(5);
      addLink('',$_GET['dashboard'],true,$LANG_TEXT_BACK_TO_DASHBOARD);
	  addLink('type','line2',true,$LANG_TXT_AVERAGE_BY_PLAN);
	  addLink('type','linehour',true,$LANG_TXT_AVEGARE_BY_HOUR);
  }
  if ($_GET['type'] == 'lineSonda')
  {
  	  echo '<div id="item1"></div>';
      $chart1 = new Highchart();
      $chart1 = GraphSonda($chart1, "item1", $_GET['idplan'], $_GET['iditem'],$_GET['from'], $_GET['avg'], $_GET['tag']);
      renderChart($chart1,"item1");
      addFromLink();
      addAverageLink();
      addTagLink('None');
	  addQoE(true);
	  addFilterLink(array('None', 'P5', 'P95',  'Peak Hour', 'Off Peak Hour', '> 0',  '< 1.5x', '< 2.0x',  '< 3.0x'));
      addSpace(5);
      addLink('type','line2',true,$LANG_TEXT_BACK_TO_AVERAGE_BY_PLAN);
      //if ($_GET['dashboard'] == 'Video' || $_GET['dashboard'] == 'Ping' || $_GET['dashboard'] == 'File' || $_GET['dashboard'] == 'YouTube') addLink('type','group',false,'Group of Similar Test for Plan ' . $_GET['plan']);
  }
}
else
{
    resetParameter('from');
    addTagLink('None',true,$_GET['tag']);
	addFilterLink(array('None', 'P5', 'P95', '> 0',  '< 1.5x', '< 2.0x',  '< 3.0x'));
    Dashboard($_GET['dashboard'],$_GET['plan'], $_GET['tag'],$_POST['move']);
}
 echo '<br><small>';
 if ($_SESSION['cacheFull'])
 	echo '<center>' . $LANG_TXT_DATA_FROM_CACHE . '</center>';
 elseif ($_SESSION['cachePartial'])
 	echo '<center>' . $LANG_TXT_SOME_DATA_FROM_CACHE . '</center>';
 else
 	echo '<center>' . $LANG_TXT_DATA_FROM_DB . '</center>';
 echo "</samll>";
 mysql_close()
?>
