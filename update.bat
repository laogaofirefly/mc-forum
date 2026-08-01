@echo off
chcp 65001 >nul
setlocal enabledelayedexpansion

:: ============================================
::  MC论坛 一键更新脚本
::  从 GitHub 拉取最新代码并自动部署
:: ============================================

:: ============================================
::  使用说明：将下面 YOUR_GITHUB_TOKEN 替换为你的 GitHub Personal Access Token
::  或在运行脚本后首次登录时输入账号密码 / 或在服务器用 git config --global credential
:: ============================================
:: 配置项（按需修改）
set "GITHUB_TOKEN=YOUR_GITHUB_TOKEN"
set "PROJECT_DIR=C:\Users\Administrator\Desktop\website\mc-forum-main"
set "BACKUP_DIR=C:\Users\Administrator\Desktop\mc-forum-backup"
set "GITHUB_USER=laogaofirefly"
set "GITHUB_REPO=mc-forum"
set "GITHUB_URL=https://%GITHUB_TOKEN%@github.com/%GITHUB_USER%/%GITHUB_REPO%.git"
set "MIRROR_URL=https://%GITHUB_TOKEN%@ghfast.top/https://github.com/%GITHUB_USER%/%GITHUB_REPO%.git"

:: 进入项目目录
cd /d "%PROJECT_DIR%" 2>nul
if errorlevel 1 (
    echo [错误] 找不到项目目录：%PROJECT_DIR%
    echo 请确认路径是否正确，或修改本脚本顶部的 PROJECT_DIR 变量
    pause
    exit /b 1
)

echo ============================================
echo    MC论坛 一键更新脚本
echo ============================================
echo 项目目录：%PROJECT_DIR%
echo 备份目录：%BACKUP_DIR%
echo.

:: ========== 第1步：备份配置和数据库 ==========
echo [1/6] 备份配置和数据库...
if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"

if exist ".env" (
    copy /Y ".env" "%BACKUP_DIR%\.env.bak" >nul
    echo     √ 已备份 .env
) else (
    echo     ! 未找到 .env，跳过
)

if exist "database\database.sqlite" (
    copy /Y "database\database.sqlite" "%BACKUP_DIR%\database.sqlite.bak" >nul
    echo     √ 已备份 database.sqlite
) else (
    echo     ! 未找到 database.sqlite，跳过
)

:: 备份 storage 目录（用户上传的文件）
if exist "storage\app\public" (
    xcopy /E /I /Y "storage\app\public" "%BACKUP_DIR%\storage_public" >nul 2>&1
    echo     √ 已备份 storage\app\public
)
echo.

:: ========== 第2步：拉取最新代码 ==========
echo [2/6] 拉取最新代码...

:: 检查是否是 git 仓库
if not exist ".git" (
    echo     当前目录不是 Git 仓库，尝试初始化...
    git init >nul 2>&1
    git remote add origin "%GITHUB_URL%" >nul 2>&1
    if errorlevel 1 (
        git remote set-url origin "%GITHUB_URL%" >nul 2>&1
    )
)

:: 先尝试官方 GitHub
echo     尝试从 GitHub 官方拉取...
git pull origin main --force 2>nul
if errorlevel 1 (
    echo     × GitHub 官方连接失败
    echo     尝试从镜像站 kkgithub.com 拉取...
    git remote set-url origin "%MIRROR_URL%" >nul 2>&1
    git pull origin main --force 2>nul
    if errorlevel 1 (
        echo     × 镜像站也连接失败
        echo.
        echo [提示] GitHub 连接不上，请手动下载 zip 包：
        echo        https://ghproxy.com/https://github.com/laogaofirefly/mc-forum/archive/refs/heads/main.zip
        echo        解压后覆盖到：%PROJECT_DIR%
        echo        然后重新运行本脚本（会自动跳过拉取步骤）
        pause
        exit /b 1
    ) else (
        echo     √ 镜像站拉取成功
        :: 拉取成功后改回官方地址，下次优先用官方
        git remote set-url origin "%GITHUB_URL%" >nul 2>&1
    )
) else (
    echo     √ GitHub 官方拉取成功
)
echo.

:: ========== 第3步：恢复配置和数据库 ==========
echo [3/6] 恢复配置和数据库...
if exist "%BACKUP_DIR%\.env.bak" (
    copy /Y "%BACKUP_DIR%\.env.bak" ".env" >nul
    echo     √ 已恢复 .env
) else (
    echo     ! 没有备份的 .env，使用默认配置
    if exist ".env.example" copy /Y ".env.example" ".env" >nul
)

if exist "%BACKUP_DIR%\database.sqlite.bak" (
    copy /Y "%BACKUP_DIR%\database.sqlite.bak" "database\database.sqlite" >nul
    echo     √ 已恢复 database.sqlite
)

if exist "%BACKUP_DIR%\storage_public" (
    xcopy /E /I /Y "%BACKUP_DIR%\storage_public" "storage\app\public" >nul 2>&1
    echo     √ 已恢复 storage\app\public
)
echo.

:: ========== 第4步：安装依赖 ==========
echo [4/6] 安装 Composer 依赖...
where composer >nul 2>&1
if errorlevel 1 (
    echo     × 未找到 composer 命令
    echo     请确认 Composer 已安装并加入环境变量
    echo     或使用完整路径：C:\ProgramData\ComposerSetup\bin\composer.bat
    pause
    exit /b 1
)

composer install --no-dev --optimize-autoloader 2>&1
if errorlevel 1 (
    echo     × composer install 失败，尝试 composer update...
    composer update --no-dev --optimize-autoloader 2>&1
    if errorlevel 1 (
        echo     × composer update 也失败了
        pause
        exit /b 1
    )
)
echo     √ 依赖安装完成
echo.

:: ========== 第5步：清缓存 ==========
echo [5/6] 清除缓存...
php artisan view:clear 2>nul && echo     √ 视图缓存已清除
php artisan route:clear 2>nul && echo     √ 路由缓存已清除
php artisan config:clear 2>nul && echo     √ 配置缓存已清除
php artisan cache:clear 2>nul && echo     √ 应用缓存已清除
echo.

:: ========== 第6步：完成 ==========
echo [6/6] 更新完成！
echo.
echo ============================================
echo    更新成功！网站已部署最新代码
echo ============================================
echo.
echo 访问地址：http://121.40.44.197:8080
echo.
echo 如果页面显示异常，请按 Ctrl+F5 强制刷新浏览器
echo.
pause
