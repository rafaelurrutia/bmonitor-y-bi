<?php
//Configuracion de la licencia
$parametro["license_company"] = "bMonitor";
$parametro["license_code"] = "dJHohqXUrwKbFZPmSh6/TxLmcqS5KVVaCGoJK+8J2sM=";

//Conexion BD
$parametro["ConnectBD"] = true;
$parametro["MotorBD"] = "mysql";
$parametro["HostBD"] = "127.0.0.1";
$parametro["UserBD"] = "root";
$parametro["PassBD"] = "bsw$$2009";
$parametro["NameBD"] = "bmonitor";

$parametro["HostBD_slave"] = "127.0.0.1";
$parametro["UserBD_slave"] = "enlaces";
$parametro["PassBD_slave"] = "bswenlaces";
$parametro["NameBD_slave"] = "bsw";

//Configuracion Base
$parametro["URL_BASE"] = "http://bmonitor.baking.cl";
$parametro["URL_BASE_LOGIN"] = "http://bmonitor.baking.cl/login.php";

//Configuracion Logs
$parametro["LOGS_APPS"] = "bswserver";
$parametro["DEBUG"] = false;
$parametro["LOG_DB"] = true;
$parametro["LOG_PARAM"] = false;
$parametro["LOG_HTTPREST"] = true;
$parametro["STDOUT"] = false;

//Correos
$parametro["FROM"] = "alertas@bsw.cl";
$parametro["TO"] = "carlos.lazcano@bsw.cl";
$parametro["REPLY_TO"] = "carlos.lazcano@bsw.cl";

//Configuracion de Curl
$parametro["RETRY"] = "1";
$parametro["MAXPARALELO"] = "30";
$parametro["TIMEOUT"] = "4";
$parametro["USERAGENT"] = "10";
$parametro["SSL_VERIFYPEER"] = FALSE;
$parametro["SSL_VERIFYHOST"] = FALSE;
$parametro["FOLLOWLOCATION"] = TRUE;
$parametro["RETURNTRANSFER"] = TRUE;

//Configuracion de Thread
$parametro["THREAD_LIMIT"] = "15";
$parametro["THREAD_TIMEOUT"] = "10";
$parametro["THREAD_PORT"] = "80";
$parametro["THREAD_HOST"] = "localhost";
$parametro["THREAD_SCRIPT"] = "/poller/process";

//Configuracion de BSW

$parametro["BSWTRAP"]			=	"PENA_H1_#_190_82_87_11_enlaces2";
$parametro["BSWCOMMUNITY"]		=	"public";

$parametro["BSWMASTER"]			=	"bmonitor.baking.cl";

$parametro["BSWSLAVE"]			=	"bmonitor.baking.cl";
$parametro["BSW_SERVER"]		=	"bmonitor.baking.cl";

$parametro["DEFAULTLANGUAGE"]	=	'es';
$parametro["LANGUAGE"]			=	array('es','en');

//Configuracion de Login
$parametro["COMPANY_FILTER"] = FALSE;
$parametro["MAX_ATTEMPTS"] = 8;
$parametro["SESSION_DURATION"] = "+6 hours";
$parametro["LOCKED_DURATION"] = "+30 MINUTE";
$parametro["SALT_1"] = 'perro$$come&&123';
$parametro["SALT_2"] = 'perro$$come&&123';
