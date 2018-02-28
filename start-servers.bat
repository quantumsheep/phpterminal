@setlocal enableextensions
@cd /d "%~dp0"
@echo off
net session >nul 2>&1
if %errorLevel% == 0 (
	start cmd /c apache2\bin\httpd.exe -f "../servers-conf/proxy.conf"
	start cmd /c apache2\bin\httpd.exe -f "../servers-conf/mainapp.conf"
) else (
	echo You need admin rights to install Apache services
    pause
)