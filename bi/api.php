<?php

/**
 * This file is property of Baking Software SpA.
 *
 * PHP version 5
 *
 * @category api
 * @package
 * @version
 * @link
 */
include_once 'lang/LANG_EN.php';
include_once 'db.php';

use Ghunti\HighchartsPHP\HighchartJsExpr;

if (defined('URL_BASE_FULL')) {
	$urlBase = explode('/', URL_BASE_FULL);

	$urlBase = $urlBase[0];

	$urlBase1 = str_replace("baking", "bsw", $urlBase);
	$urlBase2 = str_replace("bsw", "baking", $urlBase);

} else {
	$urlBase = 'none';
}

/**
 * aasort
 *
 * Sort an array with keys
 *
 * @param 	array 	$array 			the array to be sorted
 * 			int 	$key			the index to use for the sort
 * @return 	array 	$array			reference vairable
 */


function aasort(&$array, $key) {
    $sorter=array();
    $ret=array();
    reset($array);
    foreach ($array as $ii => $va) {
        $sorter[$ii]=$va[$key];
    }
    asort($sorter);
    foreach ($sorter as $ii => $va) {
        $ret[$ii]=$array[$ii];
    }
    $array=$ret;
}

/**
 * Days
 *
 * Calculate the number of days between two dates
 *
 * @param 	date 	$date1 			first date
 * 			date 	$date2			second date
 * @return 	int						number of days between the two dates
 */

function Days($date1, $date2)
{
    $startTimeStamp = strtotime($date1);
    $endTimeStamp = strtotime($date2);
    $timeDiff = abs($endTimeStamp - $startTimeStamp);
    $numberDays = $timeDiff / 86400;
    // 86400 seconds in one day
    // and you might want to convert to integer
    $numberDays = intval($numberDays);
    return $numberDays;
}

/**
 * getDates
 *
 * Calculate a range of dates from a starting date. By default will calculate 7 days ahead. Parameter by text
 *
 * @param 	string 	$from 			date from start. Deafult format is Y/m/d. If Y is 2 digits, it adds 20 to the the $from
 * @return 	array					dates range array([0]=>from,[1]=>to)
 */

function getDates($from)
{
    $dt = time();
	//echo "from " . $from;
    switch ($from) {
    case 'Last 15 Days' :
        $dt = strtotime('yesterday', $dt);
        $to = gmdate("Y/m/d", $dt);
        for ($i = 1; $i < 15; $i++)
            $dt = strtotime('yesterday', $dt);
        $from = gmdate("Y/m/d", $dt);
        break;	
    case 'Last 7 Days' :
        $dt = strtotime('yesterday', $dt);
        $to = gmdate("Y/m/d", $dt);
        for ($i = 1; $i < 7; $i++)
            $dt = strtotime('yesterday', $dt);
        $from = gmdate("Y/m/d", $dt);
        break;
    case 'Last Week' :
        $dt = strtotime('last sunday', $dt);
        $to = gmdate("Y/m/d", $dt);
        for ($i = 1; $i < 7; $i++)
            $dt = strtotime('yesterday', $dt);
        $from = gmdate("Y/m/d", $dt);
        break;
    case 'Last Month' :
        $from = gmdate("Y/m/01", strtotime('last month', $dt));
        $to = gmdate("Y/m/d", strtotime('last day of last month', $dt));
        break;
    case 'This Month' :
        $from = gmdate("Y/m/01", strtotime('today', $dt));
        $to = gmdate("Y/m/d", strtotime('last day of this month', $dt));
        break;
    default :
		if (strlen($from)==5)
		{
			$from=date('20' . substr($from,3) . '/' . substr($from,0,2) . '/01');
			$to=gmdate('Y/m/d',strtotime($from . ' +1 months'));
			$to=gmdate('Y/m/d',strtotime($to . ' -1 days'));
		}
		else {
			$dt = strtotime('+6 day', strtotime($from));
        	$to = gmdate("Y/m/d", $dt);
		}
        break;
    }
	//echo $from . '-' . $to . ' ';
    return array(
        $from,
        $to
    );
}

