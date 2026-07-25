# MC论坛 - Windows 服务器部署指南

## 系统要求
- Windows Server 2016/2019/2022
- PHP 8.2 或更高版本
- Composer
- 可选:MySQL/MariaDB(默认使用 SQLite,无需额外安装)

---

## 方法一:使用 phpStudy(推荐,最简单)

### 1. 安装 phpStudy
1. 下载 phpStudy: https://www.xp.cn/download.html
2. 安装并打开 phpStudy
3. 在「软件商店」安装 PHP 8.2+ 和 Nginx 或 Apache

### 2. 部署网站
1. 将整个 `mc-forum` 文件夹复制到 phpStudy 的网站根目录(如 `D:\phpstudy_pro\WWW\mc-forum`)
2. 打开 phpStudy → 「网站」→「创建网站」
   - 域名:填你的域名或服务器 IP(如 `121.40.44.197`)
   - 根目录:选择 `mc-forum/public` 文件夹
   - PHP 版本:选择 PHP 8.2+
   - 点击确认

### 3. 配置权限
1. 确保以下目录有写入权限:
   - `storage/`
   - `bootstrap/cache/`
   - `database/database.sqlite` (如果已存在)

### 4. 初始化数据库
在 phpStudy 中打开网站根目录的 CMD,执行:
```cmd
cd D:\phpstudy_pro\WWW\mc-forum
php artisan key:generate
php artisan migrate --seed
```

### 5. 配置 MC 服务器信息
编辑 `.env` 文件,修改你的 MC 服务器地址:
```
MINECRAFT_SERVER_HOST=你的MC服务器IP
MINECRAFT_SERVER_PORT=25565
```

---

## 方法二:手动部署(IIS + PHP)

### 1. 安装 PHP
1. 下载 PHP for Windows: https://windows.php.net/download/
   - 选择 PHP 8.2+ Non Thread Safe (NTS) x64
2. 解压到 `C:\php`
3. 把 `C:\php` 添加到系统环境变量 PATH
4. 复制 `php.ini-development` 为 `php.ini`,修改:
   ```ini
   extension_dir = "ext"
   extension=pdo_sqlite
   extension=openssl
   extension=mbstring
   extension=fileinfo
   extension=curl
   date.timezone = Asia/Shanghai
   ```

### 2. 安装 Composer
1. 下载: https://getcomposer.org/download/
2. 安装 Composer-Setup.exe

### 3. 配置 IIS
1. 服务器管理器 → 添加角色和功能 → 安装 Web 服务器(IIS)
2. 安装 URL Rewrite 模块: https://www.iis.net/downloads/microsoft/url-rewrite
3. 安装 PHP Manager for IIS

### 4. 部署网站
1. 将 `mc-forum` 复制到 `C:\inetpub\wwwroot\mc-forum`
2. IIS 管理器 → 添加网站
   - 物理路径:`C:\inetpub\wwwroot\mc-forum\public`
   - 绑定:你的域名/IP,端口 80
3. 在 PHP Manager 中注册 PHP 版本,指向 `C:\php\php-cgi.exe`

### 5. 配置 web.config
在 `public` 目录下创建 `web.config`:
```xml
<?xml version="1.0" encoding="UTF-8"?>
<configuration>
    <system.webServer>
        <rewrite>
            <rules>
                <rule name="Laravel" stopProcessing="true">
                    <match url="^(.*)$" ignoreCase="false" />
                    <conditions logicalGrouping="MatchAll">
                        <add input="{REQUEST_FILENAME}" matchType="IsFile" ignoreCase="false" negate="true" />
                        <add input="{REQUEST_FILENAME}" matchType="IsDirectory" ignoreCase="false" negate="true" />
                    </conditions>
                    <action type="Rewrite" url="index.php/{R:1}" appendQueryString="true" />
                </rule>
            </rules>
        </rewrite>
        <defaultDocument>
            <files>
                <clear />
                <add value="index.php" />
            </files>
        </defaultDocument>
    </system.webServer>
</configuration>
```

### 6. 初始化
打开 CMD,执行:
```cmd
cd C:\inetpub\wwwroot\mc-forum
composer install --optimize-autoloader --no-dev
php artisan key:generate
php artisan migrate --seed
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 7. 设置目录权限
- 给 `storage` 和 `bootstrap/cache` 目录添加 IIS_IUSRS 用户的写入权限

---

## 默认账号

| 角色 | 邮箱 | 密码 |
|------|------|------|
| 管理员 | admin@mcforum.com | password123 |
| 红石工程师 | redstone@mcforum.com | password123 |
| 建筑大师 | builder@mcforum.com | password123 |
| 生存玩家 | survival@mcforum.com | password123 |

> 登录后请及时修改默认密码!

---

## 常用命令

```cmd
# 清除缓存
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# 重新生成缓存(生产环境)
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 数据库迁移
php artisan migrate
php artisan migrate:rollback  # 回滚

# 重置数据库(会清空所有数据!)
php artisan migrate:fresh --seed

# 查看路由列表
php artisan route:list
```

---

## 配置说明

### 修改服务器信息
编辑 `.env` 文件:
```env
APP_NAME=MC论坛
APP_URL=http://你的域名或IP

MINECRAFT_SERVER_HOST=你的MC服务器IP
MINECRAFT_SERVER_PORT=25565
```

### 切换到 MySQL 数据库(可选)
编辑 `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mc_forum
DB_USERNAME=root
DB_PASSWORD=你的密码
```
然后执行 `php artisan migrate --seed`

---

## 安全建议

1. 修改 `.env` 中的 `APP_KEY`(已通过 `key:generate` 生成)
2. 修改所有默认账号密码
3. 生产环境设置 `APP_ENV=production` 和 `APP_DEBUG=false`
4. 配置 HTTPS(推荐使用 Let's Encrypt 免费证书)
5. 定期备份数据库文件(`database/database.sqlite`)
