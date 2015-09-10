<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>BI</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
    <META HTTP-EQUIV="EXPIRES" CONTENT="Mon, 22 Jul 2002 11:12:01 GMT">
    <meta http-equiv="refresh" content="30;url=all.php">
    <!-- Le styles -->
    <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/bootstrap-iw.css" rel="stylesheet">
    <link href="assets/css/libs/font-Awesome/css/font-awesome.min.css" 
        rel="stylesheet" >
    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
    <link href="calendar/calendar.css" rel="stylesheet" type="text/css">
    <link rel="icon" type="image/x-icon" href="favicon.ico" />
    <script language="javascript" src="calendar/calendar.js"></script>
    <script language="javascript" src="assets/js/libs/jquery/jquery-1.9.1.min.js"></script>
    <script language="javascript" src="assets/bootstrap/js/bootstrap.min.js"></script>
    
    <link href="js/bootstrap-combined.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" media="screen"  href="js/bootstrap-datetimepicker.min.css">
   
    <script type="text/javascript"
     src="js/bootstrap-datetimepicker.min.js">
    </script>
</noscript>
    <style>
        
        /* Large desktop */
@media (min-width: 979px) { 
   .multiSize {
       width: 40.17094017094017%;  
   }
}
 
/* Portrait tablet to landscape and desktop */
@media (min-width: 768px) and (max-width: 979px) { 
   .multiSize {
       width: 90.055249%;  
   } 
}
 
/* Landscape phone to portrait tablet */
@media (max-width: 767px) {
   .multiSize {
       width: 90.055249%;  
   } 
}
 
/* Landscape phones and down */
@media (max-width: 480px) { 
   .multiSize {
       width: 90.055249%;  
   }    
}

    </style>
</head>
<body>

<table class="iwBody">
<tbody>
    <tr><td class="iwSidebar">
        <div class="navbar navbar-fixed-top">
            <div class="navbar-inner navbar-inner-iw">
                <div class="container-fluid">
                    <a class="brand" href="#"> 
                        </span><?php echo 'BakingSoftware '; ?>
                    </a>              
                </div>
            </div>
        </div>         

        <ul class="unstyled iwMenu">
            <li>
                <a href="<?php echo 'http://www.bsw.cl';?>"><i class="icon-arrow-right"></i><span>&nbsp;Company</span></a>
            </li>
        </ul>
      </td>
      <td class="iwMain">
        <div class="row-fluid"> 
            <div class="well well-white multiSize"> 
                    <?php Go(); ?>       
            </div>
        </div>
      </td>
   </tr>
</tbody>
</table>
</body>
</html>