/**
 * getAverage
 *
 * Calculate the division od the minutes to calculate the average of results. Used by graph presentations
 *
 * @param 	string 		$avg 			a text indicating the average to be used (None, 5 Minutes, 10 Minutes, etc)
 * @return 	array						return the representation format and the divisor array([0]=>format,[1]=>divisor)
 */

function getAverage($avg)
{
    $format = '%Y/%m/%d %H:%i:%s';
    $divide = 1;
    switch ($avg) {
    case 'None' :
        break;
    case '5 Minutes' :
        $format = '%Y/%m/%d %H:%i:00';
        $divide = 60 * 5;
        break;
	 case '10 Minutes' :
        $format = '%Y/%m/%d %H:%i:00';
        $divide = 60 * 10;
        break;
	 case '15 Minutes' :
        $format = '%Y/%m/%d %H:%i:00';
        $divide = 60 * 15;
        break;
    case '20 Minutes' :
        $format = '%Y/%m/%d %H:%i:00';
        $divide = 60 * 20;
        break;
    case 'Half Hour' :
        $format = '%Y/%m/%d %H:%i:00';
        $divide = 60 * 30;
        break;
    case 'Hour' :
        $format = '%Y/%m/%d %H:00:00';
        $divide = 60 * 60;
        break;
    case '4 Hours' :
        $format = '%Y/%m/%d %H:00:00';
        $divide = 60 * 60 * 4;
        break;
    case '6 Hours' :
        $format = '%Y/%m/%d %H:00:00';
        $divide = 60 * 60 * 6;
        break;
    case 'Half Day' :
        $format = '%Y/%m/%d %H:00:00';
        $divide = 60 * 60 * 12;
        break;
    case 'Day' :
        $format = '%Y/%m/%d';
        $divide = 60 * 60 * 24;
        break;
    case 'Week' :
        $format = '%Y/%m/%d';
        $divide = 60 * 60 * 24 * 7;
        break;
    case 'Month' :
        $format = '%Y/%m';
        $divide = 60 * 60 * 24 * 30;
        break;
    }
    return array(
        $format,
        $divide
    );
}

/**
 * addLink
 *
 * Contructs the URL link to be used on each button link as a GET
 *
 * @param 	string 	$var 				the URL variable
 *  		string 	$string 			the text display
 *  		string 	$space 				add space between buttons
 *     		string 	$display 			the text to be used on the button
 *     		string 	$changetype 		the new type of
 *     		string 	$nocolor 			button selected if match with the link in the paramers passed in the URL
 * @return 	void						nothing
 */

