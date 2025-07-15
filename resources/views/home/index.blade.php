@extends('layout', ['title' => "wither's blog", 'hasCanonical' => true])

@push('meta')
    <!-- SEO Meta Tags for Home Page -->
    <meta name="description" content="Wither's Blog - Personal tech blog focused on cybersecurity, penetration testing, and CTF challenges. Sharing HackTheBox writeups, security tools usage, and programming techniques.">
    <meta name="keywords" content="wither,blog,cybersecurity,penetration testing,HackTheBox,CTF,tech sharing,lab reports,writeup,security research,programming">
    <meta name="author" content="Wither">
    <meta name="robots" content="index, follow">
    <meta name="revisit-after" content="3 days">
    <link rel="canonical" href="{{ route('home.index') }}">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="Wither's Blog - Cybersecurity Tech Blog">
    <meta property="og:description" content="Personal tech blog focused on cybersecurity, penetration testing, and CTF challenges. Sharing HackTheBox writeups, security tools usage, and programming techniques.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('home.index') }}">
    <meta property="og:site_name" content="Wither's Blog">
    <meta property="og:image" content="{{ asset('images/wither-og.jpg') }}">
    <meta property="og:image:alt" content="Wither's Blog - Cybersecurity Tech Blog">
    <meta property="og:locale" content="en_US">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Wither's Blog - Cybersecurity Tech Blog">
    <meta name="twitter:description" content="Personal tech blog focused on cybersecurity, penetration testing, and CTF challenges. Sharing HackTheBox writeups, security tools usage, and programming techniques.">
    <meta name="twitter:image" content="{{ asset('images/wither-og.jpg') }}">
    <meta name="twitter:site" content="@WitherSec">
    <meta name="twitter:creator" content="@WitherSec">
    
    <!-- Additional SEO -->
    <meta name="application-name" content="Wither's Blog">
    <meta name="msapplication-TileColor" content="#3b82f6">
    <meta name="theme-color" content="#3b82f6">
    
    <!-- Structured Data for Homepage -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Blog",
        "name": "Wither's Blog",
        "description": "专注于网络安全、渗透测试、CTF挑战的个人技术博客",
        "url": "{{ route('home.index') }}",
        "author": {
            "@type": "Person",
            "name": "Wither",
            "description": "网络安全研究者，专注于渗透测试和CTF挑战",
            "url": "{{ route('aboutme.index') }}"
        },
        "publisher": {
            "@type": "Organization",
            "name": "Wither's Blog",
            "url": "{{ route('home.index') }}"
        },
        "mainEntityOfPage": {
            "@type": "WebPage",
            "@id": "{{ route('home.index') }}"
        },
        "potentialAction": {
            "@type": "SearchAction",
            "target": "{{ route('blog.index') }}?search={search_term_string}",
            "query-input": "required name=search_term_string"
        }
    }
    </script>
@endpush

@push('styles')
    @vite(['resources/css/home.css'])
@endpush

