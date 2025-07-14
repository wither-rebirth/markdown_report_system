@extends('layout', ['title' => '关于我 | Wither\'s Blog', 'hasCanonical' => true])

@push('meta')
    <!-- SEO Meta Tags for About Page -->
    <meta name="description" content="了解更多关于Wither的信息 - 网络安全研究者，专注于渗透测试、CTF挑战和安全工具开发。分享个人学习经历和技术成长历程。">
    <meta name="keywords" content="Wither,关于我,网络安全研究者,渗透测试,CTF,技术博客作者,安全工具开发,个人简介">
    <meta name="author" content="Wither">
    <meta name="robots" content="index, follow">
    <meta name="revisit-after" content="30 days">
    <link rel="canonical" href="{{ route('aboutme.index') }}">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="关于我 | Wither's Blog">
    <meta property="og:description" content="了解更多关于Wither的信息 - 网络安全研究者，专注于渗透测试、CTF挑战和安全工具开发。">
    <meta property="og:type" content="profile">
    <meta property="og:url" content="{{ route('aboutme.index') }}">
    <meta property="og:site_name" content="Wither's Blog">
    <meta property="og:image" content="{{ asset('images/wither.JPG') }}">
    <meta property="og:image:alt" content="Wither - 网络安全研究者">
    <meta property="og:locale" content="zh_CN">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="关于我 | Wither's Blog">
    <meta name="twitter:description" content="了解更多关于Wither的信息 - 网络安全研究者，专注于渗透测试、CTF挑战和安全工具开发。">
    <meta name="twitter:image" content="{{ asset('images/wither.JPG') }}">
    <meta name="twitter:site" content="@WitherSec">
    <meta name="twitter:creator" content="@WitherSec">
    
    <!-- Structured Data for Person -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Person",
        "name": "{{ $personalInfo['name'] ?? 'Wither' }}",
        "description": "网络安全研究者，专注于渗透测试、CTF挑战和安全工具开发",
        "url": "{{ route('aboutme.index') }}",
        "image": "{{ asset('images/wither.JPG') }}",
        "jobTitle": "{{ $personalInfo['title'] ?? '网络安全研究者' }}",
        @if(!empty($personalInfo['location']))
        "address": {
            "@type": "PostalAddress",
            "addressLocality": "{{ $personalInfo['location'] }}"
        },
        @endif
        "sameAs": [
            @if(!empty($personalInfo['social_links']['github']))
            "{{ $personalInfo['social_links']['github'] }}",
            @endif
            @if(!empty($personalInfo['social_links']['blog']))
            "{{ $personalInfo['social_links']['blog'] }}"
            @endif
        ],
        "knowsAbout": [
            "网络安全",
            "渗透测试", 
            "CTF竞赛",
            "Web安全",
            "系统安全",
            "安全工具开发"
        ],
        "alumniOf": "{{ $personalInfo['education'] ?? '计算机科学' }}",
        "worksFor": {
            "@type": "Organization",
            "name": "Wither's Blog",
            "url": "{{ route('home.index') }}"
        }
    }
    </script>
@endpush

@section('content')
@push('styles')
    @vite(['resources/css/aboutme.css'])
@endpush

