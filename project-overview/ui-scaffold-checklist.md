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
- [x] Stat row (streak, pool size, avg recall) — hardcoded, using `<x-stat-card>`
- [x] Quota card + progress bar, "Continue Session" link to `/quiz`
- [x] Did You Know card
- [x] Category grid (mastery bars) — hardcoded from mockup's 5 sample categories
      (Laravel, Vue/Blade, Git, SQL, JS Fundamentals), per-category gradient colors
      matched to the mockup
- [x] Verify: curl content-string check for every stat/label/category, confirmed
      "Continue Session" anchor resolves to `/quiz`, other 4 routes unaffected

## Step 3 — Quiz Session (`/quiz`)
- [x] Mode-selection lobby (4 mode cards, real Alpine selection state via a single
      `quizSession()` Alpine component registered in `@script`/`@endscript`, Start button
      enabled only once a mode is picked)
- [x] Active-quiz view: progress bar, all three question types (mc/fill/code) handled by
      one Alpine component switching on `currentQuestion.type` (kept as one component
      rather than three Livewire sub-components — no server round-trip needed yet at
      this stage, matches "promote to a framework component only when behavior appears")
      fed from a hardcoded 6-question sample set mirroring the mockup's `allQuestions`
- [x] Submit → reveal-answer state (per-type correct/incorrect check + explanation),
      Skip, Exit-session back to lobby
- [x] *(Deviation)* the mockup's `contenteditable` code editor became a `<textarea>` —
      more reliable with Alpine's `x-model` than binding to a contenteditable div
- [x] Verify: `/quiz` returns 200 with all mode/question-type strings present; extracted
      and `node --check`'d the embedded Alpine script for syntax errors; hit a Blade
      compile bug along the way — the mockup's code-answer sample text contains literal
      `@forelse`/`@if`/`{{ }}` (it's a Blade-syntax quiz question), which Blade's compiler
      parsed as real directives even inside the JS string. Fixed by breaking the
      character adjacency with JS string concatenation (`'@' + 'forelse(...)'`) rather
      than the usual `@@` escape, which turned out not to reliably suppress `@forelse`'s
      paired `@empty`/`@endforelse` directives

## Step 4 — Import & Logs (`/import`)
- [x] Tab switcher (Obsidian Notes / Session Log) — real Alpine state (`x-data="{ tab }"`)
- [x] Notes panel: drop-zone (visual only, dispatches the shared `toast` event on click —
      "Nothing persisted yet — wired up in Stage 3")
- [x] Session Log panel: textarea
- [x] Pipeline status row + "Generate Questions" button (toast stub, same event)
- [x] Generated-this-session list — 3 hardcoded sample items via `<x-generated-item>`
- [x] Verify: curl content-string check for both tabs' content, pipeline steps, and all
      3 generated items; other 3 routes + no new log errors confirmed unaffected

## Step 5 — Library (`/library`)
- [x] Difficulty filter tabs (All/Easy/Medium/Hard) — real Alpine filter (`questionBrowser()`
      Alpine component) over the mockup's 5 hardcoded sample questions; tab count labels
      (184/61/74/49) kept as the mockup's static text since real counts are a Stage-3 concern
- [x] Question list items with difficulty dot + category/difficulty tag
- [x] Verify: curl content-string check for all 4 tab labels and the Alpine component name;
      other routes unaffected, no new log errors

## Step 6 — Settings (`/settings`)
- [x] Account section (edit → save/cancel toggle, real Alpine state via `settingsPage()`,
      toast stub on save, password show/hide)
- [x] Quiz Preferences (default-mode picker, daily-quota stepper 1–30, 4 toggle switches
      via the new shared `<x-toggle-switch>` component) — real Alpine state, no persistence
- [x] Notifications (3 toggle switches, stub)
- [x] Backup & Restore (3 export buttons → toast; import drop-zone reads the real selected
      `File` for name/size display — client-side only, no upload — conflict radio + Import
      button → toast, disabled until a file is "selected")
- [x] Danger Zone (three destructive buttons → toast only, no confirmation flow yet —
      real type-to-confirm modal is a Stage-3 concern per the Development Plan)
- [x] Verify: curl content-string check for every section/control label; extracted +
      `node --check`'d the embedded Alpine script; other 4 routes unaffected, no new
      log errors

## Step 7 — Polish & mockup cross-check
- [x] Responsive breakpoint (820px — sidebar collapses to horizontal row) ported to the
      shared layout + Dashboard's stat row, using Tailwind v4's `max-[820px]:` arbitrary
      variant; verified all 13 responsive utility rules compiled into a single
      `@media (width < 820px)` block
- [x] Empty-state pass: none needed yet (all data hardcoded) — deferred to Stage 3
- [x] Side-by-side diff against `index.html` — done incrementally per step (each step's
      verify pass cross-checked content/labels against the mockup) rather than as one
      final pass
- [x] Every mockup-shown screen is reachable; every transition has a visible control
      (nav links, Continue Session, mode selection → start, tab switches, filter tabs,
      account edit/save/cancel, file-select → import)
- [x] This checklist fully checked

**Not yet verified visually in a real browser** — this Windows dev environment has no
`chromium-cli`/headless-browser tooling available, so every step above was verified via
HTTP status codes, content-string assertions, and `node --check` on extracted Alpine
scripts, not a screenshot. Recommend a manual click-through in an actual browser before
treating Stage 2 as fully closed.

---
**Working agreement:** one step per session, verified before starting the next; leave
each step's changes staged (not committed) for review; commit message `feat: Step N - …`.
