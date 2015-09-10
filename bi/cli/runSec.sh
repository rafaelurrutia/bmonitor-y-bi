mkdir /var/log/bsw/ 2> /dev/null
LOG=/var/log/bsw/DBsplit.log
cd /var/www/site/bmonitor25/bi/cli
u=$(ps -fea | grep "php DBsplit" | grep -v grep | wc -l)
if [ $u -eq 0 ] ; then
  logrotate  logrotate.conf
  d=$(date)
  echo $d >> $LOG
  php DBsplit6.php >> $LOG
  d=$(date)
  echo $d >> $LOG
fi


