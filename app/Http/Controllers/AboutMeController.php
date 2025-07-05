<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AboutMeController extends Controller
{
    /**
     * 显示关于我页面
     */
    public function index()
    {
        // 这里可以放置一些个人信息数据
        $personalInfo = [
            'name' => 'wither',
            'title' => '安全研究员 & 开发者',
            'bio' => '热爱网络安全、渗透测试和Web开发。专注于CTF竞赛、靶场挑战和安全研究。',
            'location' => '中国',
            'email' => 'example@example.com',
            'skills' => [
                'Web安全' => ['SQL注入', 'XSS', 'CSRF', '文件上传漏洞', '权限绕过'],
                '渗透测试' => ['信息收集', '漏洞扫描', '权限提升', '后渗透'],
                '编程语言' => ['PHP', 'Python', 'JavaScript', 'Bash', 'SQL'],
                '框架工具' => ['Laravel', 'Vue.js', 'Burp Suite', 'Nmap', 'Metasploit'],
                '系统环境' => ['Linux', 'Windows', 'Docker', 'Kali Linux', 'Parrot OS']
            ],
            'achievements' => [
                [
                    'title' => 'CTF竞赛获奖',
                    'description' => '多次参加CTF竞赛，获得优异成绩',
                    'year' => '2023'
                ],
                [
                    'title' => '安全漏洞发现',
                    'description' => '发现并报告多个web应用安全漏洞',
                    'year' => '2023'
                ],
                [
                    'title' => '开源项目贡献',
                    'description' => '为多个安全工具和项目贡献代码',
                    'year' => '2022-2023'
                ]
            ],
            'social_links' => [
                'github' => 'https://github.com/wither',
                'twitter' => 'https://twitter.com/wither',
                'blog' => url('/'),
                'email' => 'mailto:example@example.com'
            ]
        ];

        return view('aboutme.index', compact('personalInfo'));
    }
} 