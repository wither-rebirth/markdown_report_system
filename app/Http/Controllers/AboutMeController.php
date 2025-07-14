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
            'title' => '',
            'bio' => '热爱网络安全、渗透测试和Web开发。专注于CTF竞赛、靶场挑战和安全研究，积极学习和探索前沿技术。',
            'location' => '',
            'email' => 'wither2rebirth@gmail.com',
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
                    'description' => '在校期间多次参加CTF竞赛，获得优异成绩',
                    'year' => '2023'
                ],
                [
                    'title' => '学习成果展示',
                    'description' => '积极参与网络安全学习，完成多个实验项目',
                    'year' => '2023'
                ],
                [
                    'title' => '技术学习进展',
                    'description' => '持续学习各种安全技术和开发技能',
                    'year' => '2022-2023'
                ]
            ],
            'social_links' => [
                'github' => 'https://github.com/wither-rebirth',
                'discord' => 'https://discord.com/users/wither0295_45837',
                'blog' => url('/'),
                'email' => 'mailto:wither2rebirth@gmail.com'
            ],
            'content_blocks' => [
                'about_site' => [
                    'title' => '关于本站',
                    'content' => '这是我的个人学习记录和技术分享平台。在这里记录我的网络安全学习历程、技术研究成果和CTF挑战的解题过程。希望能够与同样热爱网络安全的同学们交流学习。'
                ],
                'wither_to_rebirth' => [
                    'title' => 'wither - to - rebirth',
                    'content' => '从凋零到重生，这是我的学习座右铭。每一次挫折都是成长的机会，每一次失败都是重新开始的起点。在网络安全的学习道路上，我相信只有不断地学习、实践和突破，才能实现真正的技术重生。'
                ]
            ]
        ];

        return view('aboutme.index', compact('personalInfo'));
    }
} 