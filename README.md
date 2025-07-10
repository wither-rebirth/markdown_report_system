# Laravel 博客与渗透测试报告系统

一个基于 Laravel 11 的内容发布平台，集成博客发布、CTF 报告管理、数据统计与评论系统，专为网络安全爱好者与技术写作者打造。

![架构图](markdown_blog.drawio.png)

---

## 🚀 项目特点

- ✍ 支持 Markdown 博客与报告展示
- 💬 评论系统与后台审核机制
- 📊 实时访问统计与内容热度分析
- 🔍 全文搜索与标签分类
- 🎨 响应式界面 + 管理后台
- 💾 自动备份与缓存优化
- 📁 HackTheBox / CTF 报告专属结构

---

## 🛠 技术栈

- **后端**：Laravel 11, PHP 8.2+, SQLite, CommonMark
- **前端**：Vite, 原生 JS, CSS3, Chart.js
- **工具**：Laravel Pint, PHPUnit

---

## 📋 环境要求

- PHP ≥ 8.2（含 `pdo`, `mbstring`, `openssl`, 等扩展）
- Node.js ≥ 18（前端构建）
- Composer 最新版
- SQLite（或其他数据库）
- Nginx / Apache

---

## ⚙️ 快速部署

```bash
# 克隆仓库
git clone https://github.com/your-username/laravel_report_system.git
cd laravel_report_system

# 安装依赖
composer install
npm install && npm run build

# 配置环境
cp .env.example .env
php artisan key:generate
touch database/database.sqlite

# 设置数据库路径
# .env 中设置：DB_DATABASE=/absolute/path/to/database.sqlite

# 数据迁移
php artisan migrate

# 初始化目录与权限
mkdir -p storage/blog storage/reports/Hackthebox-Walkthrough
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

部署后访问：

- 前台：`http://your-domain/`
- 后台：`http://your-domain/admin`

---

## 📁 博客与报告写法

### 发布博客（支持 YAML 元数据）

```
storage/blog/article.md
storage/blog/article/index.md + /images
```

### 发布报告

```
storage/reports/Example.md
storage/reports/Hackthebox-Walkthrough/Machine/Walkthrough.md + /images/
```

---

## 🎛 后台功能

- 内容管理：文章、报告、标签、分类
- 评论系统：用户评论 + 审核回复
- 数据分析：PV/UV、跳出率、热度排行
- 系统维护：缓存、备份、配置管理

---

## 🧰 运维与优化

### 缓存构建

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 定时任务（crontab）

```
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

### 队列监听

```bash
php artisan queue:work
```

---

## 🔐 安全建议

- `.env` 设置 `APP_ENV=production` 且关闭 `APP_DEBUG`
- 数据库与存储目录权限控制（如 `chmod 775` / `664`）
- 配合 HTTPS 使用（建议搭配 Let’s Encrypt）

---

## 📦 部署建议（生产环境）

```bash
composer install --no-dev --optimize-autoloader
npm run build
```

- 使用 Nginx + PHP-FPM + OPcache
- 启用 gzip、缓存策略、错误页优化

---

## 🤝 贡献方式

```bash
# Fork 仓库 → 创建分支 → 提交 PR
git checkout -b feature/my-feature
git commit -m "Add: xxx"
```

---

## 📝 更新日志

### v1.0.0 - 2024-01-15

- 博客系统与 Markdown 渲染
- 渗透报告模块与 HackTheBox 结构
- 管理后台与评论系统
- 数据分析 + 缓存优化 + 备份功能

---

## 📄 开源协议

MIT License

---

## 📧 联系方式

作者：**wither**  
邮箱：**wither2rebirth@gmail.com**  
项目主页：[GitHub Repository](https://github.com/your-username/laravel_report_system)

<div align="center"><strong>⭐ 如果觉得有用，请给个 Star！</strong></div>
