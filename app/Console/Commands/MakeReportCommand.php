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
    protected $signature = 'make:report {title : 报告标题}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '创建一个新的靶场报告 Markdown 文件';

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
            $this->error("报告文件 {$filename} 已存在！");
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

        $this->info("✅ 报告创建成功！");
        $this->line("📄 文件位置: {$filepath}");
        $this->line("🌐 访问链接: " . url($slug . '.html'));
        $this->line("");
        $this->line("💡 提示：你可以直接编辑 Markdown 文件来添加内容");

        return 0;
    }

    /**
     * 生成报告模板
     */
    private function generateTemplate($title)
    {
        $date = now()->format('Y年m月d日');
        $time = now()->format('H:i');

        return <<<EOT
# {$title}

**创建时间：** {$date} {$time}  
**作者：** 你的名字  
**标签：** 靶场报告, 渗透测试  

---

## 📋 目标信息

| 项目 | 详情 |
|------|------|
| 目标名称 | 待填写 |
| 目标IP | 待填写 |
| 测试时间 | {$date} |
| 测试类型 | 渗透测试 |

## 🎯 测试目标

- [ ] 信息收集
- [ ] 漏洞扫描
- [ ] 漏洞验证
- [ ] 权限提升
- [ ] 横向移动
- [ ] 数据收集

## 🔍 信息收集

### 端口扫描

```bash
# Nmap 扫描命令
nmap -sS -sV -p- target_ip

# 结果
# 在这里记录扫描结果
```

### 服务识别

| 端口 | 服务 | 版本 | 状态 |
|------|------|------|------|
| 80 | HTTP | Apache 2.4 | 开放 |
| 22 | SSH | OpenSSH 7.4 | 开放 |

## 🛡️ 漏洞发现

### 漏洞 1：[漏洞名称]

**风险等级：** 🔴 高危 / 🟡 中危 / 🟢 低危

**漏洞描述：**
描述发现的漏洞...

**漏洞位置：**
```
http://target.com/vulnerable_page.php
```

**漏洞验证：**
```bash
# 验证命令或代码
curl -X POST "http://target.com/login" -d "username=admin' OR 1=1--&password=test"
```

**利用截图：**
![漏洞截图](screenshot.png)

## 🚀 漏洞利用

### 利用过程

1. **第一步：** 描述利用步骤
2. **第二步：** 继续描述
3. **第三步：** 最终结果

**利用代码：**
```python
# Python 利用脚本
import requests

def exploit():
    # 漏洞利用代码
    pass
```

## 🔐 权限提升

### 提权方法

描述权限提升的过程...

```bash
# 提权命令
sudo -l
find / -perm -u=s -type f 2>/dev/null
```

## 📊 测试总结

### 发现的漏洞

| 漏洞名称 | 风险等级 | 影响范围 | 修复建议 |
|---------|---------|---------|---------|
| SQL注入 | 🔴 高危 | 数据泄露 | 使用参数化查询 |
| XSS | 🟡 中危 | 用户劫持 | 输入验证和输出编码 |

### 修复建议

1. **立即修复：**
   - 修复 SQL 注入漏洞
   - 更新过期的软件版本

2. **近期修复：**
   - 加强输入验证
   - 实施安全配置

3. **长期改进：**
   - 建立安全开发流程
   - 定期安全评估

## 📎 附录

### 相关文档
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [CVE 详情](https://cve.mitre.org/)

### 工具清单
- Nmap
- Burp Suite
- SQLmap
- Metasploit

---

> **免责声明：** 本报告仅用于授权的安全测试，不得用于非法目的。
EOT;
    }
} 