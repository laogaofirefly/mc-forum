#!/usr/bin/env bash
#
# MC 论坛一键更新脚本
# 用法：在项目根目录执行 bash update.sh
#
set -euo pipefail

echo "========================================"
echo "  MC 论坛一键更新"
echo "  $(date '+%Y-%m-%d %H:%M:%S')"
echo "========================================"
echo ""

# ── 1. 拉取最新代码 ──
echo "[1/6] 拉取最新代码..."
git pull
echo ""

# ── 2. 安装依赖 ──
echo "[2/6] 安装 Composer 依赖..."
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist
echo ""

# ── 3. 数据库迁移 ──
echo "[3/6] 执行数据库迁移..."
php artisan migrate --force
echo ""

# ── 4. 清理所有缓存 ──
echo "[4/6] 清理缓存..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
echo ""

# ── 5. 重建缓存（生产环境优化） ──
echo "[5/6] 重建缓存..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo ""

# ── 6. 确保 storage 软链接存在 ──
echo "[6/6] 检查 storage 链接..."
php artisan storage:link 2>/dev/null || true
echo ""

echo "========================================"
echo "  更新完成！"
echo "========================================"