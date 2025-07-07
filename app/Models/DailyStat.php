<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DailyStat extends Model
{
    protected $fillable = [
        'date',
        'total_pv',
        'total_uv',
        'new_visitors',
        'returning_visitors',
        'bounce_rate',
        'avg_session_duration',
        'top_pages',
        'top_referers',
        'device_stats',
        'browser_stats',
        'os_stats',
        'country_stats',
    ];

    protected $casts = [
        'date' => 'date',
        'top_pages' => 'array',
        'top_referers' => 'array',
        'device_stats' => 'array',
        'browser_stats' => 'array',
        'os_stats' => 'array',
        'country_stats' => 'array',
    ];

    /**
     * 作用域：按日期范围
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * 作用域：最近N天
     */
    public function scopeLastDays($query, $days = 7)
    {
        return $query->where('date', '>=', now()->subDays($days)->toDateString())
                    ->orderBy('date', 'desc');
    }

    /**
     * 作用域：本月
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('date', now()->month)
                    ->whereYear('date', now()->year);
    }

    /**
     * 作用域：本年
     */
    public function scopeThisYear($query)
    {
        return $query->whereYear('date', now()->year);
    }

    /**
     * 生成或更新指定日期的统计数据
     */
    public static function generateForDate(Carbon $date)
    {
        $dateString = $date->toDateString();
        
        // 获取当日访问数据
        $visits = PageVisit::whereDate('visited_at', $date)->get();
        
        if ($visits->isEmpty()) {
            return null;
        }

        // 计算基础统计
        $totalPv = $visits->count();
        $totalUv = $visits->unique('ip_address')->count();
        $newVisitors = $visits->filter(function ($visit) {
            return $visit->isNewVisitor();
        })->count();
        $returningVisitors = $totalUv - $newVisitors;

        // 计算会话统计
        $sessions = $visits->groupBy('session_id');
        $totalSessionDuration = 0;
        $bounceSessions = 0;

        foreach ($sessions as $sessionVisits) {
            if ($sessionVisits->count() == 1) {
                $bounceSessions++;
            }
            
            $firstVisit = $sessionVisits->sortBy('visited_at')->first();
            $lastVisit = $sessionVisits->sortBy('visited_at')->last();
            $sessionDuration = $lastVisit->visited_at->diffInSeconds($firstVisit->visited_at);
            $totalSessionDuration += $sessionDuration;
        }

        $bounceRate = $sessions->count() > 0 ? round(($bounceSessions / $sessions->count()) * 100) : 0;
        $avgSessionDuration = $sessions->count() > 0 ? round($totalSessionDuration / $sessions->count()) : 0;

        // 计算热门页面
        $topPages = $visits->groupBy('url')
                          ->map(function ($pageVisits) {
                              return $pageVisits->count();
                          })
                          ->sortDesc()
                          ->take(10)
                          ->toArray();

        // 计算热门来源
        $topReferers = $visits->whereNotNull('referer')
                             ->groupBy('referer')
                             ->map(function ($refererVisits) {
                                 return $refererVisits->count();
                             })
                             ->sortDesc()
                             ->take(10)
                             ->toArray();

        // 计算设备统计
        $deviceStats = $visits->groupBy('device_type')
                             ->map(function ($deviceVisits) {
                                 return $deviceVisits->count();
                             })
                             ->toArray();

        // 计算浏览器统计
        $browserStats = $visits->groupBy('browser')
                              ->map(function ($browserVisits) {
                                  return $browserVisits->count();
                              })
                              ->sortDesc()
                              ->take(10)
                              ->toArray();

        // 计算操作系统统计
        $osStats = $visits->groupBy('os')
                         ->map(function ($osVisits) {
                             return $osVisits->count();
                         })
                         ->sortDesc()
                         ->take(10)
                         ->toArray();

        // 计算国家统计
        $countryStats = $visits->whereNotNull('country')
                              ->groupBy('country')
                              ->map(function ($countryVisits) {
                                  return $countryVisits->count();
                              })
                              ->sortDesc()
                              ->take(10)
                              ->toArray();

        // 创建或更新统计记录
        return static::updateOrCreate(
            ['date' => $dateString],
            [
                'total_pv' => $totalPv,
                'total_uv' => $totalUv,
                'new_visitors' => $newVisitors,
                'returning_visitors' => $returningVisitors,
                'bounce_rate' => $bounceRate,
                'avg_session_duration' => $avgSessionDuration,
                'top_pages' => $topPages,
                'top_referers' => $topReferers,
                'device_stats' => $deviceStats,
                'browser_stats' => $browserStats,
                'os_stats' => $osStats,
                'country_stats' => $countryStats,
            ]
        );
    }

    /**
     * 获取趋势数据
     */
    public static function getTrendData($days = 30)
    {
        return static::lastDays($days)
                    ->get()
                    ->reverse()
                    ->values();
    }

    /**
     * 获取汇总统计
     */
    public static function getSummaryStats($startDate = null, $endDate = null)
    {
        $query = static::query();
        
        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        } else {
            // 默认最近30天
            $query->lastDays(30);
        }

        $stats = $query->get();

        return [
            'total_pv' => $stats->sum('total_pv'),
            'total_uv' => $stats->sum('total_uv'),
            'avg_bounce_rate' => $stats->avg('bounce_rate'),
            'avg_session_duration' => $stats->avg('avg_session_duration'),
            'total_new_visitors' => $stats->sum('new_visitors'),
            'total_returning_visitors' => $stats->sum('returning_visitors'),
        ];
    }
}
