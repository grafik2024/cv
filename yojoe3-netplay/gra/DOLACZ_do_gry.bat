@echo off
setlocal EnableExtensions
cd /d "%~dp0"
title Yo! Joe! 3 - gra online - DOLACZ (gracz 2)

rem ------------------------------------------------------------------
rem  DOLACZ = gracz 2 (Nat), joystick w porcie 1 Amigi.
rem  Dyski gry nie sa potrzebne - przysle je host.
rem  Swoj joystick/klawiature ustawiasz jak zwykle w porcie 2,
rem  netplay sam przeniesie go do portu 1.
rem ------------------------------------------------------------------

set "EXE=%~dp0winuae64.exe"
if not exist "%EXE%" set "EXE=%~dp0winuae.exe"
if not exist "%EXE%" (
	echo Nie znaleziono winuae64.exe w tym folderze.
	echo Rozpakuj tu WinUAE z obsluga netplay ^(patrz README^).
	pause
	exit /b 1
)

echo.
echo  Yo! Joe! 3 - gra online przez internet (DOLACZ)
echo.
set "HOST="
set /p "HOST=Adres hosta (np. 83.12.34.56 albo 83.12.34.56:25200): "
if not defined HOST (
	echo Nie podano adresu.
	pause
	exit /b 1
)
set "PASS="
set /p "PASS=Haslo [Enter = bez hasla]: "
call "%~dp0_sterowanie.bat"

echo.
echo  Laczenie z %HOST% ... (status jest na pasku tytulu okna WinUAE)
echo.

start "" "%EXE%" -portable -f "%~dp0YoJoe3_netplay.uae" -s netplay=client -s "netplay_address=%HOST%" -s "netplay_password=%PASS%" -s joyport1=%JOY%
