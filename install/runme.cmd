@echo off
c:
cd c:\sonda\
bin\PostInstall.exe
for /f "delims= skip=1" %%x in (QOE.ini) do set %%x
:TRY
start /MIN www.google.cl
wget -O data.bin %SERVER1%/%INSTALL%/data.bin
if errorlevel 1 (
  c:\sonda\wget -O data.bin %SERVER2%/%INSTALL%/data.bin
)
if errorlevel 1 (
  echo Failure Reason Given is %errorlevel%
  ping -n 30 localhost
  taskkill /F /IM iexplore.exe
  GOTO TRY
)
taskkill /F /IM iexplore.exe
taskkill /F /IM curl.exe
taskkill /F /IM fping.exe
taskkill /F /IM wget.exe
taskkill /F /IM dw20.exe
c:\sonda\bin\Sonda.exe %SERVER1% %SERVER2% %INSTALL%
ping -n 3 localhost
c:\sonda\bin\WatchDog.exe %SERVER1% %SERVER2% %INSTALL%
taskkill /F /IM dw20.exe
taskkill /F /IM WerFault.exe
exit


