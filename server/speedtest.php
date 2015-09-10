<?php
	require '../core/startup.inc.php';
	/*
	 ** BSW
	 ** Copyright (C) 2012-2013 BSW S.A.
	 **
	 ** This modification allow the user to modify SEC data
	 **/

	//Global Param:
	$dateStart = date("U");
	$var = (object) array(
		"logsFile" => "logs_qoeSpeed",
		"crontab" => "qos_crontab"
	);

	if( ! isset($argv[1])) {
		$domain = 'bmonitor.baking.cl';
	} else {
		$domain = $argv[1];
	}

	$cmd = new Control( true, $domain );

	$cmd->parametro->remove('STDOUT');
	$cmd->parametro->remove('DEBUG');

	$cmd->parametro->set('STDOUT', true);
	$cmd->parametro->set('DEBUG', false);

	$cmd->parametro->set('LOGS_FILE', $var->logsFile);

	$cmd->logs->info("[QoS] ===============================================");
	$cmd->logs->info("[QoS] Starting speedtest report generator");
	$cmd->logs->info("[QoS] Copyright Baking Software 2014");
	$cmd->logs->info("[QoS] Version 1");
	$cmd->logs->info("[QoS] ===============================================");

	$active = $cmd->_crontab($var->crontab, "start");

	if($active) {
		$cmd->logs->info("[QoS] Starting OK");
	} else {
		$cmd->logs->info("[QoS] Starting NOK");
		exit ;
	}

	//Get ID Items

	$idItemDownload = $cmd->parametro->get('ITEM_QOE_REPORT_SPEEDTEST_DOWNLOAD', 148);
	$idItemUpload = $cmd->parametro->get('ITEM_QOE_REPORT_SPEEDTEST_UPLOAD', 149);
	$idItemDelay = $cmd->parametro->get('ITEM_QOE_REPORT_SPEEDTEST_DELAY', 150);

	//Set Param
	$param = (object) array();

	$param->year = date("Y", strtotime("yesterday"));
	$param->month = date("n", strtotime("yesterday"));

	//$param->month = 6;

	if($param->month < 10) {
		$param->month = "0" .$param->month;
	}
    
	$param->clock = $param->year ."/" .$param->month;
	$param->clockS = $param->year.$param->month;

	$param->where = "H.`clock` BETWEEN UNIX_TIMESTAMP('" .$param->year ."-" .$param->month ."-01') AND 
	                                  UNIX_TIMESTAMP(adddate('" .$param->year ."-" .$param->month ."-01', interval 1 month))-1 ";

    $QOE_ISP_SPEEDTEST = $cmd->parametro->get('QOE_ISP_SPEEDTEST', '');

	//Valid Table report_speedtest
	
	$createTableSpeedSQL = "CREATE TABLE IF NOT EXISTS `report_speedtest` (
              `id_speedtest` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `date` int(6) DEFAULT NULL,
              `groupid` int(11) unsigned NOT NULL,
              `ip` varchar(16) NOT NULL DEFAULT '',
              `city` varchar(100) NOT NULL DEFAULT '',
              `region` varchar(100) NOT NULL DEFAULT '',
              `country` varchar(100) NOT NULL DEFAULT '',
              `isp` varchar(100) NOT NULL DEFAULT '',
              `latitude` decimal(10,7) NOT NULL,
              `longitude` decimal(10,7) NOT NULL,
              `testDate` datetime NOT NULL,
              `serverName` varchar(150) NOT NULL DEFAULT '',
              `download` int(11) unsigned NOT NULL,
              `upload` int(11) unsigned NOT NULL,
              `latency` int(11) unsigned NOT NULL,
              `browser` varchar(100) NOT NULL DEFAULT '',
              `operatingSystem` varchar(100) NOT NULL DEFAULT '',
              `userAgent` varchar(150) NOT NULL DEFAULT '',
              `pdownload` int(11) DEFAULT NULL,
              `pupload` int(11) DEFAULT NULL,
              PRIMARY KEY (`id_speedtest`)
        ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;";
    /*
    $createTableSpeedREPORT = $cmd->conexion->query($createTableSpeedSQL);

    if( ! $createTableSpeedREPORT) {
        $cmd->logs->error("[QoS] Create Table Report Speed, ", NULL);
        $cmd->_crontab($var->crontab, "finish");
        exit ;
    }*/
  	                                  
	//GetHost Data

	$getHostDataSQL = "SELECT H.`id_host`, H.`groupid`, H.`host` , H.`ip_wan` ,
    HP.`id_plan`, HP.`nacD`, HP.`nacU`, 
    GROUP_CONCAT(CONCAT(HD.`id_feature`,':', HD.`value`) ORDER BY `id_feature`) as 'feature'
    FROM `bm_host`  H
        LEFT JOIN `bm_host_groups` HG ON H.`groupid`=HG.`groupid`
        LEFT JOIN `bm_host_detalle` HD ON HD.`id_host`=H.`id_host`
        LEFT JOIN `bm_plan` HP ON H.`id_plan` = HP.`id_plan`
    WHERE H.`borrado` = 0 AND HG.`type` = 'QoS' AND HD.`id_feature` IN (67,68,69,70,71,72,77,78,79,80,81,82,84,85,3)
    GROUP BY H.`id_host`";

	$getHostDataRESULT = $cmd->conexion->queryFetch($getHostDataSQL);

	if( ! $getHostDataRESULT) {
		$cmd->logs->error("[QoS] Error get host data, ", NULL);
		$cmd->_crontab($var->crontab, "finish");
		exit ;
	}
    
	$hostDetail = array();
    
	foreach ($getHostDataRESULT as $key => $value) {
		$allFeature = explode(',', $value['feature']);

		$featureList = array(
			67 => 0, //LotitudGrados
			68 => 0, //LatitudMinutos
			69 => 0, //LatitudSegundos
            70 => 0, //LongitudGrados
            71 => 0, //LongitudMinutos
            72 => 0, //LongitudSegundos
            77 => '', //Country
            78 => '', //Region
            79 => '', //City
            80 => '', //State
            82 => '', //Other
            84 => '0', //daneMunicipio
            85 => '0', //daneDepartamento
		);

		foreach ($allFeature as $keyLocation => $valueLocation) {
			list( $idFeauture, $valueFeature ) = explode(':', $valueLocation);
			if($valueFeature != '' && !is_null($valueFeature)) {
				$featureList[$idFeauture] = $valueFeature;
			}
		}
        if(isset($featureList[67]) && $featureList[67] != "") {
            $latitude = $cmd->basic->convertDMStoDEC($featureList[67],$featureList[68],$featureList[69]);
        } else {
            $latitude = '0.0000000';
        }
		
        if(isset($featureList[70]) && $featureList[70] != "") {
            $longitude = $cmd->basic->convertDMStoDEC($featureList[70],$featureList[71],$featureList[72]);
        } else {
            $longitude = '0.0000000'; 
        }
        
        $country = $cmd->basic->getLocationValue('country',$featureList[77]);
        $state = $cmd->basic->getLocationValue('state',$featureList[80]);

        if($featureList[78] != "" && !is_null($featureList[78])){
           $regionR = $cmd->basic->getLocationValue($featureList[78]); 
            if($regionR != false) {
                $region = $regionR->name;
            } else {
                $region = '';
            }
        } else {
           $region = '';
        }

        if($featureList[82] != "" && !is_null($featureList[82])){
            $city = $featureList[82];
        } elseif ($featureList[79] != "" && !is_null($featureList[79])) {
            $cityR = $cmd->basic->getLocationValue($featureList[79]);
            if($cityR != false) {
               $city = $cityR->name;
            } else {
               $city = ''; 
            }
        } else {
            $city = '';
        }
        
		$hostDetail[$value['id_host']] = array(
           "latitude" =>  $latitude,
           "longitude" => $longitude,
           "country" => $country,
           "state" => $state,
           "region" => $region,
           "city" => $city,
           "groupid" => $value['groupid'],
           "host" => $value['host'],
           "id_plan" => $value['id_plan'],
           "nacD" => $value['nacD']*1024,
           "nacU" => $value['nacU']*1024,
           "isp" => $QOE_ISP_SPEEDTEST,
           "ip" => $value['ip_wan'],
           "daneMunicipio" => $featureList[84],
           "daneDepartamento" => $featureList[85]
        );
        
        $hostList[] = $value['id_host'];
	}

    $getHistorySQL = "SELECT  H.`id_host`, H.`clock` , HI.`ip`, H.`id_item` , 
    SUBSTRING_INDEX(I.`descriptionLong`,'-',-1) As 'ServerName', SUBSTRING_INDEX(SUBSTRING_INDEX(I.`description`,'_',2),'_',-1) As 'IDCategory', 
    GROUP_CONCAT(CONCAT(H.`id_item`,':', H.`value`) ORDER BY I.`description`) as 'feature'
    FROM `bm_history` H
        LEFT JOIN `bm_items` I ON H.`id_item` = I.`id_item`
        LEFT OUTER JOIN `bm_history_ip` HI ON ( HI.`clock`=H.`clock`  AND H.`id_host`=HI.`id_host` AND H.`id_item`=HI.`id_item`)
    WHERE 
        H.`id_host` IN (".join(',', $hostList).") AND  ( I.`description` LIKE \"LSP_%_AVGD\"  OR I.`description` LIKE \"LSP_%_AVGU\"  OR I.`description` LIKE \"LSP_%_PING\")  
        AND $param->where 
    GROUP BY `clock`
    ORDER BY `clock`";

    $getHistoryRESULT = $cmd->conexion->queryFetch($getHistorySQL);

    if(!$getHistoryRESULT) {
        $cmd->logs->error("[QoS] Error get history", NULL);
        $cmd->_crontab($var->crontab, "finish");
        exit;
    }
    
    //Delete report 
    
    $deleteReportRESULT = $cmd->conexion->query("DELETE FROM `report_speedtest` WHERE `date` = '$param->clockS';");
    
    if(!$deleteReportRESULT) {
        $cmd->logs->error("[QoS] delete report: ", $param->clockS);
        $cmd->_crontab($var->crontab, "finish");
        exit;
    }    
    
    foreach ($getHistoryRESULT as $keyHistory => $hvalue) {
        
        $host = (object)$hostDetail[$hvalue['id_host']];
        
        $detailResultTest = explode(",", $hvalue['feature']);
        
        list($downloadID,$download) = explode(':', $detailResultTest[0]);
        list($uploadID,$upload) = explode(':', $detailResultTest[1]);
        list($latencyID,$latency) = explode(':', $detailResultTest[2]);
        
        if(!isset($download) || $download == "" ){
            $download = 0;
        }

        if(!isset($upload) || $upload == "" ){
            $upload = 0;
        }
        
        if(!isset($latency) || $latency == "" ){
            $latency = 0;
        }
               
        $percenDownload = ($download/$host->nacD)*100;
        $percenDownloadResult = round( $percenDownload  );

        $percenUpload = ($upload/$host->nacU)*100;
        $percenUploadResult = round( $percenUpload  );
        
        if($hvalue['ip'] == ""){
            $hvalue['ip'] = $host->ip;
        }
          
        $reportValue[] = "( ".$hvalue['id_host'].", ".$host->groupid.", ".$param->clockS." , '".$hvalue['ip']."', '".$host->city."', '".$host->region."', '".$host->country."', '".$host->isp."',
                         ".$host->latitude.", ".$host->longitude.", FROM_UNIXTIME(".$hvalue['clock']."),
                             '".trim($hvalue['ServerName'])."', ".$download.", ".$upload.", ".$latency.", ".$percenDownloadResult.", ".$percenUploadResult.", 'Internet Explorer 9.0', 'Windows 7 Home', '','".$host->daneMunicipio."','".$host->daneDepartamento."')" ;
    }

    $insertReportSpeedValueSQL = "INSERT INTO `report_speedtest` ( `idHost` , `groupid`, `date`, `ip`, `city`, `region`, `country`, `isp`, `latitude`, `longitude`, 
                                            `testDate`, `serverName`, `download`, `upload`, `latency`,  `pdownload`, `pupload`,
                                                    `browser`, `operatingSystem`, `userAgent`,`daneMunicipio`,`daneDepartamento`) VALUES ";
                                        
    $insertReportSpeedValueRESULT = $cmd->conexion->query($insertReportSpeedValueSQL.join(',', $reportValue));
    
    if( ! $insertReportSpeedValueRESULT) {
        $cmd->logs->error("[QoS] Error insert value", NULL);
        $cmd->_crontab($var->crontab, "finish");
        exit;
    }
    
    $cmd->_crontab($var->crontab, "finish");
    exit ;      
?>