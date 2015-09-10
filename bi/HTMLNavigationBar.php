<?php

/**
 * This file is property of Baking Software SpA.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  Config
 * @author   Rodrigo Montes <rodrigo@bsw.cl>
 * @license  http://www.baking.cl/license BSD Licence
 * @version  SVN: $Id: index.phpl,v 1.9 2008-10-09 15:16:47 cweiske Exp $
 * @link     http://mnla.com
 */
?>
<!-- HEADER -->

<nav id="header" class="navbar navbar-blue navbar-fixed-top" role="navigation">
    <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="#">BakingSoftware</a>
    </div>
    
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
        <ul class="nav navbar-nav navbar-right">
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo $_SESSION['username'] . ' [' .$_SESSION['profile'] . ']'; ?>
                <b class="caret"></b></a>
                <ul class="dropdown-menu">
                    
                        <?php echo listProfiles(); ?>
       
                </ul>
            </li>
        </ul>
    </div>
</nav>