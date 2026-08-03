# Structured Markdown — Quiz Import Format

This is the format the **Structured Markdown** tab on the Import & Logs page
reads. No AI runs inside the app for this tab — parsing is deterministic, so
nothing here costs an API call or depends on `AI_IMPORT_ENABLED`.

## The rules

- Every question starts with a line beginning `Q:`.
- What follows decides the type automatically — nothing needs to be tagged:
  - Lettered options (`a)`, `b)`, `c)` … or `A.`, `B.`, `C.` …) plus an
    `Answer: <letter>` line → **Multiple Choice**
  - A fenced code block, with no lettered options → **Code**
  - A plain `Answer: <text>` line — no fence, no options → **Fill in the Blank**
- Optional on any question: `Explanation: <text>` and `Difficulty: easy|medium|hard`
  (defaults to `medium` if left out or misspelled).
- Blank lines and `---` between questions are just for readability — the
  parser ignores them. It splits on `Q:` lines, not on spacing.
- A line that looks like `Q:` *inside* a fenced code block does not start a
  new question — fences are respected.
- If something's off (say, an `Answer:` letter that doesn't match any listed
  option), the question still comes through to the review screen rather than
  vanishing — fix it there before saving.

---

## Multiple Choice

```
Q: What does PSR-4 define?
a) Autoloading standard
b) Coding style guide
c) Testing framework
d) Deployment process
Answer: a
Explanation: PSR-4 maps namespaces to file paths, which is what lets Composer autoload classes without a manifest.
Difficulty: easy
```

## Fill in the Blank

```
Q: The ____ directive escapes output in Blade.
Answer: {{ }}
Explanation: {!! !!} skips escaping and should only be used for markup you trust.
Difficulty: medium
```

## Code

````
Q: Write a function that reverses a string.
```php
function reverse(string $s): string {
    return strrev($s);
}
```
Difficulty: hard
````

---

## The prompt

Paste this into any AI chat (ChatGPT, Claude, whatever you've already got),
followed by your raw notes, session log, or `.md` file. Save what comes back
as a `.md` file and drop it into the Structured Markdown tab — this costs
nothing on the app's own AI, since that tab never calls out anywhere.

```
You convert raw notes into quiz questions in a specific plain-text format.
Read the notes below and output ONLY questions in this exact grammar — no
commentary before or after, no wrapping the whole thing in a code fence.

Every question starts with a line beginning "Q:". What follows decides its
type — do not add any other tag or label for the type:

- Multiple choice: 3-5 lettered options (a), b), c) ...), one of which is
  correct, followed by "Answer: <letter>". Wrong options should be plausible
  confusions a learner might actually make, not obviously wrong filler.
- Fill in the blank: the prompt contains "____" where the answer goes,
  followed by a plain "Answer: <text>" line with no code fence and no
  lettered options.
- Code: the prompt asks for a short snippet or command, followed by a fenced
  code block containing a reference answer (not a rubric or explanation).

On every question, optionally add:
- "Explanation: <one or two sentences>" — the "why", worth knowing after
  answering, not a restatement of the question.
- "Difficulty: easy" | "medium" | "hard" — how hard the concept is to recall,
  not how long the question is.

Separate questions with a blank line. Cover what's actually in the notes —
don't pad with trivial questions to hit a count, don't invent facts the
notes don't support, and skip anything too thin or vague to make a fair
question from. Produce a natural mix of the three types based on what suits
each piece of content — a definition might fit fill-in-the-blank, a
comparison might fit multiple choice, a task might fit code.

Here are the notes:

[PASTE YOUR NOTES HERE]
```
