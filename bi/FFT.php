<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="la" lang="la">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo "FTT"; ?></title>
        <link rel="stylesheet" type="text/css" href="theme/css/bootstrap.org.min.css"/>
        <link rel="stylesheet" type="text/css" href="theme/css/bootstrap-baking.css"/>
        <link rel="stylesheet" type="text/css" href="theme/css/font-awesome.min.css">
        <link rel="stylesheet" type="text/css" href="theme/css/datepicker.css"/>
        <link rel="stylesheet" type="text/css" href="theme/css/bootstrap-datetimepicker.min.css"/>
        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->
        <script src="theme/js/jquery-1.11.1.min.js"></script>
        <script src="theme/js/moment.min.js"></script>
        <script src="theme/js/en-ca.js"></script>
        <script src="theme/js/bootstrap.min.js"></script>
        <script src="theme/js/bootstrap-datepicker.js"></script>
        <script src="theme/js/bootstrap-datetimepicker.js"></script>
        <script src="theme/js/multifilter.min.js"></script>
        <script type="text/javascript" src="plugins/Highcharts-4.0.4/js/highcharts.js"></script>
        <link rel="icon" type="image/x-icon" href="favicon.ico" />
        
        <script type="text/javascript">
        
            function preloader(){
                document.getElementById("loading").style.display = "block";
                document.getElementById("imgLoading").style.display = "block";
            }
    
            window.onload = preloader;
        
            $(window).load(function(){
                $("#loading").fadeOut("slow");
                $("#imgLoading").fadeOut("slow");
                $('#loading').hide();
                $('#imgLoading').hide();
                
            });
            
            $(document).ready(function() {
                $('#loading').show();
                $('#imgLoading').show();
            });
			$(function() {
			    try {
				Highcharts.setOptions({
					global : {
						useUTC : false
					}
				});
			}  catch(err) {
			    
			}	
			});
			
			$(window).resize(function() {
    			if(this.resizeTO) clearTimeout(this.resizeTO);
    			this.resizeTO = setTimeout(function() {
        		$(this).trigger('resizeEnd');
    			}, 500);
			});

			$(window).bind('resizeEnd', function() {
    			var W=$("#width").text($(this).width());
    			var H=$("#height").text($(this).height());

  				window.location='FFT.php?w=' + window.innerWidth;
  				// alert(window.location); 
    			windows.location.reload(true);
			});


        </script>
    </head>
<?php

