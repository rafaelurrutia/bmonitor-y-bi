#!/bin/bash

# Parametros

DB_NAME=bsw
DAY_BACKUP=3
MAIL_ERROR=soporte@baking.cl

DIR_BACKUP=/home/linksys/backup

# Variables

MYSQLDUMP="$(which mysqldump)"
CHOWN="$(which chown)"
CHMOD="$(which chmod)"
GZIP="$(which gzip)"
MAIL="$(which mail)"
HOSTNAME="$(hostname)"


NOW="$(date +"%d-%m-%Y")"
YESTERDAY="$(awk 'BEGIN{ printf("%s\n", strftime("%Y-%m-%d", systime()-86400)) }')"

#Ingresando al directorio

cd $DIR_BACKUP


#Registrando tamaño

FILE_COUNT=$(ls -1 *.gz | wc -l)

if [ $FILE_COUNT -ge 1 ] ; then

	LAST_FILE_NAME="$(ls -1 *.gz | tail -n 1)"

	LAST_FILE_SIZE="$(stat -c %s $LAST_FILE_NAME)"

fi

#Iniciando Backup

FILE="$DB_NAME"_$(date +"%Y-%m-%d").sql.gz

$MYSQLDUMP $DB_NAME | $GZIP -9 > $FILE


#Validando tamaño

if [ $FILE_COUNT -ge 1 ] ; then

	CURRENT_FILE_SIZE="$(stat -c %s $FILE)"
	
	if [ $CURRENT_FILE_SIZE -ge $LAST_FILE_SIZE ]; then
		
		FILE_COUNT=$(ls -1 *.gz | wc -l)
		
		if [ $FILE_COUNT -ge 4 ] ; then
		  OLD_FILE=$(ls -1 *.gz | head -1)
		  echo "Delete $OLD_FILE"
		  rm -f $OLD_FILE
		fi
		
	else
		mv $FILE "$FILE"_error
		echo "Error $$CURRENT_FILE_SIZE es menor a $LAST_FILE_SIZE"
		echo " $HOSTNAME  : Error el archivo de respaldo de la base de BMONITOR es menor al dia anterior" | $MAIL -s "Error en el archivo $CURRENT_FILE" $MAIL_ERROR
	fi
fi