function addLink($var, $string, $space = false, $display = '', $changetype = '', $nocolor = false)
{
    global $route;

    if ($display == '') $display = $string;
    if (!isset($_GET['dashboard'])) $_GET['dashboard'] = '';
    if (!isset($_GET['type'])) $_GET['type'] = '';
    if (!isset($_GET['avg'])) $_GET['avg'] = '4 Hours';
    if (!isset($_GET['move'])) $_GET['move'] = 0;
    if (!isset($_GET['from'])) $_GET['from'] = 'Last 7 Days';
    if (!isset($_GET['to'])) $_GET['to'] = '';
    if (!isset($_GET['iditem'])) $_GET['iditem'] = '';
	if (!isset($_GET['idplan'])) $_GET['idplan'] = '';
    if (!isset($_GET['graph'])) $_GET['graph'] = '';
    if (!isset($_GET['host'])) $_GET['host'] = '';
    if (!isset($_GET['tag'])) $_GET['tag'] = '';
    if (!isset($_GET['filter'])) $_GET['filter'] = '';
    if (!isset($_GET['qoe'])) $_GET['qoe'] = 'Averge On';
    if (!isset($_GET['more'])) $_GET['more'] = '';
    if (!isset($_GET['expand'])) $_GET['expand'] = 'Last 7 Days';

	if ($var == 'tag') {
		if (isset($_SESSION['tag'][$display]))
			$label = 'btn-primary btn-xs';
		else
			$label = 'btn-default btn-xs';
	}
	elseif ($var == 'filter') {
		if (isset($_SESSION['filter'][$display]))
			$label = 'btn-primary btn-xs';
		else
			$label = 'btn-default btn-xs';
	}
	else {
	    if (isset($_GET[$var]) && $_GET[$var] == $string && !$nocolor)
	        $label = 'btn-primary btn-xs';
	    else
	        $label = 'btn-default btn-xs';
	    if ($var == "type") $label = "btn-success btn-xs";
	    if ($var == "") $label = "btn-danger btn-xs";
	}

    $type = ($var == 'type') ? $string : $_GET['type'];
    $tag = ($var == 'tag') ? $string : $_GET['tag'];
    $avg = ($var == 'avg') ? $string : $_GET['avg'];
    $move = ($var == 'move') ? $string : $_GET['move'];
    $graph = ($var == 'graph') ? $string : $_GET['graph'];
    $from = ($var == 'from') ? $string : $_GET['from'];
    $to = ($var == 'to') ? $string : $_GET['to'];
	$iditem = ($var == 'iditem') ? $string : $_GET['iditem'];
	$idplan = ($var == 'idplan') ? $string : $_GET['idplan'];
	$filter = ($var == 'filter') ? $string : $_GET['filter'];
    $qoe = ($var == 'qoe') ? $string : $_GET['qoe'];
    $expand = ($var == 'expand') ? $string : $_GET['expand'];
    $host = ($var == 'host') ? $string : $_GET['host'];

    if ($changetype != '')
        $type = $changetype;

    if ($var == 'dashboard' || $var == '') {
        echo '<a role="button" class="btn ' . $label . '" href="index.php?route=' . $route . '&move=' . $move . '&dashboard=' . $string . '">' . $display . '</a>';
    } else {
         echo '<a role="button" class="btn ' . $label . '" href="index.php?route=' . $route .
        			'&type=' . $type .
        			'&avg=' . $avg .
        			'&dashboard=' . $_GET['dashboard'] .
        			'&expand=' . $expand .
        			'&move=' . $move .
        			'&from=' . $from .
        			'&tag=' . $tag .
        			'&graph=' . $graph .
        			'&host=' . $host .
        			'&idplan=' . $idplan .
        			'&filter=' . $filter .
        			'&iditem=' . $iditem.
        			'&qoe=' . $qoe . '">' . $display . '</a>';
    }
    if ($space)
        echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
}

/**
 * resetParameters
 *
 * Clean the URL link
 *
 * @param 	string 	$link 				the parameter to clean
 * @return 	void						nothing
 */

function resetParameter($link)
{
    $_GET[$link] = '';
}

/**
 * addSpace
 *
 * add &nbsp; on the web page. Used normally when adding buttons to add spece between them
 *
 * @param 	int 	$numberOfSpaces 		number of spaces
 * @return 	void							nothing
 */

function addSpace($numberOfSpaces)
{
    for ($i = 1; $i <= $numberOfSpaces; $i++) {
        echo '&nbsp;';
    }
}

/**
 * addText
 *
 * display simple text on the web page
 *
 * @param 	int 	$numberOfSpaces 		number of spaces
 * @return 	nothing							nothing
 */

function addText($textToDisplay)
{
    echo '<font color="black">' . $textToDisplay . '</font>';
}

/**
 * renderChart
 *
 * display simple text on the web page
 *
 * @param 	Highchart 	$chart 			number of spaces
 * 			int			$id_chart		id of the chart in the div to display the content and render it
 * @return 	nothing						nothing
 */

function renderChart($chart,$id_chart)
{
    foreach ($chart->getScripts() as $script) {
        echo '<script type="text/javascript" src="' . $script . '"></script>';
    }
    echo '<script type="text/javascript">' . $chart->render($id_chart) . '</script>';
}

