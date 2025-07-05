@extends('layout')

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
                    <img src="https://via.placeholder.com/120x120/3b82f6/ffffff?text=W" alt="wither" class="avatar-img">
                </div>
                <div class="profile-info">
                    <h1 class="profile-name">{{ $personalInfo['name'] }}</h1>
                    <p class="profile-title">{{ $personalInfo['title'] }}</p>
                    <p class="profile-location">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4M12,6A6,6 0 0,0 6,12A6,6 0 0,0 12,18A6,6 0 0,0 18,12A6,6 0 0,0 12,6M12,8A4,4 0 0,1 16,12A4,4 0 0,1 12,16A4,4 0 0,1 8,12A4,4 0 0,1 12,8Z"/>
                        </svg>
                        {{ $personalInfo['location'] }}
                    </p>
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
                            @case('twitter')
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M22.46,6C21.69,6.35 20.86,6.58 20,6.69C20.88,6.16 21.56,5.32 21.88,4.31C21.05,4.81 20.13,5.16 19.16,5.36C18.37,4.5 17.26,4 16,4C13.65,4 11.73,5.92 11.73,8.29C11.73,8.63 11.77,8.96 11.84,9.27C8.28,9.09 5.11,7.38 3,4.79C2.63,5.42 2.42,6.16 2.42,6.94C2.42,8.43 3.17,9.75 4.33,10.5C3.62,10.5 2.96,10.3 2.38,10C2.38,10 2.38,10 2.38,10.05C2.38,12.15 3.86,13.85 5.82,14.24C5.46,14.34 5.08,14.39 4.69,14.39C4.42,14.39 4.15,14.36 3.89,14.31C4.43,16 6,17.26 7.89,17.29C6.43,18.45 4.58,19.13 2.56,19.13C2.22,19.13 1.88,19.11 1.54,19.07C3.44,20.29 5.7,21 8.12,21C16,21 20.33,14.46 20.33,8.79C20.33,8.6 20.33,8.42 20.32,8.23C21.16,7.63 21.88,6.87 22.46,6Z"/>
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

        <!-- 技能部分 -->
        <div class="skills-section">
            <h2 class="section-title">技能专长</h2>
            <div class="skills-grid">
                @foreach($personalInfo['skills'] as $category => $skills)
                    <div class="skill-category">
                        <h3 class="skill-category-title">{{ $category }}</h3>
                        <div class="skill-tags">
                            @foreach($skills as $skill)
                                <span class="skill-tag">{{ $skill }}</span>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- 成就部分 -->
        <div class="achievements-section">
            <h2 class="section-title">主要成就</h2>
            <div class="achievements-grid">
                @foreach($personalInfo['achievements'] as $achievement)
                    <div class="achievement-card">
                        <div class="achievement-year">{{ $achievement['year'] }}</div>
                        <div class="achievement-content">
                            <h3 class="achievement-title">{{ $achievement['title'] }}</h3>
                            <p class="achievement-description">{{ $achievement['description'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
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
                <div class="contact-item">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4M12,6A6,6 0 0,0 6,12A6,6 0 0,0 12,18A6,6 0 0,0 18,12A6,6 0 0,0 12,6M12,8A4,4 0 0,1 16,12A4,4 0 0,1 12,16A4,4 0 0,1 8,12A4,4 0 0,1 12,8Z"/>
                    </svg>
                    <span>{{ $personalInfo['location'] }}</span>
                </div>
            </div>
            <div class="contact-message">
                <p>如果您有任何问题或想要合作，欢迎随时与我联系！</p>
            </div>
        </div>
    </div>
</div>
@endsection 