<?php

namespace Tests\Feature\Platform;

use App\Models\User;
use App\Platform\Support\NavItem;
use App\Platform\Support\Tool;
use App\Platform\Support\ToolRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    private function tools(): \Illuminate\Support\Collection
    {
        return app(ToolRegistry::class)->enabled();
    }

    public function test_the_sidebar_lists_every_enabled_tool(): void
    {
        $response = $this->get(route('hub'))->assertOk();

        foreach ($this->tools() as $tool) {
            $response->assertSee($tool->url(), false);
        }
    }

    /**
     * The regression this guards: a tool's inner pages had routes but nothing
     * in the UI linked to them, so Import and Library were unreachable.
     */
    public function test_opening_a_tool_reveals_a_link_to_every_one_of_its_pages(): void
    {
        foreach ($this->tools()->filter(fn (Tool $t) => $t->hasNav()) as $tool) {
            $response = $this->get($tool->url())->assertOk();

            foreach ($tool->navItems as $item) {
                $response->assertSee($item->url(), false);
                // Escaped: labels like "Import & Logs" render as "Import &amp; Logs".
                $response->assertSee($item->label);
            }
        }
    }

    public function test_the_quiz_tool_exposes_import_and_library(): void
    {
        $labels = collect(app(ToolRegistry::class)->find('quiz')->navItems)
            ->map(fn (NavItem $item) => $item->label);

        $this->assertContains('Import & Logs', $labels);
        $this->assertContains('Library', $labels);

        $this->get(route('quiz.dashboard'))
            ->assertOk()
            ->assertSee(route('quiz.import'), false)
            ->assertSee(route('quiz.library'), false);
    }

    public function test_a_tools_inner_pages_stay_hidden_while_another_tool_is_open(): void
    {
        $this->get(route('visualizer.index'))
            ->assertOk()
            ->assertDontSee(route('quiz.import'), false)
            ->assertDontSee(route('quiz.library'), false);
    }

    public function test_inner_page_links_are_still_present_on_a_tools_deeper_pages(): void
    {
        $this->get(route('quiz.library'))
            ->assertOk()
            ->assertSee(route('quiz.import'), false)
            ->assertSee(route('quiz.session'), false);
    }

    public function test_every_nav_item_points_at_a_route_that_resolves(): void
    {
        foreach ($this->tools() as $tool) {
            foreach ($tool->navItems as $item) {
                $this->assertIsString(
                    $item->url(),
                    "Tool [{$tool->key}] has a nav item with an unresolvable route [{$item->route}]."
                );
            }
        }
    }
}
