<?php

use App\Livewire\Auth\LoginPage;
use App\Livewire\Dashboard\DashboardPage;
use App\Livewire\Import\ImportPage;
use App\Livewire\Library\QuestionBrowser;
use App\Livewire\Quiz\QuizSession;
use App\Livewire\Settings\SettingsPage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/login', LoginPage::class)->middleware('guest')->name('login');

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', DashboardPage::class)->name('dashboard');
    Route::get('/quiz', QuizSession::class)->name('quiz');
    Route::get('/import', ImportPage::class)->name('import');
    Route::get('/library', QuestionBrowser::class)->name('library');
    Route::get('/settings', SettingsPage::class)->name('settings');
});
