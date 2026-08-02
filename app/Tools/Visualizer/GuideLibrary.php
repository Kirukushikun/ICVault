<?php

namespace App\Tools\Visualizer;

/**
 * Guides are hand-authored Blade pages, not database rows — each one is a
 * bespoke interactive layout rather than a template filled with content. This
 * class is the index of what exists.
 *
 * To add a guide:
 *   1. Create resources/views/tools/visualizer/guides/<slug>.blade.php
 *   2. Add an entry below keyed by that slug.
 */
final class GuideLibrary
{
    /** @var array<string, array<string, string|int>> */
    private const GUIDES = [
        'git-commands' => [
            'title' => '10 Git Commands',
            'subtitle' => 'Every developer should know',
            'eyebrow' => 'Developer Field Guide 01',
            'blurb' => 'One continuous workflow — from init through push — with the working tree, staging area, and remote drawn as you step through each command.',
            'steps' => 10,
            'accent' => '#F05133',
            'logo' => 'images/git-logo.png',
        ],

        'filesystem-commands' => [
            'title' => 'Navigating the Filesystem',
            'subtitle' => 'Nine commands to move through Linux',
            'eyebrow' => 'Developer Field Guide 02',
            'blurb' => 'One directory tree, walked end to end — pwd, ls, cd and friends, with your position in the tree redrawn as each command runs.',
            'steps' => 9,
            'accent' => '#E95420',
            'logo' => 'images/ubuntu-logo.png',
        ],

        'docker-commands' => [
            'title' => '10 Docker Commands',
            'subtitle' => 'One container workflow',
            'eyebrow' => 'Developer Field Guide 03',
            'blurb' => 'Image to registry to running container — pull, run, exec and the rest, with layers, state and ports shown as they change.',
            'steps' => 10,
            'accent' => '#0DB7ED',
            'logo' => 'images/docker-logo.png',
        ],
    ];

    /** @return array<string, array<string, string|int>> */
    public function all(): array
    {
        return self::GUIDES;
    }

    public function count(): int
    {
        return count(self::GUIDES);
    }

    public function exists(string $slug): bool
    {
        return array_key_exists($slug, self::GUIDES);
    }

    /** @return array<string, string|int>|null */
    public function find(string $slug): ?array
    {
        return self::GUIDES[$slug] ?? null;
    }

    /** Blade view backing a guide slug. */
    public function view(string $slug): string
    {
        return 'tools.visualizer.guides.'.$slug;
    }
}
