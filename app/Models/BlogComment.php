<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BlogComment extends Model
{
    protected $table = 'blog_comments';
    
    protected $fillable = [
        'blog_slug',
        'author_name',
        'content',
        'ip_address',
        'user_agent',
        'is_approved'
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // 作用域：只查询已审核的评论
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    // 作用域：根据blog slug查询
    public function scopeForBlog($query, $blogSlug)
    {
        return $query->where('blog_slug', $blogSlug);
    }

    // 作用域：按时间排序
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // 格式化创建时间
    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('Y年m月d日 H:i');
    }

    // 获取相对时间
    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    // 清理HTML标签和特殊字符
    public function getCleanContentAttribute()
    {
        return htmlspecialchars(strip_tags($this->content), ENT_QUOTES, 'UTF-8');
    }

    // 生成随机用户名
    public static function generateRandomName()
    {
        $adjectives = ['智慧的', '勇敢的', '神秘的', '优雅的', '聪明的', '机敏的', '风趣的', '幽默的'];
        $nouns = ['访客', '读者', '路人', '学者', '探索者', '思考者', '观察者', '旅行者'];
        
        $adjective = $adjectives[array_rand($adjectives)];
        $noun = $nouns[array_rand($nouns)];
        $number = rand(100, 999);
        
        return $adjective . $noun . $number;
    }
}
