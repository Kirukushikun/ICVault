<?php

namespace App\Providers;

use App\Services\AI\NaiveLineParser;
use App\Services\AI\QuestionParserContract;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Stand-in for a real AI provider (see NaiveLineParser docblock) —
        // swap this binding when one is wired up.
        $this->app->bind(QuestionParserContract::class, NaiveLineParser::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
