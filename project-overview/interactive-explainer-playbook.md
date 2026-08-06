# Interactive Explainer — Build Playbook

A field guide for building **interactive explainers**: single-file, click-driven
web pages that teach a concept by *animating what each step does* to a system's
state. Distilled from building the "10 Git Commands" explainer so the same quality
is repeatable for new topics (Docker, Linux, HTTP, databases, etc.).

> **What to call these:** *interactive explainer* (best general term), *animated
> diagram*, or *explorable explanation* (the insider term for the genre). Not
> "conceptualizer."

---

## 0. Status — what's shipped

Five field guides are built and live in ICVault's **Visualizer** tool
(`app/Tools/Visualizer`), each its own Blade view under
`resources/views/tools/visualizer/guides/`, indexed by `GuideLibrary.php`:

| # | Guide | State model | Steps | Notable technique proven here |
|---|---|---|---|---|
| 01 | Git — 10 Commands | Working tree → Staging → Local repo (commit graph) → Remote | 10 | The original build — growing edges, FLIP file transfers, `rideTo` HEAD marker, chain transitions |
| 02 | Filesystem — 9 Commands | Terminal + file tree + current-directory marker | 9 | Position marker walking a static tree |
| 03 | Docker — 10 Commands | Dockerfile → Image → Container(s) → Registry | 10 | Multi-container state (several live objects at once, not just one) |
| 04 | HTTP Request Lifecycle | Client → DNS → Server → Response (headers/body) | 8 | Free-flight packet (`position:fixed` + `getScreenCTM`), plus a picker for "what goes wrong" at each hop |
| 05 | SQL — 10 Statements | Tables → query → result set (rows kept/dropped/grouped/deleted) | 10 | Full chain-transition engine (§6) — the first guide built *with* incremental deltas from day one, not retrofitted |

Each guide went through the same arc: static layout → one shared renderer → one
command animated end-to-end and confirmed → the rest filled in → chain/FLIP
polish last. That build order (§7) held for all five and is no longer a
hypothesis — it's the proven default.

**What changed from the original single-file-artifact plan:** these ended up as
Blade views inside a real Livewire tool rather than standalone HTML files handed
over one at a time — `GuideLibrary.php` is a hand-authored index (deliberately
*not* database-backed; see its own docblock) mapping a slug to a view, an accent
color, and a step count, with the platform's own header/nav wrapping each guide.
The single-file engine described below still describes each guide's *internal*
architecture (one big self-contained script per Blade file) — it's the outer
packaging that moved from "here's an .html file" to "here's a route in the app."

**Confirmed still true after 5 builds**, not just theory:
- The `seqToken` cancellation pattern (§2) is in every guide — grep for it and
  it's there in Git, and the SQL guide's chain engine leans on it directly to
  know when a delta render has been superseded mid-flight.
- The stroke-dashoffset growing-edge gotcha (§3a) — attributes not CSS
  transitions — is exactly what shipped in the Git guide's edge-growth code.
- FLIP (§3c) is what the Git guide's persistent file registry actually runs on
  (`fileReg` + `getBoundingClientRect` before/after).
- `prefers-reduced-motion` (§4) is honored per-guide, not just centrally — each
  guide has its own `@media(prefers-reduced-motion:reduce)` block and, in SQL's
  case, also checks it in JS before deciding whether to run a `pre` step at all.
- Numbering the guides "Developer Field Guide 01–05" (with a matching accent
  color and real vendor logo per topic — Git orange, Ubuntu orange, Docker blue,
  a purple for HTTP, green for SQL) turned out to matter enough to be worth a
  dedicated commit (`fix: number the Git guide as field guide 01, from a single
  source`) — treat the numbering/masthead as part of the deliverable, not an
  afterthought.

**One real addition beyond the original playbook — §6 chain transitions turned
out to need per-statement-type pacing.** The SQL guide's commit history
(`chain the SQL steps so linked ones carry their state forward`, then
`give SQL its mark, and let INSERT and UPDATE take their time`) shows the chain
engine wasn't just "animate the delta" — a `SELECT` filtering rows and an
`INSERT`/`UPDATE` mutating them don't read well at the same speed. Worth adding
to §6: **when two adjacent chained steps have meaningfully different semantics
(read vs. write), give the write its own slower timing rather than reusing the
read's pace** — it's the difference between a chain transition looking
mechanical versus looking like it's actually showing you something happening.

---

## 1. The one idea that makes these work

**Pick a STATE MODEL first.** Everything else follows from it.

A good explainer animates *change to a system's state*. Before writing any code,
name the 3–5 "zones" or objects that change as the user steps through commands.
That's the visual spine.