function addPlanLink($type = '', $text = '', $nocolor = false, $displayNone = true)
{
    global $LANG_TXT_FILTER_BY_PLAN;

    echo '<center>';
    if ($text == '')
        addText($LANG_TXT_FILTER_BY_PLAN);
    else
        addText($text);
    addSpace(4);
    if ($displayNone)
        addLink('plan', 'None', true);
	$plan=readPlan();
    foreach ($plan as $r){
        $theplan = $r['plan'];
        $id_plan = $r['id_plan'];
        if ($_GET['idplan'] == $id_plan) {
 			$_GET['idplan'] = $id_plan;
        }
        addLink('idplan', $id_plan, true, $theplan, $type, $nocolor);
    }
    echo '</center>';
}

function addHostLink($id_plan)
{
    global $LANG_TXT_FILTER_BY_PROBE;

    echo '<center>';
    addText($LANG_TXT_FILTER_BY_PROBE);
    addSpace(4);
    $hosts=readHosts($id_plan);
    foreach ($hosts as $r){
        $host = $r['host'];
        addLink('host', $host, true, $host, 'group', false);
    }
    echo '</center>';
}

function addHostList()
{
    global $LANG_TXT_FILTER_BY_PROBE;

    echo '<center>';
    addText($LANG_TXT_FILTER_BY_PROBE . ":");
    addSpace(4);
	$hosts=readHosts(0);
    foreach ($hosts as $r) {
        $host = $r['host'];
        addLink('host', $host, true, $host, 'group', false);
    }
    echo '</center>';
}

