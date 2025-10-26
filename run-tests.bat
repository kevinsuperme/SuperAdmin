@echo off
REM ========================================
REM SuperAdmin 测试运行脚本
REM ========================================

echo.
echo ========================================
echo   SuperAdmin 测试套件
echo ========================================
echo.

REM 检查PHPUnit是否存在
if not exist "vendor\bin\phpunit" (
    echo [错误] 未找到PHPUnit，请先运行: composer install
    pause
    exit /b 1
)

echo [1/3] 运行Service层测试...
echo.
vendor\bin\phpunit tests\Unit\Service\ --colors=always

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo [错误] 测试失败！
    pause
    exit /b 1
)

echo.
echo [2/3] 生成测试覆盖率报告...
echo.
vendor\bin\phpunit tests\Unit\Service\ --coverage-html tests\coverage\html --coverage-text

echo.
echo [3/3] 测试完成！
echo.
echo ========================================
echo   测试结果摘要
echo ========================================
echo.
echo ✓ 所有测试已通过
echo ✓ 覆盖率报告已生成: tests\coverage\html\index.html
echo.
echo 按任意键打开覆盖率报告...
pause >nul

start tests\coverage\html\index.html

echo.
echo 完成！
pause
