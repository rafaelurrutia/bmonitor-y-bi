<?php
/**
 * This file is property of Baking Software SpA.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  Sidebar
 * @author   Rodrigo Montes <rodrigo@bsw.cl>
 * @license  http://www.baking.cl/license BSD Licence
 * @version  SVN: $Id: index.phpl,v 1.9 2008-10-09 15:16:47 cweiske Exp $
 * @link     http://mnla.com 
 */
?>
 
<?php
$route = 'home0';
if (isset($_GET['route'])) {
    $route = $_GET['route'];
}
if (isset($_POST['route'])) {
    $route = $_POST['route'];
}

if (php_uname("s") != "Darwin")
{
  $valida = $cmd->protect->allowed('APPS_BMONITOR');
}
else {
	$valida=true;
}

if($valida) {
	$bmonitorLink = '<li><a class="list-group-item" href="/"><span class="glyphicon glyphicon-arrow-right"></span>bMonitor</a></li>';
} else {
	$bmonitorLink = '';
}

	
?>

<!--<div class="input-append iwAppSearcher">
<input class="iwAppFilterInput" type="text" placeholder='Buscar app' />
<button class="btn" type="button"><i class="icon-search"></i></button>
</div>-->


 <ul class="nav list-group">
    <li <?php if ($route=='home0') echo 'class="active"'; ?>>
        <a  class="list-group-item" href="index.php?route=home0"><span class="glyphicon glyphicon glyphicon-th-list"></span>Dashboard</a>
    </li>
    <li <?php if ($route=='home') echo 'class="active"'; ?>>
        <a  class="list-group-item" href="index.php?route=home"><span class="glyphicon glyphicon glyphicon-stats"></span>Graph Compare</a>
    </li>
    <li <?php if ($route=='home1') echo 'class="active"'; ?>>
        <a  class="list-group-item" href="index.php?route=home1"> <span class="glyphicon glyphicon glyphicon-stats"></span>Grouped Graphs</a>
    </li>
    <li <?php if ($route=='status') echo 'class="active"'; ?>>
        <a  class="list-group-item" href="index.php?route=status"> <span class="glyphicon glyphicon glyphicon-warning-sign"></span>Status</a>
    </li>
    <li <?php if ($route=='homec') echo 'class="active"'; ?>>
        <a  class="list-group-item" href="index.php?route=homec"><span class="glyphicon glyphicon-cog"></span>Configure</a>
    </li>
    <li>
        <a  class="list-group-item" href="<?php echo 'http://www.bsw.cl';?>"><span class="glyphicon glyphicon-arrow-right"></span>Company</a>
    </li>
    <?php echo $bmonitorLink; ?>
    <li>
        <a  class="list-group-item" href="index.php?route=logout"><span class="glyphicon glyphicon-off"></span>Exit</a>
    </li>
</ul>

