@echo off
chcp 65001 >nul
REM MC 论坛一键更新脚本 (Windows)
REM 用法：双击运行或在项目根目录命令行执行 update.bat

echo ========================================
echo   MC 论坛一键更新
echo   %date% %time%
echo ========================================
echo.

echo [1/6] 拉取最新代码...
git pull
if %errorlevel% neq 0 (
    echo 拉取失败，请检查网络或 Git 配置
    pause
    exit /b 1
)
echo.

echo [2/6] 安装 Composer 依赖...
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist
echo.

echo [3/6] 执行数据库迁移...
php artisan migrate --force
echo.

echo [4/6] 清理缓存...
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
echo.

echo [5/6] 重建缓存...
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo.

echo [6/6] 检查 storage 链接...
php artisan storage:link 2>nul
echo.

echo ========================================
echo   更新完成！
echo ========================================
pause