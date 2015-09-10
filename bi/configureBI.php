<?php

function page()
{
    echo '<div class="row">';
    echo '<form method="POST" name="Add" id="Add" action="index.php?route=homec">';  
    
    echo '<div class="col-md-4"><div class="form-group">
    <label for="from">Item</label><select class="form-control" name="from" id="from">';
    if (!isset($_POST['from'])) {
        $_POST['from'] = -1;
        echo '<option selected value="-1">Select</option>';
    } else {
        echo '<option value="-1">Select</option>';
    }
	$items=readMonitorsAvailableForBI();
    foreach ($items as $item) {
        if (isset($_POST) && $_POST['from'] == $item['id_item']) {
            echo '<option selected value="' . $item['id_item'] . '">' . $item['descriptionLong'] . '</option>';
        } else {
            echo '<option value="' . $item['id_item'] . '">' . $item['descriptionLong'] . '</option>';
        }
    }
    echo '</select></div></div>';

    echo '<div class="col-md-4"><div class="form-group">
    <label for="dash">In Dahboard</label><select class="form-control" name="dash" id="dash">';
    if ($_POST['dash'] == 'HTTP')
        echo '<option selected value="HTTP">HTTP</option>';
    else
        echo '<option value="HTTP">HTTP</option>';
    if ($_POST['dash'] == 'FTP')
        echo '<option selected value="FTP">FTP</option>';
    else
        echo '<option value="FTP">FTP</option>';
    if ($_POST['dash'] == 'WEB')
        echo '<option selected value="WEB">WEB</option>';
    else
        echo '<option value="WEB">WEB</option>';
    if ($_POST['dash'] == 'Ping')
        echo '<option selected value="Ping">Ping</option>';
    else
        echo '<option value="Ping">Ping</option>';
    if ($_POST['dash'] == 'YouTube')
        echo '<option selected value="YouTube">YouTube</option>';
    else
        echo '<option value="YouTube">YouTube</option>';
    if ($_POST['dash'] == 'File')
        echo '<option selected value="File">File</option>';
    else
        echo '<option value="File">File</option>';
    if ($_POST['dash'] == 'Video')
        echo '<option selected value="Video">Video</option>';
    else
        echo '<option value="Video">Video</option>';
    if ($_POST['dash'] == 'DNS')
        echo '<option selected value="DNS">DNS</option>';
    else
        echo '<option value="DNS">DNS</option>';
    if ($_POST['dash'] == 'Speedtest Download')
        echo '<option selected value="Speedtest Download">Speedtest Download</option>';
    else
        echo '<option value="Speedtest Download">Speedtest Download</option>';
    if ($_POST['dash'] == 'Speedtest Upload')
        echo '<option selected value="Speedtest Upload">Speedtest Upload</option>';
    else
        echo '<option value="Speedtest Upload">Speedtest Upload</option>';
    if ($_POST['dash'] == 'Video Experience')
        echo '<option selected value="Video Experience">Video Experience</option>';
    else
        echo '<option value="Video Experience">Video Experience</option>';    

    echo '</select>';
    echo '</div></div>';

    echo '<div class="col-md-2"><div class="form-group">
    <label for="Add">Class</label><input class="btn btn-default form-control" type="submit" value="Add" id="Add"> ';
    echo '</div></div>';
    echo '</form></div>';

    echo '<div class="row">';

    echo '<form method="POST" name="Config" id="Config" action="index.php?route=homec">';
    
    echo '<div class="col-md-4"><div class="form-group">
    <label for="thr">Item</label>';
   
    echo '<select class="form-control" name="thr" id="thr">';
    if (!isset($_POST[$item])) {
        $_POST[$item] = '';
        echo '<option selected value="-1">Select</option>';
    } else {
        echo '<option value="-1">Select</option>';
    }
	$items=readMonitorsUsedOnBI();
    foreach ($items as $item) {
        if (isset($_POST) && $_POST[$item] == $item['id_item']) {
            echo '<option selected value="' . $item['id_item'] . '">' . $item['descriptionLong'] . '</option>';
        } else {
            echo '<option value="' . $item['id_item'] . '">' . $item['descriptionLong'] . '</option>';
        }
    }
    echo '</select></div></div>';
    echo '<div class="col-md-2"><div class="form-group">
    <label for="Add">Config</label><input class="btn btn-default form-control" type="submit" value="Config" id="Config"> ';
    echo '</div></div></div>';
    echo '</form>';
}

//var_dump($_POST);

