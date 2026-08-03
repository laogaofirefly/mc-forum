@echo off
chcp 65001 >nul
setlocal enabledelayedexpansion
REM ============================================
REM  MC 论坛一键更新（桌面版）
REM  把此文件放到桌面，双击即可更新
REM  如果项目路径不同，修改下面 PROJECT_DIR 即可
REM ============================================

set "PROJECT_DIR=C:\Users\Administrator\Desktop\mc-forum"

if not exist "%PROJECT_DIR%" (
    echo 错误：项目目录不存在
    echo %PROJECT_DIR%
    echo.
    echo 请右键编辑此文件，修改 PROJECT_DIR 为实际项目路径
    pause
    exit /b 1
)

cd /d "%PROJECT_DIR%"

echo ========================================
echo   MC 论坛一键更新
echo   %date% %time%
echo ========================================
echo.

echo [1/6] 拉取最新代码...

REM 先暂存本地未提交的修改
git diff --quiet
if %errorlevel% neq 0 (
    echo   检测到本地有未提交的修改，先暂存...
    git stash push -m "update.bat 自动暂存 %date% %time%"
    if %errorlevel% neq 0 (
        echo   暂存失败，请手动处理本地修改后再试
        pause
        exit /b 1
    )
    set "STASHED=1"
    echo   已暂存
)

git pull 2>&1
if %errorlevel% neq 0 (
    echo.
    echo   拉取失败！可能原因：
    echo     1. 网络不通，无法连接 GitHub
    echo     2. GitHub Token 过期（去 GitHub Settings 重新生成）
    echo     3. 本地有冲突需要手动解决
    echo.
    if "!STASHED!"=="1" ( git stash pop )
    pause
    exit /b 1
)

if "!STASHED!"=="1" (
    echo   恢复暂存的修改...
    git stash pop
    if %errorlevel% neq 0 ( echo   警告：恢复暂存时出现冲突，请手动处理 )
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