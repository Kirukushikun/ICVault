<?php

namespace App\Platform\Support;

use Illuminate\Support\Collection;

/**
 * The single place that knows which tools exist. Adding a tool means adding an
 * entry to config/tools.php — the sidebar and Hub pick it up with no further
 * changes anywhere else.
 */
final class ToolRegistry
{
    /** @var Collection<string, Tool> */
    private Collection $tools;

    /** @param array<string, array<string, mixed>> $config */
    public function __construct(array $config)
    {
        $this->tools = collect($config)
            ->map(fn (array $definition, string $key) => Tool::fromConfig($key, $definition));
    }

    /** @return Collection<string, Tool> */
    public function all(): Collection
    {
        return $this->tools;
    }

    /** @return Collection<string, Tool> */
    public function enabled(): Collection
    {
        return $this->tools->filter(fn (Tool $tool) => $tool->enabled);
    }

    public function find(string $key): ?Tool
    {
        return $this->tools->get($key);
    }

    /** The tool owning the current request, if any (the Hub owns none). */
    public function current(): ?Tool
    {
        return $this->enabled()->first(fn (Tool $tool) => $tool->isCurrent());
    }
}
