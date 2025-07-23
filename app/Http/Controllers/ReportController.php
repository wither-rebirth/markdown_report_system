<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use App\Models\ReportLock;

class ReportController extends Controller
{
    private $markdownConverter;
    
    public function __construct()
    {
        // 配置 Markdown 转换器
        $config = [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 5,
        ];
        
        $this->markdownConverter = new CommonMarkConverter($config);
    }
    
    /**
     * Display report list - support pagination and search
     */
    public function index(Request $request)
    {
        $reportsDir = storage_path('reports');
        $hacktheboxDir = storage_path('reports/Hackthebox-Walkthrough');
        
        if (!File::exists($reportsDir)) {
            File::makeDirectory($reportsDir, 0755, true);
        }
        
        // Get search query parameter
        $searchQuery = $request->input('search');
        
        // Use more precise cache key with latest modification time of all related files
        $cacheKey = 'all_reports_' . $this->generateReportsCacheKey($reportsDir, $hacktheboxDir);
        $allReports = Cache::remember($cacheKey, 600, function () use ($reportsDir, $hacktheboxDir) {
            $reports = collect();
            
            // Process traditional single .md files
            $mdFiles = collect(File::glob($reportsDir . '/*.md'))
                ->map(function ($file) {
                    $filename = pathinfo($file, PATHINFO_FILENAME);
                    $content = File::get($file);
                    
                    // Extract title (first # heading or filename)
                    $title = $filename;
                    if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
                        $title = trim($matches[1]);
                    }
                    
                    // Extract excerpt (first paragraph or first 100 characters)
                    $excerpt = $this->extractExcerpt($content);
                    
                    return [
                        'slug' => $filename,
                        'title' => $title,
                        'excerpt' => $excerpt,
                        'content' => $content, // Save full content for search
                        'mtime' => File::lastModified($file),
                        'size' => File::size($file),
                        'status' => 'active',
                        'type' => 'file'
                    ];
                });
            
            // Process Hackthebox-Walkthrough folder
            if (File::exists($hacktheboxDir) && File::isDirectory($hacktheboxDir)) {
                $hacktheboxReports = $this->getHacktheboxReports($hacktheboxDir);
                $reports = $reports->merge($hacktheboxReports);
            }
            
            // Merge and sort
            return $reports->merge($mdFiles)
                ->sortByDesc('mtime')
                ->values()
                ->toArray();
        });
        
        // Apply search filter
        if (!empty($searchQuery)) {
            $allReports = $this->filterReportsBySearch($allReports, $searchQuery);
        }
        
        // Add lock status to reports
        $allReports = $this->addLockStatusToReports($allReports);
        
        // Pagination settings - fixed 10 per page
        $perPage = 10; // Fixed 10 per page
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = array_slice($allReports, ($currentPage - 1) * $perPage, $perPage);
        
