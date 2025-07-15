<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:report {title : Report Title}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new lab report Markdown file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $title = $this->argument('title');
        $slug = Str::slug($title, '-');
        // 如果slug为空（中文处理问题），使用时间戳
        if (empty($slug)) {
            $slug = 'report-' . now()->format('YmdHis');
        }
        $filename = $slug . '.md';
        $filepath = storage_path('reports/' . $filename);

        // 检查文件是否已存在
        if (File::exists($filepath)) {
            $this->error("Report file {$filename} already exists!");
            return 1;
        }

        // 确保目录存在
        $reportsDir = storage_path('reports');
        if (!File::exists($reportsDir)) {
            File::makeDirectory($reportsDir, 0755, true);
        }

        // 生成报告模板
        $template = $this->generateTemplate($title);

        // 写入文件
        File::put($filepath, $template);

        $this->info("✅ Report created successfully!");
        $this->line("📄 File location: {$filepath}");
        $this->line("🌐 Access link: " . url($slug . '.html'));
        $this->line("");
        $this->line("💡 Tip: You can edit the Markdown file directly to add content");

        return 0;
    }

    /**
     * 生成报告模板
     */
    private function generateTemplate($title)
    {
        $date = now()->format('Y-m-d');
        $time = now()->format('H:i');

        return <<<EOT
# {$title}

**Created:** {$date} {$time}  
**Author:** Your Name  
**Tags:** Lab Report, Penetration Testing  

---

## 📋 Target Information

| Item | Details |
|------|---------|
| Target Name | To be filled |
| Target IP | To be filled |
| Test Date | {$date} |
| Test Type | Penetration Testing |

## 🎯 Test Objectives

- [ ] Information Gathering
- [ ] Vulnerability Scanning
- [ ] Vulnerability Verification
- [ ] Privilege Escalation
- [ ] Lateral Movement
- [ ] Data Collection

## 🔍 Information Gathering

### Port Scanning

```bash
# Nmap scan command
nmap -sS -sV -p- target_ip

# Results
# Record scan results here
```

### Service Identification

| Port | Service | Version | Status |
|------|---------|---------|--------|
| 80 | HTTP | Apache 2.4 | Open |
| 22 | SSH | OpenSSH 7.4 | Open |

## 🛡️ Vulnerability Discovery

### Vulnerability 1: [Vulnerability Name]

**Risk Level:** 🔴 High / 🟡 Medium / 🟢 Low

**Vulnerability Description:**
Describe the discovered vulnerability...

**Vulnerability Location:**
```
http://target.com/vulnerable_page.php
```

**Vulnerability Verification:**
```bash
# Verification command or code
curl -X POST "http://target.com/login" -d "username=admin' OR 1=1--&password=test"
```

**Exploitation Screenshot:**
![Vulnerability Screenshot](screenshot.png)

## 🚀 Vulnerability Exploitation

### Exploitation Process

1. **Step 1:** Describe exploitation steps
2. **Step 2:** Continue description
3. **Step 3:** Final result

**Exploitation Code:**
```python
# Python exploitation script
import requests

def exploit():
    # Vulnerability exploitation code
    pass
```

## 🔐 Privilege Escalation

### Escalation Method

Describe the privilege escalation process...

```bash
# Privilege escalation commands
sudo -l
find / -perm -u=s -type f 2>/dev/null
```

## 📊 Test Summary

### Discovered Vulnerabilities

| Vulnerability Name | Risk Level | Impact Scope | Fix Recommendation |
|-------------------|------------|--------------|-------------------|
| SQL Injection | 🔴 High | Data Leakage | Use parameterized queries |
| XSS | 🟡 Medium | User Hijacking | Input validation and output encoding |

### Fix Recommendations

1. **Immediate Fix:**
   - Fix SQL injection vulnerability
   - Update outdated software versions

2. **Short-term Fix:**
   - Strengthen input validation
   - Implement security configuration

3. **Long-term Improvement:**
   - Establish secure development process
   - Regular security assessments

## 📎 Appendix

### Related Documents
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [CVE Details](https://cve.mitre.org/)

### Tool List
- Nmap
- Burp Suite
- SQLmap
- Metasploit

---

> **Disclaimer:** This report is for authorized security testing only and should not be used for illegal purposes.
EOT;
    }
} 