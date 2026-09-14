<?php

namespace App\Platform\Support;

use App\Platform\Contracts\ProvidesToolSummary;

/**
 * A single entry from config/tools.php. Tools are described as plain data so
 * the sidebar, the Hub, and anything else can render them without knowing
 * what any particular tool does.
 */
final readonly class Tool
{
    public function __construct(
        public string $key,
        public string $name,
        public string $tagline,
        public string $icon,
        public string $accent,
        public string $route,
        public string $routePattern,
        public string $status,
        public bool $enabled,
        public ?string $summaryProvider,
        public ?string $settingsRoute,
        /** @var array<int, NavItem> */
        public array $navItems,
    ) {}

    /** @param array<string, mixed> $config */
    public static function fromConfig(string $key, array $config): self
    {
        return new self(
            key: $key,
            name: $config['name'],
            tagline: $config['tagline'] ?? '',
            icon: $config['icon'] ?? 'square',
            accent: $config['accent'] ?? '#C3073F',
            route: $config['route'],
            routePattern: $config['route_pattern'] ?? $key.'.*',
            status: $config['status'] ?? 'active',
            enabled: $config['enabled'] ?? true,
            summaryProvider: $config['summary'] ?? null,
            settingsRoute: $config['settings_route'] ?? null,
            navItems: array_map(
                fn (array $item) => NavItem::fromConfig($item),
                $config['nav'] ?? []
            ),
        );
    }

    /** Whether this tool exposes inner pages worth showing in the sidebar. */
    public function hasNav(): bool
    {
        return $this->navItems !== [];
    }

    /** Whether this tool owns a settings page of its own. */
    public function hasSettings(): bool
    {
        return $this->settingsRoute !== null;
    }

    public function settingsUrl(): ?string
    {
        return $this->settingsRoute === null ? null : route($this->settingsRoute);
    }

    public function url(): string
    {
        return route($this->route);
    }

    /** Blade component name for this tool's icon, e.g. "lucide-flask-conical". */
    public function iconComponent(): string
    {
        return 'lucide-'.$this->icon;
    }

    public function isCurrent(): bool
    {
        return request()->routeIs($this->routePattern);
    }

    /** Live status line from the owning tool, or null when it provides none. */
    public function summary(): ?string
    {
        if ($this->summaryProvider === null) {
            return null;
        }

        $provider = app($this->summaryProvider);

        return $provider instanceof ProvidesToolSummary ? $provider->summary() : null;
    }

    /**
     * Inline custom properties so accent colours survive Tailwind's static
     * class scan — the palette lives in config, not in generated class names.
     */
    public function accentStyle(): string
    {
        return sprintf(
            '--accent: %1$s; --accent-bg: color-mix(in srgb, %1$s 14%%, transparent); --accent-border: color-mix(in srgb, %1$s 38%%, transparent); --accent-glow: color-mix(in srgb, %1$s 9%%, transparent);',
            $this->accent
        );
    }
}
