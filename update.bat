@echo off
chcp 65001 >nul
setlocal enabledelayedexpansion

:: ================================================
::  MC 论坛一键更新脚本 (Windows)
::  把这个文件放在项目根目录（和 artisan 同目录）
::  双击运行即可
:: ================================================

title MC论坛一键更新工具
color 0A

echo.
echo  ╔══════════════════════════════════════════════╗
echo  ║     MC 论坛 一键更新工具 v1.0                ║
echo  ╚══════════════════════════════════════════════╝
echo.

:: --- 自动检测当前目录是否正确（看 artisan 是否存在）
if not exist "artisan" (
    color 0C
    echo  [错误] 当前目录不对！
    echo.
    echo  请把这个 .bat 文件放到项目根目录，
    echo  也就是里面有 artisan 文件的那个文件夹里。
    echo.
    echo  当前目录是：%cd%
    echo.
    pause
    exit /b 1
)
echo  [1/7] 检测到项目目录：%cd%
echo.

:: --- 查找 PHP.exe（优先用 phpStudy 的）
set "PHP_EXE="
where php >nul 2>nul
if %errorlevel%==0 (
    set "PHP_EXE=php"
) else (
    :: 尝试常见 phpStudy 路径
    for %%p in (
        "C:\phpstudy_pro\Extensions\php\php8.2.9nts\php.exe"
        "C:\phpstudy_pro\Extensions\php\php8.2.9\php.exe"
        "C:\phpstudy_pro\Extensions\php\php8.2.0nts\php.exe"
        "C:\phpstudy_pro\Extensions\php\php8.2.0\php.exe"
        "D:\phpstudy_pro\Extensions\php\php8.2.9nts\php.exe"
        "D:\phpstudy_pro\Extensions\php\php8.2.9\php.exe"
    ) do (
        if exist %%~p (
            set "PHP_EXE=%%~p"
            goto :php_found
        )
    )
)
:php_found
if "%PHP_EXE%"=="" (
    color 0C
    echo  [错误] 找不到 PHP.exe！
    echo.
    echo  请先在 phpStudy 里启动 PHP，或者把 php.exe 所在目录
    echo  加到系统环境变量 PATH 中，然后再运行本脚本。
    echo.
    pause
    exit /b 1
)
echo  [2/7] 检测到 PHP：%PHP_EXE%
"%PHP_EXE%" -v 2>nul | findstr /C:"PHP"
echo.

:: --- 开始执行
color 0B
echo  ──────────────────────────────────────────────
echo.
echo  [3/7] 正在执行 git pull 拉取最新代码...
echo.
where git >nul 2>nul
if %errorlevel%==0 (
    call git pull
    if %errorlevel% neq 0 (
        color 0E
        echo.
        echo  [警告] git pull 失败，请检查仓库地址或手动更新代码
        color 0B
    )
) else (
    color 0E
    echo  [提示] 没装 git，请确认已经手动把最新文件放进来了
    color 0B
)
echo.

:: --- 自动运行 migrate（建表）
echo  [4/7] 正在执行数据库迁移 (migrate)...
echo.
call "%PHP_EXE%" artisan migrate --force
if %errorlevel% neq 0 (
    color 0E
    echo.
    echo  [警告] 迁移可能有问题，请检查上方信息
    color 0B
)
echo.

:: --- 清理缓存
echo  [5/7] 正在清理 Laravel 缓存...
echo.
call "%PHP_EXE%" artisan optimize:clear
echo.

:: --- 运行聊天功能测试命令
echo  [6/7] 正在测试聊天功能插入...
echo.
if exist "app\Console\Commands\TestGameChat.php" (
    call "%PHP_EXE%" artisan chat:test "一键更新测试消息"
) else (
    echo  [跳过] TestGameChat.php 还没上传，这一步跳过。
    echo  可以通过浏览器访问 /chat-test 来诊断。
)
echo.

:: --- 完成
color 0A
echo  ──────────────────────────────────────────────
echo.
echo  [7/7] 更新完成！
echo.
echo  下一步操作：
echo    1. 打开浏览器访问：http://你的网址/chat-test
echo       （查看诊断结果，确认聊天功能是否正常）
echo    2. 如果诊断全部通过，访问：http://你的网址/game-chat
echo       （查看聊天页面）
echo.
pause
endlocal
