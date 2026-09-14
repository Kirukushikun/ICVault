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

    /** @return array<string, array<string, string|int>> */
    private function guides(): array
    {
        return app(GuideLibrary::class)->all();
    }

    // ---------------------------------------------------------------- index

    public function test_the_index_lists_every_published_guide(): void
    {
        $response = $this->get(route('visualizer.index'))->assertOk();

        foreach ($this->guides() as $slug => $guide) {
            $response->assertSee($guide['title'], false);
            $response->assertSee(route('visualizer.guide', $slug), false);
        }
    }

    public function test_guides_are_numbered_without_gaps_or_duplicates(): void
    {
        $numbers = collect($this->guides())
            ->map(fn (array $g) => (int) filter_var($g['eyebrow'], FILTER_SANITIZE_NUMBER_INT))
            ->values()
            ->sort()
            ->all();

        $this->assertSame(range(1, count($numbers)), $numbers, 'Field guide numbering has a gap or a repeat.');
    }

    public function test_each_guide_has_a_distinguishable_accent(): void
    {
        $accents = collect($this->guides())->map(fn (array $g) => strtolower($g['accent']));

        $this->assertCount(
            $accents->unique()->count(),
            $accents,
            'Two guides share an accent colour, so their index cards look identical.'
        );
    }

    // --------------------------------------------------------------- guides

    public function test_every_guide_renders_full_bleed_outside_the_app_shell(): void
    {
        foreach ($this->guides() as $slug => $guide) {
            $this->get(route('visualizer.guide', $slug))
                ->assertOk()
                ->assertSee('ICVault — '.$guide['title'], false)
                ->assertDontSee('Installed Tools');   // sidebar belongs to the shell
        }
    }

    public function test_every_guide_carries_the_platform_header(): void
    {
        foreach ($this->guides() as $slug => $guide) {
            $this->get(route('visualizer.guide', $slug))
                ->assertOk()
                ->assertSee('images/ic-logo.png', false)
                ->assertSee(route('hub'), false)
                ->assertSee(route('visualizer.index'), false)
                ->assertSee('Concept Vault', false)
                ->assertSee($guide['title'], false);
        }
    }

    /**
     * The masthead and the index card used to hold separate copies of these
     * labels, so renumbering a guide meant editing two files and silently
     * getting it half-right.
     */
    public function test_every_guide_reads_its_labels_from_the_library(): void
    {
        $index = $this->get(route('visualizer.index'))->assertOk();

        foreach ($this->guides() as $slug => $guide) {
            $this->get(route('visualizer.guide', $slug))
                ->assertOk()
                ->assertSee($guide['eyebrow'], false)
                ->assertSee($guide['subtitle'], false);

            $index->assertSee($guide['eyebrow'], false);
        }
    }

    /** The card's accent and the guide's own palette must be the same colour. */
    public function test_every_guide_uses_its_own_registered_accent(): void
    {
        foreach ($this->guides() as $slug => $guide) {
            $html = $this->get(route('visualizer.guide', $slug))->assertOk()->getContent();

            $this->assertStringContainsStringIgnoringCase(
                $guide['accent'],
                $html,
                "Guide [{$slug}] is registered as {$guide['accent']} but its stylesheet never uses that colour."
            );
        }
    }

    /**
     * Ported guides arrived carrying their source palette. Any of these means a
     * guide is still wearing another product's dark theme instead of ICVault's.
     */
    public function test_no_guide_keeps_its_original_page_background(): void
    {
        $foreign = [
            '#0d1117', '#161b22',                 // git
            '#1a0f18', '#2a1826',                 // filesystem
            '#0a0e14', '#111823',                 // docker
            '#080b16', '#0f1424', '#141b30', '#0a0e1c',   // http
        ];

        foreach ($this->guides() as $slug => $guide) {
            $html = $this->get(route('visualizer.guide', $slug))->assertOk()->getContent();

            foreach ($foreign as $hex) {
                $this->assertStringNotContainsStringIgnoringCase(
                    $hex,
                    $html,
                    "Guide [{$slug}] still uses the foreign surface colour {$hex}."
                );
            }

            $this->assertStringContainsString('#1A1A1D', $html, "Guide [{$slug}] is not on the ICVault page colour.");
        }
    }

    /**
     * The canvas layout loads Roboto, League Gothic, League Spartan and
     * JetBrains Mono. Anything else silently falls back to system-ui, which is
     * how two ported guides ended up not rendering in the face they asked for.
     */
    public function test_no_guide_asks_for_a_font_the_layout_does_not_load(): void
    {
        $loaded = ['Roboto', 'League Gothic', 'League Spartan', 'JetBrains Mono'];

        foreach ($this->guides() as $slug => $guide) {
            $html = $this->get(route('visualizer.guide', $slug))->assertOk()->getContent();

            preg_match_all("/font-family:\s*'([^']+)'/", $html, $matches);

            foreach (array_unique($matches[1]) as $family) {
                $this->assertContains(
                    $family,
                    $loaded,
                    "Guide [{$slug}] asks for '{$family}', which the canvas layout never loads."
                );
            }
        }
    }

    /**
     * A guide ships its own inline <script>. wire:navigate swaps the body
     * without re-running it, which renders the guide's interactive parts blank,
     * so links into a guide must be plain navigations.
     */
    public function test_links_into_a_guide_do_not_use_wire_navigate(): void
    {
        $html = $this->get(route('visualizer.index'))->assertOk()->getContent();

        preg_match_all('/<a\b[^>]*>/i', $html, $anchors);

        foreach (array_keys($this->guides()) as $slug) {
            $target = route('visualizer.guide', $slug);

            $guideAnchors = array_values(array_filter(
                $anchors[0],
                fn (string $tag) => str_contains($tag, $target)
            ));

            $this->assertNotEmpty($guideAnchors, "The index does not link to [{$slug}] at all.");

            foreach ($guideAnchors as $tag) {
                $this->assertStringNotContainsString('wire:navigate', $tag);
            }
        }
    }

    public function test_an_unknown_guide_slug_returns_404(): void
    {
        $this->get(route('visualizer.guide', 'does-not-exist'))->assertNotFound();
    }

    // ------------------------------------------------------------- registry

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

    /** A mistyped logo path renders an invisible broken image, so assert the file. */
    public function test_every_guide_logo_points_at_a_file_that_exists(): void
    {
        foreach ($this->guides() as $slug => $guide) {
            if (! isset($guide['logo'])) {
                continue;
            }

            $this->assertFileExists(
                public_path($guide['logo']),
                "Guide [{$slug}] references a logo that is not in public/."
            );
        }
    }

    /**
     * A guide about a product shows that product's real mark. A guide about
     * something nobody owns — HTTP, say — has no logo registered and keeps a
     * drawn glyph instead; what must never happen is a registered logo that
     * the masthead ignores in favour of the stand-in it shipped with.
     */
    public function test_every_guide_renders_its_registered_logo_in_the_masthead(): void
    {
        foreach ($this->guides() as $slug => $guide) {
            $response = $this->get(route('visualizer.guide', $slug))->assertOk();

            if (! isset($guide['logo'])) {
                $response->assertSee('class="glyph"', false);

                continue;
            }

            $response
                ->assertSee($guide['logo'], false)
                ->assertDontSee('<div class="glyph" aria-hidden="true">', false);
        }
    }
}
