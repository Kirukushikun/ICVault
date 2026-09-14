<div class="lab-project">
<!-- {{--
    Lab Vault project: Scratch Notes.

    Reference implementation for the localStorage-CRUD pattern new Lab
    projects should follow: a self-contained page — markup, styles and script
    together — that reads and writes window.localStorage directly, with no
    server round-trip. Everything is wrapped in @ verbatim so Blade leaves the
    CSS at-rules and JS template syntax alone.

    Rendered full-bleed by layouts/canvas.blade.php; registered in
    App\Tools\Lab\ProjectLibrary.

    To graduate this to real persistence: add a Model + migration under
    app/Tools/Lab, swap the four localStorage calls below (load/save/delete)
    for fetch() calls against a couple of routes, and flip this project's
    `storage` entry in ProjectLibrary to 'database'. The registry, the route,
    and the index card need no changes.
--}} -->

@verbatim
<style>
:root{
  /* ICVault surface tokens, matched to the other full-bleed tools so a lab
     project reads as part of the platform, not a separate document. */
  --bg:#1A1A1D;
  --panel:#222226;
  --panel-lift:#27272C;
  --line:rgba(255,255,255,.08);
  --ink:#ffffff;
  --ink-dim:rgba(255,255,255,.45);
  --ink-faint:rgba(255,255,255,.28);
  --accent:#7c5cfc;
  --accent-soft:rgba(124,92,252,.14);
  --radius:16px;
}
.lab-project *{box-sizing:border-box;margin:0;padding:0}
.lab-project{
  padding:clamp(16px,3vw,40px);
  background:var(--bg);
  color:var(--ink);
  font-family:'Roboto',ui-sans-serif,system-ui,sans-serif;
  line-height:1.5;
  min-height:calc(100vh - 57px);
}
.wrap{max-width:820px;margin:0 auto}

.eyebrow{
  font-family:'JetBrains Mono',monospace;font-size:12px;letter-spacing:.18em;
  color:var(--accent);text-transform:uppercase;margin-bottom:10px;
}
h1{
  font-family:'League Spartan','JetBrains Mono',sans-serif;font-weight:700;
  font-size:clamp(24px,4vw,34px);letter-spacing:.02em;margin-bottom:6px;
}
.sub{color:var(--ink-dim);font-size:13px;margin-bottom:28px}
.sub b{color:var(--ink)}

form{display:flex;gap:10px;margin-bottom:20px}
textarea{
  flex:1;resize:vertical;min-height:52px;background:var(--panel);
  border:1px solid var(--line);border-radius:12px;padding:12px 14px;
  color:var(--ink);font:inherit;font-size:13.5px;
}
textarea:focus{outline:none;border-color:var(--accent)}
button{
  background:var(--accent);color:#fff;border:none;border-radius:12px;
  padding:0 20px;font-weight:600;font-size:13px;cursor:pointer;
  transition:filter .15s;
}
button:hover{filter:brightness(1.1)}
button:disabled{opacity:.4;cursor:not-allowed}

.empty{
  text-align:center;padding:48px 20px;color:var(--ink-faint);
  border:1px dashed var(--line);border-radius:var(--radius);font-size:13px;
}
.note{
  background:var(--panel);border:1px solid var(--line);border-radius:var(--radius);
  padding:16px 18px;margin-bottom:10px;display:flex;gap:14px;align-items:flex-start;
}
.note .body{flex:1;font-size:13.5px;white-space:pre-wrap;word-break:break-word}
.note .meta{font-size:10.5px;color:var(--ink-faint);margin-top:8px;font-family:'JetBrains Mono',monospace}
.note button.del{
  background:transparent;color:var(--ink-faint);padding:4px 8px;font-size:16px;
  line-height:1;border-radius:8px;
}
.note button.del:hover{background:var(--panel-lift);color:#fff}
</style>

<div class="wrap">
  <div class="eyebrow">Lab 01 · localStorage</div>
  <h1>Scratch Notes</h1>
  <div class="sub">Everything below lives in <b>this browser only</b> — clear site data and it's gone.</div>

  <form id="note-form">
    <textarea id="note-input" placeholder="Jot something down…" required></textarea>
    <button type="submit">Add</button>
  </form>

  <div id="note-list"></div>
</div>

<script>
(function(){
  const STORAGE_KEY = 'lab.scratch-notes.v1';

  function load(){
    try { return JSON.parse(localStorage.getItem(STORAGE_KEY)) || []; }
    catch (e) { return []; }
  }
  function save(notes){
    localStorage.setItem(STORAGE_KEY, JSON.stringify(notes));
  }

  const list = document.getElementById('note-list');
  const form = document.getElementById('note-form');
  const input = document.getElementById('note-input');

  function render(){
    const notes = load();

    if (notes.length === 0) {
      list.innerHTML = '<div class="empty">No notes yet — add your first one above.</div>';
      return;
    }

    list.innerHTML = notes
      .slice()
      .reverse()
      .map(n => `
        <div class="note" data-id="${n.id}">
          <div class="body">${escapeHtml(n.body)}</div>
          <button class="del" title="Delete" type="button">&times;</button>
        </div>
      `.replace(
        '<div class="body">' + escapeHtml(n.body) + '</div>',
        '<div><div class="body">' + escapeHtml(n.body) + '</div><div class="meta">' + new Date(n.at).toLocaleString() + '</div></div>'
      ))
      .join('');
  }

  function escapeHtml(str){
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }

  form.addEventListener('submit', function(e){
    e.preventDefault();
    const body = input.value.trim();
    if (!body) return;

    const notes = load();
    notes.push({ id: crypto.randomUUID(), body, at: Date.now() });
    save(notes);

    input.value = '';
    render();
  });

  list.addEventListener('click', function(e){
    const del = e.target.closest('button.del');
    if (!del) return;

    const id = del.closest('.note').dataset.id;
    save(load().filter(n => n.id !== id));
    render();
  });

  render();
})();
</script>
@endverbatim
</div>
