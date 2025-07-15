# Laravel Blog & Penetration Testing Report System

A content publishing platform based on Laravel 11, integrating blog publishing, CTF report management, data statistics, and comment systems, designed for cybersecurity enthusiasts and technical writers.

![Architecture Diagram](markdown_blog.drawio.png)

---

## 🚀 Project Features

- ✍ Support for Markdown blog and report display
- 💬 Comment system with backend moderation
- 📊 Real-time access statistics and content popularity analysis
- 🔍 Full-text search and tag classification
- 🎨 Responsive interface + management backend
- 💾 Automatic backup and cache optimization
- 📁 HackTheBox / CTF report dedicated structure

---

## 🛠 Tech Stack

- **Backend**: Laravel 11, PHP 8.2+, SQLite, CommonMark
- **Frontend**: Vite, Native JS, CSS3, Chart.js
- **Tools**: Laravel Pint, PHPUnit

---

## 📋 Environment Requirements

- PHP ≥ 8.2 (with `pdo`, `mbstring`, `openssl`, etc. extensions)
- Node.js ≥ 18 (for frontend build)
- Composer latest version
- SQLite (or other database)
- Nginx / Apache

---

## ⚙️ Quick Deployment

```bash
# Clone repository
git clone https://github.com/your-username/laravel_report_system.git
cd laravel_report_system

# Install dependencies
composer install
npm install && npm run build

# Configure environment
cp .env.example .env
php artisan key:generate
touch database/database.sqlite

# Set database path
# Set in .env: DB_DATABASE=/absolute/path/to/database.sqlite

# Database migration
php artisan migrate

# Initialize directories and permissions
mkdir -p storage/blog storage/reports/Hackthebox-Walkthrough
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

Access after deployment:

- Frontend: `http://your-domain/`
- Backend: `http://your-domain/admin`

---

## 📁 Blog & Report Writing

### Publishing Blogs (supports YAML metadata)

```
storage/blog/article.md
storage/blog/article/index.md + /images
```

### Publishing Reports

```
storage/reports/Example.md
storage/reports/Hackthebox-Walkthrough/Machine/Walkthrough.md + /images/
```

---

## 🎛 Backend Features

- Content Management: Articles, Reports, Tags, Categories
- Comment System: User comments + moderation replies
- Data Analysis: PV/UV, bounce rate, popularity ranking
- System Maintenance: Cache, backup, configuration management

---

## 🧰 Operations & Optimization

### Cache Building

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Scheduled Tasks (crontab)

```
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

### Queue Listener

```bash
php artisan queue:work
```

---

## 🔐 Security Recommendations

- Set `APP_ENV=production` and disable `APP_DEBUG` in `.env`
- Database and storage directory permission control (e.g., `chmod 775` / `664`)
- Use with HTTPS (recommended with Let's Encrypt)

---

## 📦 Deployment Recommendations (Production)

```bash
composer install --no-dev --optimize-autoloader
npm run build
```

- Use Nginx + PHP-FPM + OPcache
- Enable gzip, caching strategies, error page optimization

---

## 🤝 Contributing

```bash
# Fork repository → Create branch → Submit PR
git checkout -b feature/my-feature
git commit -m "Add: xxx"
```

---

## 📝 Changelog

### v1.0.0 - 2024-01-15

- Blog system and Markdown rendering
- Penetration report module and HackTheBox structure
- Management backend and comment system
- Data analysis + cache optimization + backup functionality

---

## 📄 Open Source License

MIT License

---

## 📧 Contact Information

Author: **wither**  
Email: **wither2rebirth@gmail.com**  
Project Homepage: [GitHub Repository](https://github.com/your-username/laravel_report_system)

<div align="center"><strong>⭐ If you find it useful, please give it a Star!</strong></div>
