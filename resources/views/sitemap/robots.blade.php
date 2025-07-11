User-agent: *
Allow: /

# 网站地图
Sitemap: {{ route('sitemap.xml') }}

# 禁止访问的目录
Disallow: /admin/
Disallow: /storage/
Disallow: /vendor/
Disallow: /.env
Disallow: /.git/

# 搜索引擎爬虫延迟
Crawl-delay: 1

# 特定搜索引擎规则
User-agent: Googlebot
Allow: /
Crawl-delay: 1

User-agent: Bingbot
Allow: /
Crawl-delay: 1

User-agent: Baiduspider
Allow: /
Crawl-delay: 2 