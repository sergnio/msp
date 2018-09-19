
@echo off

set sqlpath=c:\xampp\mysql\bin
set phppath=c:\xampp\php

echo %path% | find "%sqlpath%" > nul

if ERRORLEVEL 1 goto modpath4sql

echo MYSQL Path already set
goto check4php

:modpath4sql
  echo Modify the path 4 MYSQL!
  set path=%path%;%sqlpath%
  goto check4php
  
  
:check4php
echo %path% | find "%phppath%" > nul

if ERRORLEVEL 1 goto modpath4php

echo PHP Path already set
goto endof

:modpath4php
  echo Modify the path 4 PHP!
  set path=%path%;%phppath%
  goto endof
  
:endof
   set sqlpath=
   set phppath=
   echo Path is : %path%
