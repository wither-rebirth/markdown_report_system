# Laravel Blog & Penetration Testing Report System

A comprehensive content publishing platform built with Laravel 11, integrating blog publishing, CTF/penetration testing report management, data analytics, and comment systems. Designed for cybersecurity enthusiasts, technical writers, and penetration testers.

![Architecture Diagram](markdown_blog.drawio.png)

---

## 🚀 Key Features

- ✍️ **Markdown Blog System** - Full support for Markdown blog posts with YAML metadata
- 📊 **Penetration Testing Reports** - Dedicated HackTheBox and CTF report management
- 🔒 **Report Lock System** - Password protection for sensitive reports  
- 💬 **Comment System** - User comments with backend moderation
- 📈 **Analytics Dashboard** - Real-time access statistics and content popularity analysis
- 🔍 **Search & Classification** - Full-text search with tag and category management
- 🎨 **Responsive Design** - Modern interface with complete admin backend
- 💾 **Backup & Caching** - Automatic backup and cache optimization
- 🛡️ **Security Features** - Admin authentication, password locks, and secure access control

---

## 🛠 Tech Stack

- **Backend**: Laravel 11, PHP 8.2+, SQLite, CommonMark
- **Frontend**: Vite, Vanilla JavaScript, CSS3, Chart.js  
- **Database**: SQLite (configurable to other databases)
- **Tools**: Laravel Pint, PHPUnit, Artisan Commands

---

## 📋 System Requirements

- **PHP** ≥ 8.2 (with `pdo`, `mbstring`, `openssl`, `fileinfo` extensions)
- **Node.js** ≥ 18 (for frontend build process)
- **Composer** (latest version)
- **Database** SQLite (or MySQL/PostgreSQL)
- **Web Server** Nginx/Apache with PHP-FPM

---

## ⚡ Quick Deployment

### 1. Clone & Install Dependencies

```bash
# Clone the repository
git clone https://github.com/wither-rebirth/markdown_report_system.git
cd markdown_report_system

# Install PHP dependencies
composer install

# Install Node.js dependencies and build assets
npm install && npm run build
```

### 2. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Create SQLite database
touch database/database.sqlite

# Configure database path in .env
# Set: DB_DATABASE=/absolute/path/to/your/project/database/database.sqlite
```

### 3. Database Setup

```bash
# Run database migrations
php artisan migrate

# Seed initial data (optional)
php artisan db:seed
```

### 4. Directory Setup & Permissions

```bash
# Create required directories
mkdir -p storage/blog storage/reports/Hackthebox-Walkthrough

# Set proper permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache  # For production
```

### 5. Access the Application

- **Frontend**: `http://your-domain/`
- **Admin Panel**: `http://your-domain/admin`
- **Reports**: `http://your-domain/reports`

---

## 🎛️ Admin Management

### Create Admin User

```bash
# Create admin user through seeder
php artisan db:seed --class=AdminUserSeeder

# Or reset existing admin password
php artisan admin:reset-password admin@example.com newpassword
```

### Admin Features

- **Content Management**: Blog posts, reports, categories, tags
- **Comment Moderation**: Review and manage user comments
- **Analytics Dashboard**: PV/UV statistics, popular content tracking
- **Report Lock Management**: Password protect sensitive reports
- **System Maintenance**: Cache management, backups, configuration
- **User Management**: Admin user management and authentication

---

## 📝 Content Management

### Blog Posts

Create blog posts in the `storage/blog/` directory:

```
storage/blog/article-name.md
storage/blog/article-name/index.md + images/
```

**YAML Metadata Support:**
```yaml
---
title: "Article Title"
date: "2024-01-15"
tags: ["security", "tutorial"]
category: "Penetration Testing"
author: "Your Name"
---

# Article Content
Your markdown content here...
```

### Penetration Testing Reports

Create reports in the `storage/reports/` directory:

```
# General reports
storage/reports/report-name.md

# HackTheBox reports (organized by difficulty)
storage/reports/Hackthebox-Walkthrough/Easy/MachineName/Walkthrough.md
storage/reports/Hackthebox-Walkthrough/Medium/MachineName/Walkthrough.md
storage/reports/Hackthebox-Walkthrough/Hard/MachineName/Walkthrough.md
storage/reports/Hackthebox-Walkthrough/Insane/MachineName/Walkthrough.md
storage/reports/Hackthebox-Walkthrough/Fortresses/MachineName/Walkthrough.md
```

---

## 🔧 Custom Artisan Commands

### Report Management Commands

#### Create New Report
```bash
# Create a new penetration testing report with template
php artisan make:report "Report Title"

# Example
php artisan make:report "HackTheBox Lame Walkthrough"
```

#### Sync Report Locks
```bash
# Sync report locks with actual files
php artisan reports:sync-locks

# Auto-enable locks for new reports
php artisan reports:sync-locks --auto-enable
```

#### Enable Report Password Protection
```bash
# Enable password lock for a specific report
php artisan reports:enable-lock {slug} {password} --description="Optional description"

# Example
php artisan reports:enable-lock htb-easy-lame mySecurePassword123 --description="Lame machine walkthrough"
```

#### Clean Report Locks Database
```bash
# Show current lock status
php artisan reports:clean --show

# Remove old format locks
php artisan reports:clean --clean-old

# Remove ALL locks (use with caution)
php artisan reports:clean --clean --force
```

#### Test Report Lock System
```bash
# Test the report lock functionality
php artisan test:report-lock
```

### Cache Management Commands

#### Clear Blog Cache
```bash
# Clear blog-specific cache
php artisan blog:clear-cache

# Force clear all cache
php artisan blog:clear-cache --force
```

