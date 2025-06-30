<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use League\CommonMark\CommonMarkConverter;

class ReportController extends Controller
{
    public function index()
    {
        $files = File::files(storage_path('reports'));
        $reports = collect($files)->map(function ($f) {
            return [
                'slug'  => $slug = $f->getFilenameWithoutExtension(),
                'title' => $this->title($f),
                'mtime' => $f->getMTime(),
            ];
        })->sortByDesc('mtime');

        return view('index', compact('reports'));
    }

    public function show(string $slug)
    {
        $path = storage_path("reports/{$slug}.md");
        abort_unless(is_file($path), 404);

        $html = Cache::remember("report:$slug", 60, function () use ($path) {
            $md = File::get($path);
            return (new CommonMarkConverter([
                'allow_unsafe_links' => false,
                'html_input'         => 'strip',
                'attributes'         => ['allow' => ['id','class']],
            ]))->convertToHtml($md);
        });

        return view('report', compact('html', 'slug'));
    }

    private function title(\SplFileInfo $file): string
    {
        $first = fgets($h = fopen($file->getPathname(), 'r')); fclose($h);
        return trim(ltrim($first, "#"));
    }
}

