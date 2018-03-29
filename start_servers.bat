@setlocal enableextensions
@cd /d "%~dp0"
@echo off
net session >nul 2>&1
if %errorLevel% == 0 (
	start cmd /c "echo Starting Apache 2 alPH Proxy... && apache2\bin\httpd.exe -f ../servers-conf/proxy.conf"
	start cmd /c "echo Starting Apache 2 alPH Application... && apache2\bin\httpd.exe -f ../servers-conf/mainapp.conf"
	start cmd /c "cd www/sockets && start_sockets.bat"
) else (
	echo You need admin rights to install Apache services
    pause
)