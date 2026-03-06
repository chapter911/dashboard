@echo off
setlocal

if "%~1"=="" (
  echo Usage: tools\use-env.bat [development^|production]
  exit /b 1
)

if /I "%~1"=="development" (
  copy /Y .env.development .env >nul
  echo Active environment: development
  exit /b 0
)

if /I "%~1"=="production" (
  copy /Y .env.production .env >nul
  echo Active environment: production
  exit /b 0
)

echo Invalid option: %~1
echo Usage: tools\use-env.bat [development^|production]
exit /b 1
