<?php

namespace App\Platform\Support;

/**
 * One entry in a tool's own sub-navigation. Tools declare these in
 * config/tools.php so the shell can render a tool's inner pages without
 * knowing what any of them do.
 */
final readonly class NavItem
{
    public function __construct(
        public string $label,
        public string $route,
        public string $icon,
    ) {}

    /** @param array<string, string> $config */
    public static function fromConfig(array $config): self
    {
        return new self(
            label: $config['label'],
            route: $config['route'],
            icon: $config['icon'] ?? '·',
        );
    }

    public function url(): string
    {
        return route($this->route);
    }

    public function isCurrent(): bool
    {
        return request()->routeIs($this->route);
    }
}
