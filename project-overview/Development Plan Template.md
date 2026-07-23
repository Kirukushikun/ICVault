# ICVault — Development Plan

**Stack baseline:** Laravel 13 · Livewire + Alpine.js · SQLite (dev/prod, single-user scale) · Vite
**References:** `system-overview.md` (behavior spec, to be written) · `ic-vault/index.html` (UI contract — the four sidebar views are the real routes; import sub-tabs are `<!-- mockup only -->` and split into their own routes)

> **Status update (2026-07-23):** Stage 1 mockup done. Stage 2 UI scaffold done in full
> (see `ui-scaffold-checklist.md`, Steps 0–7 all checked) — five routes, Livewire shell,
> Alpine-driven interactivity, hardcoded sample data throughout, not yet verified in a
> real browser (no headless-browser tooling in the dev environment). Stage 3 Phase 0
> (auth) is done: single seeded user (`admin_it@bfcgroup.org`), Livewire login page,
> `auth`/`guest` middleware on all routes, logout, 6 passing feature tests
> (`tests/Feature/AuthTest.php`). Phase 1 (categories) is done: migration
> (id/name/slug/color), `Category` model + factory, `CategorySeeder` mirroring the
> mockup's 5 sample categories, 3 passing feature tests. Phase 2 is done: `Difficulty`/
> `QuestionType`/`MasteryState` enums, `questions`/`attempts` migrations, plus a bare
> `quiz_sessions` migration pulled forward from Phase 3 to satisfy the FK order (its
> Livewire wiring/quota logic is still Phase 3) — `questions.import_batch_id` is a
> nullable column with no FK constraint yet, since `import_batches` doesn't exist until
> Phase 4. `MasteryService` implements the full `new → learning → review → mastered`
> state machine (streak thresholds 1/2/3) with a one-step regression rule on a wrong
> answer, covered by 9 tests — one per state × correct/incorrect branch. 19/19 tests
> passing overall. Next: Phase 3 (Quiz Session module — real `quiz_sessions` quota
> logic, wiring the mockup's mode-selection/question-type components to real data).

---

## 1. Recommended tech per function

| Function | Default recommendation | Alternative / notes |
|---|---|---|
| Auth | Laravel's built-in session auth, single seeded user, no registration route | Skip auth entirely behind a local-only `.env` flag if this never leaves localhost |
| Roles & permissions | N/A — single user, no roles model | — |
| Core domain state machine (if any) | Question mastery: `new → learning → review → mastered` (SM-2-lite leveling driven by Attempt correctness) + Import pipeline: `uploaded → parsed → ai_converted → queued → imported` | Keep both as explicit enum-backed state machines in `Services/`, not booleans on the model |
| Notifications | None for v1 (personal, opens app manually) | Later: browser notification / mail digest for "quiz not done today" |
| File attachments | `.md` note upload (Obsidian export) stored in `storage/app/imports`, parsed then archived | Paste-as-text also supported (already in mockup's Session Log tab) — no upload needed for that path |
| Backups + health check | `php artisan backup:run` (spatie/laravel-backup) nightly via scheduler, dumps SQLite file + question pool JSON | Manual "Export Pool" button doubles as an ad-hoc backup |
| Import/export (if any) | Core feature — Obsidian `.md` + session-log text → AI → JSON question pool; pool exportable/importable as JSON | — |
| Audit trail | Attempt log (every answer: question_id, correct, timestamp, quiz_session_id) — this *is* the audit trail and the recall-stat source | No separate audit table needed |
| Access log | N/A — single user, skip | — |
| Sensitive fields | None — no PII, no credentials stored in question data | — |
| Danger zone (destructive ops) | Reset question pool / reset mastery progress — preview count → type-to-confirm → queued job | — |
| Queue driver | `database` queue driver for the AI parse/generate pipeline (can be slow, shouldn't block the request) | Fall back to `sync` in local dev if queue worker is a hassle |
| Testing | Pest, feature tests around the two state machines + quiz-session quota logic | — |

---

## 2. Folder structure

```
app/
├── Enums/
│   ├── Difficulty.php          # easy | medium | hard
│   ├── QuestionType.php        # multiple_choice | fill_blank | code
│   ├── MasteryState.php        # new | learning | review | mastered
│   └── ImportStatus.php        # uploaded | parsed | ai_converted | queued | imported
├── Models/                     # see §3
├── Livewire/
│   ├── Dashboard/               # streak, quota card, Did-You-Know, category mastery
│   ├── Quiz/                    # quiz loop, one component per question type
│   ├── Import/                  # note upload, session-log input, pipeline status
│   └── Library/                 # browse/filter question pool
├── Services/
│   ├── MasteryService.php       # question mastery state machine
│   ├── ImportPipelineService.php # import status state machine
│   └── AI/                      # note/log → question parsing + generation
├── Jobs/
│   ├── ParseImportBatch.php
│   └── ResetQuestionPool.php    # danger-zone job, preview → confirm → execute
├── Policies/                    # single-user, but kept for future multi-user headroom
└── Console/Commands/            # backup, pool export/import

resources/views/livewire/…       # Blade partials per Livewire component
routes/web.php                   # thin: route → Livewire component, all behind auth middleware
```

**Conventions to commit to early**

- Authorization happens in **policies**, called from every entry point — never only from
  navigation/menu visibility (even single-user, keep this so multi-user is a policy change later,
  not a rewrite).
- Every list/table gets a consistent action grammar (settled during the mockup stage — mockup
  currently uses: primary action button + ghost/skip button, no overflow menus yet).
- Difficulty and question-type colors/labels live in the `Difficulty`/`QuestionType` enums, not
  scattered as string literals — the mockup's `diff-easy`/`diff-medium`/`diff-hard` CSS classes
  map 1:1 to enum cases.

---

## 3. Data model — migrations & relationships

### Migration order (respects FK dependencies)

```
1. categories            (id, name, slug, color)
2. users                 (single seeded row for v1)
3. import_batches        (source_type: note|log, raw_content, status, created_at)
4. questions             (category_id, import_batch_id nullable, difficulty, type,
                          prompt, options_json nullable, answer, mastery_state,
                          mastery_streak, last_reviewed_at)
5. quiz_sessions         (date, quota, completed_count)
6. attempts              (question_id, quiz_session_id, correct, answered_at)
7. tips                  (category_id nullable, body, source_question_id nullable)
```

### Relationship map

```
Category      1─* Question
ImportBatch   1─* Question        (questions generated_from a batch)
QuizSession   1─* Attempt
Question      1─* Attempt
Question      1─* Tip             (a "Did You Know" can be spun off a question)
```

### Modeling decisions worth locking in

- **One `mastery_state` enum column on `Question`**, not separate booleans (`is_learning`,
  `is_mastered`, …) — mirrors the "single status enum" rule and keeps the mastery state machine
  the only writer of that column.
- **`ImportBatch.status`** is a separate state machine from `Question.mastery_state` — a batch
  finishing its pipeline (`imported`) is what *creates* Questions in `new` mastery state; the two
  machines only touch at that one transition.
- Questions keep `import_batch_id` nullable so hand-authored questions (added directly in
  Library, not via the AI pipeline) are still valid rows.
- Guard in **both** policy and query: the danger-zone pool reset must delete/reset Attempts and
  Questions in a DB transaction, not just hide the button after confirming — a direct re-submit
  of the confirm request must still be safe.
- No confidentiality/visibility tiers needed (single user, no sharing in v1).

---

## 4. UI module ↔ mockup mapping

| Mockup tab (static) | Real route | Component |
|---|---|---|
| Dashboard | `/` | `Livewire\Dashboard\DashboardPage` |
| Quiz Session | `/quiz` | `Livewire\Quiz\QuizSession` (+ one sub-component per question type: `McQuestion`, `FillBlankQuestion`, `CodeQuestion`) |
| Import & Logs → "Obsidian Notes" tab *(mockup only — becomes its own route)* | `/import/notes` | `Livewire\Import\NoteUpload` |
| Import & Logs → "Session Log" tab *(mockup only — becomes its own route)* | `/import/logs` | `Livewire\Import\SessionLogInput` |
| Import & Logs → pipeline status + generated preview | shared partial on both `/import/*` routes | `Livewire\Import\PipelineStatus` |
| Library | `/library` | `Livewire\Library\QuestionBrowser` |
| Settings *(added to mockup after this table was written — real page, not a mockup-only tab)* | `/settings` | `Livewire\Settings\SettingsPage` |

---

## 5. Build order

Each phase ends runnable and demoable. Don't start a phase before the previous one's tests pass.

> **Note (post-scaffold):** if a UI scaffold exists, "build X module" means: add the
> migrations/models/policies, then swap the component's hardcoded data for real queries and its
> stubbed actions for real ones — keeping markup and sample-data-as-seeders intact.

### Phase 0 — Foundation *(everything depends on this)*
- Laravel scaffold, Livewire install, app layout ported from `ic-vault/index.html` (sidebar,
  dot-grid background, design tokens as Tailwind/CSS vars), single-user auth stub, all four
  routes stubbed and navigable.

### Phase 1 — Reference data
- `categories` migration + seeder mirroring the mockup's Laravel/Blade/Git/SQL/JS Fundamentals
  set. No admin UI needed yet beyond what Library's filter row already implies.

### Phase 2 — Core domain + state machine
- `questions`, `attempts` migrations, `Difficulty`/`QuestionType`/`MasteryState` enums,
  `MasteryService` with a full transition table (`new→learning→review→mastered`, and the
  regression rule when an attempt is wrong) + tests **before Quiz Session touches it**.

### Phase 3 — Quiz Session module
- `quiz_sessions` migration, daily-quota logic (the mockup's "4 / 8 answered" quota card), the
  three question-type components ported from the mockup's `renderQuestion()` cases, Attempt
  logging on submit.

### Phase 4 — Import pipeline module
- `import_batches` migration, `ImportStatus` enum, `ImportPipelineService`, the note-upload and
  session-log-input Livewire components, `ParseImportBatch` queued job calling the AI service,
  the pipeline-status partial and generated-question preview from the mockup.

### Phase 5 — Did You Know layer
- `tips` migration, surfaced on Dashboard tied to the current quiz session's categories; AI
  pipeline can spin a tip off any generated question.

### Phase 6 — Library module
- Browse/search/filter over the full pool (difficulty filter row already in the mockup), direct
  question edit/delete for hand-authored corrections.

### Phase 7 — Documents & export
- JSON pool export/import (also doubles as portable backup), no notification channels planned
  for v1 (personal, opened manually).

### Phase 8 — Maintenance
- `spatie/laravel-backup` scheduled dump, `ResetQuestionPool` danger-zone job
  (preview count → type-to-confirm → execute).

### Phase 9 — Hardening *(checklist-driven)*
- [ ] Every route/action authorized by policy, not menu visibility.
- [ ] No question/import record reachable by direct link/URL that Library/Import wouldn't show.
- [ ] Every mastery/import transition has a reachable control with a real handler.
- [ ] Test coverage: full happy path + every mastery-state branch + quota edge cases (0 due,
      more than quota due).
- [ ] Pool reset re-verifies its counts server-side; typed confirmation is UX, not the guard.

---

## 6. Suggested first week

1. Phase 0 in full — get the ported layout navigable and matching the mockup pixel-for-pixel.
2. Phase 1 migrations + seeders mirroring the mockup's sample categories.
3. Mastery state machine transition table on paper first, then as tested code, before Phase 3
   starts.
