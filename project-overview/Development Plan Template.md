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
> passing overall. Phase 3 is done: added a nullable `questions.explanation` column
> (needed for the answer-reveal text; not in the original §3 schema list — a minimal,
> clearly-needed addition). Added `QuestionSeeder` mirroring the mockup's 6 sample quiz
> questions, tied to real categories. `QuizSession` Livewire component rewritten from
> Alpine-only/hardcoded to fully server-driven: mode selection queries real `Question`
> rows by type, submit logs a real `Attempt` and calls `MasteryService` for
> multiple-choice/fill-blank types. **Design call:** code-type questions are self-graded
> (reference-answer reveal only, matching the mockup's neutral treatment) — no
> Attempt/mastery change, though they still count toward the session's
> `completed_count`. `QuizSession::today()` provides the daily-quota row (also now
> wired into Dashboard's quota card); note it uses `whereDate()` rather than
> `firstOrCreate(['date' => ...])`, since the `date` cast persists a full
> `Y-m-d H:i:s` timestamp and an exact-string match would create a new row on every
> call (caught by a failing test, now fixed). Dashboard's stat row (streak/pool/avg
> recall) and category-mastery grid remain hardcoded — out of scope for this phase.
> 9 new tests for the quiz flow. 28/28 tests passing overall. Phase 4 is done: added
> `import_batches` migration (`source_type`, `category_id` nullable FK, `raw_content`,
> `status`, `error_message` — the latter two are additions beyond the original §3 list,
> needed for job-failure reporting; `category_id` lives on the batch rather than being
> re-picked per question since one submission is parsed as one unit) plus a follow-up
> migration adding the deferred `questions.import_batch_id` foreign key, exactly as
> flagged in the Phase 2 note. `ImportStatus` enum (`uploaded → parsed → ai_converted →
> queued → imported`, plus a `failed` exit state — another deviation, added because a
> queued job needs somewhere to land on error). **Design call (per your explicit
> choice):** the AI parsing step is a stubbed `NaiveLineParser` (splits raw text into
> lines, blanks the last word of each to make a fill-blank question) behind a
> `QuestionParserContract` interface bound in `AppServiceProvider` — swapping in a real
> AI provider later is a one-line binding change, no pipeline/job rewrite needed.
> `ImportPipelineService` enforces the status transition table (guards against skipping
> or re-entering a terminal state) and turns parser candidates into real `Question`
> rows. `ParseImportBatch` is a queued job (`sync` in tests per `phpunit.xml`,
> `database` driver in `.env` — a worker must be running for real async processing, not
> yet part of this build). The `ImportPage` Livewire component keeps the Stage 2
> scaffold's single-route, Alpine-tab-switching layout (still one `/import` route, not
> the two-route `/import/notes` + `/import/logs` split in §4 — matching the
> already-established scaffold deviation, not a new one) but now does a real category
> select, `.md` file upload (`WithFileUploads`, stored under `storage/app/private/imports`
> since Laravel 11+'s default local disk root moved from the plan's `storage/app/imports`),
> or session-log textarea, dispatches the job on submit, and renders live pipeline-stage
> badges plus the actual generated `Question` rows in the "Generated This Session" list.
> 10 new tests (pipeline transitions, job success/failure, Livewire upload/paste/
> validation flows). 38/38 tests passing overall. Phase 5 is done: `tips` migration
> (`category_id` nullable FK, `body`, `source_question_id` nullable FK to `questions`),
> `Tip` model, `TipSeeder` (seeds the mockup's original dispatch()/dispatchSync() card
> as the Laravel tip, plus one tip per other seeded category so Dashboard always has
> something to show). Dashboard's Did-You-Know card now pulls a real `Tip`: it prefers
> one from a category touched by today's `QuizSession` (via its `Attempts` →
> `Question` → `category_id`), falling back to any random tip before the day's first
> answer, and hiding the card entirely if no tips exist at all. **Design call:**
> `ImportPipelineService::importQuestions()` now spins a `Tip` off every generated
> question that has an `explanation` — cheap to create, and since Dashboard only ever
> shows one at random per category, duplicate tips across repeated imports aren't a
> problem worth guarding against. 4 new tests (Dashboard tip-selection fallback/
> preference/empty-state, import-spun tip creation). 42/42 tests passing overall.
> Phase 6 is done: `QuestionBrowser` rewritten from Alpine-only/hardcoded to real
> queries — a debounced prompt search, a category `<select>`, and the existing
> difficulty tabs (counts now computed per filter scope, not hardcoded) all combine
> via the same base query. Editing is inline-per-row rather than a modal (no modal
> pattern existed anywhere else in the app yet, and a full-page component swap seemed
> heavier than warranted for what the plan scopes as "corrections"): prompt, answer,
> explanation, difficulty, and category are editable, plus a line-per-option textarea
> for `multiple_choice` questions specifically. **Design call:** `type` itself isn't
> editable, since changing it would silently invalidate the existing `answer`/
> `options_json` shape — out of scope for a correction tool. Delete uses Livewire's
> `wire:confirm` for a native confirmation prompt rather than a custom dialog (no
> modal/dialog component exists yet to reuse) and relies on the `attempts` table's
> existing `cascadeOnDelete()` FK to clean up attempt history. 7 new tests (listing +
> tab counts, search filter, difficulty filter, category filter, update, validation,
> delete). 49/49 tests passing overall.
> Next: Phase 7 (Documents & export — JSON pool export/import, doubles as backup).

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
