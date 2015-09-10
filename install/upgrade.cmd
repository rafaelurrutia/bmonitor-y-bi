@echo off
c:
cd c:\sonda\
for /f "delims= skip=1" %%x in (QOE.ini) do set %%x
REM ping -n 10 localhost
:TRY
cd c:\sonda\bin
c:\sonda\bin\curl -s -O %SERVER1%/%INSTALL%/1.txt

if errorlevel 1 (
  c:\sonda\bin\curl -s -O %SERVER1%/%INSTALL%/1.txt
)
if errorlevel 1 (
   echo Failure Reason Given is %errorlevel%
   ping -n 30 localhost
   GOTO TRY
)
taskkill /F /IM Sonda.exe
taskkill /F /IM Watchdog.exe
taskkill /F /IM dig.exe
taskkill /F /IM dw20.exe
taskkill /F /IM curl.exe
taskkill /F /IM WerFault.exe
cd c:\sonda\bin
for /f  %%x in (1.txt) do (
   if NOT "%%x" == "html.php" IF NOT "%%x" == "curl.exe" IF NOT "%%x" == "Setup_QoE.exe" IF NOT "%%x" == "data.bin" IF NOT "%%x" == "qoe.zip" IF NOT "%%x" == "7za.exe" (
     echo Downloading %%x
     c:\sonda\bin\curl -s -O %SERVER1%/%INSTALL%/%%x
     if errorlevel 1 (
       echo Failure Reason Given is %errorlevel%
       ping -n 30 localhost
       GOTO TRY
     )
  )
)
start c:\sonda\bin\Sonda.exe %SERVER1% %SERVER2% %INSTALL%
ping -n 3 localhost
start c:\sonda\bin\WatchDog.exe %SERVER1% %SERVER2% %INSTALL%
ping -n 3 localhost
taskkill /F /IM dw20.exe
taskkill /F /IM WerFault.exe