| Topic | State model (the zones that change) | Status |
|---|---|---|
| Git | Working tree → Staging → Local repo (commit graph) → Remote | ✅ shipped |
| Docker | Dockerfile → Image → Container(s) → Registry | ✅ shipped |
| Linux fs | Terminal + file tree + current-directory marker + process list | ✅ shipped (no process list — 9 commands stayed scoped to navigation) |
| HTTP request | Client → DNS → Server → Response, with headers/body | ✅ shipped |
| SQL | Tables → query → result set (rows highlighting in/out) | ✅ shipped |

If you can't name the state model, the explainer will feel like disconnected
slides. If you *can*, each command becomes "what changes in these zones," and the
animations write themselves.

**Rule of thumb:** 8–12 commands/steps max. Each needs its own animation; 30 gets
shallow. (Held across all five: 8, 9, 10, 10, 10.)

---

## 2. Architecture (the reusable engine)

Single self-contained script per guide (packaged as a Blade view in the Visualizer
tool, not a bare `.html` file — see §0). No build step. No external JS deps
required (fonts via Google Fonts is fine). Structure:

```
COMMANDS = [ { id, label, description, lit:[zones], render() } , ... ]
         ↓  (click / arrow keys)
select(i)  →  updates text UI  →  either:
   • incremental transition (if adjacent in a "chain")   → advanced, see §6
   • c.render()  →  drives zone + graph animations
```

### Data-driven commands
Each command is **one object** with a `render()` that declares the end state.
Adding a command = pushing one object. Keep `render()` declarative: "files here,
graph has N nodes, this zone lit." Don't hand-animate per command; call shared
primitives.

### A cancellation token is essential
Users click fast. Every animation must be abortable. Use a module-level
`seqToken` that increments on each new command. Every `setTimeout`/rAF loop
captures the token and bails if it's stale:

```js
let seqToken = 0;
function select(i){ seqToken++; /* ... */ }

const myToken = seqToken;
const step = (fn, delay) => setTimeout(() => { if (myToken === seqToken) fn(); }, delay);
```
This single pattern prevents 90% of "animations overlapping / glitching" bugs.
**Confirmed in production across all five guides** — the SQL guide's chain
engine additionally stores a per-handle `_token` (`h._token`) so a delta render
in flight can tell it's been superseded even mid-transition, not just at the
start of a new one.

---

## 3. Animation primitives that worked

These are the workhorses. Reuse them verbatim across topics.

### (a) SVG line that "grows" — the signature move
Draw a path, then animate `stroke-dashoffset` from full length → 0.

**CRITICAL GOTCHA (cost us hours):** do NOT use a CSS `transition` on
`stroke-dashoffset`. The element paints its *finished* state for one frame, then
jumps to hidden, then transitions — reading as "finish → start → finish" flash.
Instead:
1. Set `stroke-dasharray` and `stroke-dashoffset` as **attributes** (paint
   immediately, no race).
2. Grow with a **one-shot keyframe**, not a transition.
3. Pad the hidden offset by `+2` so near-flat curves don't show a sliver.

```js
function mkEdge(g, d, cls){
  const p = document.createElementNS(NS,"path");
  p.setAttribute("d", d); p.setAttribute("class","edge "+cls);
  g.appendChild(p);
  const len = p.getTotalLength();
  const hide = len + 2;                        // pad so near-flat curves fully hide
  p.setAttribute("stroke-dasharray", hide);
  p.setAttribute("stroke-dashoffset", hide);
  p._len = hide;
  return p;
}
function growEdge(p){ p.style.setProperty("--len", p._len); p.classList.add("growing"); }
```
```css
.edge.growing{ animation: grow .7s cubic-bezier(.65,0,.35,1) forwards; }
@keyframes grow{ from{ stroke-dashoffset: var(--len);} to{ stroke-dashoffset: 0;} }
```

### (b) Staggered reveal (nodes/labels appear in sequence)
Reveal with keyframe animations that ALWAYS start hidden and play forward once —
again, not transitions (same flash problem). Class `.node-in` → add `.shown`,
`.shown` runs a `forwards` keyframe. Sequence with the token'd `step()` helper so
root → edge grows → next node → its label, staggered by ~260ms.

