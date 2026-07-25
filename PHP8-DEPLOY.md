# PHP 8.0 部署指南

> ⚠️ **重要说明**：本项目基于 Laravel 13 开发，官方要求 PHP 8.2+。
> 本指南通过特殊配置让其在 PHP 8.0 上运行，但**推荐升级到 PHP 8.2+ 以获得最佳体验**。

## 兼容性说明

| 项目 | 状态 |
|------|------|
| 项目业务代码 | ✅ 兼容 PHP 8.0 |
| Laravel 13 框架 | ⚠️ 需要 PHP 8.2+（通过 --ignore-platform-reqs 强制安装） |
| 风险 | ⚠️ 框架某些功能可能不工作，运行时可能报错 |

## 部署步骤

### 1. 克隆代码

```cmd
mkdir D:\www
cd D:\www
git clone https://github.com/laogaofirefly/mc-forum.git
cd mc-forum
```

### 2. 安装依赖（关键步骤）

由于 PHP 8.0 不满足 Laravel 13 的要求，需要使用 `--ignore-platform-reqs` 参数：

```cmd
composer install --optimize-autoloader --no-dev --ignore-platform-reqs
```

> 如果速度慢，先换国内镜像：
> ```cmd
> composer config -g repo.packagist composer https://packagist.phpcomposer.com
> ```

### 3. 配置环境

```cmd
copy .env.example .env
php artisan key:generate
```

编辑 `.env` 文件：

```env
APP_NAME=MC论坛
APP_ENV=local
APP_DEBUG=true
APP_URL=http://你的服务器IP

DB_CONNECTION=sqlite

MINECRAFT_SERVER_HOST=127.0.0.1
MINECRAFT_SERVER_PORT=25565
```

### 4. 创建数据库

```cmd
cd database
type nul > database.sqlite
cd ..
```

### 5. 数据库迁移

```cmd
php artisan migrate --seed
```

### 6. 配置 Web 服务器

参考主部署文档 [DEPLOY.md](DEPLOY.md)

### 7. 设置权限

```cmd
icacls storage /grant "Everyone:(OI)(CI)F" /T
icacls bootstrap\cache /grant "Everyone:(OI)(CI)F" /T
icacls database /grant "Everyone:(OI)(CI)F" /T
```

### 8. 访问测试

```
http://你的服务器IP
```

## 常见问题

### Q1: composer install 报错 PHP 版本不够

确保使用 `--ignore-platform-reqs` 参数：

```cmd
composer install --optimize-autoloader --no-dev --ignore-platform-reqs
```

### Q2: 运行 php artisan 命令时报错

如果出现类似 `Fatal error: ... not supported on PHP 8.0` 的错误，说明 Laravel 框架用了 PHP 8.1+ 的特性。

**解决方案**：升级 PHP 到 8.2+

1. 从 https://windows.php.net/download/ 下载 PHP 8.2 NTS 版本
2. 解压到 `D:\php82`
3. 在 phpStudy 中切换到 PHP 8.2
4. 重新执行 `composer install`

### Q3: 页面显示 500 错误

```cmd
php artisan key:generate
php artisan config:clear
icacls storage /grant "Everyone:(OI)(CI)F" /T
```

查看详细错误：

```cmd
type storage\logs\laravel.log
```

## 推荐：升级到 PHP 8.2+

如果 PHP 8.0 部署后遇到问题，强烈建议升级到 PHP 8.2+：

### 方法 1: phpStudy 安装 PHP 8.2

1. phpStudy → 软件商店 → PHP → 安装 PHP 8.2.x
2. 切换网站 PHP 版本为 8.2
3. 重新安装 Composer(指向新的 PHP)

### 方法 2: 从官网下载 PHP 8.2

1. 访问 https://windows.php.net/download/
2. 下载 **PHP 8.2.x Non Thread Safe** 的 zip 包
3. 解压到 `D:\php82`
4. 复制 `php.ini-development` 为 `php.ini`
5. 修改 `php.ini`:
   ```ini
   extension_dir = "ext"
   extension=pdo_sqlite
   extension=openssl
   extension=mbstring
   extension=fileinfo
   extension=curl
   ```
6. 把 `D:\php82` 添加到系统 PATH 环境变量
7. 安装 VC++ Redistributable: https://aka.ms/vs/17/release/vc_redist.x64.exe
8. 重启电脑
9. 测试: `php -v`