        // Create paginator
        $reports = new LengthAwarePaginator(
            $currentItems,
            count($allReports),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'pageName' => 'page',
            ]
        );
        
        // Preserve query parameters
        $reports->appends($request->query());
        
        return view('report.index', compact('reports'));
    }
    
    /**
     * Add lock status information to reports
     */
    private function addLockStatusToReports($reports)
    {
        // Get all report locks in one query for efficiency
        $locks = ReportLock::where('is_enabled', true)->get()->keyBy('slug');
        
        return array_map(function ($report) use ($locks) {
            $lock = $locks->get($report['slug']);
            
            $report['is_locked'] = $lock ? true : false;
            $report['lock_info'] = $lock ? [
                'description' => $lock->description,
                'locked_at' => $lock->locked_at,
                'label' => $lock->label
            ] : null;
            
            return $report;
        }, $reports);
    }
    
    /**
     * Generate reports cache key based on latest modification time of all related files
     */
    private function generateReportsCacheKey($reportsDir, $hacktheboxDir)
    {
        $latestMtime = 0;
        $fileCount = 0;
        
        // Check regular report files
        if (File::exists($reportsDir)) {
            $reportFiles = File::glob($reportsDir . '/*.md');
            foreach ($reportFiles as $file) {
                $latestMtime = max($latestMtime, File::lastModified($file));
                $fileCount++;
            }
        }
        
        // Check Hackthebox report files
        if (File::exists($hacktheboxDir) && File::isDirectory($hacktheboxDir)) {
            $directories = File::directories($hacktheboxDir);
            foreach ($directories as $dir) {
                $walkthroughFile = $dir . '/Walkthrough.md';
                if (File::exists($walkthroughFile)) {
                    $latestMtime = max($latestMtime, File::lastModified($walkthroughFile));
                    $fileCount++;
                }
            }
        }
        
        // Combine cache key: timestamp + file count
        return $latestMtime . '_' . $fileCount;
    }
    
    /**
     * Clear all report-related cache
     */
    public function clearAllReportsCache()
    {
        try {
            // Clear report list cache
            $this->clearReportListCache();
            
            // Clear individual report cache
            $this->clearIndividualReportsCache();
            
            // Clear homepage cache
            Cache::forget('home_stats');
            Cache::forget('latest_reports_3');
            
            return response()->json(['message' => 'All report cache cleared']);
        } catch (\Exception $e) {
            Log::error('Failed to clear report cache: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to clear cache'], 500);
        }
    }
    
    /**
     * Clear report list cache
     */
    private function clearReportListCache()
    {
        // Use Redis keys command to find all related cache keys
        $cacheKeys = Cache::getRedis()->keys('all_reports_*');
        if (!empty($cacheKeys)) {
            Cache::getRedis()->del($cacheKeys);
        }
    }
    
    /**
     * Clear all individual report cache
     */
    private function clearIndividualReportsCache()
    {
        // Clear regular report cache
        $reportKeys = Cache::getRedis()->keys('report.*');
        if (!empty($reportKeys)) {
            Cache::getRedis()->del($reportKeys);
        }
        
        // Clear HackTheBox report cache
        $htbKeys = Cache::getRedis()->keys('htb.report.*');
        if (!empty($htbKeys)) {
            Cache::getRedis()->del($htbKeys);
        }
    }
    
    /**
     * Filter reports by search query
     */
    private function filterReportsBySearch($reports, $searchQuery)
    {
        $searchQuery = mb_strtolower(trim($searchQuery));
        if (empty($searchQuery)) {
            return $reports;
        }
        
        return collect($reports)->filter(function ($report) use ($searchQuery) {
            // Search title
            $title = mb_strtolower($report['title']);
            if (mb_strpos($title, $searchQuery) !== false) {
                return true;
            }
            
            // Search excerpt
            $excerpt = mb_strtolower($report['excerpt'] ?? '');
            if (mb_strpos($excerpt, $searchQuery) !== false) {
                return true;
            }
            
            // Search content (if available)
            $content = mb_strtolower($report['content'] ?? '');
            if (mb_strpos($content, $searchQuery) !== false) {
                return true;
            }
            
            // Search folder name (for Hackthebox reports)
            if (isset($report['folder_name'])) {
                $folderName = mb_strtolower($report['folder_name']);
                if (mb_strpos($folderName, $searchQuery) !== false) {
                    return true;
                }
            }
            
            return false;
        })->values()->toArray();
    }

    /**
     * Get reports from Hackthebox-Walkthrough folder
     */
    private function getHacktheboxReports($hacktheboxDir)
    {
        $reports = collect();
        
        // Read all subfolders
        $directories = File::directories($hacktheboxDir);
        
        foreach ($directories as $dir) {
            $dirName = basename($dir);
            $walkthroughFile = $dir . '/Walkthrough.md';
            $imagesDir = $dir . '/images';
            
            // Check if Walkthrough.md file exists
            if (File::exists($walkthroughFile)) {
                $content = File::get($walkthroughFile);
                $excerpt = $this->extractExcerpt($content);
                $mtime = File::lastModified($walkthroughFile); // Use actual file modification time
                $size = File::size($walkthroughFile);
                
                // Count images
                $imageCount = 0;
                if (File::exists($imagesDir) && File::isDirectory($imagesDir)) {
                    $imageFiles = File::glob($imagesDir . '/*.{jpg,jpeg,png,gif,bmp,webp}', GLOB_BRACE);
                    $imageCount = count($imageFiles);
                }
                
                $reports->push([
                    'slug' => 'htb-' . $dirName,
                    'title' => $dirName,
                    'excerpt' => $excerpt,
                    'content' => $content, // Save full content for search
                    'mtime' => $mtime,
                    'size' => $size,
                    'status' => 'active',
                    'type' => 'hackthebox',
                    'folder_name' => $dirName,
                    'image_count' => $imageCount,
                    'has_images' => $imageCount > 0
                ]);
            }
        }
        
        return $reports;
    }
    
    /**
     * Display single report
     */
    public function show($slug)
    {
        // Check if password protection is required using database
        $needsPassword = ReportLock::isLocked($slug);
        
        if ($needsPassword && !$this->isPasswordVerified($slug)) {
            return $this->showPasswordForm($slug);
        }

        // Check if it's a Hackthebox report
        if (str_starts_with($slug, 'htb-')) {
            return $this->showHacktheboxReport($slug);
        }
        
        $filePath = storage_path("reports/{$slug}.md");
        
        if (!File::exists($filePath)) {
            abort(404, 'Report not found');
        }
        
        // Use cache for performance
        $cacheKey = "report.{$slug}." . File::lastModified($filePath) . '.v2';
        
        $data = Cache::remember($cacheKey, 3600, function () use ($filePath, $slug) {
            $content = File::get($filePath);
            
            // Remove metadata comments
            $content = preg_replace('/<!--.*?-->/s', '', $content);
            
            // Convert Markdown to HTML
            $html = $this->markdownConverter->convert($content);
            
            // Extract title
            $title = $slug;
            if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
                $title = trim($matches[1]);
            }
            
            // Extract SEO data
            $excerpt = $this->extractExcerpt($content);
            $keywords = $this->extractKeywords($content, $title);
            
            return [
                'title' => $title,
                'html' => $html,
                'slug' => $slug,
                'mtime' => File::lastModified($filePath),
                'size' => File::size($filePath),
                'excerpt' => $excerpt,
                'keywords' => $keywords,
                'type' => 'report',
                'full_title' => $title,
                'canonical_url' => route('reports.show', $slug),
            ];
        });
        
        return view('report.show', $data);
    }
    
    /**
     * Display Hackthebox report
     */
    private function showHacktheboxReport($slug)
    {
        // Extract folder name (remove htb- prefix)
        $folderName = substr($slug, 4);
        $reportDir = storage_path("reports/Hackthebox-Walkthrough/{$folderName}");
        $walkthroughFile = $reportDir . '/Walkthrough.md';
        
        if (!File::exists($walkthroughFile)) {
            abort(404, 'Report not found');
        }
        
        // Use cache for performance
        $cacheKey = "htb.report.{$slug}." . File::lastModified($walkthroughFile) . '.v2';
        
        $data = Cache::remember($cacheKey, 3600, function () use ($walkthroughFile, $slug, $folderName, $reportDir) {
            $content = File::get($walkthroughFile);
            
            // Process image links - convert relative paths to accessible URLs
            $content = $this->processHacktheboxImages($content, $folderName);
            
            // Convert Markdown to HTML
            $html = $this->markdownConverter->convert($content);
            
            // Count images
            $imagesDir = $reportDir . '/images';
            $imageCount = 0;
            if (File::exists($imagesDir) && File::isDirectory($imagesDir)) {
                $imageFiles = File::glob($imagesDir . '/*.{jpg,jpeg,png,gif,bmp,webp}', GLOB_BRACE);
                $imageCount = count($imageFiles);
            }
            
            // Extract SEO data
            $excerpt = $this->extractExcerpt($content);
            $keywords = $this->extractKeywords($content, $folderName);
            
            return [
                'title' => $folderName,
                'html' => $html,
                'slug' => $slug,
                'mtime' => File::lastModified($walkthroughFile),
                'size' => File::size($walkthroughFile),
                'type' => 'hackthebox',
                'folder_name' => $folderName,
                'image_count' => $imageCount,
                'excerpt' => $excerpt,
                'keywords' => $keywords,
                'full_title' => $folderName . ' - HackTheBox Writeup',
                'canonical_url' => route('reports.show', $slug),
            ];
        });
        
        return view('report.show', $data);
    }
    
    /**
     * Process image links in Hackthebox reports
     */
    private function processHacktheboxImages($content, $folderName)
    {
        // Process Markdown image syntax ![alt](images/filename.ext)
        $content = preg_replace_callback(
            '/!\[([^\]]*)\]\(images\/([^)]+)\)/',
            function ($matches) use ($folderName) {
                $alt = $matches[1];
                $filename = $matches[2];
                $url = route('reports.htb-image', ['folder' => $folderName, 'filename' => $filename]);
                return "![{$alt}]({$url})";
            },
            $content
        );
        
        // Process HTML img tags <img src="images/filename.ext">
        $content = preg_replace_callback(
            '/<img([^>]*?)src=["\']images\/([^"\']+)["\']([^>]*?)>/i',
            function ($matches) use ($folderName) {
                $before = $matches[1];
                $filename = $matches[2];
                $after = $matches[3];
                $url = route('reports.htb-image', ['folder' => $folderName, 'filename' => $filename]);
                return "<img{$before}src=\"{$url}\"{$after}>";
            },
            $content
        );
        
        return $content;
    }
    
    /**
     * Serve Hackthebox report images
     */
    public function getHacktheboxImage($folder, $filename)
    {
        // URL decode filename
        $decodedFilename = urldecode($filename);
        $imagePath = storage_path("reports/Hackthebox-Walkthrough/{$folder}/images/{$decodedFilename}");
        
        if (!File::exists($imagePath)) {
            abort(404, 'Image not found');
        }
        
        // Check file type
        $mimeType = mime_content_type($imagePath);
        if (!str_starts_with($mimeType, 'image/')) {
            abort(403, 'File type not supported');
        }
        
        return response()->file($imagePath);
    }
    
    /**
     * Delete report
     */
    public function destroy($slug)
    {
        try {
            $filePath = storage_path("reports/{$slug}.md");
            $htmlPath = public_path("{$slug}.html");
            
            if (!File::exists($filePath)) {
                return response()->json(['error' => 'Report not found'], 404);
            }
            
            // Delete markdown file
            File::delete($filePath);
            
            // Delete HTML file (if exists)
            if (File::exists($htmlPath)) {
                File::delete($htmlPath);
            }
            
            // Clear cache
            $this->clearReportCache($slug);
            
            return response()->json(['message' => 'Report deleted successfully']);
            
        } catch (\Exception $e) {
            Log::error('Failed to delete report: ' . $e->getMessage());
            return response()->json(['error' => 'Delete failed'], 500);
        }
    }
    
    /**
     * Batch delete reports
     */
    public function destroyMultiple(Request $request)
    {
        $slugs = $request->input('slugs', []);
        $deleted = [];
        $errors = [];
        
        foreach ($slugs as $slug) {
            try {
                $filePath = storage_path("reports/{$slug}.md");
                if (File::exists($filePath)) {
                    File::delete($filePath);
                    $this->clearReportCache($slug);
                    $deleted[] = $slug;
                }
            } catch (\Exception $e) {
                $errors[] = $slug;
            }
        }
        
        return response()->json([
            'deleted' => $deleted,
            'errors' => $errors,
            'message' => count($deleted) . ' reports deleted successfully'
        ]);
    }
    
    /**
     * Clear report cache
     */
    public function clearCache($slug = null)
    {
        if ($slug) {
            $this->clearReportCache($slug);
            return response()->json(['message' => "Report {$slug} cache cleared"]);
        } else {
            Cache::flush();
            return response()->json(['message' => 'All cache cleared']);
        }
    }
    
    /**
     * Get report statistics
     */
    public function stats()
    {
        $reportsDir = storage_path('reports');
        
        if (!File::exists($reportsDir)) {
            return response()->json([
                'total' => 0,
                'total_size' => 0,
                'latest' => null
            ]);
        }
        
        $files = File::glob($reportsDir . '/*.md');
        $totalSize = array_sum(array_map('filesize', $files));
        $latest = null;
        
        if (!empty($files)) {
            $latestFile = collect($files)->sortByDesc(function ($file) {
                return File::lastModified($file);
            })->first();
            
            $latest = [
                'name' => pathinfo($latestFile, PATHINFO_FILENAME),
                'modified' => File::lastModified($latestFile)
            ];
        }
        
        return response()->json([
            'total' => count($files),
            'total_size' => $this->formatFileSize($totalSize),
            'latest' => $latest
        ]);
    }
    
    // Private helper methods
    
    /**
     * Generate slug from content
     */
    private function generateSlugFromContent($content, $originalName)
    {
        // Try to generate from title
        if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
            $title = trim($matches[1]);
            $slug = $this->slugify($title);
            if (!empty($slug)) {
                return $slug;
            }
        }
        
        // Use original filename
        $filename = pathinfo($originalName, PATHINFO_FILENAME);
        return $this->slugify($filename) ?: 'report-' . time();
    }
    
    /**
     * Generate URL-friendly slug
     */
    private function slugify($text)
    {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s-_]/', '', $text);
        $text = preg_replace('/[\s-_]+/', '-', $text);
        return trim($text, '-');
    }
    
    /**
     * Extract excerpt
     */
    private function extractExcerpt($content)
    {
        // 1. Priority check for description in YAML front matter
        if (preg_match('/^---\s*\n.*?description:\s*[\'"]?(.*?)[\'"]?\s*\n.*?---\s*\n/s', $content, $matches)) {
            $description = trim($matches[1]);
            if (!empty($description)) {
                return $description;
            }
        }
        
        // 2. Check for Description section in markdown
        if (preg_match('/^#{1,6}\s*Description\s*\n(.*?)(?=\n#{1,6}|\n\n|\Z)/sim', $content, $matches)) {
            $description = trim($matches[1]);
            if (!empty($description)) {
                // Remove markdown syntax and limit length
                $description = strip_tags($description);
                $description = preg_replace('/\*\*(.*?)\*\*/', '$1', $description);
                $description = preg_replace('/\*(.*?)\*/', '$1', $description);
                $description = preg_replace('/`(.*?)`/', '$1', $description);
                
                if (mb_strlen($description) > 200) {
                    return mb_substr($description, 0, 200) . '...';
                }
                return $description;
            }
        }
        
        // 3. Check for specific description block (if special format exists)
        if (preg_match('/^description:\s*(.+)$/m', $content, $matches)) {
            $description = trim($matches[1]);
            if (!empty($description)) {
                return $description;
            }
        }
        
        // 4. If no description block found, use original excerpt logic
        $processedContent = $content;
        
        // Remove titles and metadata
        $processedContent = preg_replace('/^#.*$/m', '', $processedContent);
        $processedContent = preg_replace('/<!--.*?-->/s', '', $processedContent);
        $processedContent = preg_replace('/^---\s*\n.*?---\s*\n/s', '', $processedContent);
        $processedContent = trim($processedContent);
        
        // Get first paragraph or first 150 characters
        $paragraphs = explode("\n\n", $processedContent);
        $firstParagraph = trim($paragraphs[0]);
        
        if (mb_strlen($firstParagraph) > 150) {
            return mb_substr($firstParagraph, 0, 150) . '...';
        }
        
        // 5. Final fallback, if nothing found, return null instead of default description
        return !empty($firstParagraph) ? $firstParagraph : null;
    }
    
    /**
     * Extract keywords
     */
    private function extractKeywords($content, $title)
    {
        $keywords = [];
        
        // Basic keywords
        $keywords[] = 'Wither';
        $keywords[] = 'Penetration Testing';
        $keywords[] = 'Cybersecurity';
        $keywords[] = 'Security Research';
        
        // Add keywords based on type
        $lowerContent = mb_strtolower($content);
        $lowerTitle = mb_strtolower($title);
        
        // HackTheBox related
        if (strpos($lowerContent, 'hackthebox') !== false || strpos($lowerTitle, 'hackthebox') !== false) {
            $keywords[] = 'HackTheBox';
            $keywords[] = 'HTB';
            $keywords[] = 'Writeup';
            $keywords[] = 'Walkthrough';
            $keywords[] = 'CTF';
        }
        
        // Common security terms
        $securityTerms = [
            'sql injection' => 'SQL Injection',
            'xss' => 'XSS',
            'csrf' => 'CSRF',
            'lfi' => 'LFI',
            'rfi' => 'RFI',
            'privilege escalation' => 'Privilege Escalation',
            'buffer overflow' => 'Buffer Overflow',
            'reverse shell' => 'Reverse Shell',
            'web shell' => 'Web Shell',
            'enumeration' => 'Enumeration',
            'reconnaissance' => 'Reconnaissance',
            'nmap' => 'Nmap',
            'burp suite' => 'Burp Suite',
            'metasploit' => 'Metasploit',
            'gobuster' => 'Gobuster',
            'dirb' => 'Dirb',
            'nikto' => 'Nikto',
            'sqlmap' => 'SQLMap',
            'hydra' => 'Hydra',
            'john' => 'John the Ripper',
            'hashcat' => 'Hashcat',
            'steganography' => 'Steganography',
            'cryptography' => 'Cryptography',
            'forensics' => 'Digital Forensics',
            'malware' => 'Malware Analysis',
            'reverse engineering' => 'Reverse Engineering',
        ];
        
        foreach ($securityTerms as $term => $keyword) {
            if (strpos($lowerContent, $term) !== false || strpos($lowerTitle, $term) !== false) {
                $keywords[] = $keyword;
            }
        }
        
        // Add keywords from title
        $titleWords = explode(' ', $title);
        foreach ($titleWords as $word) {
            $cleanWord = preg_replace('/[^a-zA-Z0-9\-]/', '', $word);
            if (strlen($cleanWord) > 3) {
                $keywords[] = $cleanWord;
            }
        }
        
        // Remove duplicates and return
        return implode(', ', array_unique($keywords));
    }
    
    /**
     * Format file size
     */
    private function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;
        
        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }
        
        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }
    
    /**
     * Clear specific report cache
     */
    private function clearReportCache($slug)
    {
        $keys = Cache::getRedis()->keys("report.{$slug}.*");
        if (!empty($keys)) {
            Cache::getRedis()->del($keys);
        }
    }
    
    /**
     * Generate HTML preview file
     */
    private function generateHtmlPreview($slug, $content)
    {
        try {
            $html = $this->markdownConverter->convert($content);
            
            $htmlContent = "<!DOCTYPE html>
<html lang=\"en\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>{$slug}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; max-width: 800px; margin: 0 auto; padding: 20px; }
        h1, h2, h3, h4, h5, h6 { color: #333; }
        code { background: #f4f4f4; padding: 2px 4px; border-radius: 3px; }
        pre { background: #f4f4f4; padding: 15px; border-radius: 5px; overflow-x: auto; }
        blockquote { border-left: 4px solid #ddd; margin: 0; padding-left: 20px; color: #666; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    {$html}
</body>
</html>";
            
            File::put(public_path("{$slug}.html"), $htmlContent);
        } catch (\Exception $e) {
            Log::warning("Unable to generate HTML preview: " . $e->getMessage());
        }
    }

    /**
     * Extract modification time from file content
     */
    private function extractModificationTime($content, $filePath)
    {
        $dates = [];
        
        // First look for timestamp format in image filenames (YYYYMMDDHHMMSS)
        if (preg_match_all('/Pasted%20image%20(\d{14})/', $content, $matches)) {
            foreach ($matches[1] as $timestamp) {
                // Parse YYYYMMDDHHMMSS format
                $year = substr($timestamp, 0, 4);
                $month = substr($timestamp, 4, 2);
                $day = substr($timestamp, 6, 2);
                $hour = substr($timestamp, 8, 2);
                $minute = substr($timestamp, 10, 2);
                $second = substr($timestamp, 12, 2);
                
                // Validate date
                if (checkdate($month, $day, $year)) {
                    $dateTime = mktime($hour, $minute, $second, $month, $day, $year);
                    if ($dateTime !== false) {
                        $dates[] = $dateTime;
                    }
                }
            }
        }
        
        // If image timestamps found, return the latest one (most accurate time)
        if (!empty($dates)) {
            return max($dates);
        }
        
        // Alternative: try other date formats
        $patterns = [
            // YYYY-MM-DD format
            '/(\d{4}-\d{1,2}-\d{1,2})/',
            // DD/MM/YYYY or MM/DD/YYYY format
            '/(\d{1,2}\/\d{1,2}\/\d{4})/',
            // Mon May 14 2018 format
            '/([A-Za-z]{3}\s+[A-Za-z]{3}\s+\d{1,2}\s+\d{4})/',
            // Nov 20, 2018 format
            '/([A-Za-z]{3}\s+\d{1,2},?\s+\d{4})/',
            // 2018-11-20 11:57 format
            '/(\d{4}-\d{1,2}-\d{1,2}\s+\d{1,2}:\d{2})/',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $content, $matches)) {
                foreach ($matches[1] as $match) {
                    try {
                        $timestamp = strtotime($match);
                        if ($timestamp !== false && $timestamp > 0) {
                            $dates[] = $timestamp;
                        }
                    } catch (\Exception $e) {
                        // Ignore invalid dates
                    }
                }
            }
        }
        
        // If other format dates found, return the latest date
        if (!empty($dates)) {
            return max($dates);
        }
        
        // Final fallback: extract date information from folder name
        $folderName = basename(dirname($filePath));
        if (preg_match('/(\d{4})[_-]?(\d{1,2})[_-]?(\d{1,2})/', $folderName, $matches)) {
            $timestamp = mktime(0, 0, 0, $matches[2], $matches[3], $matches[1]);
            if ($timestamp !== false) {
                return $timestamp;
            }
        }
        
        // If nothing found, return system modification time
        return File::lastModified($filePath);
    }
    
    /**
     * Generate XML sitemap
     */
    public function sitemap()
    {
        $reports = $this->getAllReportsForSitemap();
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        // Add homepage
        $xml .= '  <url>' . "\n";
        $xml .= '    <loc>' . route('home.index') . '</loc>' . "\n";
        $xml .= '    <lastmod>' . date('Y-m-d\TH:i:s\Z') . '</lastmod>' . "\n";
        $xml .= '    <changefreq>daily</changefreq>' . "\n";
        $xml .= '    <priority>1.0</priority>' . "\n";
        $xml .= '  </url>' . "\n";
        
        // Add report list page
        $xml .= '  <url>' . "\n";
        $xml .= '    <loc>' . route('reports.index') . '</loc>' . "\n";
        $xml .= '    <lastmod>' . date('Y-m-d\TH:i:s\Z') . '</lastmod>' . "\n";
        $xml .= '    <changefreq>weekly</changefreq>' . "\n";
        $xml .= '    <priority>0.9</priority>' . "\n";
        $xml .= '  </url>' . "\n";
        
        // Add blog page
        $xml .= '  <url>' . "\n";
        $xml .= '    <loc>' . route('blog.index') . '</loc>' . "\n";
        $xml .= '    <lastmod>' . date('Y-m-d\TH:i:s\Z') . '</lastmod>' . "\n";
        $xml .= '    <changefreq>weekly</changefreq>' . "\n";
        $xml .= '    <priority>0.9</priority>' . "\n";
        $xml .= '  </url>' . "\n";
        
        // Add about me page
        $xml .= '  <url>' . "\n";
        $xml .= '    <loc>' . route('aboutme.index') . '</loc>' . "\n";
        $xml .= '    <lastmod>' . date('Y-m-d\TH:i:s\Z') . '</lastmod>' . "\n";
        $xml .= '    <changefreq>monthly</changefreq>' . "\n";
        $xml .= '    <priority>0.8</priority>' . "\n";
        $xml .= '  </url>' . "\n";
        
        // Add all reports
        foreach ($reports as $report) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . route('reports.show', $report['slug']) . '</loc>' . "\n";
            $xml .= '    <lastmod>' . date('Y-m-d\TH:i:s\Z', $report['mtime']) . '</lastmod>' . "\n";
            $xml .= '    <changefreq>monthly</changefreq>' . "\n";
            $xml .= '    <priority>0.7</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }
        
        $xml .= '</urlset>';
        
        return response($xml)->header('Content-Type', 'application/xml');
    }
    
    /**
     * Get all reports for sitemap
     */
    private function getAllReportsForSitemap()
    {
        $reportsDir = storage_path('reports');
        $hacktheboxDir = storage_path('reports/Hackthebox-Walkthrough');
        
        if (!File::exists($reportsDir)) {
            return [];
        }
        
        $reports = collect();
        
        // Process traditional single .md files
        $mdFiles = collect(File::glob($reportsDir . '/*.md'))
            ->map(function ($file) {
                $filename = pathinfo($file, PATHINFO_FILENAME);
                return [
                    'slug' => $filename,
                    'mtime' => File::lastModified($file),
                ];
            });
        
        // Process Hackthebox-Walkthrough folder
        if (File::exists($hacktheboxDir) && File::isDirectory($hacktheboxDir)) {
            $directories = File::directories($hacktheboxDir);
            
            foreach ($directories as $dir) {
                $dirName = basename($dir);
                $walkthroughFile = $dir . '/Walkthrough.md';
                
                if (File::exists($walkthroughFile)) {
                    $reports->push([
                        'slug' => 'htb-' . $dirName,
                        'mtime' => File::lastModified($walkthroughFile),
                    ]);
                }
            }
        }
        
        return $reports->merge($mdFiles)->sortByDesc('mtime')->toArray();
    }
    
    /**
     * Check if report requires password protection using database
     */
    private function checkIfPasswordRequired($slug)
    {
        return ReportLock::isLocked($slug);
    }
    
    /**
     * Get report modification time
     */
    private function getReportModificationTime($slug)
    {
        if (str_starts_with($slug, 'htb-')) {
            // HackTheBox report - extract creation time from content
            $folderName = substr($slug, 4);
            $walkthroughFile = storage_path("reports/Hackthebox-Walkthrough/{$folderName}/Walkthrough.md");
            
            if (File::exists($walkthroughFile)) {
                // Use actual file modification time for accuracy
                return File::lastModified($walkthroughFile);
            }
        } else {
            // Regular report - use file modification time
            $filePath = storage_path("reports/{$slug}.md");
            
            if (File::exists($filePath)) {
                return File::lastModified($filePath);
            }
        }
        
        return null;
    }
    
    /**
     * Check if password has been verified for this report
     */
    private function isPasswordVerified($slug)
    {
        return session()->has("report_unlocked_{$slug}");
    }
    
    /**
     * Show password form for protected report
     */
    private function showPasswordForm($slug)
    {
        // Get basic report info for meta tags and content
        $reportInfo = $this->getBasicReportInfo($slug);
        if (!$reportInfo) {
            abort(404, 'Report not found');
        }
        
        // Get lock info from database for additional details
        $lockInfo = ReportLock::getLockInfo($slug);
        
        return view('report.password', [
            'slug' => $slug,
            'title' => $reportInfo['title'],
            'mtime' => $reportInfo['mtime'],
            'excerpt' => $reportInfo['excerpt'] ?? null, // 传递摘要用于SEO，如果不存在则为null
            'description' => null // Don't show description in password hint
        ]);
    }
    
    /**
     * Get basic report information without full content
     */
    private function getBasicReportInfo($slug)
    {
        if (str_starts_with($slug, 'htb-')) {
            // HackTheBox report
            $folderName = substr($slug, 4);
            $walkthroughFile = storage_path("reports/Hackthebox-Walkthrough/{$folderName}/Walkthrough.md");
            
            if (File::exists($walkthroughFile)) {
                $content = File::get($walkthroughFile);
                
                // For HTB reports, format as "Machine - HackTheBox Writeup"
                $title = $folderName . ' - HackTheBox Writeup';
                
                // Extract excerpt from content
                $excerpt = $this->extractExcerpt($content);
                
                return [
                    'title' => $title,
                    'excerpt' => $excerpt,
                    'mtime' => File::lastModified($walkthroughFile),
                    'type' => 'hackthebox'
                ];
            }
        } else {
            // Regular report
            $filePath = storage_path("reports/{$slug}.md");
            
            if (File::exists($filePath)) {
                $content = File::get($filePath);
                
                // For regular reports, also use slug as title for consistency
                $title = $slug;
                
                // Extract excerpt from content
                $excerpt = $this->extractExcerpt($content);
                
                return [
                    'title' => $title,
                    'excerpt' => $excerpt,
                    'mtime' => File::lastModified($filePath),
                    'type' => 'report'
                ];
            }
        }
        
        return null;
    }
    
    /**
     * Verify report password
     */
    public function verifyPassword(Request $request, $slug)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string'
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Get lock info from database
        $lockInfo = ReportLock::getLockInfo($slug);
        
        if (!$lockInfo) {
            return redirect()->back()
                ->withErrors(['password' => 'Report lock configuration not found.'])
                ->withInput();
        }
        
        // Verify password using model method (raw comparison, no escaping)
        if ($lockInfo->verifyPassword($request->password)) {
            // Store in session that this report is unlocked
            session()->put("report_unlocked_{$slug}", true);
            
            return redirect()->route('reports.show', $slug);
        } else {
            return redirect()->back()
                ->withErrors(['password' => 'Incorrect password. Please try again.'])
                ->withInput();
        }
    }
} 