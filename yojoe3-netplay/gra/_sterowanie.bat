@echo off
rem Wybor sterowania gracza (wywolywane z HOST/DOLACZ).
echo.
echo  Sterowanie:
echo    1 = klawiatura: strzalki + prawy Ctrl (ogien)
echo    2 = joystick / pad (pierwszy podlaczony)
echo    3 = klawiatura: W A S D + lewy Alt (ogien)
choice /c 123 /n /m " Wybierz 1, 2 lub 3: "
set "JOY=kbd2"
if errorlevel 2 set "JOY=joy0"
if errorlevel 3 set "JOY=kbd3"