include_once "fast/phpfastcache/phpfastcache.php";
include_once "pChart/class/pData.class.php";
include_once "pChart/class/pDraw.class.php";
include_once "pChart/class/pImage.class.php";
include_once "plugins/Highchart.php";
include_once "plugins/HighchartOption.php";
include_once "plugins/HighchartJsExpr.php";
include_once "plugins/HighchartOptionRenderer.php";
use Ghunti\HighchartsPHP\Highchart;
use Ghunti\HighchartsPHP\HighchartJsExpr;
use Ghunti\HighchartsPHP\HighchartOptionRenderer;
use Ghunti\HighchartsPHP\HighchartOption;



	function mComplexDiv($first,$second)
	{
		if(($second['re']==0 && $second['im']==0))
		{
			return(-1);
		}
		$a=$first['re'];
		$b=$first['im'];
		$c=$second['re'];
		$d=$second['im'];
		$e=($a*$c)+($b*$d);
		$f=($c*$c)+($d*$d);
		$g=($b*$c)-($a*$d);
		$result['re']=$e/$f;
		$result['im']=$g/$f;
		return($result);
	}
	
	function mComplexAdd($first,$second)
	{
		$result['re']=$first['re']+$second['re'];
		$result['im']=$first['im']+$second['im'];
		return($result);
	}
	
	function mComplexSub($first,$second)
	{
		$result['re']=$first['re']-$second['re'];
		$result['im']=$first['im']-$second['im'];
		return($result);
	}
	
	function mComplexMul($first,$second)
	{
		$result['re']=($first['re']*$second['re'])-($first['im']*$second['im']);
		$result['im']=($first['im']*$second['re'])+($first['re']*$second['im']);
		return($result);
	}
	
	function mComplexConj($first)
	{
		$result['re']=$first['re'];
		$result['im']=-1.0*$first['im'];
		return($result);
	}
	
	function mComplexRec2pol($first)
	{
		$result['re']=sqrt(pow($first['re'],2)+pow($first['im'],2));
		$result['im']=rad2deg(atan($first['im']/$first['re']));
		return($result);
	}
	
	function mComplexPol2rec($first)
	{
		$result['re']=$first['re']*cos(deg2rad($first['im']));
		$result['im']=$first['re']*sin(deg2rad($first['im']));
		return($result);
	}
	   
	function FFT($data,$inverse)
	{
		if ($inverse)
		  $mfft=iFFT($data);
		else
		  $mfft=aFFT($data);
		return $mfft;	
	}   
	   
	function mComplexToString($data)
	{
		for ($i=0;$i< count($data);$i++)
		{
			echo '(' . round($data[$i]['re'],4) . ',' . round($data[$i]['im'],4) . ')' . "\n";
		}
	}
	
	function aFFT($data)
	{
		$n=count($data);
		$m=log($n,2);
		$le = $n/2;
		$arg=pi()/$le;
		
		$xrecur=cos($arg);
		$yrecur=sin($arg);
		$xtemp=$xrecur;
		$ytenp=$yrecur;
		
		$wrecur['re']=cos($arg);
		$wrecur['im']=-sin($arg);
		
		$wtemp=$wrecur;
		
		for ($i=1; $i< $le;$i++)
		{
			$w[$i-1] = $wtemp;
			$wtemp = mComplexMul($wtemp, $wrecur);
		}
		//var_dump($w);
		$rdata=fftWorker($data,$w,$m);
		return $rdata;
	}
	
	function iFFT($data)
	{
		$n=count($data);
		$m=log($n,2);
		$le = $n/2;
		$arg=pi()/$le;
		
		$xrecur=cos($arg);
		$yrecur=sin($arg);
		$xtemp=$xrecur;
		$ytenp=$yrecur;
		
		$wrecur['re']=cos($arg);
		$wrecur['im']=sin($arg);
		
		$wtemp=$wrecur;
		
		for ($i=1; $i< $le;$i++)
		{
			$w[$i-1] = $wtemp;
			$wtemp = mComplexMul($wrecur, $wrecur);
		}
		$rdata=fftWorker($data,$w,$m);
		$n=count($data);
		$scale=1.0/$n;
		for ($i=0;$i<count($data);$i++)
		{
			$rdata[$i]['re']*=$scale;
			$rdata[$i]['im']*=$scale;
		}
		return $rdata;
	}
	
	function fftWorker($data,$w,$m)
	{
		$le=count($data);
		
		$wStep=1;
		for ($l=0;$l<$m;$l++)
		{
			$le = $le / 2;
			for ($i=0;$i<count($data);$i+=2*$le)
			{
				$xi=$data[$i];
				$xip=$data[$i+$le];
				$temp=mComplexAdd($xi,$xip);
				$data[$i+$le]=mComplexSub($xi,$xip);
				$data[$i]=$temp;
			}
			
			$wIndex=$wStep-1;
			for ($j=1; $j<$le;$j++)
			{
				for ($i=$j; $i<count($data);$i+=2*$le)
				{
					$xi=$data[$i];
					$xip=$data[$i+$le];
					$temp=mComplexAdd($xi,$xip);
					$temp2=mComplexSub($xi,$xip);
					$data[$i+$le]=mComplexMul($temp2,$w[$wIndex]);
					$data[$i]=$temp;
				}
				$wIndex+=$wStep;
			}
			$wStep*=2;
		}
		
		// re arrange
		
		$ji=0;
		$ki=0;
		
		for ($i=1;$i<count($data)-1;$i++)
		{
			$ki=count($data)/2;
			while ($ki<=$ji)
			{
				$ji=$ji-$ki;
				$ki=$ki/2;	
			}
			$ji = $ji + $ki;
			if ($i < $ji)
			{
				$xi = $data[$i];
				$xj = $data[$ji];
				
				$temp = $xj;
				$data[$ji]=$data[$i];
				$data[$i]=$temp;
			}
		}
		return $data;
	}
	   
	   
	function Graph($item,$chart,$dataA,$dataB,$dataC,$size)
	{
		  $chart->chart->renderTo = $item;
		  $chart->chart->zoomType = 'x';
		  $chart->chart->spacingRight = 20;
		  //$chart->xAxis->type='datetime';
		  $chart->xAxis->lineWidth=0;
		  $chart->xAxis->tickWidth=1;
		  //$chart->xAxis->gridLineColor= '#707073';
		  //$chart->xAxis->labels->style->color= '#E0E0E3';
		  $chart->xAxis->gridLineWidth= 1;
		  
		  $chart->yAxis->startOnTick = false;
		  $chart->yAxis->showFirstLabel = false;
		  //$chart->yAxis->min=0;
		  //$chart->yAxis->gridLineColor= '#707073';
		  //$chart->yAxis->labels->style->color= '#E0E0E3';
		  
		  $chart->tooltip->shared = true;
		  $chart->legend->enabled = false;
		  $chart->chart->shadow =true;
		  $chart->chart->plotShadow =true;
		  //$chart->chart->backgroundColor->linearGradient=array('x1'=>0,'y1'=>0,'x2'=>500,'y2'=>500);
		  //$chart->chart->backgroundColor->stops=array(0=>'#2a2a2b',1=>'#3e3e40');
		  //$chart->chart->backgroundColor ='#FCFFC5';
		  //$chart->chart->polar =true;
		  //$chart->chart->type ='line';
		  //$chart->chart->plotBorderColor='#606063';
		  
		  
		  $move=0;
		 
	
		  //var_dump($q);
		  
		  
		  $index=0;
		  for ($i=1;$i<$size;$i++)  
	        {
	             
	             $category[$index] =$i;
	             $data0[$index] = sqrt($dataA[$i]['re']*$dataA[$i]['re']+$dataA[$i]['im']*$dataA[$i]['im']);
				 $data1[$index] = sqrt($dataB[$i]['re']*$dataB[$i]['re']+$dataB[$i]['im']*$dataB[$i]['im']);
				 $data2[$index] = sqrt($dataC[$i]['re']*$dataC[$i]['re']+$dataC[$i]['im']*$dataC[$i]['im']);
	             
	             $data3[$index] = $dataA[$i]['im'];
				 $data4[$index] = $dataB[$i]['im'];
				 $data5[$index] = $dataC[$i]['im'];
	             $index+=1;
	        }
		      
	        $chart->yAxis->title->text="Amplitude";
	        //$chart->plotOptions->column->stacking = "normal";
	        
	        //$chart->tooltip->formatter = new HighchartJsExpr("function() { return Highcharts.numberFormat(this.y, 2, ',') +' " . $unit . "';}");
	        $chart->xAxis->categories =$category;
	        $chart->series[0]->type ='spline';
	        $chart->series[0]->data=$data0;
	        $chart->series[0]->stack =0;
	        $chart->series[0]->name = 'Sample average for ';
			$chart->series[0]->lineWidth = 1;
			$chart->series[0]->marker->radius = 2;
			
			$chart->xAxis->categories =$category;
	        $chart->series[1]->type ='spline';
	        $chart->series[1]->data=$data1;
	        $chart->series[1]->stack =0;
	        $chart->series[1]->name = 'Sample average for ';
			$chart->series[1]->lineWidth = 1;
			$chart->series[1]->marker->radius = 2;
			
			$chart->xAxis->categories =$category;
	        $chart->series[2]->type ='spline';
	        $chart->series[2]->data=$data2;
	        $chart->series[2]->stack =0;
	        $chart->series[2]->name = 'Sample average for ';
			$chart->series[2]->lineWidth = 1;
			$chart->series[2]->marker->radius = 2;
			
			/*
			$chart->series[3]->dashStyle = 'shortdot';
			$chart->series[3]->type ='spline';
	        $chart->series[3]->data=$data3;
	        $chart->series[3]->stack =0;
	        $chart->series[3]->name = 'Sample average for ';
			
			$chart->series[4]->dashStyle = 'shortdot';
			$chart->series[4]->type ='spline';
	        $chart->series[4]->data=$data4;
	        $chart->series[4]->stack =0;
	        $chart->series[4]->name = 'Sample average for ';
			
			$chart->series[5]->dashStyle = 'shortdot';
			$chart->series[5]->type ='spline';
	        $chart->series[5]->data=$data5;
	        $chart->series[5]->stack =0;
	        $chart->series[5]->name = 'Sample average for ';
				        */
	  		return $chart;
	}
	
	
	function hGraph($item,$chart,$dataA)
	{
		  $chart->chart->renderTo = $item;
		  $chart->chart->zoomType = 'x';
		  $chart->chart->spacingRight = 20;
		  //$chart->xAxis->type='datetime';
		  $chart->xAxis->lineWidth=0;
		  $chart->xAxis->tickWidth=1;
		
		  $chart->yAxis->startOnTick = false;
		  $chart->yAxis->showFirstLabel = false;
		  //$chart->yAxis->min=0;
		  $chart->xAxis->gridLineWidth= 1;
		  $chart->tooltip->shared = true;
		  $chart->legend->enabled = false;
		  $chart->chart->shadow =true;
		  $chart->chart->plotShadow =true;
		  $chart->plotOptions->series->pointPadding=-0.33333;
		 	  
		  $index=0;
		  foreach($dataA as $key => $val) 
	        {
	             
	             $category[$index] =$key;
	             $data0[$index] = $val;
				 //$data1[$index] = $dataB[$key];
				 //$data2[$index] = $dataC[$key];
	             
	             $index+=1;
	        }
		      
	        $chart->yAxis->title->text="Frequency";
	        //$chart->plotOptions->column->stacking = "normal";
	        
	        //$chart->tooltip->formatter = new HighchartJsExpr("function() { return Highcharts.numberFormat(this.y, 2, ',') +' " . $unit . "';}");
	        $chart->xAxis->categories =$category;
	        $chart->series[0]->type ='column';
	        $chart->series[0]->data=$data0;
	        $chart->series[0]->stack =0;
	        $chart->series[0]->name = 'Sample average for ';
			
			$chart->series[1]->type ='spline';
	        $chart->series[1]->data=$data0;
	        $chart->series[1]->stack =1;
	       
			
			
			/*
			$chart->series[1]->type ='column';
	        $chart->series[1]->data=$data1;
	        $chart->series[1]->stack =0;
	        $chart->series[1]->name = 'Sample average for ';
			
			$chart->series[2]->type ='column';
	        $chart->series[2]->data=$data2;
	        $chart->series[2]->stack =0;
	        $chart->series[2]->name = 'Sample average for ';
			*/
	  		return $chart;
	}


	function hram($data)
	{

		// flattening the array so I remove the "Price" key
		// the array takes the form of array(10,10,11,...)
		$fdata=array();
		foreach ($data as $mdata)
		{
			$fdata +=$mdata;
		}
		
		foreach($mdata as $listing) {
	    	$flattenedListings[] =  $listing['re']; //$listing['Price'];
		}
	
		$widths = range(0, 200, 50); // creates array of the form: array(0, 10, 20, 30, 40, ...)
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
	
	
$cache = phpFastCache();
$x = $cache->get("x");
if ($x == null)
{
	
	echo "set";
$x[0]['re']=96308224;
$x[1]['re']=96702464;
$x[2]['re']=96746496;
$x[3]['re']=94471168;
$x[4]['re']=95558656;
$x[5]['re']=96260096;
$x[6]['re']=96784384;
$x[7]['re']=96476160;
$x[8]['re']=96569344;
$x[9]['re']=96786432;
$x[10]['re']=96867328;
$x[11]['re']=96992256;
$x[12]['re']=96793600;
$x[13]['re']=96923648;
$x[14]['re']=96651264;
$x[15]['re']=97064960;
$x[16]['re']=97041408;
$x[17]['re']=96427008;
$x[18]['re']=96633856;
$x[19]['re']=96686080;
$x[20]['re']=96291840;
$x[21]['re']=96834560;
$x[22]['re']=96703488;
$x[23]['re']=95717376;
$x[24]['re']=95374336;
$x[25]['re']=96317440;
$x[26]['re']=96924672;
$x[27]['re']=96958464;
$x[28]['re']=96698368;
$x[29]['re']=96673792;
$x[30]['re']=97094656;
$x[31]['re']=95942656;
$x[32]['re']=97054720;
$x[33]['re']=96955392;
$x[34]['re']=95016960;
$x[35]['re']=94832640;
$x[36]['re']=96373760;
$x[37]['re']=96181248;
$x[38]['re']=96562176;
$x[39]['re']=96508928;
$x[40]['re']=96329728;
$x[41]['re']=96403456;
$x[42]['re']=96490496;
$x[43]['re']=94560256;
$x[44]['re']=95628288;
$x[45]['re']=96504832;
$x[46]['re']=95833088;
$x[47]['re']=95458304;
$x[48]['re']=94329856;
$x[49]['re']=96551936;
$x[50]['re']=94112768;
$x[51]['re']=96265216;
$x[52]['re']=96206848;
$x[53]['re']=96222208;
$x[54]['re']=96812032;
$x[55]['re']=96174080;
$x[56]['re']=96842752;
$x[57]['re']=96737280;
$x[58]['re']=97025024;
$x[59]['re']=96483328;
$x[60]['re']=96902144;
$x[61]['re']=96808960;
$x[62]['re']=96580608;
$x[63]['re']=96419840;
$x[64]['re']=96957440;
$x[65]['re']=96317440;
$x[66]['re']=97068032;
$x[67]['re']=97013760;
$x[68]['re']=96966656;
$x[69]['re']=96951296;
$x[70]['re']=96535552;
$x[71]['re']=96951296;
$x[72]['re']=95870976;
$x[73]['re']=97070080;
$x[74]['re']=96959488;
$x[75]['re']=97143808;
$x[76]['re']=96932864;
$x[77]['re']=96447488;
$x[78]['re']=96970752;
$x[79]['re']=96624640;
$x[80]['re']=97020928;
$x[81]['re']=96889856;
$x[82]['re']=97157120;
$x[83]['re']=96763904;
$x[84]['re']=96959488;
$x[85]['re']=97061888;
$x[86]['re']=96385024;
$x[87]['re']=97047552;
$x[88]['re']=97044480;
$x[89]['re']=96908288;
$x[90]['re']=97095680;
$x[91]['re']=96243712;
$x[92]['re']=96957440;
$x[93]['re']=96868352;
$x[94]['re']=96641024;
$x[95]['re']=97036288;
$x[96]['re']=96859136;
$x[97]['re']=96057344;
$x[98]['re']=96292864;
$x[99]['re']=95995904;
$x[100]['re']=95312896;
$x[101]['re']=97080320;
$x[102]['re']=96403456;
$x[103]['re']=96403456;
$x[104]['re']=95220736;
$x[105]['re']=96403456;

$x[0]['im']=0;
$x[1]['im']=0;
$x[2]['im']=0;
$x[3]['im']=0;
$x[4]['im']=0;
$x[5]['im']=0;
$x[6]['im']=0;
$x[7]['im']=0;
$x[8]['im']=0;
$x[9]['im']=0;
$x[10]['im']=0;
$x[11]['im']=0;
$x[12]['im']=0;
$x[13]['im']=0;
$x[14]['im']=0;
$x[15]['im']=0;
$x[16]['im']=0;
$x[17]['im']=0;
$x[18]['im']=0;
$x[19]['im']=0;
$x[20]['im']=0;
$x[21]['im']=0;
$x[22]['im']=0;
$x[23]['im']=0;
$x[24]['im']=0;
$x[25]['im']=0;
$x[26]['im']=0;
$x[27]['im']=0;
$x[28]['im']=0;
$x[29]['im']=0;
$x[30]['im']=0;
$x[31]['im']=0;
$x[32]['im']=0;
$x[33]['im']=0;
$x[34]['im']=0;
$x[35]['im']=0;
$x[36]['im']=0;
$x[37]['im']=0;
$x[38]['im']=0;
$x[39]['im']=0;
$x[40]['im']=0;
$x[41]['im']=0;
$x[42]['im']=0;
$x[43]['im']=0;
$x[44]['im']=0;
$x[45]['im']=0;
$x[46]['im']=0;
$x[47]['im']=0;
$x[48]['im']=0;
$x[49]['im']=0;
$x[50]['im']=0;
$x[51]['im']=0;
$x[52]['im']=0;
$x[53]['im']=0;
$x[54]['im']=0;
$x[55]['im']=0;
$x[56]['im']=0;
$x[57]['im']=0;
$x[58]['im']=0;
$x[59]['im']=0;
$x[60]['im']=0;
$x[61]['im']=0;
$x[62]['im']=0;
$x[63]['im']=0;
$x[64]['im']=0;
$x[65]['im']=0;
$x[66]['im']=0;
$x[67]['im']=0;
$x[68]['im']=0;
$x[69]['im']=0;
$x[70]['im']=0;
$x[71]['im']=0;
$x[72]['im']=0;
$x[73]['im']=0;
$x[74]['im']=0;
$x[75]['im']=0;
$x[76]['im']=0;
$x[77]['im']=0;
$x[78]['im']=0;
$x[79]['im']=0;
$x[80]['im']=0;
$x[81]['im']=0;
$x[82]['im']=0;
$x[83]['im']=0;
$x[84]['im']=0;
$x[85]['im']=0;
$x[86]['im']=0;
$x[87]['im']=0;
$x[88]['im']=0;
$x[89]['im']=0;
$x[90]['im']=0;
$x[91]['im']=0;
$x[92]['im']=0;
$x[93]['im']=0;
$x[94]['im']=0;
$x[95]['im']=0;
$x[96]['im']=0;
$x[97]['im']=0;
$x[98]['im']=0;
$x[99]['im']=0;
$x[100]['im']=0;
$x[101]['im']=0;
$x[102]['im']=0;
$x[103]['im']=0;
$x[104]['im']=0;
$x[105]['im']=0;
$cache->set("x",$x,600);
}
else {
	echo "cache";
}


$y[0]['re']=146526208;
$y[1]['re']=146352128;
$y[2]['re']=149030912;
$y[3]['re']=146969600;
$y[4]['re']=149733376;
$y[5]['re']=147209216;
$y[6]['re']=149398528;
$y[7]['re']=148208640;
$y[8]['re']=150109184;
$y[9]['re']=150408192;
$y[10]['re']=149471232;
$y[11]['re']=149073920;
$y[12]['re']=152051712;
$y[13]['re']=149053440;
$y[14]['re']=140637184;
$y[15]['re']=148956160;
$y[16]['re']=137807872;
$y[17]['re']=128965632;
$y[18]['re']=141059072;
$y[19]['re']=150039552;
$y[20]['re']=150235136;
$y[21]['re']=145651712;
$y[22]['re']=146170880;
$y[23]['re']=137804800;
$y[24]['re']=151254016;
$y[25]['re']=137396224;
$y[26]['re']=102049792;
$y[27]['re']=104747008;
$y[28]['re']=119752704;
$y[29]['re']=126198784;
$y[30]['re']=82009088;
$y[31]['re']=106519552;
$y[32]['re']=124399616;
$y[33]['re']=105236480;
$y[34]['re']=124568576;
$y[35]['re']=118240256;
$y[36]['re']=121837568;
$y[37]['re']=103870464;
$y[38]['re']=116190208;
$y[39]['re']=108233728;
$y[40]['re']=91789312;
$y[41]['re']=84292608;
$y[42]['re']=98995200;
$y[43]['re']=108271616;
$y[44]['re']=101434368;
$y[45]['re']=104171520;
$y[46]['re']=98205696;
$y[47]['re']=124567552;
$y[48]['re']=110804992;
$y[49]['re']=120029184;
$y[50]['re']=130577408;
$y[51]['re']=141403136;
$y[52]['re']=147092480;
$y[53]['re']=149527552;
$y[54]['re']=148982784;
$y[55]['re']=149311488;
$y[56]['re']=150403072;
$y[57]['re']=147868672;
$y[58]['re']=147168256;
$y[59]['re']=147616768;
$y[60]['re']=146200576;
$y[61]['re']=147957760;
$y[62]['re']=149708800;
$y[63]['re']=146945024;
$y[64]['re']=147358720;
$y[65]['re']=147666944;
$y[66]['re']=145666048;
$y[67]['re']=146699264;
$y[68]['re']=146803712;
$y[69]['re']=147325952;
$y[70]['re']=145820672;
$y[71]['re']=145531904;
$y[72]['re']=146438144;
$y[73]['re']=145992704;
$y[74]['re']=145449984;
$y[75]['re']=146826240;
$y[76]['re']=146450432;
$y[77]['re']=147329024;
$y[78]['re']=147594240;
$y[79]['re']=147930112;
$y[80]['re']=148864000;
$y[81]['re']=147300352;
$y[82]['re']=148241408;
$y[83]['re']=148493312;
$y[84]['re']=148335616;
$y[85]['re']=149008384;
$y[86]['re']=149946368;
$y[87]['re']=150228992;
$y[88]['re']=150471680;
$y[89]['re']=148207616;
$y[90]['re']=150564864;
$y[91]['re']=149807104;
$y[92]['re']=147112960;
$y[93]['re']=137097216;
$y[94]['re']=149782528;
$y[95]['re']=139898880;
$y[96]['re']=150244352;
$y[97]['re']=149735424;
$y[98]['re']=148629504;
$y[99]['re']=140643328;
$y[100]['re']=144399360;
$y[101]['re']=146392064;
$y[102]['re']=137538560;
$y[103]['re']=144135168;
$y[104]['re']=129903616;
$y[105]['re']=134052864;

$y[0]['im']=0;
$y[1]['im']=0;
$y[2]['im']=0;
$y[3]['im']=0;
$y[4]['im']=0;
$y[5]['im']=0;
$y[6]['im']=0;
$y[7]['im']=0;
$y[8]['im']=0;
$y[9]['im']=0;
$y[10]['im']=0;
$y[11]['im']=0;
$y[12]['im']=0;
$y[13]['im']=0;
$y[14]['im']=0;
$y[15]['im']=0;
$y[16]['im']=0;
$y[17]['im']=0;
$y[18]['im']=0;
$y[19]['im']=0;
$y[20]['im']=0;
$y[21]['im']=0;
$y[22]['im']=0;
$y[23]['im']=0;
$y[24]['im']=0;
$y[25]['im']=0;
$y[26]['im']=0;
$y[27]['im']=0;
$y[28]['im']=0;
$y[29]['im']=0;
$y[30]['im']=0;
$y[31]['im']=0;
$y[32]['im']=0;
$y[33]['im']=0;
$y[34]['im']=0;
$y[35]['im']=0;
$y[36]['im']=0;
$y[37]['im']=0;
$y[38]['im']=0;
$y[39]['im']=0;
$y[40]['im']=0;
$y[41]['im']=0;
$y[42]['im']=0;
$y[43]['im']=0;
$y[44]['im']=0;
$y[45]['im']=0;
$y[46]['im']=0;
$y[47]['im']=0;
$y[48]['im']=0;
$y[49]['im']=0;
$y[50]['im']=0;
$y[51]['im']=0;
$y[52]['im']=0;
$y[53]['im']=0;
$y[54]['im']=0;
$y[55]['im']=0;
$y[56]['im']=0;
$y[57]['im']=0;
$y[58]['im']=0;
$y[59]['im']=0;
$y[60]['im']=0;
$y[61]['im']=0;
$y[62]['im']=0;
$y[63]['im']=0;
$y[64]['im']=0;
$y[65]['im']=0;
$y[66]['im']=0;
$y[67]['im']=0;
$y[68]['im']=0;
$y[69]['im']=0;
$y[70]['im']=0;
$y[71]['im']=0;
$y[72]['im']=0;
$y[73]['im']=0;
$y[74]['im']=0;
$y[75]['im']=0;
$y[76]['im']=0;
$y[77]['im']=0;
$y[78]['im']=0;
$y[79]['im']=0;
$y[80]['im']=0;
$y[81]['im']=0;
$y[82]['im']=0;
$y[83]['im']=0;
$y[84]['im']=0;
$y[85]['im']=0;
$y[86]['im']=0;
$y[87]['im']=0;
$y[88]['im']=0;
$y[89]['im']=0;
$y[90]['im']=0;
$y[91]['im']=0;
$y[92]['im']=0;
$y[93]['im']=0;
$y[94]['im']=0;
$y[95]['im']=0;
$y[96]['im']=0;
$y[97]['im']=0;
$y[98]['im']=0;
$y[99]['im']=0;
$y[100]['im']=0;
$y[101]['im']=0;
$y[102]['im']=0;
$y[103]['im']=0;
$y[104]['im']=0;
$y[105]['im']=0;

$z[0]['re']=149364736;
$z[1]['re']=146921472;
$z[2]['re']=148500480;
$z[3]['re']=147030016;
$z[4]['re']=145134592;
$z[5]['re']=146501632;
$z[6]['re']=146009088;
$z[7]['re']=145373184;
$z[8]['re']=145000448;
$z[9]['re']=147729408;
$z[10]['re']=146488320;
$z[11]['re']=147038208;
$z[12]['re']=146343936;
$z[13]['re']=146902016;
$z[14]['re']=149262336;
$z[15]['re']=104980480;
$z[16]['re']=104346624;
$z[17]['re']=86627328;
$z[18]['re']=116195328;
$z[19]['re']=135673856;
$z[20]['re']=57220096;
$z[21]['re']=145951744;
$z[22]['re']=145746944;
$z[23]['re']=146177024;
$z[24]['re']=144312320;
$z[25]['re']=145318912;
$z[26]['re']=145039360;
$z[27]['re']=149391360;
$z[28]['re']=143629312;
$z[29]['re']=145175552;
$z[30]['re']=145198080;
$z[31]['re']=143379456;
$z[32]['re']=144713728;
$z[33]['re']=144174080;
$z[34]['re']=143324160;
$z[35]['re']=143757312;
$z[36]['re']=143862784;
$z[37]['re']=143937536;
$z[38]['re']=144166912;
$z[39]['re']=143696896;
$z[40]['re']=143523840;
$z[41]['re']=144466944;
$z[42]['re']=144194560;
$z[43]['re']=143693824;
$z[44]['re']=143950848;
$z[45]['re']=143987712;
$z[46]['re']=144100352;
$z[47]['re']=144024576;
$z[48]['re']=143814656;
$z[49]['re']=144425984;
$z[50]['re']=144129024;
$z[51]['re']=144287744;
$z[52]['re']=143549440;
$z[53]['re']=146801664;
$z[54]['re']=144121856;
$z[55]['re']=144071680;
$z[56]['re']=144985088;
$z[57]['re']=145249280;
$z[58]['re']=144428032;
$z[59]['re']=146425856;
$z[60]['re']=144504832;
$z[61]['re']=145636352;
$z[62]['re']=145849344;
$z[63]['re']=144619520;
$z[64]['re']=145154048;
$z[65]['re']=146004992;
$z[66]['re']=146087936;
$z[67]['re']=145623040;
$z[68]['re']=147125248;
$z[69]['re']=147034112;
$z[70]['re']=146945024;
$z[71]['re']=145659904;
$z[72]['re']=114582528;
$z[73]['re']=96270336;
$z[74]['re']=98415616;
$z[75]['re']=103014400;
$z[76]['re']=150564864;
$z[77]['re']=144821248;
$z[78]['re']=145836032;
$z[79]['re']=145881088;
$z[80]['re']=144894976;
$z[81]['re']=144704512;
$z[82]['re']=145442816;
$z[83]['re']=144455680;
$z[84]['re']=143901696;
$z[85]['re']=146532352;
$z[86]['re']=144896000;
$z[87]['re']=144313344;
$z[88]['re']=143922176;
$z[89]['re']=144148480;
$z[90]['re']=143753216;
$z[91]['re']=145870848;
$z[92]['re']=144528384;
$z[93]['re']=145021952;
$z[94]['re']=143551488;
$z[95]['re']=144388096;
$z[96]['re']=143361024;
$z[97]['re']=145697792;
$z[98]['re']=144950272;
$z[99]['re']=145826816;
$z[100]['re']=146331648;
$z[101]['re']=146109440;
$z[102]['re']=146006016;
$z[103]['re']=147804160;
$z[104]['re']=145334272;
$z[105]['re']=146394112;

$z[0]['im']=0;
$z[1]['im']=0;
$z[2]['im']=0;
$z[3]['im']=0;
$z[4]['im']=0;
$z[5]['im']=0;
$z[6]['im']=0;
$z[7]['im']=0;
$z[8]['im']=0;
$z[9]['im']=0;
$z[10]['im']=0;
$z[11]['im']=0;
$z[12]['im']=0;
$z[13]['im']=0;
$z[14]['im']=0;
$z[15]['im']=0;
$z[16]['im']=0;
$z[17]['im']=0;
$z[18]['im']=0;
$z[19]['im']=0;
$z[20]['im']=0;
$z[21]['im']=0;
$z[22]['im']=0;
$z[23]['im']=0;
$z[24]['im']=0;
$z[25]['im']=0;
$z[26]['im']=0;
$z[27]['im']=0;
$z[28]['im']=0;
$z[29]['im']=0;
$z[30]['im']=0;
$z[31]['im']=0;
$z[32]['im']=0;
$z[33]['im']=0;
$z[34]['im']=0;
$z[35]['im']=0;
$z[36]['im']=0;
$z[37]['im']=0;
$z[38]['im']=0;
$z[39]['im']=0;
$z[40]['im']=0;
$z[41]['im']=0;
$z[42]['im']=0;
$z[43]['im']=0;
$z[44]['im']=0;
$z[45]['im']=0;
$z[46]['im']=0;
$z[47]['im']=0;
$z[48]['im']=0;
$z[49]['im']=0;
$z[50]['im']=0;
$z[51]['im']=0;
$z[52]['im']=0;
$z[53]['im']=0;
$z[54]['im']=0;
$z[55]['im']=0;
$z[56]['im']=0;
$z[57]['im']=0;
$z[58]['im']=0;
$z[59]['im']=0;
$z[60]['im']=0;
$z[61]['im']=0;
$z[62]['im']=0;
$z[63]['im']=0;
$z[64]['im']=0;
$z[65]['im']=0;
$z[66]['im']=0;
$z[67]['im']=0;
$z[68]['im']=0;
$z[69]['im']=0;
$z[70]['im']=0;
$z[71]['im']=0;
$z[72]['im']=0;
$z[73]['im']=0;
$z[74]['im']=0;
$z[75]['im']=0;
$z[76]['im']=0;
$z[77]['im']=0;
$z[78]['im']=0;
$z[79]['im']=0;
$z[80]['im']=0;
$z[81]['im']=0;
$z[82]['im']=0;
$z[83]['im']=0;
$z[84]['im']=0;
$z[85]['im']=0;
$z[86]['im']=0;
$z[87]['im']=0;
$z[88]['im']=0;
$z[89]['im']=0;
$z[90]['im']=0;
$z[91]['im']=0;
$z[92]['im']=0;
$z[93]['im']=0;
$z[94]['im']=0;
$z[95]['im']=0;
$z[96]['im']=0;
$z[97]['im']=0;
$z[98]['im']=0;
$z[99]['im']=0;
$z[100]['im']=0;
$z[101]['im']=0;
$z[102]['im']=0;
$z[103]['im']=0;
$z[104]['im']=0;
$z[105]['im']=0;


for ($i=106;$i<128;$i++)
{
	$x[$i]['im']=$x[$i-100]['im'];
	$x[$i]['re']=$x[$i-100]['re'];
	$y[$i]['im']=$y[$i-100]['im'];
	$y[$i]['re']=$y[$i-100]['re'];
	$z[$i]['im']=$z[$i-100]['im'];
	$z[$i]['re']=$z[$i-100]['re'];
}

for ($i=0;$i<count($x);$i++)
{
	$x[$i]['im']/=1024*1024;
	$x[$i]['re']/=1024*1024;
	$y[$i]['im']/=1024*1024;
	$y[$i]['re']/=1024*1024;
	$z[$i]['im']/=1024*1024;
	$z[$i]['re']/=1024*1024;
}

	$mfftx=FFT($x,false);
	$mffty=FFT($y,false);
	$mfftz=FFT($z,false);
	
	$imfftx=FFT($x,true);
	$imffty=FFT($y,true);
	$imfftz=FFT($z,true);
	
	$xx=hram(array($x,$y,$z));
	
	$hchart1 = new Highchart();
	$hchart1=hGraph("hitem1", $hchart1, $xx);
	
	echo '<div id="hitem1"></div>';
    foreach ($hchart1->getScripts() as $script) 
    {
          echo '<script type="text/javascript" src="' . $script . '"></script>';
    }
    echo '<script type="text/javascript">' . $hchart1->render("hchart1") . '</script>';
	
	
	
	// mComplexToString($mfft);
	$chart1 = new Highchart();
	$chart1=Graph('item1',$chart1,$mfftx,$mffty,$mfftz,count($mfftx)/2);
	echo '<div id="item1"></div>';
    foreach ($chart1->getScripts() as $script) 
    {
          echo '<script type="text/javascript" src="' . $script . '"></script>';
    }
    echo '<script type="text/javascript">' . $chart1->render("chart1") . '</script>';

/*
	$chart2 = new Highchart();
	$chart2=Graph('item2',$chart2,$x,$y,$z,count($x));
	echo '<div id="item2"></div>';
    foreach ($chart2->getScripts() as $script) 
    {
          echo '<script type="text/javascript" src="' . $script . '"></script>';
    }
    echo '<script type="text/javascript">' . $chart2->render("chart2") . '</script>';

*/
 /* Create the pChart object */

 for ($j=0;$j<4;$j++)
 {
   for ($i=0;$i<count($x);$i++)
   {
 	  $data1[]=round($x[$i]['re'],0);
	  $data2[]=round($y[$i]['re'],0);
	  $data3[]=round($z[$i]['re'],0);
	  $labels[]=$i+$j*count($x);
	  
   }
 }
 //var_dump($data1);
 $MyData = new pData();  
 $MyData->addPoints($data1,"Probe 1");
 $MyData->addPoints($data2,"Probe 2");
 $MyData->addPoints($data3,"Probe 3");

 //$myPicture->drawScale(array("DrawYLines"=>array(0)));

 //$MyData->setSerieTicks("Probe 2",1);
 //$MyData->setSerieWeight("Probe 3",1);
 $MyData->setAxisName(0,"Temperatures");
 $MyData->addPoints($labels,"Labels");
 $MyData->setSerieDescription("Labels","Months");
 $MyData->setSerieTicks(10);
 $MyData->setAbscissa("Labels");
 
 $MyData->setPalette("Probe 1",array("R"=>13,"G"=>35,"B"=>58));
 $MyData->setPalette("Probe 2",array("R"=>145,"G"=>0,"B"=>0)); 
 $MyData->setPalette("Probe 3",array("R"=>242,"G"=>143,"B"=>67)); 
 
 /* Create the pChart object */
 $W=1024;
 if (isset($_GET["w"])) 
 {
 	$W=$_GET["w"];
 }


 $myPicture = new pImage($W,400,$MyData);
 /* Turn of Antialiasing */
 $myPicture->Antialias = FALSE;
 //$myPicture->drawGraphAreaGradient(132,153,172,50,TARGET_BACKGROUND);  
 /* Add a border to the picture */
 //$myPicture->drawRectangle(0,0,1023,399,array("R"=>0,"G"=>0,"B"=>0));
 //$myPicture->drawGradientArea(0,0,1024,400, DIRECTION_HORIZONTAL); 
 //$myPicture->drawFilledRectangle(0,0,1024,400,array("R"=>47,"G"=>126,"B"=>216,"Surrounding"=>-200,"Alpha"=>80));
 /* Write the chart title */ 
 
 $Settings = array("R"=>179, "G"=>217, "B"=>91, "Dash"=>1, "DashR"=>199, "DashG"=>237, "DashB"=>111);
 $myPicture->drawFilledRectangle(0,0,$W,400,$Settings);
 $Settings = array("StartR"=>194, "StartG"=>231, "StartB"=>44, "EndR"=>43, "EndG"=>107, "EndB"=>58, "Alpha"=>50);
 $myPicture->drawGradientArea(60,40,$W-20,380,DIRECTION_VERTICAL,$Settings);
 //$myPicture->drawGradientArea(60,40,1010,380,DIRECTION_VERTICAL,array("StartR"=>0,"StartG"=>0,"StartB"=>0,"EndR"=>50,"EndG"=>50,"EndB"=>50,"Alpha"=>100));


 $myPicture->setFontProperties(array("FontName"=>"pChart/fonts/Forgotte.ttf","FontSize"=>11));
 $myPicture->drawText(150,35,"Average temperature",array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));

 /* Set the default font */
 $myPicture->setFontProperties(array("FontName"=>"pChart/fonts/pf_arma_five.ttf","FontSize"=>6));

 /* Define the chart area */
 $myPicture->setGraphArea(60,40,$W-20,380);

 /* Draw the scale */
 $scaleSettings = array("LabelSkip"=>round(count($data1)/10),"DrawXLines"=>TRUE,"XMargin"=>10,"YMargin"=>10,"Floating"=>TRUE,"GridR"=>200,"GridG"=>200,"GridB"=>200,"DrawSubTicks"=>FALSE,"CycleBackground"=>TRUE);
 $myPicture->drawScale($scaleSettings);

 /* Turn on Antialiasing */
 $myPicture->Antialias = TRUE;

 /* Draw the line chart */
 $myPicture->drawsplineChart();
 $myPicture->drawPlotChart();

 /* Write the chart legend */
 $myPicture->drawLegend(540,20,array("Style"=>LEGEND_BOX,"Mode"=>LEGEND_HORIZONTAL));

 //$myPicture->drawScale(array("AutoAxisLabels"=>TRUE,"Pos"=>SCALE_POS_TOPBOTTOM));

 /* Render the picture (choose the best way) */
  // $myPicture->autoOutput("simple2.png");
 
  ob_start();
  imagepng($myPicture->Picture);
  $contents = ob_get_contents();
  ob_end_clean();


print "<img src='data:image/png;base64,".base64_encode($contents)."' />\n";
 

 

?>