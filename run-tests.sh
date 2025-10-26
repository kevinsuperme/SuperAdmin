#!/bin/bash

# ========================================
# SuperAdmin 测试运行脚本
# ========================================

echo ""
echo "========================================"
echo "  SuperAdmin 测试套件"
echo "========================================"
echo ""

# 检查PHPUnit是否存在
if [ ! -f "vendor/bin/phpunit" ]; then
    echo "[错误] 未找到PHPUnit，请先运行: composer install"
    exit 1
fi

echo "[1/3] 运行Service层测试..."
echo ""
./vendor/bin/phpunit tests/Unit/Service/ --colors=always

if [ $? -ne 0 ]; then
    echo ""
    echo "[错误] 测试失败！"
    exit 1
fi

echo ""
echo "[2/3] 生成测试覆盖率报告..."
echo ""
./vendor/bin/phpunit tests/Unit/Service/ --coverage-html tests/coverage/html --coverage-text

echo ""
echo "[3/3] 测试完成！"
echo ""
echo "========================================"
echo "  测试结果摘要"
echo "========================================"
echo ""
echo "✓ 所有测试已通过"
echo "✓ 覆盖率报告已生成: tests/coverage/html/index.html"
echo ""

# 根据操作系统打开浏览器
if [[ "$OSTYPE" == "darwin"* ]]; then
    open tests/coverage/html/index.html
elif [[ "$OSTYPE" == "linux-gnu"* ]]; then
    xdg-open tests/coverage/html/index.html
fi

echo ""
echo "完成！"
