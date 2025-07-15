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
            'bio' => 'Passionate about cybersecurity, penetration testing, and web development. Focused on CTF competitions, lab challenges, and security research, actively learning and exploring cutting-edge technologies.',
            'location' => '',
            'email' => 'wither2rebirth@gmail.com',
            'skills' => [
                'Web Security' => ['SQL Injection', 'XSS', 'CSRF', 'File Upload Vulnerabilities', 'Privilege Escalation'],
                'Penetration Testing' => ['Information Gathering', 'Vulnerability Scanning', 'Privilege Escalation', 'Post-Exploitation'],
                'Programming Languages' => ['PHP', 'Python', 'JavaScript', 'Bash', 'SQL'],
                'Frameworks & Tools' => ['Laravel', 'Vue.js', 'Burp Suite', 'Nmap', 'Metasploit'],
                'System Environment' => ['Linux', 'Windows', 'Docker', 'Kali Linux', 'Parrot OS']
            ],
            'achievements' => [
                [
                    'title' => 'CTF Competition Awards',
                    'description' => 'Participated in multiple CTF competitions during college and achieved excellent results',
                    'year' => '2023'
                ],
                [
                    'title' => 'Learning Achievement Showcase',
                    'description' => 'Actively participated in cybersecurity learning and completed multiple experimental projects',
                    'year' => '2023'
                ],
                [
                    'title' => 'Technical Learning Progress',
                    'description' => 'Continuously learning various security technologies and development skills',
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
                    'title' => 'About This Site',
                    'content' => 'This is my personal learning record and technology sharing platform. Here I document my cybersecurity learning journey, technical research results, and CTF challenge solutions. I hope to communicate and learn with fellow cybersecurity enthusiasts.'
                ],
                'wither_to_rebirth' => [
                    'title' => 'wither - to - rebirth',
                    'content' => 'From withering to rebirth, this is my learning motto. Every setback is an opportunity for growth, every failure is a starting point for a new beginning. On the path of cybersecurity learning, I believe that only through continuous learning, practice, and breakthrough can one achieve true technical rebirth.'
                ]
            ]
        ];

        return view('aboutme.index', compact('personalInfo'));
    }
} 