if ((isset($_POST['thr']) && $_POST['thr'] > 0) || (isset($_POST['from']) && $_POST['from'] > 0) || isset($_POST['Delete']) || isset($_POST['Save'])) {

    	if (isset($_POST['Save'])) {
    	    if (strtoupper($_POST['nominal']) == "USE DOWNLOAD") $_POST['nominal'] = -1;
    		if (strtoupper($_POST['nominal']) == "USE UPLOAD") $_POST['nominal'] = -2;
    		if (!is_numeric($_POST['nominal'])) $_POST['nominal'] = -1;
    		if (!is_numeric($_POST['warning'])) $_POST['warning'] = 90;
    		if (!is_numeric($_POST['critical']))
        	$_POST['critical'] = 80;
		
        //$_POST['critical'] = $_POST['warning'];
        if($_POST['nominal'] == '-1') {
        	$typeThreshold = 'download';
        } elseif ($_POST['nominal'] == '-2') {
            $typeThreshold = 'upload';
        } else {
        	$typeThreshold = 'personalized';
        }
        
        $q = 'update bm_threshold set `type`="'.$typeThreshold.'", 
        							   nominal=' . $_POST['nominal'] . ',
        							   warning=' . $_POST['warning'] . ',
        							   critical=' . $_POST['critical'] . ' 
        	  where id_item=' . $_POST['id_item'];
       
        $qq = mysql_query($q);
        
        $_POST['thr'] = $_POST['id_item'];
        page();
    }

    if (isset($_POST['Delete'])) {
        $q = 'delete from  bm_threshold where id_item=' . $_POST['id_item'];
        $qq = mysql_query($q);
        $q = 'delete from  bi_dashboard where id_item=' . $_POST['id_item'];
        $qq = mysql_query($q);
        page();
    }
    if (!(isset($_POST['Save']) || isset($_POST['Delete']))) {
        if (isset($_POST['from'])) {
    
            $index=90;
            if ($_POST['dash'] == 'Speedtest Download') $index=1;
            if ($_POST['dash'] == 'Speedtest Upload') $index=2;
            if ($_POST['dash'] == 'File') $index=3;
            if ($_POST['dash'] == 'FTP') $index=4;
            if ($_POST['dash'] == 'HTTP') $index=5;
            if ($_POST['dash'] == 'DNS')  $index=6;
            if ($_POST['dash'] == 'WEB') $index=7;
            if ($_POST['dash'] == 'Ping') $index=8;
            if ($_POST['dash'] == 'YouTube') $index=9;
            if ($_POST['dash'] == 'Video') $index=10;
            if ($_POST['dash'] == 'Video Experience') $index=11;
            
			$q = 'insert into bm_threshold(`id_item`,`nominal`,`warning`,`critical`,`dashboard`) values (' . $_POST['from'] . ',-1,90,80,"'.$_POST['dash'].'")';
            $qq = mysql_query($q);
			
            $q = 'insert into bi_dashboard(dashboard,id_item,displayorder) values ("' . $_POST['dash'] . '",' . $_POST['from'] . ',' . $index . ')';
            $qq = mysql_query($q);
            $_POST['thr'] = $_POST['from'];
        }
        
		$thr=getThreasholds($_POST['thr']);
		$nominal=$thr['nominal'];
		$warning=$thr['warning'];
		$critical=$thr['critical'];
		$descriptionLong=$thr['descriptionLong'];

        if ($nominal == -1) $nominal = "Use Download";
        if ($nominal == -2) $nominal = "Use Upload";
        echo '<form method="POST" name="Config" id="Config" action="index.php?route=homec">';
        echo '<table border="0"><td>Config ' . $descriptionLong . '</td></tr>';
        echo '<tr><td>Nominal</td><td><input type="text" name="nominal" id="nominal" value="' . $nominal . '">(Use Download/Use Upload/Value)</td></tr>';
        echo '<tr><td>Warning (%)</td><td><input type="text" name="warning" id="warning" value="' . $warning . '"></td></tr>';
        echo '<tr><td>Critical (%)</td><td><input type="text" name="critical" id="critical" value="' . $critical . '"></td></tr>';
        echo '<input type="hidden" name="id_item" id="nominal" value="' . $_POST['thr'] . '">';

        echo '<tr><td><input type="submit" name="Save" value="Save"><input type="submit" Name="Delete" value="Delete"></td></tr>';
        echo '</table>';
        echo '</form>';
    }
} else {
    page();
}
?>
