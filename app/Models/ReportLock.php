<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportLock extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'label',
        'title',
        'password',
        'description',
        'is_enabled',
        'locked_at'
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'locked_at' => 'datetime',
    ];

    /**
     * 检查密码是否正确
     */
    public function verifyPassword(string $inputPassword): bool
    {
        // 直接比较原始密码，不进行任何转义或编码
        return $this->password === $inputPassword;
    }

    /**
     * 检查该报告是否需要密码保护
     */
    public static function isLocked(string $slug): bool
    {
        return self::where('slug', $slug)
            ->where('is_enabled', true)
            ->exists();
    }

    /**
     * 获取报告的锁定信息
     */
    public static function getLockInfo(string $slug): ?self
    {
        return self::where('slug', $slug)
            ->where('is_enabled', true)
            ->first();
    }

    /**
     * 根据label分组获取锁定报告
     */
    public static function getByLabel(string $label = null)
    {
        $query = self::query();
        
        if ($label) {
            $query->where('label', $label);
        }
        
        return $query->orderBy('locked_at', 'desc')->get();
    }

    /**
     * 获取所有可用的标签
     */
    public static function getLabels(): array
    {
        return self::distinct('label')
            ->orderBy('label')
            ->pluck('label')
            ->toArray();
    }
}
