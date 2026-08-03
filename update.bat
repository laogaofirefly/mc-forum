@echo off
chcp 65001 >nul
setlocal enabledelayedexpansion
REM MC 论坛一键更新脚本 (Windows)
REM 用法：双击运行或在项目根目录命令行执行 update.bat

echo ========================================
echo   MC 论坛一键更新
echo   %date% %time%
echo ========================================
echo.

echo [1/6] 拉取最新代码...

REM 先检查是否有本地未提交的修改
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

REM 拉取，同时显示错误信息
set "GIT_ERR="
for /f "delims=" %%i in ('git pull 2^>^&1') do (
    echo   %%i
    set "GIT_ERR=!GIT_ERR! %%i"
)
if %errorlevel% neq 0 (
    echo.
    echo   ========================================
    echo   拉取失败！错误信息见上方输出
    echo   ========================================
    echo.
    echo !GIT_ERR! | findstr /i "Authentication Invalid remote rejected fatal could not" >nul
    if !errorlevel! equ 0 (
        echo   很可能是 GitHub Token 过期了。
        echo.
        echo   解决方法：
        echo     1. 浏览器打开 https://github.com/settings/tokens
        echo     2. 点击 Generate new token ^(classic^)
        echo     3. 勾选 repo 权限，生成后复制新 Token
        echo     4. 粘贴到下方输入框中
        echo.
        set /p "NEW_TOKEN=请输入新的 GitHub Token: "
        if not "!NEW_TOKEN!"=="" (
            echo   正在更新远程地址...
            git remote set-url origin https://!NEW_TOKEN!@github.com/laogaofirefly/mc-forum.git
            echo   已更新，重新尝试拉取...
            git pull
            if !errorlevel! neq 0 (
                echo   仍然失败，请检查 Token 是否正确
                if "!STASHED!"=="1" ( git stash pop )
                pause
                exit /b 1
            )
        ) else (
            if "!STASHED!"=="1" ( git stash pop )
            pause
            exit /b 1
        )
    ) else (
        if "!STASHED!"=="1" ( git stash pop )
        pause
        exit /b 1
    )
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