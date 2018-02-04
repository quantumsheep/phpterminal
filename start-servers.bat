@setlocal enableextensions
@cd /d "%~dp0"
@echo off
net session >nul 2>&1
if %errorLevel% == 0 (
	start /b cmd /c apache2\bin\httpd.exe -f "../servers-conf/mainapp.conf"
	start /b cmd /c apache2\bin\httpd.exe -f "../servers-conf/sockets.conf"
) else (
	echo You need admin rights to install Apache services
    pause
)
pause