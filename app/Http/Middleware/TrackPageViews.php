<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\PageVisit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TrackPageViews
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // 只统计GET请求且响应成功的页面
        if ($request->isMethod('GET') && $response->isSuccessful()) {
            $this->trackVisit($request);
        }

        return $response;
    }

    /**
     * 记录访问统计
     */
    private function trackVisit(Request $request)
    {
        try {
            // 排除管理端、API、静态资源等不需要统计的路径
            $excludedPaths = [
                'admin/*',
                'api/*',
                'css/*',
                'js/*',
                'images/*',
                'favicon.ico',
                'sitemap.xml',
            ];

            $currentPath = $request->path();
            foreach ($excludedPaths as $pattern) {
                if (fnmatch($pattern, $currentPath)) {
                    return;
                }
            }

            // 获取或生成会话ID
            $sessionId = $request->session()->getId();
            if (!$sessionId) {
                $sessionId = $request->ip() . '_' . time();
            }

            // 获取用户代理信息
            $userAgent = $request->userAgent();

            // 简单的设备类型检测
            $deviceType = $this->getDeviceType($userAgent);

            // 简单的浏览器检测
            $browser = $this->getBrowser($userAgent);

            // 简单的操作系统检测
            $os = $this->getOS($userAgent);

            // 简单的地理位置检测（可以后续集成第三方API）
            $country = $this->getCountryFromIp($request->ip());

            // 创建访问记录
            PageVisit::create([
                'url' => $request->fullUrl(),
                'ip_address' => $request->ip(),
                'user_agent' => $userAgent,
                'referer' => $request->header('referer'),
                'session_id' => $sessionId,
                'user_id' => Auth::id(),
                'country' => $country,
                'city' => null, // 可以后续添加城市检测
                'device_type' => $deviceType,
                'browser' => $browser,
                'os' => $os,
                'visited_at' => now(),
            ]);

        } catch (\Exception $e) {
            // 记录错误但不影响页面正常访问
            Log::error('访问统计记录失败: ' . $e->getMessage());
        }
    }

    /**
     * 根据IP获取国家信息（简单实现）
     */
    private function getCountryFromIp($ip)
    {
        // 这里可以集成GeoIP或其他地理位置服务
        // 目前先返回简单的判断
        
        if ($ip === '127.0.0.1' || $ip === '::1' || strpos($ip, '192.168.') === 0) {
            return 'Local';
        }

        // 可以后续集成 MaxMind GeoIP 或其他服务
        return 'Unknown';
    }

    /**
     * 检测设备类型
     */
    private function getDeviceType($userAgent)
    {
        if (preg_match('/Mobile|Android|iPhone|iPod|BlackBerry|IEMobile|Opera Mini/i', $userAgent)) {
            return 'Mobile';
        } elseif (preg_match('/Tablet|iPad/i', $userAgent)) {
            return 'Tablet';
        } else {
            return 'Desktop';
        }
    }

    /**
     * 检测浏览器
     */
    private function getBrowser($userAgent)
    {
        if (preg_match('/Chrome\/([0-9\.]+)/i', $userAgent, $matches)) {
            return 'Chrome ' . $matches[1];
        } elseif (preg_match('/Firefox\/([0-9\.]+)/i', $userAgent, $matches)) {
            return 'Firefox ' . $matches[1];
        } elseif (preg_match('/Safari\/([0-9\.]+)/i', $userAgent, $matches)) {
            return 'Safari ' . $matches[1];
        } elseif (preg_match('/Edge\/([0-9\.]+)/i', $userAgent, $matches)) {
            return 'Edge ' . $matches[1];
        } elseif (preg_match('/Opera\/([0-9\.]+)/i', $userAgent, $matches)) {
            return 'Opera ' . $matches[1];
        } else {
            return 'Unknown';
        }
    }

    /**
     * 检测操作系统
     */
    private function getOS($userAgent)
    {
        if (preg_match('/Windows NT ([0-9\.]+)/i', $userAgent, $matches)) {
            return 'Windows ' . $matches[1];
        } elseif (preg_match('/Mac OS X ([0-9_\.]+)/i', $userAgent, $matches)) {
            return 'macOS ' . str_replace('_', '.', $matches[1]);
        } elseif (preg_match('/Linux/i', $userAgent)) {
            return 'Linux';
        } elseif (preg_match('/Android ([0-9\.]+)/i', $userAgent, $matches)) {
            return 'Android ' . $matches[1];
        } elseif (preg_match('/iPhone OS ([0-9_\.]+)/i', $userAgent, $matches)) {
            return 'iOS ' . str_replace('_', '.', $matches[1]);
        } else {
            return 'Unknown';
        }
    }
}