### (c) FLIP transfer — move an element between zones smoothly
For "files slide from working tree → staging," keep elements **persistent** in a
registry keyed by name (don't wipe & rebuild each render). Then use **FLIP**
(First-Last-Invert-Play):

```
1. FIRST : record each element's rect (getBoundingClientRect) BEFORE the change
2. LAST  : move it to the new zone in the DOM (append to new parent)
3. INVERT: transform it back to its old screen position instantly
4. PLAY  : next frame, release the transform — it eases to the new spot
```
New elements fade in; removed ones fade out. This one engine handles *every*
transfer (stage, commit-clears-staging, stash-to-shelf) once built. **Shipped**
as the Git guide's `fileReg` registry — confirmed the "build once, reuse for
every transfer" bet paid off; nothing needed a bespoke transfer animation.

### (d) Flying pulse / traveling packet along a path
- **Free flight (A→B across the layout):** an HTML dot with `position:fixed`,
  animate `left/top`. Map SVG coords → screen pixels with
  `svg.getScreenCTM()` + `createSVGPoint().matrixTransform(ctm)` — handles
  viewBox scaling & letterboxing automatically (don't hand-compute). **Shipped**
  in the HTTP Lifecycle guide, where the packet genuinely travels client → DNS →
  server → back, across a layout that isn't one single SVG.
- **Follow a curve:** an SVG `<circle>` appended to the graph, tween along the
  path with `edge.getPointAtLength(total * eased(p))`. Rides the true curve.

### (e) Moving a label/marker on SVG (e.g. HEAD tag riding to a new commit)
**HARD-WON GOTCHA:** CSS transforms OVERRIDE SVG transform attributes, and a
reveal keyframe ending in `transform:none` will silently wipe your position.
Fixes:
1. Give the moving element an **opacity-only** reveal (no transform keyframe).
2. Animate its position by tweening the `transform` **attribute** in JS
   (`setAttribute("transform", "translate(x y)")`) via rAF — NOT a CSS transition.
3. Keep a known "home" position (set rect/text x/y) and express all movement as
   `translate` offsets from home, so offsets compose predictably.

```js
function rideTo(grp, tx, ty){                  // tx,ty = offset from home
  const cur = parseTranslate(grp.getAttribute("transform"));
  const t0 = performance.now(), tok = seqToken, dur = 500;
  const ease = p => 1 - Math.pow(1-p, 3);      // easeOutCubic
  (function tick(now){
    if (seqToken !== tok) return;              // cancelled
    const p = Math.min(1,(now-t0)/dur), e = ease(p);
    grp.setAttribute("transform",
      `translate(${cur.x+(tx-cur.x)*e} ${cur.y+(ty-cur.y)*e})`);
    if (p<1) requestAnimationFrame(tick);
  })(performance.now());
}
```
This is what rides the Git guide's HEAD tag between commits.

---

## 4. Easing & timing cheatsheet
- **Line grows / packets:** `cubic-bezier(.65,0,.35,1)` (smooth in-out), ~0.6–0.7s.
- **Nodes/labels popping in:** `cubic-bezier(.34,1.56,.64,1)` (springy overshoot),
  ~0.45–0.55s. Use sparingly; overshoot everywhere looks jittery.
- **Marker rides:** easeOutCubic `1-(1-p)^3`, ~0.5s.
- **Stagger:** 60–90ms between sibling items; 260–340ms between sequence phases.
- **Chained write steps (INSERT/UPDATE) vs. read steps (SELECT):** don't reuse
  the read's pace — give the write its own, slightly slower timing so the
  mutation reads as *happening* rather than just *appearing*. (New — learned
  from the SQL guide; see §0 and §6.)
- Always honor `@media (prefers-reduced-motion: reduce)` — reveal everything
  instantly, no motion. Confirmed worth checking in JS too, not just CSS, when
  a step's animation is conditionally skippable (SQL guide checks
  `matchMedia(...).matches` before deciding whether to run a `pre` phase).

---

## 5. Visual design notes (GitHub-dark palette used here)
```
--bg:#0d1117  --panel:#161b22  --line:#30363d  --ink:#e6edf3  --ink-dim:#8b949e
accent examples: amber #ff8c42 / green #3fb950 / blue #58a6ff / purple #bc8cff
```
- Dark canvas + one or two accent colors reads as "developer tool."
- Monospace (JetBrains Mono) for code/labels; a clean sans (Space Grotesk) for prose.
- Light a zone (border + soft inset glow) only **when the animation reaches it**,
  not on click — makes cause/effect legible. Clear it when leaving the command.
- Minimal chrome. Let the animation be the content.
- **Per-guide accent + real vendor logo, shipped:** each guide in `GuideLibrary.php`
  carries its own `accent` hex and `logo` (Git orange `#F05133` + real Git mark,
  Ubuntu orange `#E95420`, Docker blue `#0DB7ED`, a purple `#9D8CFF` for HTTP,
  green `#3FCF8E` for SQL) rather than reusing one palette for every topic —
  confirmed this reads better than a single shared accent across all five.

---

## 6. ADVANCED: incremental "chain" transitions (no reset between related steps)
For a run of related commands that build on one another (Git:
`commit → branch → switch → merge`; SQL: a run of clauses against the same
table), rebuilding the whole graph each click feels choppy. Instead, animate only
the DELTA when moving between **adjacent** steps, and full-rebuild only on
non-adjacent jumps.

Pattern:
- Keep `graphState = { step, refs }` holding live element handles from the last
  draw of a chain command.
- In `select`, before rendering: `if (tryChainStep(prevVerb, newVerb)) return;`
  It succeeds only if both are in the chain AND adjacent AND state is fresh.
- Each incremental handler mutates just what changed (grow one branch line, ride
  HEAD one hop, born/remove one merge node). Forward *and* backward.
- Anything else → normal full `render()` (the reset path stays as the fallback).

Caveat: this roughly doubles graph complexity. Only worth it for a genuinely
sequential story.

**Shipped in full for the SQL guide** (`chainRender()` / `fullRender()`, chosen
per-step via `const chained = !revisiting && !!step.scene && step.scene===sceneOn`).
Two things learned from actually building the second chain engine, not just the
first:
- **Protect any pre-existing smooth transition by having it register chain state
  only *after* it finishes** — held true again; still the right guard.
- **Give the chain engine's own "is this actually a valid continuation" check a
  name and keep it boring** (`revisiting`, `step.scene===sceneOn`) — the SQL
  guide's version is closer to a small state machine than the "just compare
  adjacency" description above made it sound; budget for that when estimating a
  second chain engine, it's not a five-line diff off the first one.
- A cell in the result grid is tracked by its **parts**, not repainted whole, so
  a chained step can diff old vs. new cell content and animate only what
  changed — worth generalizing as its own primitive (call it "cell diffing")
  alongside FLIP and growing-edges next time a tabular (not graph) state model
  comes up.

---

## 7. Build order that worked (do it this way)
1. Static layout + all commands as clickable list (no animation yet).
2. The state zones as static boxes.
3. One shared graph/zone renderer driven by each command's `render()`.
4. Add the growing-line + staggered-reveal primitives. Get ONE command perfect.
5. Then go command-by-command. **Nail one, confirm with the user, move on.**
   Don't batch — each animation has a "is this how it really works?" question.
6. Layer polish last: transfers (FLIP), flying pulse, traveling packet, chain
   transitions.

**Confirmed across five builds, not just the first one** — same order held for
Filesystem, Docker, HTTP, and SQL. Nothing in five guides warranted a different
sequence.

**Correctness > flash.** The reference video we copied actually animated a Git
commit landing on the *old* node (wrong). Question the concept, not just the code:
a commit creates a NEW node; your changes live there, not in the parent. Getting
the mental model right is the whole point of an explainer.

---

## 8. Sandbox / tooling notes
- No browser in the build sandbox — can't screenshot. **Syntax-check** the script
  by extracting it and `new Function(stubbedGlobals + body)`; rely on the user's
  eyes for visual verification. Ask targeted questions ("does HEAD glide or pop?").
- Keep it one file so the user can open it anywhere with zero setup. In practice
  this became "one Blade view per guide, wrapped by the platform's own layout" —
  still zero setup for the *reader* (open the route, no build step), even though
  the guide is no longer a standalone file the way the original artifacts were.
- `localStorage`/`sessionStorage` do NOT work in the artifact sandbox — keep all
  state in JS memory. Still true; not applicable once a guide lives in the real
  app, but keep it in mind if a guide is ever prototyped as a throwaway artifact
  before being ported in.

---

## 9. Ready-to-use starter prompt

> Build an interactive explainer as a single self-contained HTML file that teaches
> the **[N] essential [TOPIC] commands/concepts** every [AUDIENCE] should know.
>
> **State model (the zones that animate):** [e.g. Dockerfile → Image → Container →
> Registry]. Each command animates what it does to these zones.
>
> **Layout:** a clickable list of commands on the left; a "stage" panel on the
> right that animates the state change. Arrow-key navigation. [BRAND] palette on a
> dark background, monospace for code. Give it a masthead: a field-guide number,
> the real vendor logo if one exists, and its own accent color — don't reuse
> another guide's palette.
>
> **Animation quality bar:** lines should *grow* (animate stroke-dashoffset via a
> one-shot keyframe, never a CSS transition — avoid the finish→start flash);
> nodes/labels reveal staggered; elements that move between zones should slide
> (FLIP), not pop. Every animation must be cancel-safe via a shared seqToken.
> Honor prefers-reduced-motion, including before starting any skippable pre-step,
> not just in CSS.
>
> **Extensibility:** each command is one object in a COMMANDS array with a
> declarative render(). Build it so I can add more commands easily.
>
> If a run of commands is genuinely sequential (each one builds on the last),
> plan for a chain-transition engine (see §6) rather than a full rebuild per
> step — and if the chain mixes reads and writes, give writes their own,
> slightly slower timing.
>
> Do it command-by-command; get one perfect before moving on, and check the
> concept is *actually accurate*, not just visually plausible.

Fill the brackets, hand it over, and drive it one command at a time — same as we
built the Git one (and, since, Filesystem, Docker, HTTP, and SQL).
