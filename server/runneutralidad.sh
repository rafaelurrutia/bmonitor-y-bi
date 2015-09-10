#!/bin/bash
#
# Provides     : BSW Limitada
# Description  : Neutralidad
# Program      : runneutralidad.sh - 1.0
# Version      : 1.0
# Release Date : 02/2012
# Config file  :
#
clear
echo "----------------------------------------------------------------------------"
echo "                                                                            "
echo "    ____   _______          __  _      _           _ _            _         "
echo "   |  _ \ / ____\ \        / / | |    (_)         (_) |          | |        "
echo "   | |_) | (___  \ \  /\  / /  | |     _ _ __ ___  _| |_ __ _  __| | __ _   "
echo "   |  _ < \___ \  \ \/  \/ /   | |    | | '_ \` _ \| | __/ _\` |/ _\` |/ _\` |  "
echo "   | |_) |____) |  \  /\  /    | |____| | | | | | | | || (_| | (_| | (_| |_ "
echo "   |____/|_____/    \/  \/     |______|_|_| |_| |_|_|\__\__,_|\__,_|\__,_(_)"
echo "                                                                            "
echo "----------------------------------------------------------------------------"

cd /var/www/sitios/apibmonitor.baking.cl/server
/usr/bin/php splitneu2.php
/usr/bin/php neutralidad2.php
/usr/bin/php neutralidad2.php Q
/usr/bin/php subtel.php