<div class="aboutme-container">
    <div class="container">
        <!-- 个人简介卡片 -->
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar">
                    <img src="{{ asset('images/wither.JPG') }}" alt="wither" class="avatar-img">
                </div>
                <div class="profile-info">
                    <h1 class="profile-name">{{ $personalInfo['name'] }}</h1>
                    @if(!empty($personalInfo['title']))
                        <p class="profile-title">{{ $personalInfo['title'] }}</p>
                    @endif
                    @if(!empty($personalInfo['location']))
                        <p class="profile-location">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4M12,6A6,6 0 0,0 6,12A6,6 0 0,0 12,18A6,6 0 0,0 18,12A6,6 0 0,0 12,6M12,8A4,4 0 0,1 16,12A4,4 0 0,1 12,16A4,4 0 0,1 8,12A4,4 0 0,1 12,8Z"/>
                            </svg>
                            {{ $personalInfo['location'] }}
                        </p>
                    @endif
                </div>
            </div>
            <div class="profile-bio">
                <p>{{ $personalInfo['bio'] }}</p>
            </div>
            <div class="profile-social">
                @foreach($personalInfo['social_links'] as $platform => $link)
                    <a href="{{ $link }}" target="_blank" rel="noopener" class="social-link">
                        @switch($platform)
                            @case('github')
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12,2A10,10 0 0,0 2,12C2,16.42 4.87,20.17 8.84,21.5C9.34,21.58 9.5,21.27 9.5,21C9.5,20.77 9.5,20.14 9.5,19.31C6.73,19.91 6.14,17.97 6.14,17.97C5.68,16.81 5.03,16.5 5.03,16.5C4.12,15.88 5.1,15.9 5.1,15.9C6.1,15.97 6.63,16.93 6.63,16.93C7.5,18.45 8.97,18 9.54,17.76C9.63,17.11 9.89,16.67 10.17,16.42C7.95,16.17 5.62,15.31 5.62,11.5C5.62,10.39 6,9.5 6.65,8.79C6.55,8.54 6.2,7.5 6.75,6.15C6.75,6.15 7.59,5.88 9.5,7.17C10.29,6.95 11.15,6.84 12,6.84C12.85,6.84 13.71,6.95 14.5,7.17C16.41,5.88 17.25,6.15 17.25,6.15C17.8,7.5 17.45,8.54 17.35,8.79C18,9.5 18.38,10.39 18.38,11.5C18.38,15.32 16.04,16.16 13.81,16.41C14.17,16.72 14.5,17.33 14.5,18.26C14.5,19.6 14.5,20.68 14.5,21C14.5,21.27 14.66,21.59 15.17,21.5C19.14,20.16 22,16.42 22,12A10,10 0 0,0 12,2Z"/>
                                </svg>
                                @break
                            @case('discord')
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515a.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0a12.64 12.64 0 0 0-.617-1.25a.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057a19.9 19.9 0 0 0 5.993 3.03a.078.078 0 0 0 .084-.028a14.09 14.09 0 0 0 1.226-1.994a.076.076 0 0 0-.041-.106a13.107 13.107 0 0 1-1.872-.892a.077.077 0 0 1-.008-.128a10.2 10.2 0 0 0 .372-.292a.074.074 0 0 1 .077-.010c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127a12.299 12.299 0 0 1-1.873.892a.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028a19.839 19.839 0 0 0 6.002-3.03a.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419c0-1.333.956-2.419 2.157-2.419c1.21 0 2.176 1.096 2.157 2.42c0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419c0-1.333.955-2.419 2.157-2.419c1.21 0 2.176 1.096 2.157 2.42c0 1.333-.946 2.418-2.157 2.418z"/>
                                </svg>
                                @break
                            @case('blog')
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4M12,6A6,6 0 0,0 6,12A6,6 0 0,0 12,18A6,6 0 0,0 18,12A6,6 0 0,0 12,6M12,8A4,4 0 0,1 16,12A4,4 0 0,1 12,16A4,4 0 0,1 8,12A4,4 0 0,1 12,8Z"/>
                                </svg>
                                @break
                            @case('email')
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M20,8L12,13L4,8V6L12,11L20,6M20,4H4C2.89,4 2,4.89 2,6V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V6C22,4.89 21.1,4 20,4Z"/>
                                </svg>
                                @break
                        @endswitch
                        <span>{{ ucfirst($platform) }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- 内容块 -->
        <div class="content-blocks">
            @foreach($personalInfo['content_blocks'] as $key => $block)
                <div class="content-block">
                    <h2 class="block-title">{{ $block['title'] }}</h2>
                    <div class="block-content">
                        <p>{{ $block['content'] }}</p>
                        @if($key === 'wither_to_rebirth')
                            <!-- 玫瑰动画效果 -->
                            <div class="rose-animation-container">
                                <div class="rose-flower">
                                    <!-- 外层花瓣 -->
                                    <div class="rose-petals outer-petals">
                                        <div class="petal outer-petal petal-1">
                                            <div class="petal-texture"></div>
                                        </div>
                                        <div class="petal outer-petal petal-2">
                                            <div class="petal-texture"></div>
                                        </div>
                                        <div class="petal outer-petal petal-3">
                                            <div class="petal-texture"></div>
                                        </div>
                                        <div class="petal outer-petal petal-4">
                                            <div class="petal-texture"></div>
                                        </div>
                                        <div class="petal outer-petal petal-5">
                                            <div class="petal-texture"></div>
                                        </div>
                                        <div class="petal outer-petal petal-6">
                                            <div class="petal-texture"></div>
                                        </div>
                                    </div>
                                    <!-- 中层花瓣 -->
                                    <div class="rose-petals middle-petals">
                                        <div class="petal middle-petal petal-7">
                                            <div class="petal-texture"></div>
                                        </div>
                                        <div class="petal middle-petal petal-8">
                                            <div class="petal-texture"></div>
                                        </div>
                                        <div class="petal middle-petal petal-9">
                                            <div class="petal-texture"></div>
                                        </div>
                                        <div class="petal middle-petal petal-10">
                                            <div class="petal-texture"></div>
                                        </div>
                                        <div class="petal middle-petal petal-11">
                                            <div class="petal-texture"></div>
                                        </div>
                                    </div>
                                    <!-- 内层花瓣 -->
                                    <div class="rose-petals inner-petals">
                                        <div class="petal inner-petal petal-12">
                                            <div class="petal-texture"></div>
                                        </div>
                                        <div class="petal inner-petal petal-13">
                                            <div class="petal-texture"></div>
                                        </div>
                                        <div class="petal inner-petal petal-14">
                                            <div class="petal-texture"></div>
                                        </div>
                                        <div class="petal inner-petal petal-15">
                                            <div class="petal-texture"></div>
                                        </div>
                                    </div>
                                    <div class="rose-center"></div>
                                    <div class="rose-stem"></div>
                                    <!-- 散落的花瓣 -->
                                    <div class="fallen-petals">
                                        <div class="fallen-petal fallen-1"></div>
                                        <div class="fallen-petal fallen-2"></div>
                                        <div class="fallen-petal fallen-3"></div>
                                        <div class="fallen-petal fallen-4"></div>
                                        <div class="fallen-petal fallen-5"></div>
                                        <div class="fallen-petal fallen-6"></div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- 联系方式 -->
        <div class="contact-section">
            <h2 class="section-title">联系方式</h2>
            <div class="contact-info">
                <div class="contact-item">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M20,8L12,13L4,8V6L12,11L20,6M20,4H4C2.89,4 2,4.89 2,6V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V6C22,4.89 21.1,4 20,4Z"/>
                    </svg>
                    <span>{{ $personalInfo['email'] }}</span>
                </div>
                @if(!empty($personalInfo['location']))
                    <div class="contact-item">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4M12,6A6,6 0 0,0 6,12A6,6 0 0,0 12,18A6,6 0 0,0 18,12A6,6 0 0,0 12,6M12,8A4,4 0 0,1 16,12A4,4 0 0,1 12,16A4,4 0 0,1 8,12A4,4 0 0,1 12,8Z"/>
                        </svg>
                        <span>{{ $personalInfo['location'] }}</span>
                    </div>
                @endif
            </div>
            <div class="contact-message">
                <p>如果您有任何问题或想要合作，欢迎随时与我联系！</p>
            </div>
        </div>
    </div>
</div>
@endsection 