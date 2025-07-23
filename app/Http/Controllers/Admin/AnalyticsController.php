<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PageVisit;
use App\Models\DailyStat;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AnalyticsController extends Controller
{
    /**
     * 显示数据分析主页
     */
    public function index(Request $request)
    {
        $period = $request->input('period', '7days');
        
        try {
            // 获取基础统计数据
            $basicStats = $this->getBasicStats($period);
            
            // 获取趋势数据
            $trendData = $this->getTrendData($period);
            
            // 获取热门页面
            $topPages = $this->getTopPages($period);
            
            // 获取设备统计
            $deviceStats = $this->getDeviceStats($period);
            
            // 获取浏览器统计
            $browserStats = $this->getBrowserStats($period);
            
        } catch (\Exception $e) {
            // 如果发生错误，使用默认值
            Log::error('Analytics data error: ' . $e->getMessage());
            
            $basicStats = [
                'total_pv' => 0,
                'total_uv' => 0,
                'total_sessions' => 0,
                'new_visitors' => 0,
                'bounce_rate' => 0,
                'avg_pages_per_session' => 0,
                'previous' => []
            ];
            $trendData = [];
            $topPages = collect([]);
            $deviceStats = collect([]);
            $browserStats = collect([]);
        }
        
        return view('admin.analytics.index', compact(
            'basicStats',
            'trendData',
            'topPages',
            'deviceStats',
            'browserStats',
            'period'
        ));
    }

    /**
     * 实时统计页面
     */
    public function realtime()
    {
        // 获取最近24小时的访问数据
        $realtimeData = $this->getRealtimeData();
        
        // 获取在线用户数（最近5分钟的活跃会话）
        $onlineUsers = $this->getOnlineUsers();
        
        // 获取最新访问记录
        $latestVisits = $this->getLatestVisits();
        
        return view('admin.analytics.realtime', compact(
            'realtimeData',
            'onlineUsers',
            'latestVisits'
        ));
    }

    /**
     * 页面详情分析
     */
    public function pages(Request $request)
    {
        $period = $request->input('period', '7days');
        $search = $request->input('search');
        
        $query = PageVisit::query();
        
        // 应用时间过滤
        $this->applyPeriodFilter($query, $period);
        
        // 应用搜索过滤
        if ($search) {
            $query->where('url', 'like', "%{$search}%");
        }
        
        // 按页面分组统计
        $pages = $query->select('url')
            ->selectRaw('COUNT(*) as pv')
            ->selectRaw('COUNT(DISTINCT ip_address) as uv')
            ->selectRaw('COUNT(DISTINCT session_id) as sessions')
            ->selectRaw('AVG(CASE WHEN session_id IN (
                SELECT session_id FROM page_visits pv2 
                WHERE pv2.session_id = page_visits.session_id 
                GROUP BY session_id HAVING COUNT(*) = 1
            ) THEN 100 ELSE 0 END) as bounce_rate')
            ->groupBy('url')
            ->orderBy('pv', 'desc')
            ->paginate(50);
            
        $pages->appends($request->query());
        
        return view('admin.analytics.pages', compact('pages', 'period', 'search'));
    }

    /**
     * 导出数据
     */
    public function export(Request $request)
    {
        $type = $request->input('type', 'visits');
        $period = $request->input('period', '7days');
        $format = $request->input('format', 'csv');
        
        switch ($type) {
            case 'visits':
                return $this->exportVisits($period, $format);
            case 'daily_stats':
                return $this->exportDailyStats($period, $format);
            case 'pages':
                return $this->exportPages($period, $format);
            default:
                return back()->withErrors(['type' => '无效的导出类型']);
        }
    }

    /**
     * 生成每日统计数据
     */
    public function generateDailyStats(Request $request)
    {
        $date = $request->input('date', today()->toDateString());
        
        try {
            $stat = DailyStat::generateForDate(Carbon::parse($date));
            
            if ($stat) {
                return response()->json([
                    'success' => true,
                    'message' => "已生成 {$date} 的统计数据",
                    'data' => $stat
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "该日期没有访问数据"
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '生成统计数据失败: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * 获取基础统计数据
     */
    private function getBasicStats($period)
    {
        $query = PageVisit::query();
        $this->applyPeriodFilter($query, $period);
        
        $totalPv = $query->count();
        $totalUv = $query->distinct('ip_address')->count('ip_address');
        $totalSessions = $query->distinct('session_id')->count('session_id');
        
        // 计算新访客数
        $newVisitors = $query->get()->filter(function ($visit) {
            return $visit->isNewVisitor();
        })->count();
        
        // 计算跳出率
        $sessions = $query->select('session_id')
            ->selectRaw('COUNT(*) as page_count')
            ->groupBy('session_id')
            ->get();
            
        $bounceSessions = $sessions->where('page_count', 1)->count();
        $bounceRate = $totalSessions > 0 ? round(($bounceSessions / $totalSessions) * 100, 2) : 0;
        
        // 获取上一个周期的数据进行对比
        $previousStats = $this->getPreviousStats($period);
        
        return [
            'total_pv' => $totalPv,
            'total_uv' => $totalUv,
            'total_sessions' => $totalSessions,
            'new_visitors' => $newVisitors,
            'bounce_rate' => $bounceRate,
            'avg_pages_per_session' => $totalSessions > 0 ? round($totalPv / $totalSessions, 2) : 0,
            'previous' => $previousStats,
        ];
    }

    /**
     * 获取趋势数据
     */
    private function getTrendData($period)
    {
        $days = $this->getPeriodDays($period);
        $data = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            
            $dayStats = PageVisit::whereDate('visited_at', $date)
                ->selectRaw('COUNT(*) as pv')
                ->selectRaw('COUNT(DISTINCT ip_address) as uv')
                ->selectRaw('COUNT(DISTINCT session_id) as sessions')
                ->first();
            
            $data[] = [
                'date' => $date,
                'pv' => (int)($dayStats->pv ?? 0),
                'uv' => (int)($dayStats->uv ?? 0),
                'sessions' => (int)($dayStats->sessions ?? 0),
            ];
        }
        
        // 确保返回的数据格式正确
        return array_values($data);
    }

    /**
     * 获取热门页面
     */
    private function getTopPages($period, $limit = 10)
    {
        $query = PageVisit::query();
        $this->applyPeriodFilter($query, $period);
        
        return $query->select('url')
            ->selectRaw('COUNT(*) as pv')
            ->selectRaw('COUNT(DISTINCT ip_address) as uv')
            ->groupBy('url')
            ->orderBy('pv', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * 获取设备统计
     */
    private function getDeviceStats($period)
    {
        $query = PageVisit::query();
        $this->applyPeriodFilter($query, $period);
        
        return $query->select('device_type')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('COUNT(DISTINCT ip_address) as unique_users')
            ->groupBy('device_type')
            ->orderBy('count', 'desc')
            ->get();
    }

    /**
     * 获取浏览器统计
     */
    private function getBrowserStats($period)
    {
        $query = PageVisit::query();
        $this->applyPeriodFilter($query, $period);
        
        return $query->select('browser')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('COUNT(DISTINCT ip_address) as unique_users')
            ->groupBy('browser')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();
    }



    /**
     * 获取实时数据
     */
    private function getRealtimeData()
    {
        $hours = [];
        
        for ($i = 23; $i >= 0; $i--) {
            $hour = now()->subHours($i);
            $startTime = $hour->copy()->startOfHour();
            $endTime = $hour->copy()->endOfHour();
            
            $stats = PageVisit::whereBetween('visited_at', [$startTime, $endTime])
                ->selectRaw('COUNT(*) as pv')
                ->selectRaw('COUNT(DISTINCT ip_address) as uv')
                ->first();
            
            $hours[] = [
                'hour' => $hour->format('H:00'),
                'pv' => $stats->pv ?? 0,
                'uv' => $stats->uv ?? 0,
            ];
        }
        
        // 计算24小时总数
        $total24h = PageVisit::where('visited_at', '>=', now()->subHours(24))->count();
        
        // 计算最近1分钟的访问量
        $currentPpm = PageVisit::where('visited_at', '>=', now()->subMinute())->count();
        
        // 计算最近1小时的访问量
        $currentPph = PageVisit::where('visited_at', '>=', now()->subHour())->count();
        
        return [
            'hourly_trend' => $hours,
            'total_24h' => $total24h,
            'current_ppm' => $currentPpm,
            'current_pph' => $currentPph,
        ];
    }

    /**
     * 获取在线用户数
     */
    private function getOnlineUsers()
    {
        return PageVisit::where('visited_at', '>=', now()->subMinutes(5))
            ->distinct('session_id')
            ->count('session_id');
    }

    /**
     * 获取最新访问记录
     */
    private function getLatestVisits($limit = 20)
    {
        return PageVisit::with('user')
            ->latest('visited_at')
            ->limit($limit)
            ->get();
    }

    /**
     * 应用时间过滤
     */
    private function applyPeriodFilter($query, $period)
    {
        switch ($period) {
            case 'today':
                $query->whereDate('visited_at', today());
                break;
            case 'yesterday':
                $query->whereDate('visited_at', now()->subDay());
                break;
            case '7days':
                $query->where('visited_at', '>=', now()->subDays(7));
                break;
            case '30days':
                $query->where('visited_at', '>=', now()->subDays(30));
                break;
            case '90days':
                $query->where('visited_at', '>=', now()->subDays(90));
                break;
        }
    }

    /**
     * 获取时间周期的天数
     */
    private function getPeriodDays($period)
    {
        switch ($period) {
            case 'today':
            case 'yesterday':
                return 1;
            case '7days':
                return 7;
            case '30days':
                return 30;
            case '90days':
                return 90;
            default:
                return 7;
        }
    }

    /**
     * 获取上一个周期的统计数据
     */
    private function getPreviousStats($period)
    {
        $query = PageVisit::query();
        
        // 根据周期设置上一个周期的时间范围
        switch ($period) {
            case 'today':
                $query->whereDate('visited_at', now()->subDay());
                break;
            case 'yesterday':
                $query->whereDate('visited_at', now()->subDays(2));
                break;
            case '7days':
                $query->whereBetween('visited_at', [
                    now()->subDays(14),
                    now()->subDays(7)
                ]);
                break;
            case '30days':
                $query->whereBetween('visited_at', [
                    now()->subDays(60),
                    now()->subDays(30)
                ]);
                break;
        }
        
        $totalPv = $query->count();
        $totalUv = $query->distinct('ip_address')->count('ip_address');
        
        return [
            'total_pv' => $totalPv,
            'total_uv' => $totalUv,
        ];
    }

    // 导出相关方法可以在后续实现
    private function exportVisits($period, $format) { /* TODO */ }
    private function exportDailyStats($period, $format) { /* TODO */ }
    private function exportPages($period, $format) { /* TODO */ }
}
