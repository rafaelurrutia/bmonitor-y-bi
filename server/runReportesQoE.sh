LOG=/var/log/bsw/runReportesQoE.log

cd /home/bsw/

u=$(ps -fea | grep "php server" | grep -v grep | wc -l)
if [ $u -eq 0 ] ; then
  d=$(date)
  echo $d >> $LOG
  #----estos archivos son necesarios para generar el tab QoE->Resumen de Bmonitor
  
  php serverQoE.php >> $LOG
  php serverSpeedtest.php >> $LOG
 
  #----fin archivos tab QoE
  d=$(date)
  echo $d >> $LOG
fi
