<?php

use App\Platform\Livewire\Auth\LoginPage;
use App\Platform\Livewire\Hub\HubPage;
use App\Platform\Livewire\Settings\SettingsPage;
use App\Tools\Lab\Livewire\ProjectIndex;
use App\Tools\Lab\Livewire\ProjectViewer;
use App\Tools\Quiz\Livewire\ImportPage;
use App\Tools\Quiz\Livewire\QuestionBrowser;
use App\Tools\Quiz\Livewire\QuizDashboard;
use App\Tools\Quiz\Livewire\QuizRunner;
use App\Tools\Quiz\Livewire\QuizSettings;
use App\Tools\Visualizer\Livewire\GuideIndex;
use App\Tools\Visualizer\Livewire\GuideViewer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Platform
|--------------------------------------------------------------------------
*/

Route::get('/login', LoginPage::class)->middleware('guest')->name('login');

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', HubPage::class)->name('hub');
    Route::get('/settings', SettingsPage::class)->name('settings');

    /*
    |----------------------------------------------------------------------
    | Tools
    |----------------------------------------------------------------------
    |
    | One prefixed, name-spaced group per tool. A tool's routes never leak
    | outside its group, which is what lets the registry's `route_pattern`
    | match them wholesale for sidebar highlighting.
    |
    */

    Route::prefix('quiz')->name('quiz.')->group(function () {
        Route::get('/', QuizDashboard::class)->name('dashboard');
        Route::get('/session', QuizRunner::class)->name('session');
        Route::get('/import', ImportPage::class)->name('import');
        Route::get('/library', QuestionBrowser::class)->name('library');
        Route::get('/settings', QuizSettings::class)->name('settings');
    });

    Route::prefix('visualizer')->name('visualizer.')->group(function () {
        Route::get('/', GuideIndex::class)->name('index');
        Route::get('/{guide}', GuideViewer::class)->name('guide');
    });

    Route::prefix('lab')->name('lab.')->group(function () {
        Route::get('/', ProjectIndex::class)->name('index');
        Route::get('/{project}', ProjectViewer::class)->name('project');
    });
});
