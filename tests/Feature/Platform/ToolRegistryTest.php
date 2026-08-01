<?php

namespace Tests\Feature\Platform;

use App\Platform\Support\ToolRegistry;
use Tests\TestCase;

class ToolRegistryTest extends TestCase
{
    private function registry(array $config): ToolRegistry
    {
        return new ToolRegistry($config);
    }

    private function definition(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Example Tool',
            'route' => 'hub',
            'accent' => '#123456',
        ], $overrides);
    }

    public function test_it_builds_tools_from_plain_config_data(): void
    {
        $registry = $this->registry(['example' => $this->definition()]);

        $tool = $registry->find('example');

        $this->assertNotNull($tool);
        $this->assertSame('example', $tool->key);
        $this->assertSame('Example Tool', $tool->name);
        $this->assertSame('#123456', $tool->accent);
    }

    public function test_it_defaults_the_route_pattern_to_the_tool_key(): void
    {
        $registry = $this->registry(['example' => $this->definition()]);

        $this->assertSame('example.*', $registry->find('example')->routePattern);
    }

    public function test_disabled_tools_are_excluded_from_navigation(): void
    {
        $registry = $this->registry([
            'shown' => $this->definition(),
            'hidden' => $this->definition(['enabled' => false]),
        ]);

        $this->assertCount(2, $registry->all());
        $this->assertCount(1, $registry->enabled());
        $this->assertSame(['shown'], $registry->enabled()->keys()->all());
    }

    public function test_a_tool_without_a_settings_route_reports_no_settings(): void
    {
        $registry = $this->registry(['example' => $this->definition()]);

        $tool = $registry->find('example');

        $this->assertFalse($tool->hasSettings());
        $this->assertNull($tool->settingsUrl());
    }

    public function test_a_tool_can_declare_its_own_settings_route(): void
    {
        $registry = $this->registry([
            'example' => $this->definition(['settings_route' => 'settings']),
        ]);

        $this->assertTrue($registry->find('example')->hasSettings());
    }

    public function test_find_returns_null_for_an_unknown_key(): void
    {
        $this->assertNull($this->registry([])->find('nope'));
    }

    public function test_the_shipped_config_registers_the_quiz_and_visualizer_tools(): void
    {
        $registry = app(ToolRegistry::class);

        $this->assertSame(['quiz', 'visualizer'], $registry->enabled()->keys()->all());
        $this->assertSame('quiz.dashboard', $registry->find('quiz')->route);
        $this->assertSame('visualizer.index', $registry->find('visualizer')->route);
    }

    public function test_every_registered_tool_points_at_a_real_route(): void
    {
        foreach (app(ToolRegistry::class)->enabled() as $tool) {
            $this->assertIsString($tool->url(), "Tool [{$tool->key}] has an unresolvable route.");
        }
    }
}
