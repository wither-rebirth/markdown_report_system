<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PageVisit extends Model
{
    protected $fillable = [
        'url',
        'ip_address',
        'user_agent',
        'referer',
        'session_id',
        'user_id',
        'country',
        'city',
        'device_type',
        'browser',
        'os',
        'visited_at',
    ];

    protected $casts = [
        'visited_at' => 'datetime',
    ];

    /**
     * 关联用户
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 作用域：今日访问
     */
    public function scopeToday($query)
    {
        return $query->whereDate('visited_at', today());
    }

    /**
     * 作用域：昨日访问
     */
    public function scopeYesterday($query)
    {
        return $query->whereDate('visited_at', now()->subDay());
    }

    /**
     * 作用域：本周访问
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('visited_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    /**
     * 作用域：本月访问
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('visited_at', now()->month)
                    ->whereYear('visited_at', now()->year);
    }

    /**
     * 作用域：按日期范围
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('visited_at', [$startDate, $endDate]);
    }

    /**
     * 作用域：按URL
     */
    public function scopeByUrl($query, $url)
    {
        return $query->where('url', $url);
    }

    /**
     * 作用域：唯一访客（按IP去重）
     */
    public function scopeUniqueVisitors($query)
    {
        return $query->distinct('ip_address');
    }

    /**
     * 作用域：唯一会话（按session_id去重）
     */
    public function scopeUniqueSessions($query)
    {
        return $query->distinct('session_id');
    }

    /**
     * 获取访问设备类型
     */
    public function getDeviceTypeAttribute($value)
    {
        if ($value) {
            return $value;
        }

        // 简单的设备类型检测
        $userAgent = $this->user_agent;
        if (preg_match('/Mobile|Android|iPhone|iPad/', $userAgent)) {
            return 'Mobile';
        } elseif (preg_match('/Tablet|iPad/', $userAgent)) {
            return 'Tablet';
        } else {
            return 'Desktop';
        }
    }

    /**
     * 获取浏览器信息
     */
    public function getBrowserAttribute($value)
    {
        if ($value) {
            return $value;
        }

        $userAgent = $this->user_agent;
        if (preg_match('/Chrome\/([0-9\.]+)/', $userAgent, $matches)) {
            return 'Chrome ' . $matches[1];
        } elseif (preg_match('/Firefox\/([0-9\.]+)/', $userAgent, $matches)) {
            return 'Firefox ' . $matches[1];
        } elseif (preg_match('/Safari\/([0-9\.]+)/', $userAgent, $matches)) {
            return 'Safari ' . $matches[1];
        } elseif (preg_match('/Edge\/([0-9\.]+)/', $userAgent, $matches)) {
            return 'Edge ' . $matches[1];
        } else {
            return 'Unknown';
        }
    }

    /**
     * 获取操作系统信息
     */
    public function getOsAttribute($value)
    {
        if ($value) {
            return $value;
        }

        $userAgent = $this->user_agent;
        if (preg_match('/Windows NT ([0-9\.]+)/', $userAgent, $matches)) {
            return 'Windows ' . $matches[1];
        } elseif (preg_match('/Mac OS X ([0-9_\.]+)/', $userAgent, $matches)) {
            return 'macOS ' . str_replace('_', '.', $matches[1]);
        } elseif (preg_match('/Linux/', $userAgent)) {
            return 'Linux';
        } elseif (preg_match('/Android ([0-9\.]+)/', $userAgent, $matches)) {
            return 'Android ' . $matches[1];
        } elseif (preg_match('/iPhone OS ([0-9_\.]+)/', $userAgent, $matches)) {
            return 'iOS ' . str_replace('_', '.', $matches[1]);
        } else {
            return 'Unknown';
        }
    }

    /**
     * 检查是否为新访客
     */
    public function isNewVisitor(): bool
    {
        return !static::where('ip_address', $this->ip_address)
                     ->where('visited_at', '<', $this->visited_at)
                     ->exists();
    }

    /**
     * 获取统计数据
     */
    public static function getStats($startDate = null, $endDate = null)
    {
        $query = static::query();
        
        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }

        return [
            'total_visits' => $query->count(),
            'unique_visitors' => $query->distinct('ip_address')->count('ip_address'),
            'unique_sessions' => $query->distinct('session_id')->count('session_id'),
            'new_visitors' => $query->get()->filter(function ($visit) {
                return $visit->isNewVisitor();
            })->count(),
        ];
    }
}
