<?php
use App\Http\Controllers\JournalController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('gateway'); // Halaman depan yang sebelumnya
});

// Grouping Journal Routes
Route::prefix('journal')->group(function () {
    Route::get('/', [JournalController::class, 'index'])->name('journal.index');
    Route::get('/create', [JournalController::class, 'create'])->name('journal.create');
    Route::post('/store', [JournalController::class, 'store'])->name('journal.store');
    Route::get('/gallery', [JournalController::class, 'gallery'])->name('journal.gallery');
    Route::get('/mood', [JournalController::class, 'mood'])->name('journal.mood');
    Route::get('/search', [JournalController::class, 'search'])->name('journal.search');
});