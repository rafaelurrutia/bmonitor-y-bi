<?php
/**
 * This file is property of Baking Software SpA.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  Index
 * @author   Rodrigo Montes <rodrigo@bsw.cl>
 * @license  http://www.baking.cl/license BSD Licence
 * @version  SVN: $Id: index.phpl,v 1.9 2008-10-09 15:16:47 cweiske Exp $
 * @link     http://mnla.com
 */
header('Content-type: text/html; charset=UTF-8');
require '../core/startup.inc.php';


if (php_uname("s") != "Darwin")
{
	$cmd = new Control(true);
 	$valida = $cmd->protect->accessPage('APPS_BI');
}
else
{
	session_start();
	$_SESSION['name']="Darwin";
}

$_SESSION['username'] = $_SESSION['name'];

if(isset($_GET['route'])) {
    $route = $_GET['route'];
} else {
    $route = "dashboardHTML.php";
}

if (isset($_GET['route'])) {

    switch ($route) {
        case
            "home" :
            include "graphCompareHTML.php";
            break;
        case
            "home0" :
            include "dashboardHTML.php";
            break;
        case "home1" :
            include "groupedGraphHTML.php";
            break;
        case "home2" :
            include "home2.php";
            break;
        case "home3" :
            include "home3.php";
            break;
        case "homec" :
            include "configureBIHTML.php";
            break;
        case "status" :
            include "probeStatusHTML.php";
            break;
 
 /*
		case "status" :
            include "homes.php";
            break;	
 */
        case "logout" :
            $cmd->protect->log_out();
            header("Location: /login");
            break;
        case "console" :
            include "console.php";
            break;
        default :
            include "dashboardHTML.php";
            break;
    }
} else {
   include "dashboardHTML.php";
}
?>
