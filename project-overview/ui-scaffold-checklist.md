# ICVault — UI Scaffold Checklist (Stage 2)

Porting `project-overview/index.html` (the mockup / UI contract) into real
Blade + Livewire components: pure UI, hardcoded sample data, no database, no auth.
See `Development Playbook.md` §Stage 2 for the method and `Development Plan.md` for
the per-phase build order that follows this.

> **Deviation from Development Plan:** the mockup has **five** sidebar views, not four —
> Settings was added to the mockup after the plan's §4 mapping table was written. Settings
> is a real full page (account, quiz preferences, notifications, backup/restore, danger
> zone), not a `<!-- mockup only -->` sub-tab, so it gets its own route: `/settings` →
> `Livewire\Settings\SettingsPage`. Plan §4 should be updated to add this row.

## Step 0 — Shell
- [x] Install Livewire (v4.3.3 — used classic `--class` MFC generation, not the new
      emoji-named SFC default, to match the Development Plan's `app/Livewire/...` layout)
- [x] Design tokens ported to `resources/css/app.css` (`@theme` — colors, fonts)
- [x] Base layout (`resources/views/layouts/app.blade.php`): sidebar (brand, nav list,
      footer), dot-grid + radial-gradient background, main content slot
- [x] Fonts wired (Roboto, League Spartan, JetBrains Mono)
- [x] All five routes registered, each a full-page Livewire component, each navigable
      from the sidebar with active-state highlighting
- [x] `wire:navigate` on nav links (SPA-style transitions)
- [x] Verify: every route returns 200, sidebar highlights the current view, no console
      errors (verified via curl status/content-string checks + Vite log — no headless
      browser available in this environment to screenshot, see note below)

## Step 1 — Shared components
- [x] `card` styling as a Tailwind utility class group (`.card`, in `app.css` `@layer components`)
- [x] Stat card (`<x-stat-card>`), section label (`<x-section-label>`), difficulty badge
      (`<x-difficulty-badge>`), generated/library list item (`<x-generated-item>`)
- [x] Toast (`<x-toast>`, bottom-center) — Alpine-driven, listens for a global
      `toast` window event (`$dispatch('toast', {message: '...'})` or
      `window.dispatchEvent(new CustomEvent('toast', {detail:{message:'...'}}))`),
      mounted once in the shared layout
- [x] Verify: rendered each via a throwaway view + `artisan tinker`, output matched
      expected markup/classes; confirmed toast partial doesn't break any of the 5 routes
      (all still 200, no Vite errors)

## Step 2 — Dashboard (`/`)
- [ ] Stat row (streak, pool size, avg recall) — hardcoded
- [ ] Quota card + progress bar, "Continue Session" link to `/quiz`
- [ ] Did You Know card
- [ ] Category grid (mastery bars) — hardcoded from mockup's 5 sample categories
- [ ] Verify: matches mockup pixel-for-pixel, links to `/quiz` work

## Step 3 — Quiz Session (`/quiz`)
- [ ] Mode-selection lobby (4 mode cards, real Alpine selection state, Start button
      enabled only once a mode is picked — this is a "cheap real interactivity" case
      per the playbook, build with real component state)
- [ ] Active-quiz view: progress bar, one sub-component per question type
      (`McQuestion`, `FillBlankQuestion`, `CodeQuestion`) fed from a hardcoded sample set
      mirroring the mockup's `allQuestions` JS array
- [ ] Submit → reveal-answer state, Skip, Exit-session back to lobby
- [ ] Verify: every question type renders and the sample set cycles through

## Step 4 — Import & Logs (`/import`)
- [ ] Tab switcher (Obsidian Notes / Session Log) — real Alpine state
- [ ] Notes panel: drop-zone (visual only, toast "nothing persisted" on drop/click)
- [ ] Session Log panel: textarea
- [ ] Pipeline status row + "Generate Questions" button (toast stub)
- [ ] Generated-this-session list — hardcoded sample items
- [ ] Verify: tab switch works, drop-zone and button show the stub toast

## Step 5 — Library (`/library`)
- [ ] Difficulty filter tabs (All/Easy/Medium/Hard) — real Alpine filter over the
      hardcoded sample list (cheap real interactivity, matches mockup behavior)
- [ ] Question list items with difficulty dot + category/difficulty tag
- [ ] Verify: filter tabs actually filter the hardcoded list

## Step 6 — Settings (`/settings`)
- [ ] Account section (edit → save/cancel toggle, real Alpine state, toast stub on save)
- [ ] Quiz Preferences (default-mode picker, daily-quota stepper, toggle switches) — real
      Alpine state, no persistence
- [ ] Notifications (toggle switches, stub)
- [ ] Backup & Restore (export buttons → toast, import drop-zone + conflict radio → toast)
- [ ] Danger Zone (three destructive buttons → toast only, no confirmation flow yet —
      real type-to-confirm modal is a Stage-3 concern per the Development Plan)
- [ ] Verify: every control in the mockup has a reachable, wired-up (even if stubbed) handler

## Step 7 — Polish & mockup cross-check
- [ ] Responsive breakpoint (820px — sidebar collapses to horizontal row) ported
- [ ] Empty-state pass: none needed yet (all data hardcoded) — note for Stage 3
- [ ] Side-by-side diff against `index.html` for all five views
- [ ] Every mockup-shown screen is reachable; every transition has a visible control
- [ ] This checklist fully checked

---
**Working agreement:** one step per session, verified before starting the next; leave
each step's changes staged (not committed) for review; commit message `feat: Step N - …`.
