#!/bin/bash


cd /var/www/site/bmonitor25/server/
#estos archivos son necesarios para generar el tab QoS->Resumen de Bmonitor
php splitneu.php
#echo "splitneu.php hecho" > /var/www/site/bmonitor.baking.cl/logs/a1.txt

php neutralidad.php
#echo "neutralidad.php hecho" > /var/www/site/bmonitor.baking.cl/logs/a2.txt

php neutralidad.php Q
#echo "neutralidad.php Q  hecho" > /var/www/site/bmonitor.baking.cl/logs/a3.txt



