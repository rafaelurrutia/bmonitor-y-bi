{header}
	<script language="JavaScript" type="text/javascript" src="{url_base}sitio/js/view.login.js?v=123"></script>
	<script type="text/javascript">
		jQuery(function($) {
			$("#username").focus();
		});
	</script>
    <style type="text/css">
        label {
            width: 75px;
            font-weight: bold;
            display: block;
            float: left;
            line-height: 24px;
            text-align: right;
        }
    
        input[type=text], input[type=password], textarea, select {
            width: 209px;
            height: 18px;
            margin-left: 5px;
        }
    
        /* Login Page Styles */
        #login #content_login {
            width: 344px;
        }
        #login div#section {
            font-size: 12px;
            width: 345px;
            background-color: #ccc;
            position: relative;
            z-index: 1003;
        }
        #login div#section p {
            padding-bottom: 20px;
        }
        #login p.ui-state-error {
            padding: 5px;
            display: none;
        }
        #login div#header_1, #login div#section div.ui-widget-content {
            padding: 12px;
        }
        #login div#section form div {
            padding: 8px 0px;
        }
        #login #btnLogin {
            margin-left: 80px;
        }
        #login .ui-widget-overlay {
            z-index: 1002;
        }
	</style>
</head>

<body id="login" class="ui-widget-overlay">
    
    <div class="container">
        {menu}
		<div id="content">
            <div id="content_login">
                <div id="section"  class="ui-widget ui-corner-all">
        
                    <div id="header_1" class="ui-widget-header ui-corner-top">Login</div>
        
                    <div class="ui-widget-content ui-corner-bottom">

                        <p class="ui-state-error">Login username/password incorrect.</p>
            
                        <form id="form_login2" method="post" action="login/access">
                            <div>
                                <label for="username">{USER_LOGIN}:</label> 
                                <input type="text" name="username" autofocus="autofocus" id="username" />
                            </div>
                            <div>
                                <label for="password">{USER_PASS}:</label> 
                                <input type="password" name="password" id="password"/>
                            </div>
                         
            				<div>
                          	<label for="lang" accesskey="g">{USER_LANG}:</label>
            				<select name="lang" id="lang">
            					<option value="false">Default</option>
            					<option value="es">Español</option>
            					<option value="en">Ingles</option>
            				</select>
            				</div>
                            <div>
                                <input type="submit" id="btnLogin" value="{USER_BUTTON}"/>
                            </div>
                            <p>ver. 2.2.0</p>
                        </form>
                </div>
            </div>
		</div>
</div>
<div class="push"></div>
</div>
{footer}
</body>
</html>