@echo off
setlocal enabledelayedexpansion

:: 检查是否安装了PHPUnit
if not exist "vendor\bin\phpunit.bat" (
    if not exist "vendor\bin\phpunit" (
        echo 错误: 未找到PHPUnit，请先运行 'composer install --dev' 安装依赖
        pause
        exit /b 1
    )
)

:: 创建必要的目录
if not exist "build" mkdir build
if not exist "build\coverage" mkdir build\coverage
if not exist "build\logs" mkdir build\logs

:: 解析命令行参数
set COMMAND=vendor\bin\phpunit
set COVERAGE=0
set VERBOSE=0
set FILTER=

:parse_args
if "%~1"=="--unit" (
    set COMMAND=%COMMAND% --testsuite Unit
    shift
    goto parse_args
)
if "%~1"=="--feature" (
    set COMMAND=%COMMAND% --testsuite Feature
    shift
    goto parse_args
)
if "%~1"=="--coverage" (
    set COMMAND=%COMMAND% --coverage-html build\coverage --coverage-text
    set COVERAGE=1
    shift
    goto parse_args
)
if "%~1"=="--verbose" (
    set COMMAND=%COMMAND% -v
    set VERBOSE=1
    shift
    goto parse_args
)
if "%~1"=="--filter" (
    set COMMAND=%COMMAND% --filter "%~2"
    shift
    shift
    goto parse_args
)
if not "%~1"=="" (
    shift
    goto parse_args
)

:: 显示将要执行的命令
echo 运行命令: %COMMAND%
echo 开始执行测试...
echo.

:: 执行测试
%COMMAND%
set EXIT_CODE=%ERRORLEVEL%

echo.
echo 测试执行完成，退出代码: %EXIT_CODE%

if %EXIT_CODE% equ 0 (
    echo ✅ 所有测试通过
) else (
    echo ❌ 测试失败
)

if %COVERAGE% equ 1 (
    echo.
    echo 覆盖率报告已生成到 build\coverage 目录
)

pause
exit /b %EXIT_CODE%