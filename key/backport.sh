#!/bin/bash

DIR="/var/www/sitios/bmonitor.baking.cl/key"

OLD=`stat -t $DIR`

cd $DIR;

while true
do

NEW=`stat -t $DIR`

if [ "$NEW" != "$OLD" ]; then

	for i in $( ls *.pub ); do
		cat $i >> /home/gateway/.ssh/authorized_keys
		rm $i;
		chown gateway:gateway /home/gateway/.ssh/authorized_keys	
		chmod 600 /home/gateway/.ssh/authorized_keys	
 	done
	NEW=`stat -t $DIR`;
	OLD=$NEW
fi

sleep 3
done	
