<?php
/**
 * This file is property of Baking Software SpA.
 *
 * PHP version 5
 *
 * @category Home
 * @package  Index
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
                <div class="panel-heading">Threshold</div>
                <div class="panel-body">
                    <?php
                    require_once "configureBI.php";
                    ?>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading">General</div>
                <div class="panel-body">
                    <?php
                    require_once "configureBIMore.php";
                    ?>
                </div>
            </div>

            <?php
             if (php_uname("s") != "Darwin")
            	$isSecret = $cmd->protect->isSecret();
			 else {
			    $isSecret = true;
			 }
            	if($isSecret) {

					$STATUS_EXECUTE_BI = $cmd->parametro->get("STATUS_EXECUTE_BI", 0);

            ?>
			<script type="text/javascript">

				$(document).ready(function() {
				<?php if($STATUS_EXECUTE_BI == 1 || $STATUS_EXECUTE_BI == 2) { ?>
 					$('#executeRunBI').button('loading');
				<?php } ?>
					$('#executeRunBI').click(function() {
						var btn = $(this)
						btn.button('loading');
						$.ajax({
							type : "POST",
							url : "/bi/index.php?route=console&SETEXECUTE=1",
							success : function(data) {
								//Valid
								//alert("Save OK");
							}
						});
					});
				});
			</script>
             <div class="panel panel-default">

                <div class="panel-heading">
                <button type="button" id="executeRunBI" data-loading-text="Loading..." class="btn btn-primary">
					Execute runBI
				</button>

                </div>
                <div class="panel-body">
                    <?php
                    require_once "console.php";
                    ?>
                </div>
            </div>
            <?php } ?>
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