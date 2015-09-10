<?php
/**
 * This file is property of Baking Software SpA.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  Login
 * @author   Rodrigo Montes <rodrigo@bsw.cl>
 * @license  http://www.baking.cl/license BSD Licence
 * @version  SVN: $Id: index.phpl,v 1.9 2008-10-09 15:16:47 cweiske Exp $
 * @link     http://mnla.com 
 */
 include_once 'api.php';
 ?>
 <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo printName(); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- Le styles -->
    <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/bootstrap-iw.css" rel="stylesheet">
    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
</head>
<body>
    
    <div id="content" class="container">
        <form method="post" class="well well-white login" method="POST" 
                action="index.php">
            <div class="logo">
                <img src="http://qos.baking.cl/sitio/img/logobmonitor.png" 
                    alt="Baking Software"/>
            </div>

            <div class="input-prepend">
                <span class="add-on"><i class="icon-user"></i></span>
                <input class="" id="prependedInput" type="text" placeholder=""  
                        tabindex="1" name="username">
            </div>
            <br/>
            <div class="input-prepend">
                <span class="add-on"><i class="icon-lock"></i></span>
                <input class=" " id="prependedInput" type="password" placeholder="" 
                        tabindex="2" name="password">
            </div>

<?php 
if (isset($_POST['username']) && !isset($_SESSION['login'] )) {
    echo '<div class="alert alert-error">User / password invalid/s</div>'; 
}
?>

            <button class="btn btn-info btn-block" tabindex="4" 
                type="submit">Login</button>

            <hr />
            <div class="footer clearfix">
            </div>
        </form>
        <br/>
        <!-- Footer
    ================================================== -->
        <footer class="footer">
            <center><p><small>&COPY; Baking Software SpA</small></p></center>
        </footer>
        
    </div>
    <script src="assets/js/libs/jquery/jquery-1.9.1.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
