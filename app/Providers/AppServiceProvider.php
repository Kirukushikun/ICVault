<?php

namespace App\Providers;

use App\Platform\Support\ToolRegistry;
use App\Tools\Quiz\Services\AI\NaiveLineParser;
use App\Tools\Quiz\Services\AI\QuestionParserContract;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // The installed-tool list is read once per request and shared by the
        // sidebar, the Hub, and anything else that renders navigation.
        $this->app->singleton(
            ToolRegistry::class,
            fn () => new ToolRegistry(config('tools', []))
        );

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
