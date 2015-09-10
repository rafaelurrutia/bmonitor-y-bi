<?php
/**
 * This file is property of Baking Software SpA.
 *
 * PHP version 5
 *
 * @category Home
 * @package  Index
 * @license  http://www.baking.cl/license BSD Licence
 * @version  SVN: $Id: index.phpl,v 1.9 2008-10-09 15:16:47 cweiske Exp $
 * @link     http://mnla.com
 */
require_once 'api.php';
require_once "HTMLHeaders.php";
require_once "HTMLNavigationBar.php";
?>
<div id="wrapper">
    <div id="sidebar-wrapper" class="col-md-2">
        <div class="menu" id="sidebar">
            <?php
            require_once "HTMLSidebar.php";
            ?>
        </div>
    </div>
    <div id="main-wrapper" class="col-md-10 pull-right">
        <div id="main">
            <div class="panel panel-default">

                <div class="panel-body">
                    <style>
                        .breadcrumb>li+li:before{padding:0 5px;color:#ccc;content: '|';}
                        .table>thead>tr>th,.table>tbody>tr>th,.table>tfoot>tr>th,.table>thead>tr>td,.table>tbody>tr>td,.table>tfoot>tr>td{padding: 3px;}
                    </style>
                    <script>
                        jQuery(function($){

                             $('.tooltip-tabla').tooltip({
                            selector: "[data-toggle=tooltip]",
                            container: "body"
                        })  });
                    </script>
                    <?php
                    require_once "dashboard.php";
                    ?>
                </div>

        </div>
        <footer class="footer">
            <center>
                <p>
                    <small>© Baking Software SpA</small>
                </p>
            </center>
        </footer>
    </div>
</div>
</body>
</html>