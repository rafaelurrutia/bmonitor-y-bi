mkdir /var/log/bsw/ 2> /dev/null
LOG=/var/log/bsw/alertas.log
cd /home/bsw
u=$(ps -fea | grep "php alertas" | grep -v grep | wc -l)
if [ $u -eq 0 ] ; then
  d=$(date)
  echo $d >> $LOG
  php alertas.php >> $LOG
  d=$(date)
  echo $d >> $LOG
fi