#### Laravel Cache Commands
```bash
# Build application cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Clear application cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Admin Management Commands

#### Reset Admin Password
```bash
# Reset admin user password
php artisan admin:reset-password admin@example.com newPassword123
```

---

## 🔒 Report Lock System

The system includes a sophisticated report password protection feature:

### Features
- **Password Protection**: Secure sensitive reports with passwords
- **Automatic Sync**: Automatically detect and sync report files
- **Multiple Formats**: Support for various report naming conventions
- **Admin Management**: Full admin control over locked reports
- **Flexible Configuration**: Enable/disable locks per report

### Workflow
1. Create reports in the storage directory
2. Run `php artisan reports:sync-locks` to detect new reports
3. Use `php artisan reports:enable-lock` to password protect specific reports
4. Users must enter the correct password to access locked reports

---

## 🏗️ Directory Structure

```
laravel_report_system/
├── app/
│   ├── Console/Commands/          # Custom Artisan commands
│   ├── Http/Controllers/          # Application controllers
│   │   ├── Admin/                 # Admin panel controllers
│   │   ├── BlogController.php     # Blog functionality
│   │   └── ReportController.php   # Report management
│   ├── Models/                    # Eloquent models
│   └── Middleware/                # Custom middleware
├── resources/
│   ├── views/                     # Blade templates
│   │   ├── admin/                 # Admin panel views
│   │   ├── blog/                  # Blog views
│   │   └── report/                # Report views
│   ├── css/                       # Stylesheets
│   └── js/                        # JavaScript files
├── storage/
│   ├── blog/                      # Blog post files
│   └── reports/                   # Report files
│       └── Hackthebox-Walkthrough/ # HackTheBox reports
├── database/
│   └── migrations/                # Database migrations
└── routes/
    └── web.php                    # Application routes
```

---

## 🚀 Production Deployment

### Optimize for Production

```bash
# Install dependencies without dev packages
composer install --no-dev --optimize-autoloader

# Build production assets
npm run build

# Enable production caching
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Web Server Configuration

#### Nginx Configuration Example
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/laravel_report_system/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### Scheduled Tasks

Add to crontab for scheduled operations:
```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

### Queue Workers (if using queues)
```bash
# Start queue worker
php artisan queue:work

# For production, use supervisor for process management
```

---

## 🔐 Security Considerations

### Environment Security
- Set `APP_ENV=production` in `.env`
- Disable `APP_DEBUG` in production
- Use strong `APP_KEY` (generated by `php artisan key:generate`)
- Configure proper database permissions

### File Permissions
```bash
# Recommended permissions
chmod 755 /path/to/laravel_report_system
chmod -R 755 storage bootstrap/cache
chmod 644 .env
```

### HTTPS Setup
- Use HTTPS in production (Let's Encrypt recommended)
- Configure proper SSL certificates
- Update `APP_URL` to use https://

### Report Security
- Use strong passwords for report locks
- Regularly audit locked reports
- Monitor access logs for suspicious activity

---

## 📊 Analytics & Monitoring

### Built-in Analytics
- **Page Views**: Track individual page visits
- **User Analytics**: Monitor user behavior and popular content
- **Report Statistics**: Track report access and engagement
- **Admin Dashboard**: Real-time analytics overview

### Monitoring Commands
```bash
# Check system status
php artisan about

# Monitor logs
tail -f storage/logs/laravel.log

# Check report lock status
php artisan reports:clean --show
```

---

## 🧪 Development & Testing

### Development Setup
```bash
# Use development environment
cp .env.example .env
# Set APP_ENV=local and APP_DEBUG=true

# Run development server
php artisan serve

# Watch assets for changes
npm run dev
```

### Testing
```bash
# Run PHP tests
php artisan test

# Run specific test suites
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Test report lock functionality
php artisan test:report-lock
```

---

## 🤝 Contributing

We welcome contributions! Please follow these steps:

1. **Fork the Repository**
   ```bash
   git clone https://github.com/your-username/laravel_report_system.git
   cd laravel_report_system
   ```

2. **Create Feature Branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```

3. **Make Changes**
   - Follow PSR-12 coding standards
   - Add tests for new features
   - Update documentation as needed

4. **Test Changes**
   ```bash
   php artisan test
   ./vendor/bin/pint  # Code style fixing
   ```

5. **Submit Pull Request**
   - Provide clear description of changes
   - Reference any related issues
   - Ensure CI passes

---

## 📝 Changelog

### v2.0.0 - 2025-Latest
- ✨ **Enhanced Report Lock System** - Advanced password protection with sync capabilities
- 🎯 **Custom Artisan Commands** - Comprehensive CLI tools for report management
- 📊 **Improved Analytics** - Enhanced dashboard with real-time statistics
- 🔧 **Better Admin Tools** - Streamlined backend management interface
- 🏗️ **Code Architecture** - Improved codebase structure and documentation

### v1.0.0 - 2025-07-01
- 🎉 **Initial Release**
- 📝 Blog system with Markdown rendering
- 🛡️ Penetration testing report module with HackTheBox structure  
- 🎛️ Management backend and comment system
- 📈 Data analysis, cache optimization, and backup functionality

---

## 📄 License

This project is open-sourced under the [MIT License](https://opensource.org/licenses/MIT).

---

## 📧 Contact & Support

**Author**: wither  
**Email**: wither2rebirth@gmail.com  
**Project Repository**: [GitHub](https://github.com/your-username/laravel_report_system)

### Getting Help
- 📚 Check the documentation above
- 🐛 Report issues on GitHub
- 💬 Join discussions in the repository
- 📧 Contact the author for support

---

<div align="center">
  <strong>⭐ If you find this project useful, please give it a star!</strong>
  <br><br>
  <em>Built with ❤️ for the cybersecurity community</em>
</div>