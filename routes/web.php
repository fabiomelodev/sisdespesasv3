<?php

use App\Http\Controllers\ReportController;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/relatorio', [ReportController::class, 'show'])->name('report.show');