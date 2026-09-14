<?php

namespace Tests\Feature\Tools\Lab;

use App\Models\User;
use App\Tools\Lab\ProjectLibrary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LabTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    /** @return array<string, array<string, string|int>> */
    private function projects(): array
    {
        return app(ProjectLibrary::class)->all();
    }

    // ---------------------------------------------------------------- index

    public function test_the_index_lists_every_parked_project(): void
    {
        $response = $this->get(route('lab.index'))->assertOk();

        foreach ($this->projects() as $slug => $project) {
            $response->assertSee($project['title'], false);
            $response->assertSee(route('lab.project', $slug), false);
        }
    }

    // ------------------------------------------------------------- projects

    public function test_every_project_renders_full_bleed_outside_the_app_shell(): void
    {
        foreach ($this->projects() as $slug => $project) {
            $this->get(route('lab.project', $slug))
                ->assertOk()
                ->assertSee('ICVault — '.$project['title'], false)
                ->assertDontSee('Installed Tools');   // sidebar belongs to the shell
        }
    }

    public function test_every_project_carries_the_platform_header(): void
    {
        foreach ($this->projects() as $slug => $project) {
            $this->get(route('lab.project', $slug))
                ->assertOk()
                ->assertSee('images/ic-logo.png', false)
                ->assertSee(route('hub'), false)
                ->assertSee(route('lab.index'), false)
                ->assertSee('Lab Vault', false)
                ->assertSee($project['title'], false);
        }
    }

    public function test_an_unknown_project_slug_returns_404(): void
    {
        $this->get(route('lab.project', 'does-not-exist'))->assertNotFound();
    }

    /**
     * A project's links out to a page it doesn't own must be plain
     * navigations: it may ship an inline <script>, which wire:navigate's
     * body swap would not re-run, leaving the project blank.
     */
    public function test_links_into_a_project_do_not_use_wire_navigate(): void
    {
        $html = $this->get(route('lab.index'))->assertOk()->getContent();

        preg_match_all('/<a\b[^>]*>/i', $html, $anchors);

        foreach (array_keys($this->projects()) as $slug) {
            $target = route('lab.project', $slug);

            $projectAnchors = array_values(array_filter(
                $anchors[0],
                fn (string $tag) => str_contains($tag, $target)
            ));

            $this->assertNotEmpty($projectAnchors, "The index does not link to [{$slug}] at all.");

            foreach ($projectAnchors as $tag) {
                $this->assertStringNotContainsString('wire:navigate', $tag);
            }
        }
    }

    // ------------------------------------------------------------- registry

    public function test_every_registered_project_has_a_backing_view(): void
    {
        $projects = app(ProjectLibrary::class);

        foreach (array_keys($projects->all()) as $slug) {
            $this->assertTrue(
                view()->exists($projects->view($slug)),
                "Project [{$slug}] is registered but has no Blade view."
            );
        }
    }

    public function test_every_project_declares_a_known_storage_backend(): void
    {
        foreach ($this->projects() as $slug => $project) {
            $this->assertContains(
                $project['storage'],
                ['localStorage', 'database'],
                "Project [{$slug}] declares an unrecognised storage backend."
            );
        }
    }
}
