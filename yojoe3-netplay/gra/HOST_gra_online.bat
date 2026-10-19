@echo off
setlocal EnableExtensions
cd /d "%~dp0"
title Yo! Joe! 3 - gra online - HOST (gracz 1)

rem ------------------------------------------------------------------
rem  HOST = gracz 1 (Joe), joystick w porcie 2 Amigi.
rem  Ten komputer ma dyski gry i wysyla je automatycznie do gracza 2.
rem ------------------------------------------------------------------

set "EXE=%~dp0winuae64.exe"
if not exist "%EXE%" set "EXE=%~dp0winuae.exe"
if not exist "%EXE%" (
	echo Nie znaleziono winuae64.exe w tym folderze.
	echo Rozpakuj tu WinUAE z obsluga netplay ^(patrz README^).
	pause
	exit /b 1
)

set "DISKA="
for %%f in ("%~dp0*_A.adf") do set "DISKA=%%~ff"
if not defined DISKA (
	echo Nie znaleziono dysku gry *_A.adf w tym folderze.
	echo Skopiuj tu oba dyski: ..._A.adf i ..._B.adf
	pause
	exit /b 1
)
set "DISKB=%DISKA:_A.adf=_B.adf%"
if not exist "%DISKB%" (
	echo Nie znaleziono dysku B: %DISKB%
	pause
	exit /b 1
)

echo.
echo  Yo! Joe! 3 - gra online przez internet (HOST)
echo  Dysk A: %DISKA%
echo  Dysk B: %DISKB%
echo.
set "PORT=25200"
set /p "PORT=Port TCP [Enter = 25200]: "
set "PASS="
set /p "PASS=Haslo dla gracza 2 [Enter = bez hasla]: "
call "%~dp0_sterowanie.bat"

echo.
echo  Twoje adresy w sieci lokalnej (LAN / VPN):
ipconfig | findstr /i "IPv4"
echo.
echo  Przez internet gracz 2 wpisuje Twoj adres PUBLICZNY
echo  (sprawdzisz go np. na https://ifconfig.me), a Ty musisz
echo  przekierowac port TCP %PORT% w routerze na ten komputer.
echo  Prosciej: obaj zainstalujcie Radmin VPN / ZeroTier / Tailscale
echo  i uzyjcie adresu z tej sieci - wtedy nic nie trzeba ustawiac.
echo.
echo  WinUAE czeka na gracza 2 (status jest na pasku tytulu okna).
echo.

start "" "%EXE%" -portable -f "%~dp0YoJoe3_netplay.uae" -s "floppy0=%DISKA%" -s "floppy1=%DISKB%" -s netplay=host -s netplay_port=%PORT% -s "netplay_password=%PASS%" -s joyport1=%JOY%