<?php
// Inicialización de base de datos.
// Cambiar según nuevos sitios
function linkPage($qoe,$connect)
{
    switch ($qoe) {
        case "mediacom.us.qoe.baking.cl":
            $db="mediacom.us.qoe";
            break;
        case "cablevision.mx.qoe.baking.cl";
            $db="cablevision.mx.qoe";
            break;
        case "cablevision.ar.qoe.baking.cl";
            $db="cablevision.ar.qoe";
            break;
        case "movistar.cl.qoe.baking.cl";
            $db="movistar.cl.qoe";
            break;
        case "vtr.cl.qoe.baking.cl";
            $db="vtr.cl.qoe";
            break;
        case "amx.pe.qoe.baking.cl";
            $db="amx.pe.qoe";
            break;
        case "amx.co.qoe.baking.cl";
            $db="bmonitor";
            break;
        case "amx.cl.qoe.baking.cl";
            $db="bmonitorclaro";
            break;
        case "amx.ar.qoe.baking.cl";
            $db="bmonitorclaroba";
            break;
        case "une.co.qoe.baking.cl";
            $db="une.co.qoe";
            break;
        case "multimedios.mx.qoe.baking.cl";
            $db="multimedios.mx.qoe";
            break;
        case "tigo.gt.qoe.baking.cl";
            $db="tigo.gt.qoe";
            break;    
        case "claro.pe.qoe.baking.cl";
            $db="claro.pe.qoe";
            break;    
        case "claro.co.qoe.baking.cl";
            $db="claro.co.qoe";
            break;
        case "claro.ar.qoe.baking.cl";
            $db="claro.ar.qoe";
            break;    
    }
    $bool = mysql_select_db($db, $connect);   
    $qoffset='select valor from Parametros where nombre="TIMEZONE_OFFSET"';
    $qresult=mysql_query($qoffset);
    if (mysql_num_rows($qresult) == 1) 
        $offset=0;
    else
        $offset=0;
    $qoffset='select valor from Parametros where nombre="TIMEZONE"';
    $qresult=mysql_query($qoffset);
    if (mysql_num_rows($qresult) == 1) 
       mysql_query("SET time_zone = '" . mysql_result($qresult, 0) . "'");
   
    $rs=mysql_query("select count(*) as cnt from bm_host where borrado=0 and status=1");
    $cnt=mysql_result($rs, 0);
    $rs=mysql_query('select codigosonda,bm_host.host,from_unixtime(max(lastclock+' . $offset . ')) as lastclock,(unix_timestamp(now())-max(lastclock+' . $offset . ')) as diff from bm_item_profile,bm_host
            where bm_item_profile.id_host=bm_host.id_host and borrado=0 and bm_host.status=1
            group by bm_host.id_host
            order by lastclock desc');
    $qn=0;
    $list="";
    while ($row = mysql_fetch_array($rs, MYSQL_ASSOC))   
    {
        
        $last=$row['lastclock'];
        $host=$row['host'];   
        $diff=$row['diff'];
        $codigosonda=$row["codigosonda"];
        if ($host != $codigosonda) $host .= "/" . $codigosonda;
        if ($diff > 60*35) 
        {
           $color="red";    
           if ($diff >= 60*35) $color="#FF6600";
           if ($diff >= 60*60) $color="black";
           $list=$list .  '<font color="' . $color . '">' . $last . '&nbsp;&nbsp;&nbsp; ' . $host . '</font><br>';   
           $qn=$qn+1;      
        }    
    }
    $rs=mysql_query('select codigosonda,bm_host.host from bm_host
            where id_host not in (select id_host from bm_item_profile where lastclock is not null)  and borrado=0 and bm_host.status=1
            order by host');
    while ($row = mysql_fetch_array($rs, MYSQL_ASSOC))   
    {
        
        $last='0000-00-00 00:00:00';
        $host=$row['host'];   
        $codigosonda=$row["codigosonda"];
        if ($host != $codigosonda) $host .= "/" . $codigosonda;
        $color="darkblue";    
        $list=$list .  '<font color="' . $color . '">' . $last . '&nbsp;&nbsp;&nbsp; ' . $host . '</font><br>';   
        $qn=$qn+1;      
    }
    echo '<li class="active">';
    echo '<a href="http://' . $qoe . '" target="_blank">';
    echo '<span class="badge badge-info pull-right">' . $cnt . '</span>';
    echo '<span class="badge badge-success pull-right">' . ($cnt-$qn) . '</span>';
    echo '<span class="badge badge-warning pull-right">' . $qn . '</span>';     
    echo $qoe;
    echo '</a>';
    if ($list == "")
    {
      echo '<div class="label label-success">No erros</div>';   
    }
    else {
     echo '<pre>' . $list . '</pre>';       
    }
    echo '</li>';   
}  

function Go()
{

$connect = mysql_connect('127.0.0.1', 'root', 'bsw$$2009') or die('Could not connect: ' . mysql_error());
echo '<style type="text/css"> pre {font-family: Consolas; font-size: small;}</style>';
echo '<center><span class="label label-danger"><h6>' . date("D M j G:i:s T Y") . '</h6></span></center>';

//echo '<table class="table table-striped">';
echo '<ul class="nav nav-pills nav-stacked">';

//linkPage('amx.ar.qoe.baking.cl',$connect);         
//linkPage('amx.cl.qoe.baking.cl',$connect);
//linkPage('amx.co.qoe.baking.cl',$connect);
//linkPage('amx.pe.qoe.baking.cl',$connect);
//linkPage('cablevision.mx.qoe.baking.cl',$connect);
//linkPage('cablevision.ar.qoe.baking.cl',$connect);
linkPage('mediacom.us.qoe.baking.cl',$connect);
linkPage('tigo.gt.qoe.baking.cl',$connect);
//linkPage('tigo.gt.qoe.baking.cl',$connect);
//linkPage('multimedios.mx.qoe.baking.cl',$connect);
//linkPage('une.co.qoe.baking.cl',$connect);
//linkPage('movistar.cl.qoe.baking.cl',$connect);
linkPage('vtr.cl.qoe.baking.cl',$connect);
linkPage('claro.pe.qoe.baking.cl',$connect);
linkPage('claro.co.qoe.baking.cl',$connect);
//echo '</table>';
  echo '</ul>';
}
?>