function addTagLink($plan, $displayNone = true, $active = false)
{
    global $LANG_TXT_FILTER_BY_TAG;

	if ($plan == 'None') $plan = '%';
    $q1 = mysql_query('select distinct tag
            from bm_host h,bm_plan p,bm_tags t,bm_host_groups g
            where p.id_plan=h.id_plan and
                h.status=1 and
                h.borrado=0 and
                h.id_host=t.id and
                p.plan like "' . $plan . '" and
                t.section="host" and
                h.groupid=g.groupid and
                g.groupid=' . $_SESSION['groupid'] . '
            order by tag');
    echo '<center>';
    addText($LANG_TXT_FILTER_BY_TAG . ":");
    addSpace(4);
    addLink('tag', 'None', true);
    while ($r = mysql_fetch_array($q1, MYSQL_ASSOC)) {
        $tag = $r['tag'];
        addLink('tag', $tag, true);
    }
    echo '</center>';
}

function addFilterLink($filter)
{
    global $LANG_TXT_FILTER_DATA;

    echo '<center>';
    addText($LANG_TXT_FILTER_DATA);
    addSpace(4);

	foreach ($filter as $f) {
		addLink('filter', $f, true);
    }
    echo '</center>';
}

function addQoE($onOff)
{
    global $LANG_TXT_FILTER_QOE;

    echo '<center>';
    addText($LANG_TXT_FILTER_QOE . ":");
    addSpace(4);
    addLink("qoe", "Both", true);
    addLink("qoe", "QoE On", true);
    addLink("qoe", "Average On", true);
    echo '</center>';
}

function addFromLink()
{
    global $LANG_TXT_FILTER_RANGE;

    echo '<center>';
    addText($LANG_TXT_FILTER_RANGE);
    addSpace(5);
	addLink('from', 'Last 15 Days', true);
    addLink('from', 'Last 7 Days', true);
    addLink('from', 'Last Week', true);
    $dt = date("Y/m/01");
	for ($i=1;$i<14;$i++)
	{
    	addLink('from', date("m/y",strtotime($dt)), true);
		$dt = date("Y/m/d",strtotime($dt . ' -1 month'));
    }
    //addLink('from', 'Last Month', true);
    echo '</center>';
}

function addAverageLink()
{
    global $LANG_TXT_FILTER_AVERAGE;

    echo '<center>';
    addText($LANG_TXT_FILTER_AVERAGE . ":");
    addSpace(5);
    addLink('avg', '10 Minutes', true);
    addLink('avg', '20 Minutes', true);
    addLink('avg', 'Half Hour', true);
    addLink('avg', 'Hour', true);
    addLink('avg', '4 Hours', true);
    addLink('avg', '6 Hours', true);
    addLink('avg', 'Half Day', true);
    addLink('avg', 'Day', true);
    echo '</center>';
}

function addWeeks($max)
{
    global $LANG_TXT_WEEKS, $LANG_TXT_DAYS;

    echo '<center>';
    addText($LANG_TXT_WEEKS);
    addSpace(5);
    $idx = 0;
    $from = strtotime('last monday', time());
    for ($i = 0; $i < $max; $i++) {
        addLink('from', gmdate("m/d", $from), 3);
        $idx += 1;
        if ($idx == 10) {
            echo '<br>';
            $idx = 0;
        }
        $from = strtotime('last monday', $from);
    }
    echo '</center>';
    echo '<center>';
    addText($LANG_TXT_DAYS . ":");
    addSpace(5);
    $from = strtotime('last monday', time());
    $idx = 0;
    for ($i = 0; $i < 7; $i++) {
        addLink('fromday', gmdate("d", $from), 3);
        $idx += 1;
        if ($idx == 10) {
            echo '<br>';
            $idx = 0;
        }
        $from = strtotime('tomorrow', $from);

    }
    echo '</center>';
}

function AddDateClass($date)
{
    echo '<div class="">';
    echo '<div id="' . $date . '" class="input-append date">';
    echo '<input data-format="dd/MM/yyyy hh:mm:ss" type="text"></input>';
    echo '<span class="add-on">';
    echo '  <i data-time-icon="icon-time" data-date-icon="icon-calendar">';
    echo '  </i>';
    echo '</span>';
    echo '</div>';
    echo '</div>';
    echo '<script type="text/javascript">';
    echo '  $(function() {';
    echo '  $(\'#' . $date . '\').datetimepicker({';
    echo '        pickTime: false';
    echo '    });';
    echo '  });';
    echo '</script>';
}

function addDate()
{
    echo '<table border="0"><tr><td>';
    addText('Days:');
    echo '</td><td>';
    AddDateClass('date1');
    echo '</td><td>';
    AddDateClass('date2');
    echo '</td></tr></table>';
}

function getColor($id)
{
    $color = array(
        '#2f7ed8',
        '#0d233a',
        '#8bbc21',
        '#910000',
        '#1aadce',
        '#492970',
        '#f28f43',
        '#77a1e5',
        '#c42525',
        '#a6c96a',
        '#4572A7',
        '#AA4643',
        '#89A54E',
        '#80699B',
        '#3D96AE',
        '#DB843D',
        '#92A8CD',
        '#A47D7C',
        '#B5CA92',
        '#89A54E',
        '#80699B',
        '#3D96AE',
        '#DB843D',
        '#92A8CD',
        '#A47D7C',
        '#B5CA92',
        '#AA4643',
        '#89A54E',
        '#80699B',
        '#3D96AE',
        '#DB843D',
        '#92A8CD',
        '#A47D7C',
        '#B5CA92',
        '#89A54E',
        '#80699B',
        '#3D96AE',
        '#DB843D',
        '#92A8CD',
        '#A47D7C',
        '#B5CA92'
            );

    return $color[$id];
}

function setTimezone()
{
    $gmt=getTimeZoneOffset();

    date_default_timezone_set($gmt);
    ini_set('date.timezone', $gmt);

    $now = new DateTime();
    $mins = $now->getOffset() / 60;

    $sgn = ($mins < 0 ? -1 : 1);
    $mins = abs($mins);
    $hrs = floor($mins / 60);
    $mins -= $hrs * 60;

    $offset = sprintf('%+d:%02d', $hrs * $sgn, $mins);
	setTimeZoneOffset($offset);
}

function getOffset()
{
    $now = new DateTime();
    $mins = $now->getOffset() / 60;

    $sgn = ($mins < 0 ? -1 : 1);
    $mins = abs($mins);
    $hrs = floor($mins / 60);
    $mins -= $hrs * 60;

    $offset = sprintf('%+d:%02d', $hrs * $sgn, $mins);
    return $offset;
}


function listProfiles()
{
    global $cmd;

    $pret="";
	if (php_uname("s") != "Darwin")
	{
    	$profiles=$cmd->bmonitor->getAllGroupsHost();
	}
	else {
      	$groups=getGroups();
		foreach ($groups as $p) {
        	$profiles[]=array('name'=>$p['name'],'groupid'=>$p['groupid']); ;
      	}
	}
    foreach ($profiles as $p => $value) {
 		$pret .= '<li><a href="index.php?route=home0&profile=' . $value['name'] . '&groupid=' . $value['groupid'] . '">' . $value['name'] . '</a></li>';
    }
    return $pret;
}

function setCurrentGroupId()
{
    global $cmd;
    if (!isset($_GET['groupid']) && !isset($_SESSION['groupid']))
    {
      if (php_uname("s") != "Darwin")
	  {
        $profiles=$cmd->bmonitor->getAllGroupsHost();
	  }
	  else {
		$groups=getGroups();
		foreach ($groups as $p) {
        	$profiles[]=array('name'=>$p['name'],'groupid'=>$p['groupid']); ;
      	}
	  }
      foreach ($profiles as $p => $value) {
          $_SESSION['profile'] = $value['name'];
          $_SESSION['groupid'] = $value['groupid'];
      }
    }
    else
    {
      if (isset($_GET['groupid']))
      {
         $_SESSION['profile'] = $_GET['profile'];
         $_SESSION['groupid'] = $_GET['groupid'];
      }
    }
}

function get_tz_options($selectedzone, $label)
{
  echo '<select name="TZ_OPTION" class="form-control">';
  function timezonechoice($selectedzone) {
    $all = timezone_identifiers_list();

    $i = 0;
    foreach($all AS $zone) {
      $zone = explode('/',$zone);
      $zonen[$i]['continent'] = isset($zone[0]) ? $zone[0] : '';
      $zonen[$i]['city'] = isset($zone[1]) ? $zone[1] : '';
      $zonen[$i]['subcity'] = isset($zone[2]) ? $zone[2] : '';
      $i++;
    }

    asort($zonen);
    $structure = '';
    foreach($zonen AS $zone) {
      extract($zone);
      if($continent == 'Africa' || $continent == 'America' || $continent == 'Antarctica' || $continent == 'Arctic' || $continent == 'Asia' || $continent == 'Atlantic' || $continent == 'Australia' || $continent == 'Europe' || $continent == 'Indian' || $continent == 'Pacific') {
        if(!isset($selectcontinent)) {
          $structure .= '<optgroup label="'.$continent.'">'; // continent
        } elseif($selectcontinent != $continent) {
          $structure .= '</optgroup><optgroup label="'.$continent.'">'; // continent
        }

        if(isset($city) != ''){
          if (!empty($subcity) != ''){
            $city = $city . '/'. $subcity;
          }
          $structure .= "<option ".((($continent.'/'.$city)==$selectedzone)?'selected="selected "':'')." value=\"".($continent.'/'.$city)."\">".str_replace('_',' ',$city)."</option>"; //Timezone
        } else {
          if (!empty($subcity) != ''){
            $city = $city . '/'. $subcity;
          }
          $structure .= "<option ".(($continent==$selectedzone)?'selected="selected "':'')." value=\"".$continent."\">".$continent."</option>"; //Timezone
        }

        $selectcontinent = $continent;
      }
    }
    $structure .= '</optgroup>';
    return $structure;
  }
  echo timezonechoice($selectedzone);
  echo '</select>';
}

function ColorBar($x,$nominal,$warning,$critical,$value,$div)
{
    $nominal=round($nominal/$div,2);
	$value=round($value/$div,2);


	if ($warning <= 100) {
		$T1= $nominal*$warning/100;
		$T2= $nominal*$critical/100;
		if ($value >= $T1)
			$color="green";
		elseif ($value >= $T2 && $value < $T1)
			$color='#F79E03';
		else
			$color='#ff4500';
	}
	else {
	    $T1= $nominal*$warning/100;
		$T2= $nominal*$critical/100;
		if ($value < $T1)
			$color="green";
		elseif ($value >= $T1 && $value < $T2)
			$color='#F79E03';
		else
			$color='#ff4500';
	}
    return array('x'=>$x,'color'=>$color,'y'=>$value);
}

function createHistogram($data,$min,$max,$step,$rkey){

	// flattening the array so I remove the "Price" key
	// the array takes the form of array(10,10,11,...)
	if($data != null) {
		foreach($data as $listing) {
			if ($listing[$rkey] > $max)
				$flattenedListings[] =  $max-1;
			elseif ($listing[$rkey] == 0)
				$flattenedListings[] =  1;
			else
	    		$flattenedListings[] =  $listing[$rkey];
		}

		$widths = range($min, $max, $step); // creates array of the form: array(0, 10, 20, 30, 40, ...)
		$bins = array();
		$isLast = count($widths);
		foreach($widths as $key => $value) {
	   	 	if($key < $isLast - 1) {
	        	$bins[] = array('min' => $value, 'max' => $widths[$key+1]);
	   	 	}
		}

		// creates array of the form:
		// $bins = array(
		//      array('min'=>0, 'max' => 10),
		//      array('min'=>10,'max' => 20),
		//      array('min'=>30, 'max'=>40)
		//     );

		$histogram = array();
	    foreach($bins as $bin) {
	    	$histogram[$bin['min']."-".$bin['max']] = array_filter($flattenedListings, function($element) use ($bin) {
	        	if( ($element > $bin['min']) && ($element <= $bin['max']) ) {
	            	return true;
	            }
	                return false;
	            });
	    }

		// Last one is a bit complicated, but basically what it does is that it creates an array of ranges as keys, so it generates this:

		// array('0-10' => array(1, 2, 3, 4, 5, 6),
		//         '10-20' => array(11, 19, 12),
		//         '20-30' => array(),
		//   );
		// Or in other words: foreach range in the histogram, php creates an array containing      values within the allowed limits.

	    foreach($histogram as $key => $val) {
	        $flotHistogram[$key] = (is_array($val)) ? ( (count($val)) ? count($val) : 0 ) : 0;
	    }

		// And finally it just counts them, and returns a new array.
		return $flotHistogram;
	}
	else
		return null;
}

function calculatePercentile(&$allData) {
	if (isset($allData)) {
		asort($allData);
	 	foreach ($allData as $key=>$value)
		{
			$adata[] = $value;
		}
		$cnt=count($adata);
		$x=round($cnt*5/100,0);
		$y=round($cnt*95/100,0);
		$dx= $adata[$x];
		$dy= $adata[$y];
		unset($adata);
		unset($allData);
		return array('I5'=>$x,'P5'=>$dx,'I95'=>$y,'P95'=>$dy);
	}
	else {
		return array('I5'=>0,'P5'=>0,'I95'=>0,'P95'=>0);
	}
}

function chartHeader($chart, $renderTo, $title, $subtitle, $yAxisTitle, $category) {
  $chart->includeExtraScripts();
  $chart->chart->renderTo = $renderTo;
  $chart->chart->zoomType = 'x';
  $chart->chart->spacingRight = 20;
  $chart->chart->shadow =true;
  $chart->chart->plotShadow =true;
  $chart->xAxis->lineWidth=0;
  $chart->xAxis->tickWidth=1;
  $chart->xAxis->labels->rotation = -45;
  if ($category != null) $chart->xAxis -> categories = $category;
  $chart->xAxis->gridLineWidth= 1;
  $chart->xAxis->labels->style->fontSize = '9px';
  $chart->xAxis->labels->style->fontFamily='Verdana, sans-serif';
  $chart->yAxis->startOnTick = false;
  $chart->yAxis->showFirstLabel = true;
  $chart->yAxis->min=0;
  $chart->yAxis->title->text = $yAxisTitle;
  $chart->tooltip->shared = true;
  $chart->legend->enabled = true;
  $chart->title->text = $title;
  $chart->subtitle->text = $subtitle;
  $chart->title->style->fontSize='14px';
  $chart->subtitle->style->fontSize='10px';
  $chart->plotOptions->column->stacking = "normal";
  $chart->plotOptions->series->animation->complete = new HighchartJsExpr(" function () {redraw();}");

  return $chart;
}


//setTimezone();
setCurrentGroupId();
?>