@section('content')
<div class="home-page">
    <!-- Hero 区域 -->
    <section class="hero-section">
        <div class="hero-background">
            <div class="particle-system"></div>
            <div class="floating-elements">
                <div class="floating-element" style="--delay: 0s; --duration: 20s;">💻</div>
                <div class="floating-element" style="--delay: 2s; --duration: 25s;">🔒</div>
                <div class="floating-element" style="--delay: 4s; --duration: 30s;">🛡️</div>
                <div class="floating-element" style="--delay: 6s; --duration: 22s;">⚡</div>
                <div class="floating-element" style="--delay: 8s; --duration: 28s;">🎯</div>
            </div>
        </div>
        
        <div class="hero-content">
            <div class="hero-avatar">
                <img src="{{ asset('images/wither.JPG') }}" alt="wither" class="avatar">
                <div class="avatar-ring"></div>
            </div>
            
            <div class="hero-text">
                <h1 class="hero-title">
                    <span class="greeting">Hello, I'm</span>
                    <span class="name typing-text" data-text="wither">wither</span>
                </h1>
                
                <div class="hero-subtitle">
                    <span class="subtitle-text typewriter" data-text="Cybersecurity Researcher | Penetration Testing Enthusiast | Tech Sharer">
                        Cybersecurity Researcher | Penetration Testing Enthusiast | Tech Sharer
                    </span>
                </div>
                
                <p class="hero-description">
                    Focused on cybersecurity learning and technology sharing, passionate about CTF competitions and lab challenges.
                    Recording my learning journey here, sharing technical insights, and growing together with everyone.
                </p>
                
                <div class="hero-actions">
                    <a href="{{ route('blog.index') }}" class="btn btn-primary">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4M12,6A6,6 0 0,0 6,12A6,6 0 0,0 12,18A6,6 0 0,0 18,12A6,6 0 0,0 12,6M12,8A4,4 0 0,1 16,12A4,4 0 0,1 12,16A4,4 0 0,1 8,12A4,4 0 0,1 12,8Z"/>
                        </svg>
                        Browse Blog
                    </a>
                    <a href="{{ route('reports.index') }}" class="btn btn-secondary">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                        </svg>
                        View Reports
                    </a>
                </div>
            </div>
        </div>
        
        <div class="hero-scroll">
            <div class="scroll-indicator">
                <span>Scroll down to explore more</span>
                <div class="scroll-arrow">↓</div>
            </div>
        </div>
    </section>

    <!-- 统计仪表板 -->
    <section class="stats-section">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-card" data-aos="fade-up" data-aos-delay="0">
                    <div class="stat-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4M12,6A6,6 0 0,0 6,12A6,6 0 0,0 12,18A6,6 0 0,0 18,12A6,6 0 0,0 12,6M12,8A4,4 0 0,1 16,12A4,4 0 0,1 12,16A4,4 0 0,1 8,12A4,4 0 0,1 12,8Z"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number" data-count="{{ $stats['total_posts'] }}">0</div>
                        <div class="stat-label">Technical Articles</div>
                    </div>
                </div>
                
                <div class="stat-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="stat-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number" data-count="{{ $stats['total_reports'] }}">0</div>
                        <div class="stat-label">Lab Reports</div>
                    </div>
                </div>
                
                <div class="stat-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="stat-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12,9A3,3 0 0,0 9,12A3,3 0 0,0 12,15A3,3 0 0,0 15,12A3,3 0 0,0 12,9M12,17A5,5 0 0,1 7,12A5,5 0 0,1 12,7A5,5 0 0,1 17,12A5,5 0 0,1 12,17M12,4.5C7,4.5 2.73,7.61 1,12C2.73,16.39 7,19.5 12,19.5C17,19.5 21.27,16.39 23,12C21.27,7.61 17,4.5 12,4.5Z"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number" data-count="{{ $stats['total_views'] }}">0</div>
                        <div class="stat-label">Total Views</div>
                    </div>
                </div>
                
                <div class="stat-card" data-aos="fade-up" data-aos-delay="300">
                    <div class="stat-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M16.2,16.2L11,13V7H12.5V12.2L17,14.9L16.2,16.2Z"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">
                            @if($stats['last_updated'])
                                {{ floor((time() - $stats['last_updated']) / 86400) }}
                            @else
                                0
                            @endif
                        </div>
                        <div class="stat-label">Days ago updated</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 最新内容 -->
    <section class="latest-content-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Latest Content</h2>
                <p class="section-subtitle">Explore my latest technical articles and lab reports</p>
            </div>
            
            <div class="content-tabs">
                <div class="tab-buttons">
                    <button class="tab-button active" data-tab="blog">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4M12,6A6,6 0 0,0 6,12A6,6 0 0,0 12,18A6,6 0 0,0 18,12A6,6 0 0,0 12,6M12,8A4,4 0 0,1 16,12A4,4 0 0,1 12,16A4,4 0 0,1 8,12A4,4 0 0,1 12,8Z"/>
                        </svg>
                        Tech Blog
                    </button>
                    <button class="tab-button" data-tab="reports">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                        </svg>
                        Lab Reports
                    </button>
                </div>
                
                <div class="tab-content">
                    <div class="tab-pane active" id="blog">
                        <div class="content-grid">
                            @forelse($latestBlogPosts as $post)
                                <div class="content-card blog-card" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                                    @if($post['image'])
                                        <div class="card-image">
                                            <img src="{{ $post['image'] }}" alt="{{ $post['title'] }}">
                                        </div>
                                    @endif
                                    <div class="card-content">
                                        <div class="card-meta">
                                            <span class="card-category">{{ $post['category'] }}</span>
                                            <span class="card-date">{{ date('m-d', $post['published_at']) }}</span>
                                        </div>
                                        <h3 class="card-title">
                                            <a href="{{ route('blog.show', $post['slug']) }}">{{ $post['title'] }}</a>
                                        </h3>
                                        <p class="card-excerpt">{{ $post['excerpt'] }}</p>
                                        <div class="card-footer">
                                            <div class="reading-time">{{ $post['reading_time'] }} min read</div>
                                            <a href="{{ route('blog.show', $post['slug']) }}" class="read-more">Read More →</a>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-state">
                                    <p>No blog articles</p>
                                </div>
                            @endforelse
                        </div>
                        
                        @if(count($latestBlogPosts) > 0)
                            <div class="section-footer">
                                <a href="{{ route('blog.index') }}" class="btn btn-outline">
                                    View All Articles
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z"/>
                                    </svg>
                                </a>
                            </div>
                        @endif
                    </div>
                    
                    <div class="tab-pane" id="reports">
                        <div class="content-grid">
                            @forelse($latestReports as $report)
                                <div class="content-card report-card" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                                    <div class="card-content">
                                        <div class="card-meta">
                                            @if(isset($report['type']) && $report['type'] === 'hackthebox')
                                                <span class="card-category hackthebox">HackTheBox</span>
                                            @else
                                                <span class="card-category">Report</span>
                                            @endif
                                            <span class="card-date">{{ date('m-d', $report['mtime']) }}</span>
                                        </div>
                                        <h3 class="card-title">
                                            <a href="{{ route('reports.show', $report['slug']) }}">{{ $report['title'] }}</a>
                                        </h3>
                                        <p class="card-excerpt">{{ $report['excerpt'] }}</p>
                                        <div class="card-footer">
                                            <div class="file-size">{{ number_format($report['size'] / 1024, 1) }} KB</div>
                                            <a href="{{ route('reports.show', $report['slug']) }}" class="read-more">View Report →</a>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-state">
                                    <p>No reports</p>
                                </div>
                            @endforelse
                        </div>
                        
                        @if(count($latestReports) > 0)
                            <div class="section-footer">
                                <a href="{{ route('reports.index') }}" class="btn btn-outline">
                                    View All Reports
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z"/>
                                    </svg>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 技术栈 -->
    <section class="tech-stack-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Tech Stack</h2>
                <p class="section-subtitle">Technologies and tools I'm familiar with</p>
            </div>
            
            <div class="tech-categories">
                <div class="tech-category" data-aos="fade-up" data-aos-delay="0">
                    <h3 class="category-title">Programming Languages</h3>
                    <div class="tech-skills">
                        @foreach($techStack['languages'] as $skill)
                            <div class="skill-item">
                                <div class="skill-header">
                                    <span class="skill-name">{{ $skill['name'] }}</span>
                                    <span class="skill-level">{{ $skill['level'] }}%</span>
                                </div>
                                <div class="skill-bar">
                                    <div class="skill-progress" 
                                         data-level="{{ $skill['level'] }}" 
                                         data-color="{{ $skill['color'] }}"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                
                <div class="tech-category" data-aos="fade-up" data-aos-delay="200">
                    <h3 class="category-title">Frameworks</h3>
                    <div class="tech-skills">
                        @foreach($techStack['frameworks'] as $skill)
                            <div class="skill-item">
                                <div class="skill-header">
                                    <span class="skill-name">{{ $skill['name'] }}</span>
                                    <span class="skill-level">{{ $skill['level'] }}%</span>
                                </div>
                                <div class="skill-bar">
                                    <div class="skill-progress" 
                                         data-level="{{ $skill['level'] }}" 
                                         data-color="{{ $skill['color'] }}"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                
                <div class="tech-category" data-aos="fade-up" data-aos-delay="400">
                    <h3 class="category-title">Security Tools</h3>
                    <div class="tech-skills">
                        @foreach($techStack['tools'] as $skill)
                            <div class="skill-item">
                                <div class="skill-header">
                                    <span class="skill-name">{{ $skill['name'] }}</span>
                                    <span class="skill-level">{{ $skill['level'] }}%</span>
                                </div>
                                <div class="skill-bar">
                                    <div class="skill-progress" 
                                         data-level="{{ $skill['level'] }}" 
                                         data-color="{{ $skill['color'] }}"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 最近活动 -->
    <section class="activity-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Recent Activity</h2>
                <p class="section-subtitle">My learning and creation dynamics</p>
            </div>
            
            <div class="activity-timeline">
                @foreach($recentActivities as $activity)
                    <div class="activity-item" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                        <div class="activity-icon">
                            @if($activity['type'] === 'blog')
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4M12,6A6,6 0 0,0 6,12A6,6 0 0,0 12,18A6,6 0 0,0 18,12A6,6 0 0,0 12,6M12,8A4,4 0 0,1 16,12A4,4 0 0,1 12,16A4,4 0 0,1 8,12A4,4 0 0,1 12,8Z"/>
                                </svg>
                            @else
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                                </svg>
                            @endif
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">{{ $activity['action'] }} {{ $activity['type'] === 'blog' ? 'blog' : 'report' }} "{{ $activity['name'] }}"</div>
                            <div class="activity-time">{{ $activity['time_ago'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- 联系我 -->
    <section class="contact-section">
        <div class="container">
            <div class="contact-content">
                <div class="contact-info">
                    <h2 class="section-title">Contact Me</h2>
                    <p class="section-subtitle">If you have any questions or want to collaborate, feel free to contact me</p>
                    
                    <div class="contact-links">
                        <a href="mailto:wither2rebirth@gmail.com" class="contact-link">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M20,8L12,13L4,8V6L12,11L20,6M20,4H4C2.89,4 2,4.89 2,6V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V6C22,4.89 21.1,4 20,4Z"/>
                            </svg>
                            <span>wither2rebirth@gmail.com</span>
                        </a>
                        <a href="https://github.com/wither-rebirth" class="contact-link">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12,2A10,10 0 0,0 2,12C2,16.42 4.87,20.17 8.84,21.5C9.34,21.58 9.5,21.27 9.5,21C9.5,20.77 9.5,20.14 9.5,19.31C6.73,19.91 6.14,17.97 6.14,17.97C5.68,16.81 5.03,16.5 5.03,16.5C4.12,15.88 5.1,15.9 5.1,15.9C6.1,15.97 6.63,16.93 6.63,16.93C7.5,18.45 8.97,18 9.54,17.76C9.63,17.11 9.89,16.67 10.17,16.42C7.95,16.17 5.62,15.31 5.62,11.5C5.62,10.39 6,9.5 6.65,8.79C6.55,8.54 6.2,7.5 6.75,6.15C6.75,6.15 7.59,5.88 9.5,7.17C10.29,6.95 11.15,6.84 12,6.84C12.85,6.84 13.71,6.95 14.5,7.17C16.41,5.88 17.25,6.15 17.25,6.15C17.8,7.5 17.45,8.54 17.35,8.79C18,9.5 18.38,10.39 18.38,11.5C18.38,15.32 16.04,16.16 13.81,16.41C14.17,16.72 14.5,17.33 14.5,18.26C14.5,19.6 14.5,20.68 14.5,21C14.5,21.27 14.66,21.59 15.17,21.5C19.14,20.16 22,16.42 22,12A10,10 0 0,0 12,2Z"/>
                            </svg>
                            <span>GitHub</span>
                        </a>
                        <a href="{{ route('aboutme.index') }}" class="contact-link">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12,4A4,4 0 0,1 16,8A4,4 0 0,1 12,12A4,4 0 0,1 8,8A4,4 0 0,1 12,4M12,14C16.42,14 20,15.79 20,18V20H4V18C4,15.79 7.58,14 12,14Z"/>
                            </svg>
                            <span>Learn More</span>
                        </a>
                    </div>
                </div>
                
                <div class="contact-visual">
                    <div class="code-editor">
                        <div class="editor-header">
                            <div class="editor-buttons">
                                <span class="editor-btn close"></span>
                                <span class="editor-btn minimize"></span>
                                <span class="editor-btn maximize"></span>
                            </div>
                            <div class="editor-title">contact.js</div>
                        </div>
                        <div class="editor-content">
                            <pre><code><span class="comment">// Contact Me</span>
<span class="keyword">const</span> <span class="variable">contact</span> = {
    <span class="property">name</span>: <span class="string">'wither'</span>,
    <span class="property">email</span>: <span class="string">'wither2rebirth@gmail.com'</span>,
    <span class="property">skills</span>: [<span class="string">'Cybersecurity'</span>, <span class="string">'Penetration Testing'</span>],
    <span class="property">motto</span>: <span class="string">'from wither to rebirth'</span>
};</code></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@push('scripts')
    @vite(['resources/js/home.js'])
@endpush
@endsection 