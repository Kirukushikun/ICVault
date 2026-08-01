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

    /**
     * A guide ships its own inline <script>. wire:navigate swaps the body
     * without re-running it, which renders the guide's interactive parts blank,
     * so links into a guide must be plain navigations.
     */
    public function test_links_into_a_guide_do_not_use_wire_navigate(): void
    {
        $html = $this->get(route('visualizer.index'))->assertOk()->getContent();
        $target = route('visualizer.guide', 'git-commands');

        preg_match_all('/<a\b[^>]*>/i', $html, $anchors);

        $guideAnchors = array_values(array_filter(
            $anchors[0],
            fn (string $tag) => str_contains($tag, $target)
        ));

        $this->assertNotEmpty($guideAnchors, 'The index does not link to the guide at all.');

        foreach ($guideAnchors as $tag) {
            $this->assertStringNotContainsString('wire:navigate', $tag);
        }
    }

    public function test_a_guide_carries_the_platform_header_so_it_reads_as_part_of_icvault(): void
    {
        $this->get(route('visualizer.guide', 'git-commands'))
            ->assertOk()
            ->assertSee('images/ic-logo.png', false)   // brand mark
            ->assertSee(route('hub'), false)           // back to the platform
            ->assertSee(route('visualizer.index'), false)
            ->assertSee('Concept Visualizer', false)   // breadcrumb trail
            ->assertSee('10 Git Commands', false);     // current guide
    }

    public function test_the_guide_uses_the_git_accent_colour(): void
    {
        $this->get(route('visualizer.guide', 'git-commands'))
            ->assertOk()
            ->assertSee('#f05133', false)
            ->assertDontSee('#0d1117', false);  // GitHub's canvas, replaced by ICVault's
    }

    public function test_an_unknown_guide_slug_returns_404(): void
    {
        $this->get(route('visualizer.guide', 'does-not-exist'))->assertNotFound();
    }

    /** A mistyped logo path renders an invisible broken image, so assert the file. */
    public function test_every_guide_logo_points_at_a_file_that_exists(): void
    {
        foreach (app(GuideLibrary::class)->all() as $slug => $guide) {
            if (! isset($guide['logo'])) {
                continue;
            }

            $this->assertFileExists(
                public_path($guide['logo']),
                "Guide [{$slug}] references a logo that is not in public/."
            );
        }
    }

    public function test_the_git_guide_renders_the_git_logo_rather_than_a_placeholder_glyph(): void
    {
        $this->get(route('visualizer.guide', 'git-commands'))
            ->assertOk()
            ->assertSee('/images/git-logo.png', false);
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
