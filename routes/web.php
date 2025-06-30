<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;

Route::get('/',           [ReportController::class, 'index']);
Route::get('{slug}.html', [ReportController::class, 'show'])
     ->where('slug', '[A-Za-z0-9\-_]+');

