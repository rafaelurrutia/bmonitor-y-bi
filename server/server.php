<?php
/**
 * This file is property of Baking Software SpA.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  cli.poller
 * @author   Carlos Lazcano <carlos@bsw.cl>
 * @license  http://www.baking.cl/license BSD Licence
 * @version  SVN: 1: index.phpl,v 1.0 2012-11-22 14:06:47 cweiske Exp $
 * @link     http://baking.cl
 */
header('Content-type: text/html; charset=UTF-8');

$SITE_PATH = realpath(dirname(__FILE__) . '/../') . "/";

require $SITE_PATH . 'core/startup.inc.php';

/**
 *  Class Poller
 */


// alerta que tiene que ver por las pruebas que no se cumplieron por el treshold
// alerta por indisponibilidad de las sondas

//importante bm_item_profile, no se esta usando
// los valores configurable de las alertas se guardan en bm_treshold. revisar los cliclos.

print_r ("Server.php");



?>