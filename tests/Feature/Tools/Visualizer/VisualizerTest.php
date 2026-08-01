<?php

namespace Tests\Feature\Tools\Visualizer;

use App\Models\User;
use App\Tools\Visualizer\GuideLibrary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisualizerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_the_index_lists_published_guides(): void
    {
        $this->get(route('visualizer.index'))
            ->assertOk()
            ->assertSee('10 Git Commands')
            ->assertSee('Every developer should know');
    }

    public function test_a_guide_renders_its_own_full_bleed_page(): void
    {
        $response = $this->get(route('visualizer.guide', 'git-commands'));

        $response->assertOk()
            ->assertSee('GIT COMMANDS', false)
            ->assertSee('repository terminal', false)
            ->assertSee('ICVault — 10 Git Commands', false);

        // Canvas layout, not the app shell — no sidebar navigation.
        $response->assertDontSee('Installed Tools');
    }

    public function test_an_unknown_guide_slug_returns_404(): void
    {
        $this->get(route('visualizer.guide', 'does-not-exist'))->assertNotFound();
    }

    public function test_every_registered_guide_has_a_backing_view(): void
    {
        $guides = app(GuideLibrary::class);

        foreach (array_keys($guides->all()) as $slug) {
            $this->assertTrue(
                view()->exists($guides->view($slug)),
                "Guide [{$slug}] is registered but has no Blade view."
            );
        }
    }
}
