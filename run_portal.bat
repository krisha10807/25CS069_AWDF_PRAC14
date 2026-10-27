@echo off
title StudentHub Portal - Practical 14 (WDF)
echo ======================================================================
echo  StudentHub Portal: Practical 14 - RBAC Dashboard & Audit Logs
echo  CHARUSAT - CSPIT | Web Development Frameworks (WDF) | 25CS069
echo  Student Name: Krisha Patel
echo ======================================================================
echo.
echo Starting local PHP development server on http://localhost:8000 ...
start "" "http://localhost:8000/dashboard.php"
"C:\xampp\php\php.exe" -S localhost:8000
pause
