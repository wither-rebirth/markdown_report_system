<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LoginAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_address',
        'email',
        'successful',
        'user_agent',
    ];

    protected $casts = [
        'successful' => 'boolean',
    ];

    /**
     * 记录登录尝试
     */
    public static function record(string $ipAddress, ?string $email, bool $successful, ?string $userAgent = null): self
    {
        return self::create([
            'ip_address' => $ipAddress,
            'email' => $email,
            'successful' => $successful,
            'user_agent' => $userAgent,
        ]);
    }

    /**
     * 获取指定IP地址在指定时间内的失败尝试次数
     */
    public static function getFailedAttempts(string $ipAddress, int $minutes = 15): int
    {
        return self::where('ip_address', $ipAddress)
            ->where('successful', false)
            ->where('created_at', '>', Carbon::now()->subMinutes($minutes))
            ->count();
    }

    /**
     * 获取指定邮箱在指定时间内的失败尝试次数
     */
    public static function getFailedAttemptsByEmail(string $email, int $minutes = 15): int
    {
        return self::where('email', $email)
            ->where('successful', false)
            ->where('created_at', '>', Carbon::now()->subMinutes($minutes))
            ->count();
    }

    /**
     * 检查IP是否被锁定
     */
    public static function isIpLocked(string $ipAddress, int $maxAttempts = 5, int $lockoutMinutes = 15): bool
    {
        return self::getFailedAttempts($ipAddress, $lockoutMinutes) >= $maxAttempts;
    }

    /**
     * 检查邮箱是否被锁定
     */
    public static function isEmailLocked(string $email, int $maxAttempts = 5, int $lockoutMinutes = 15): bool
    {
        return self::getFailedAttemptsByEmail($email, $lockoutMinutes) >= $maxAttempts;
    }

    /**
     * 获取IP锁定剩余时间（分钟）
     */
    public static function getIpLockoutTime(string $ipAddress, int $maxAttempts = 5, int $lockoutMinutes = 15): int
    {
        $firstAttempt = self::where('ip_address', $ipAddress)
            ->where('successful', false)
            ->where('created_at', '>', Carbon::now()->subMinutes($lockoutMinutes))
            ->orderBy('created_at')
            ->skip($maxAttempts - 1)
            ->first();

        if (!$firstAttempt) {
            return 0;
        }

        $unlockTime = $firstAttempt->created_at->addMinutes($lockoutMinutes);
        $remaining = $unlockTime->diffInMinutes(Carbon::now(), false);

        return max(0, $remaining);
    }

    /**
     * 清理过期的登录尝试记录
     */
    public static function cleanupOldAttempts(int $days = 30): int
    {
        return self::where('created_at', '<', Carbon::now()->subDays($days))->delete();
    }
}
