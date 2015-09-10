<?php
// Link Page
function linkPage($qoe)
{
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
    $rs=mysql_query('select codigosonda,bm_host.host,from_unixtime(max(lastclock+' . $offset . ')) as lastclock,(unix_timestamp(now())-max(lastclock+' . $offset . ')) as diff, plan 
            from bm_item_profile,bm_host,bm_plan
            where bm_host.id_plan=bm_plan.id_plan and  bm_item_profile.id_host=bm_host.id_host and borrado=0 and bm_host.status=1 and
                  bm_host.status=1 and
                  bm_host.groupid = ' . $_SESSION['groupid'] . '
            group by bm_host.id_host
            order by lastclock desc');
    $qn=0;
    $qnok=0;
    $list="";
    $listok="";
    while ($row = mysql_fetch_array($rs, MYSQL_ASSOC))   
    {
        
        $last=$row['lastclock'];
        $host=$row['host']; 
        $plan=str_replace('_','&nbsp;',str_pad($row['plan'],15,'_', STR_PAD_RIGHT));   
        $diff=$row['diff'];
        $codigosonda=$row["codigosonda"];
        if ($host != $codigosonda) $host = $codigosonda . "/" . $host;
        if ($diff > 60*40) 
        {
           $color="red";    
           if ($diff >= 60*40) $color="#FF6600";
           if ($diff >= 60*60) $color="black";
           $list=$list .  '<font color="' . $color . '">' . $last . '&nbsp;&nbsp;' . $plan . '&nbsp;' . $host . '</font><br>';   
           $qn=$qn+1;      
        }  
        else {
	       $color="#47A447";    
           if ($last != "")
           {
           	  $listok=$listok .  '<font color="' . $color . '">' . $last . '&nbsp;&nbsp;' . $plan . '&nbsp;' . $host . '</font><br>';   
             $qnok=$qnok+1;    
		   }  
        }  
        //echo '<tr class="error"><td><p class="text-error"><i class="icon-fire"></i>' . $host . ' ' . $last . '</p></td></tr>';  
    }
    $rs=mysql_query('select codigosonda,bm_host.host,plan from bm_host,bm_plan
            where bm_host.id_plan=bm_plan.id_plan and 
                  id_host not in (select id_host from bm_item_profile where lastclock is not null)  and 
                  borrado=0 and 
                  bm_host.status=1 and
                  bm_host.groupid = ' . $_SESSION['groupid'] . '
            order by host');
    while ($row = mysql_fetch_array($rs, MYSQL_ASSOC))   
    {
        
        $last='0000-00-00 00:00:00';
        $host=$row['host'];   
        $plan=str_replace('_','&nbsp;',str_pad($row['plan'],15,'_', STR_PAD_RIGHT));  
        $color="darkblue";    
        $codigosonda=$row["codigosonda"];
        if ($host != $codigosonda) $host = $codigosonda . "/" . $host;
        $list=$list .  '<font color="' . $color . '">' . $last . '&nbsp;&nbsp;' . $plan . '&nbsp;' . $host . '</font><br>';   
        $qn=$qn+1;      
    }
    
    echo '<li class="active">';
    echo '<a href="#">&nbsp;On ' . date("D M j G:i:s T Y") . ' follow Smart Agents seems to be unavailable';
    echo '<span class="badge badge-warning pull-left">' . $qn . '</span>';     
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
    echo '<li class="active">';
    echo '<a href="#">&nbsp;On ' . date("D M j G:i:s T Y") . ' follow Smart Agents reported activity';
    echo '<span class="badge badge-success pull-left">' . $qnok . '</span>';
    echo $qoe;
    echo '</a>';
    if ($listok == "")
    {
      echo '<div class="label label-success">No erros</div>';   
    }
    else {
     echo '<pre>' . $listok . '</pre>';       
    }
    echo '</li>';   
    mysql_close();
}  

function Go()
{
   echo '<style type="text/css"> pre {font-family: Consolas; font-size: small;}</style>';

   echo '<ul class="nav nav-pills nav-stacked">';
   linkPage('');
   echo '</ul>';
    
}

?>
<td class="iwMain">
        <div class="row-fluid"> 
            <div class="span5 well well-white"> 
                    <?php Go(); ?>       
            </div>
        </div>
</td>
      