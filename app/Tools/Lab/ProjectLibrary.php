<?php

namespace App\Tools\Lab;

/**
 * Lab projects are one-off mini-systems that don't warrant their own
 * app/Tools module yet — static pages or localStorage-backed CRUD, parked
 * here until (if ever) one earns real persistence and graduates out.
 *
 * To add a project:
 *   1. Create resources/views/tools/lab/projects/<slug>.blade.php
 *   2. Add an entry below keyed by that slug.
 *
 * `storage` says what backs a project right now: 'localStorage' for a
 * browser-only prototype, 'database' once it has been wired to real Eloquent
 * models. Flipping that value doesn't touch this class or the route — the
 * Blade view just starts calling the server instead of window.localStorage,
 * which is the seam this registry exists to keep clean.
 */
final class ProjectLibrary
{
    /** @var array<string, array<string, string|int>> */
    private const PROJECTS = [
        'scratch-notes' => [
            'title' => 'Scratch Notes',
            'subtitle' => 'A minimal note board',
            'eyebrow' => 'Lab 01',
            'blurb' => 'Quick capture with no schema yet — every note lives in this browser until it either gets deleted or earns a real table.',
            'accent' => '#7C5CFC',
            'storage' => 'localStorage',
        ],
    ];

    /** @return array<string, array<string, string|int>> */
    public function all(): array
    {
        return self::PROJECTS;
    }

    public function count(): int
    {
        return count(self::PROJECTS);
    }

    public function exists(string $slug): bool
    {
        return array_key_exists($slug, self::PROJECTS);
    }

    /** @return array<string, string|int>|null */
    public function find(string $slug): ?array
    {
        return self::PROJECTS[$slug] ?? null;
    }

    /** Blade view backing a project slug. */
    public function view(string $slug): string
    {
        return 'tools.lab.projects.'.$slug;
    }
}
