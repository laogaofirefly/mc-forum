# MC-Forum —— Minecraft 服务器配套论坛

基于 **Laravel 12** + **Tailwind CSS** 构建的轻量级 Minecraft 社区论坛，为你的 Minecraft 服务器提供玩家交流、公告发布、讨论互动的完整解决方案。

---

## ✨ 功能特性

### 核心功能
- 📝 **帖子系统** — 创建、编辑、置顶、锁定帖子，支持 Markdown 和图片上传
- 💬 **回复系统** — 对帖子进行回复，支持 Markdown 语法
- ❤️ **点赞功能** — 对帖子和回复进行点赞/取消点赞，实时 AJAX 更新
- 🔔 **通知系统** — 数据库持久化通知，支持回复、点赞、@提及三种通知类型
- 👤 **用户@提及** — 在回复中使用 `@用户名` 提及他人，自动发送通知
- 👥 **用户系统** — 注册、登录、头像上传、个人主页，支持管理员角色
- 📂 **分区/标签** — 通过分类组织内容
- 🎮 **游戏数据集成** — MC 玩家绑定、MOTD 服务器状态查询（需额外 Minecraft 插件配合）
- 📱 **响应式设计** — 基于 Tailwind CSS，手机端完美适配

### 技术栈
| 层级 | 技术 |
|------|------|
| 后端框架 | Laravel 12 (PHP 8.2+) |
| 前端样式 | Tailwind CSS |
| 数据库 | MySQL 8 / MariaDB / SQLite |
| JavaScript | 原生 JS (Vanilla JS) |
| Markdown | 自建 MarkdownService (支持图片、代码高亮) |

---

## 🚀 快速开始

### 环境要求
- PHP ≥ 8.2（需启用 `pdo_mysql`、`mbstring`、`gd`、`fileinfo` 等扩展）
- Composer ≥ 2.x
- Node.js ≥ 18 + npm
- MySQL 8.0 / SQLite（任选其一）

### 安装步骤

```bash
# 1. 克隆项目
git clone https://github.com/laogaofirefly/mc-forum.git
cd mc-forum

# 2. 安装 PHP 依赖
composer install

# 3. 安装前端依赖并编译
npm install && npm run build

# 4. 配置环境
cp .env.example .env
php artisan key:generate

# 5. 编辑 .env，填入数据库信息
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=mc_forum
# DB_USERNAME=root
# DB_PASSWORD=yourpassword

# 6. 运行数据库迁移
php artisan migrate

# 7. 创建存储链接（用于头像/图片上传）
php artisan storage:link

# 8. 启动开发服务器
php artisan serve
```

刷新页面即可看到论坛外观，在浏览器访问：`http://127.0.0.1:8000`

---

## 🔧 目录结构

```
mc-forum/
├── app/
│   ├── Http/Controllers/   # 控制器（Thread、Reply、Like、Notification 等）
│   ├── Models/              # 模型（User、Thread、Reply、Like、Notification）
│   └── Services/            # 服务层（Markdown, Mention, Minecraft*）
├── database/migrations/     # 数据库迁移文件
├── resources/views/         # Blade 视图
│   ├── layouts/             # 布局（app.blade.php）
│   ├── threads/             # 帖子相关视图
│   ├── notifications/       # 通知中心
│   └── partials/            # 通用组件
├── routes/
│   └── web.php              # Web 路由
└── public/                  # 前端入口
```

---

## 📋 数据库核心表

| 表名 | 说明 |
|------|------|
| `users` | 用户（含头像、签名、角色） |
| `threads` | 帖子（含标题、正文、分类、置顶/锁定状态） |
| `replies` | 回复（关联帖子与用户） |
| `likes` | 点赞（多态关联帖子与回复） |
| `notifications` | 通知（类型：reply/like/mention） |
| `categories` | 帖子分类 |

---

## 🎨 Markdown 支持

回复与帖子编辑框使用自研 Markdown 编辑脚本，支持：

- 标题（`### 标题`）
- 粗体/斜体（`**粗体**`、`*斜体*`）
- 链接和图片（`[文字](url)`、`![alt](url)`）
- 代码块（` ``` ` 包起来）
- 图片粘贴上传 / 拖拽上传
- 字符计数器

---

## 📦 扩展开发计划

- [ ] Markdown 预览面板（所见即所得）
- [ ] 数据统计 + 管理后台
- [ ] 与 Minecraft 服务器深度集成（RCON 指令发送、在线人数监控）
- [ ] 夜间模式/主题切换
- [ ] 国际化（多语言）

---

## 👨‍💻 作者

**laogaofirefly** — 一位热爱 Minecraft 的全栈开发者。

GitHub：[@laogaofirefly](https://github.com/laogaofirefly)

---

## 📄 许可证

MIT License © 2025 laogaofirefly
