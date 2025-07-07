<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // 作用域：只查询激活的分类
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // 作用域：按排序顺序
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // 生成URL友好的slug
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        if (empty($this->attributes['slug'])) {
            $this->attributes['slug'] = \Illuminate\Support\Str::slug($value);
        }
    }

    // 获取使用该分类的博客文章数量（需要扫描文件系统）
    public function getBlogCountAttribute()
    {
        // 这里可以通过扫描博客文件来统计使用该分类的文章数量
        return 0; // 暂时返回0，后续实现
    }
}
