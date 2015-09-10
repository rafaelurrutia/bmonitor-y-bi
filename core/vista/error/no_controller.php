<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv='Content-type' content='text/html; charset=UTF-8' />
	<title>KairoFW</title>
	<link rel="stylesheet" href="{base_url}sitio/css/style.css" />
    <link rel="stylesheet" href="{base_url}sitio/css/bienvenida.css" />
    <link rel="stylesheet" href="{base_url}sitio/css/exception.css" />
</head>
<body>
<div class="exception">
<div  class="error_message round">
<h1>Controlador no encontrado</h1>
<p><strong>Error: </strong><em>{name_control} Controller</em> no existe o no se puede encontrar.</p>
</div>
<div class="success_message round">
<h1>Solución</h1>
    <h3><strong>Crea la clase <em>{name_control} Controller</em> de abajo en el fichero : <em>{dir_control}{name_control}.php</em></strong></h3>
        
<pre>
&lt;?php
class {name_control} extends Control  {

	
}
</pre>
</div>
</div>
    <div id="footer">

	</div>
</body>
</html>