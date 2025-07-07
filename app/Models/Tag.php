<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'color',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // 作用域：只查询激活的标签
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // 作用域：按名称排序
    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }

    // 生成URL友好的slug
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        if (empty($this->attributes['slug'])) {
            $this->attributes['slug'] = \Illuminate\Support\Str::slug($value);
        }
    }

    // 获取使用该标签的博客文章数量（需要扫描文件系统）
    public function getBlogCountAttribute()
    {
        // 这里可以通过扫描博客文件来统计使用该标签的文章数量
        return 0; // 暂时返回0，后续实现
    }

    // 获取标签的显示颜色，如果没有设置则使用默认颜色
    public function getDisplayColorAttribute()
    {
        return $this->color ?: '#6366f1';
    }
}
