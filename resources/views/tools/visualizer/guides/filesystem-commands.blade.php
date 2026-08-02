<div class="visualizer-guide">
{{--
    Concept Visualizer guide, ported from project-overview/2 filesystem-commands.html.

    Guides are hand-authored and self-contained. Only <style> and <script>
    sit inside verbatim — the markup is plain Blade so the masthead reads
    its labels from App\Tools\Visualizer\GuideLibrary.

    Rendered full-bleed by layouts/canvas.blade.php.
--}}

@verbatim
<style>
:root{
  --panel-lift:#27272C;
  --inset:#161617;
  --bg:#1A1A1D;              /* deep aubergine-black */
  --panel:#222226;          /* raised aubergine surface */
  --panel-2:#1c1c20;
  --line:rgba(255,255,255,.08);           /* borders */
  --line-soft:rgba(255,255,255,.05);
  --ink:#ffffff;            /* primary */
  --ink-dim:rgba(255,255,255,.45);        /* muted */
  --ink-faint:rgba(255,255,255,.28);
  --orange:#E95420;         /* ubuntu orange */
  --orange-hot:#ff6a30;
  --orange-soft:rgba(233,84,32,.14);
  --aubergine:#9b5fcf;      /* ubuntu aubergine */
  --gold:#e8b04b;
  --folder:#e8a94b;         /* folder icon warmth */
  --file:#9fb6c9;           /* file icon cool */
  --green:#2e9c6b;
  --radius:16px;
}
/* Scoped: an unlayered `*` reset would out-rank Tailwind's utility
   layer and strip the platform header's spacing. */
.visualizer-guide *{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{
  background:
    radial-gradient(1100px 560px at 82% -8%, rgba(233,84,32,.10), transparent 60%),
    radial-gradient(900px 500px at -10% 110%, rgba(195,7,63,.06), transparent 55%),
    url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='28' height='28'%3E%3Ccircle cx='14' cy='14' r='1' fill='white' fill-opacity='0.1'/%3E%3C/svg%3E"),
    var(--bg);
  background-size:auto,auto,28px 28px,auto;
  background-attachment:fixed;
  color:var(--ink);
  font-family:'Roboto',ui-sans-serif,system-ui,sans-serif;
  line-height:1.5;
  min-height:100vh;
}
/* Padding lives here, not on body, so the platform header stays flush. */
.visualizer-guide{padding:clamp(16px,3vw,40px)}
.wrap{max-width:1180px;margin:0 auto}

/* ---------- Header ---------- */
.eyebrow{display:flex;justify-content:space-between;align-items:center;
  font-family:'JetBrains Mono',monospace;font-size:12px;letter-spacing:.18em;
  color:var(--orange);text-transform:uppercase;margin-bottom:18px}
.eyebrow .right{color:var(--ink-faint)}
.masthead{display:flex;align-items:center;gap:18px;flex-wrap:wrap}
.glyph{width:52px;height:52px;flex:none;border-radius:50%;
  background:linear-gradient(135deg,var(--orange),var(--aubergine));
  display:grid;place-items:center;box-shadow:0 6px 24px rgba(233,84,32,.35)}
.glyph svg{width:30px;height:30px}
h1{font-family:'League Spartan','JetBrains Mono',sans-serif;font-weight:700;
  font-size:clamp(28px,5.5vw,48px);line-height:1;letter-spacing:-.01em}
h1 .accent{color:var(--orange)}
.sub{font-family:'JetBrains Mono',monospace;letter-spacing:.26em;text-transform:uppercase;
  color:var(--ink-dim);font-size:clamp(11px,2vw,14px);margin-top:8px}
.rule{height:2px;background:linear-gradient(90deg,var(--orange),transparent);margin:22px 0 26px;border-radius:2px}

/* ---------- Layout ---------- */
.grid{display:grid;grid-template-columns:minmax(300px,1fr) minmax(340px,1.05fr);gap:24px;align-items:start}
@media(max-width:860px){.grid{grid-template-columns:1fr}}

/* ---------- Command list ---------- */
.list{display:flex;flex-direction:column;gap:8px}
.cmd{position:relative;text-align:left;width:100%;cursor:pointer;
  background:var(--panel);border:1px solid var(--line-soft);border-radius:12px;
  padding:14px 16px 14px 18px;color:var(--ink);transition:border-color .2s,background .2s;
  display:grid;grid-template-columns:auto 1fr;gap:14px;align-items:center;font-family:inherit}
.cmd:hover{border-color:var(--line);background:var(--panel-2)}
.cmd:focus-visible{outline:2px solid var(--orange);outline-offset:2px}
.cmd.active{border-color:var(--orange);background:linear-gradient(90deg,var(--orange-soft),transparent 70%)}
.cmd.active::before{content:"";position:absolute;left:0;top:8px;bottom:8px;width:3px;border-radius:3px;
  background:var(--orange);box-shadow:0 0 12px var(--orange-hot)}
.badge{font-family:'JetBrains Mono',monospace;font-weight:700;font-size:11px;color:var(--orange);
  border:1px solid var(--line);border-radius:6px;padding:3px 6px;min-width:26px;text-align:center;background:var(--inset)}
.cmd.active .badge{border-color:var(--orange);color:#1A1A1D;background:var(--orange-hot)}
.cmd-main{display:flex;flex-direction:column;gap:3px;min-width:0}
.cmd-code{font-family:'JetBrains Mono',monospace;font-weight:700;font-size:16px}
.cmd-code .kw{color:var(--ink)}
.cmd.active .cmd-code .kw{color:var(--orange)}
.cmd-code .arg{color:var(--ink-faint);font-weight:500}
.cmd-desc{font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:var(--ink-faint)}

/* ---------- Stage ---------- */
.stage{position:sticky;top:24px;background:var(--panel);border:1px solid var(--line);
  border-radius:var(--radius);overflow:hidden}
.stage-inner{padding:16px;display:flex;flex-direction:column;gap:14px}

/* breadcrumb / current path */
.pathbar{display:flex;align-items:center;gap:8px;border:1px solid var(--line);border-radius:10px;
  background:var(--inset);padding:10px 14px;font-family:'JetBrains Mono',monospace}
.pathbar .pwd-label{font-size:9px;letter-spacing:.16em;text-transform:uppercase;color:var(--ink-faint)}
.pathbar .pwd{font-size:14px;color:var(--orange);font-weight:700}
.pathbar .pwd .seg{color:var(--ink-dim);font-weight:500}
.pathbar .pwd .seg.here{color:var(--orange);font-weight:700}

/* file tree — the signature element */
.tree-zone{border:1px solid var(--line);border-radius:10px;background:#160b14;padding:14px 12px;min-height:230px}
.tree-h{font-family:'JetBrains Mono',monospace;font-size:9px;letter-spacing:.16em;
  color:var(--ink-faint);text-transform:uppercase;margin-bottom:10px}
.tree{font-family:'JetBrains Mono',monospace;font-size:14px;line-height:1}
.node{display:flex;align-items:center;gap:8px;padding:5px 8px;border-radius:7px;position:relative;
  transition:background .3s,opacity .35s,transform .35s}
.node .indent{display:inline-block}
.node .twig{color:var(--ink-faint)}
.node .icon{width:16px;height:16px;flex:none}
.node .icon.folder path{fill:var(--folder)}
.node .icon.file path{fill:var(--file)}
.node .name{color:var(--ink)}
.node.dir .name{color:var(--folder);font-weight:500}
.node.here{background:var(--orange-soft);box-shadow:inset 0 0 0 1px rgba(233,84,32,.4)}
.node.here .marker{margin-left:auto;font-size:9px;letter-spacing:.12em;text-transform:uppercase;
  color:var(--orange);border:1px solid var(--orange);border-radius:5px;padding:2px 6px}
/* a folder acknowledging that you just entered it */
.node.entered{animation:enterFolder .7s ease}
@keyframes enterFolder{0%{background:transparent}30%{background:rgba(233,84,32,.28)}100%{background:var(--orange-soft)}}
/* floating marker that glides between rows during cd */
.glide-marker{position:fixed;pointer-events:none;z-index:70;
  font-family:'JetBrains Mono',monospace;font-size:9px;letter-spacing:.12em;text-transform:uppercase;
  color:var(--orange);border:1px solid var(--orange);border-radius:5px;padding:2px 6px;
  background:var(--panel);box-shadow:0 2px 10px rgba(233,84,32,.4)}
.glide-marker.moving{transition:left .5s cubic-bezier(.5,0,.2,1),top .5s cubic-bezier(.5,0,.2,1)}
/* prompt path flashing on change */
.pathbar.changed{animation:pathFlash .6s ease}
@keyframes pathFlash{0%{border-color:var(--line)}40%{border-color:var(--orange);box-shadow:0 0 14px rgba(233,84,32,.3)}100%{border-color:var(--line)}}
.node.dim{opacity:.4}
.node.highlight{background:rgba(232,176,75,.14)}
/* enter / leave / move animations */
.node.enter{animation:nodeIn .45s cubic-bezier(.34,1.56,.64,1)}
@keyframes nodeIn{0%{opacity:0;transform:translateX(-10px) scale(.9)}100%{opacity:1;transform:none}}
.node.leaving{animation:nodeOut .4s ease forwards}
@keyframes nodeOut{to{opacity:0;transform:translateX(10px) scale(.85);height:0;padding:0}}
.node.moving{transition:transform .5s cubic-bezier(.5,0,.2,1),background .3s}
.node.copied{animation:copyPulse .5s ease}
@keyframes copyPulse{0%{background:rgba(78,185,106,.25)}100%{background:transparent}}

/* terminal */
.terminal{border:1px solid var(--line);border-radius:10px;background:#0f0710;overflow:hidden}
.term-bar{display:flex;align-items:center;gap:7px;padding:10px 12px;border-bottom:1px solid var(--line);background:var(--inset)}
.dot{width:11px;height:11px;border-radius:50%}
.dot.r{background:#ff5f57}.dot.y{background:#febc2e}.dot.g{background:#28c840}
.term-label{margin-left:8px;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:.16em;
  color:var(--ink-faint);text-transform:uppercase}
.term-body{padding:12px 14px;min-height:80px;font-family:'JetBrains Mono',monospace;font-size:13px;line-height:1.7}
.term-line{white-space:pre-wrap;word-break:break-word}
.term-line .p{color:var(--green)}
.term-line .p .path{color:var(--orange)}
.term-line .out{color:var(--ink-dim)}
.caret{display:inline-block;width:8px;height:15px;background:var(--orange);vertical-align:-2px;
  margin-left:2px;animation:blink 1s steps(1) infinite}
@keyframes blink{50%{opacity:0}}

/* explanation */
.explain{border-top:1px solid var(--line);padding:16px;background:var(--panel-lift)}
.explain h3{font-family:'JetBrains Mono',monospace;font-size:13px;letter-spacing:.08em;
  color:var(--orange);text-transform:uppercase;margin-bottom:6px}
.explain p{font-size:14px;color:var(--ink);margin-bottom:10px}
.explain .flow{display:flex;align-items:center;gap:6px;flex-wrap:wrap;font-family:'JetBrains Mono',monospace;
  font-size:10px;letter-spacing:.1em;text-transform:uppercase;color:var(--ink-faint)}
.explain .flow .on{color:var(--orange)}

/* footer */
footer{margin-top:28px;border-top:1px solid var(--line);padding-top:16px;
  display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap}
.foot-flow{font-family:'JetBrains Mono',monospace;font-size:12px;letter-spacing:.12em;color:var(--ink-dim)}
.foot-flow b{color:var(--orange)}
.counter{font-family:'JetBrains Mono',monospace;font-size:13px;color:var(--orange);
  border:1px solid var(--line);border-radius:8px;padding:6px 12px}
.tagline{font-family:'JetBrains Mono',monospace;font-weight:700;color:var(--ink);
  font-size:clamp(14px,3vw,18px);margin-top:14px}

@media(prefers-reduced-motion:reduce){*{animation:none!important;transition:none!important}}
</style>
@endverbatim

<div class="wrap">

  <div class="eyebrow">
    <span>&gt;_ coding chops</span>
    <span class="right">{{ $guide['eyebrow'] }}</span>
  </div>

  <div class="masthead">
    <div class="glyph" aria-hidden="true">
      <svg viewBox="0 0 24 24" fill="none">
        <circle cx="12" cy="12" r="10" stroke="#ffffff" stroke-width="1.5"/>
        <circle cx="12" cy="4.5" r="1.8" fill="#ffffff"/>
        <circle cx="5" cy="16" r="1.8" fill="#ffffff"/>
        <circle cx="19" cy="16" r="1.8" fill="#ffffff"/>
        <circle cx="12" cy="12" r="2.2" fill="#ffffff"/>
      </svg>
    </div>
    <div>
      <h1>Navigating the <span class="accent">Filesystem</span></h1>
      <div class="sub">{{ $guide['subtitle'] }}</div>
    </div>
  </div>

  <div class="rule"></div>

  <div class="grid">
    <div class="list" id="list" role="tablist" aria-label="Filesystem commands"></div>

    <div class="stage">
      <div class="stage-inner">
        <div class="pathbar" id="pathbar">
          <span class="pwd-label">pwd</span>
          <span class="pwd" id="pwd"></span>
        </div>

        <div class="tree-zone">
          <div class="tree-h">~/  file tree</div>
          <div class="tree" id="tree"></div>
        </div>

        <div class="terminal">
          <div class="term-bar">
            <span class="dot r"></span><span class="dot y"></span><span class="dot g"></span>
            <span class="term-label">bash — user&#64;ubuntu</span>
          </div>
          <div class="term-body" id="termBody"></div>
        </div>
      </div>

      <div class="explain">
        <h3 id="exTitle">pwd</h3>
        <p id="exBody"></p>
        <div class="flow" id="exFlow"></div>
      </div>
    </div>
  </div>

  <footer>
    <div class="foot-flow">home <b>›</b> projects <b>›</b> docs <b>›</b> files</div>
    <div class="counter" id="counter">01 / 09</div>
  </footer>
  <div class="tagline">ONE TREE. NINE WAYS TO MOVE.</div>

</div>

@verbatim
<script>
/* ============================================================
   Navigating the filesystem — interactive explainer.
   State model: a live file TREE + a "you are here" marker
   (current working directory). Each command animates a change
   to the tree or the marker. Engine mirrors Git/Docker:
   data-driven commands + a seqToken cancellation guard.
   ============================================================ */

/* The tree is a nested structure. Each node: {name, type:'dir'|'file', children?} */
function baseTree(){
  return {
    name:"user", type:"dir", children:[
      { name:"projects", type:"dir", children:[
        { name:"app.js", type:"file" },
        { name:"readme.md", type:"file" },
      ]},
      { name:"docs", type:"dir", children:[] },
      { name:"notes.txt", type:"file" },
    ]
  };
}
const HOME_PREFIX = "/home";

let seqToken=0;
const step=(fn,delay,tok)=>setTimeout(()=>{ if(tok===seqToken) fn(); },delay);

/* ---------- icons ---------- */
const folderSvg=`<svg class="icon folder" viewBox="0 0 16 16"><path d="M1.5 3.5c0-.6.4-1 1-1h3l1.2 1.2H13c.6 0 1 .4 1 1v7c0 .6-.4 1-1 1H2.5c-.6 0-1-.4-1-1v-8Z"/></svg>`;
const fileSvg=`<svg class="icon file" viewBox="0 0 16 16"><path d="M3.5 1.5h6L13 5v9.5c0 .3-.2.5-.5.5h-9c-.3 0-.5-.2-.5-.5v-13c0-.3.2-.5.5-.5Z"/></svg>`;

/* ---------- render the tree ----------
   herePath = array of names describing current dir (e.g. ["user","projects"]).
   opts.highlightDir = name of dir whose children to highlight (for ls).       */
function renderTree(tree, herePath, opts={}){
  const host=document.getElementById("tree");
  host.innerHTML="";
  const rows=[];
  (function walk(node, depth, path){
    const here = arraysEqual(path.concat(node.name), herePath);
    rows.push({node, depth, here, path:path.concat(node.name)});
    if(node.children) node.children.forEach(ch=>walk(ch, depth+1, path.concat(node.name)));
  })(tree, 0, []);

  rows.forEach(r=>{
    const d=document.createElement("div");
    d.className="node "+(r.node.type==="dir"?"dir":"file")+(r.here?" here":"");
    d.dataset.key=r.path.join("/");
    const indent="&nbsp;&nbsp;".repeat(r.depth);
    const twig=r.depth>0?`<span class="twig">└─</span>`:"";
    d.innerHTML=`<span class="indent">${indent}</span>${twig}`+
      (r.node.type==="dir"?folderSvg:fileSvg)+
      `<span class="name">${r.node.name}${r.node.type==="dir"?"/":""}</span>`+
      (r.here?`<span class="marker">you are here</span>`:"");
    host.appendChild(d);
  });
  return rows;
}
function arraysEqual(a,b){ return a.length===b.length && a.every((x,i)=>x===b[i]); }

/* ---------- path bar ---------- */
function setPwd(herePath){
  const el=document.getElementById("pwd");
  // herePath like ["user","projects"] → /home/user/projects
  const segs=herePath.slice();
  const parts=[`<span class="seg">/home</span>`];
  segs.forEach((s,i)=>{
    const last=i===segs.length-1;
    parts.push(`<span class="seg ${last?"here":""}">/${s}</span>`);
  });
  el.innerHTML=parts.join("");
}

/* ---------- terminal ---------- */
function termLineEl(ln, herePath){
  const d=document.createElement("div");d.className="term-line";
  if(ln.cmd!==undefined){
    const p=herePath?herePath[herePath.length-1]:"user";
    d.innerHTML=`<span class="p">user@ubuntu:<span class="path">~${pathTail(herePath)}</span>$</span> ${ln.cmd}`;
  } else d.innerHTML=`<span class="out">${ln.out}</span>`;
  return d;
}
function pathTail(herePath){
  if(!herePath||herePath.length<=1) return "";
  return "/"+herePath.slice(1).join("/");
}
function caretEl(){ const c=document.createElement("span");c.className="caret";return c; }
function clearTerm(){ document.getElementById("termBody").innerHTML=""; }
function appendTerminal(ln, herePath){
  const host=document.getElementById("termBody");
  const caret=host.querySelector(".caret"); if(caret) caret.remove();
  host.appendChild(termLineEl(ln, herePath));
  host.appendChild(caretEl());
}
function typeCommand(cmd, herePath, done, tok){
  const host=document.getElementById("termBody");
  host.innerHTML="";
  const line=document.createElement("div");line.className="term-line";
  const promptWrap=document.createElement("span");promptWrap.className="p";
  promptWrap.innerHTML=`user@ubuntu:<span class="path">~${pathTail(herePath)}</span>$`;
  const text=document.createElement("span");
  line.appendChild(promptWrap);line.appendChild(document.createTextNode(" "));line.appendChild(text);
  line.appendChild(caretEl());
  host.appendChild(line);
  let i=0;const speed=40;
  (function tick(){
    if(tok!==seqToken) return;
    text.textContent=cmd.slice(0,i);
    if(i<=cmd.length){ i++; setTimeout(tick,speed); } else if(done) done();
  })();
}
function runSequence(cmd, herePath, effect, outputs, tok, opts={}){
  const afterType=opts.afterType||250, afterEffect=opts.afterEffect||0;
  typeCommand(cmd, herePath, ()=>{
    step(()=>{ if(effect) effect(); }, afterType, tok);
    (outputs||[]).forEach((o,k)=>step(()=>appendTerminal(o, herePath), afterType+afterEffect+150+k*140, tok));
  }, tok);
}

/* helpers to animate specific nodes after a render */
function nodeByKey(key){ return document.querySelector(`#tree .node[data-key="${key}"]`); }
function enterNode(key){ const n=nodeByKey(key); if(n) n.classList.add("enter"); }
function leaveNode(key, after){
  const n=nodeByKey(key); if(!n){ if(after) after(); return; }
  n.classList.add("leaving");
  setTimeout(()=>{ if(after) after(); }, 380);
}

/* cd: glide the "you are here" marker from the current dir row to the target
   dir row, then commit the new current directory (prompt + breadcrumb update).
   fromKey/toKey are node data-keys; herePathAfter is the new location.        */
function glideMarker(tree, fromKey, toKey, herePathAfter, tok){
  const fromRow=nodeByKey(fromKey), toRow=nodeByKey(toKey);
  if(!fromRow||!toRow){ commitLocation(tree, herePathAfter, toKey, tok); return; }
  // spawn a floating marker over the current row's marker position
  const fm=fromRow.querySelector(".marker");
  const startRect=(fm||fromRow).getBoundingClientRect();
  const g=document.createElement("div");
  g.className="glide-marker"; g.textContent="you are here";
  g.style.left=startRect.left+"px"; g.style.top=startRect.top+"px";
  document.body.appendChild(g);
  // hide the static marker on the old row while gliding
  if(fm) fm.style.visibility="hidden";
  // target position = right side of the destination row
  const dRect=toRow.getBoundingClientRect();
  requestAnimationFrame(()=>requestAnimationFrame(()=>{
    if(tok!==seqToken){ g.remove(); return; }
    g.classList.add("moving");
    g.style.left=(dRect.right-72)+"px";
    g.style.top=dRect.top+"px";
  }));
  setTimeout(()=>{
    if(tok!==seqToken){ g.remove(); return; }
    commitLocation(tree, herePathAfter, toKey, tok);
    g.remove();
  }, 560);
}
function commitLocation(tree, herePath, toKey, tok){
  renderTree(tree, herePath);
  setPwd(herePath);
  const pb=document.getElementById("pathbar"); if(pb){ pb.classList.remove("changed"); void pb.offsetWidth; pb.classList.add("changed"); }
  const dest=nodeByKey(toKey); if(dest) dest.classList.add("entered");
}

/* ---------- COMMANDS ---------- */
const HERE0=["user"];
const COMMANDS=[
  { n:"01", verb:"pwd", arg:"", title:"Where am I",
    body:"Print working directory — shows the absolute path of the folder you're currently in. Your answer to \"where am I?\"",
    flow:["<span class='on'>current dir</span>"],
    render:(tok)=>{ const t=baseTree(); renderTree(t,["user"]); setPwd(["user"]);
      runSequence("pwd",["user"],null,[{out:"/home/user"}],tok); } },

  { n:"02", verb:"ls", arg:"", title:"List contents",
    body:"Lists the files and folders inside the current directory — what's here, right now.",
    flow:["<span class='on'>current dir</span>","→","contents"],
    render:(tok)=>{ const t=baseTree(); renderTree(t,["user"]); setPwd(["user"]);
      runSequence("ls",["user"],
        ()=>{ ["user/projects","user/docs","user/notes.txt"].forEach(k=>{const n=nodeByKey(k);if(n)n.classList.add("highlight");}); },
        [{out:"projects  docs  notes.txt"}],tok); } },

  { n:"03", verb:"cd", arg:"projects", title:"Change directory",
    body:"Move yourself into another folder. The \"you are here\" marker glides to the target, the prompt updates to ~/projects, and that becomes your current directory — you'll notice mv moves a file, but cd moves you.",
    flow:["home","→","<span class='on'>projects</span>"],
    render:(tok)=>{ const t=baseTree(); renderTree(t,["user"]); setPwd(["user"]);
      typeCommand("cd projects",["user"],()=>{
        step(()=>glideMarker(t,"user","user/projects",["user","projects"],tok),220,tok);
      },tok); } },

  { n:"04", verb:"mkdir", arg:"src", title:"Make a directory",
    body:"Creates a new, empty folder in the current directory — ready to fill with files.",
    flow:["current dir","+","<span class='on'>new folder</span>"],
    render:(tok)=>{ const t=baseTree(); renderTree(t,["user"]); setPwd(["user"]);
      typeCommand("mkdir src",["user"],()=>{
        step(()=>{ t.children.push({name:"src",type:"dir",children:[]}); renderTree(t,["user"]); enterNode("user/src");
          appendTerminal({cmd:""},["user"]); },260,tok);
      },tok); } },

  { n:"05", verb:"touch", arg:"todo.txt", title:"Create a file",
    body:"Creates a new empty file (or updates its timestamp if it already exists). The quickest way to make a file.",
    flow:["current dir","+","<span class='on'>new file</span>"],
    render:(tok)=>{ const t=baseTree(); renderTree(t,["user"]); setPwd(["user"]);
      typeCommand("touch todo.txt",["user"],()=>{
        step(()=>{ t.children.push({name:"todo.txt",type:"file"}); renderTree(t,["user"]); enterNode("user/todo.txt"); },260,tok);
      },tok); } },

  { n:"06", verb:"cp", arg:"notes.txt backup.txt", title:"Copy a file",
    body:"Duplicates a file. The original stays; a copy appears under the new name. Handy before risky edits.",
    flow:["notes.txt","→","<span class='on'>backup.txt</span>"],
    render:(tok)=>{ const t=baseTree(); renderTree(t,["user"]); setPwd(["user"]);
      typeCommand("cp notes.txt backup.txt",["user"],()=>{
        step(()=>{ const src=nodeByKey("user/notes.txt"); if(src) src.classList.add("copied");
          t.children.push({name:"backup.txt",type:"file"}); renderTree(t,["user"]);
          const s=nodeByKey("user/notes.txt"); if(s) s.classList.add("copied");
          enterNode("user/backup.txt"); },300,tok);
      },tok); } },

  { n:"07", verb:"mv", arg:"notes.txt docs/", title:"Move / rename",
    body:"Moves a file or folder to a new location — or renames it in place. Here notes.txt moves into docs/.",
    flow:["home","→","<span class='on'>docs/</span>"],
    render:(tok)=>{ const t=baseTree(); renderTree(t,["user"]); setPwd(["user"]);
      typeCommand("mv notes.txt docs/",["user"],()=>{
        step(()=>{
          leaveNode("user/notes.txt",()=>{
            if(tok!==seqToken) return;
            t.children=t.children.filter(c=>c.name!=="notes.txt");
            const docs=t.children.find(c=>c.name==="docs"); docs.children.push({name:"notes.txt",type:"file"});
            renderTree(t,["user"]); enterNode("user/docs/notes.txt");
          });
        },260,tok);
      },tok); } },

  { n:"08", verb:"rm", arg:"notes.txt", title:"Remove a file",
    body:"Deletes a file. There's no recycle bin on the command line — rm is permanent, so use it deliberately.",
    flow:["current dir","−","<span class='on'>notes.txt</span>"],
    render:(tok)=>{ const t=baseTree(); renderTree(t,["user"]); setPwd(["user"]);
      typeCommand("rm notes.txt",["user"],()=>{
        step(()=>{ leaveNode("user/notes.txt",()=>{ if(tok!==seqToken) return;
          t.children=t.children.filter(c=>c.name!=="notes.txt"); renderTree(t,["user"]); }); },260,tok);
      },tok); } },

  { n:"09", verb:"tree", arg:"", title:"See the whole structure",
    body:"Draws the entire directory structure as a tree — every folder and file, nested, in one view.",
    flow:["<span class='on'>whole tree</span>"],
    render:(tok)=>{ const t=baseTree(); const rows=renderTree(t,["user"]); setPwd(["user"]);
      // reveal each row in sequence
      const nodes=[...document.querySelectorAll("#tree .node")];
      nodes.forEach(n=>n.style.opacity="0");
      typeCommand("tree",["user"],()=>{
        nodes.forEach((n,i)=>step(()=>{ n.style.opacity="1"; n.classList.add("enter"); }, 120+i*110, tok));
      },tok); } },
];

/* ---------- build list ---------- */
const listEl=document.getElementById("list");
function buildList(){
  listEl.innerHTML="";
  COMMANDS.forEach((c,i)=>{
    const b=document.createElement("button");
    b.className="cmd";b.setAttribute("role","tab");
    b.innerHTML=`<span class="badge">${c.n}</span>
      <span class="cmd-main">
        <span class="cmd-code"><span class="kw">${c.verb}</span>${c.arg?` <span class="arg">${c.arg}</span>`:""}</span>
        <span class="cmd-desc">${c.title}</span>
      </span>`;
    b.addEventListener("click",()=>select(i));
    listEl.appendChild(b);
  });
}

/* ---------- select ---------- */
let current=0;
function select(i){
  current=i;const c=COMMANDS[i];
  seqToken++;const tok=seqToken;
  [...listEl.querySelectorAll(".cmd")].forEach((el,idx)=>el.classList.toggle("active",idx===i));
  document.getElementById("exTitle").textContent=c.verb+(c.arg?" "+c.arg:"");
  document.getElementById("exBody").textContent=c.body;
  document.getElementById("exFlow").innerHTML=(c.flow||[]).map(x=>`<span>${x}</span>`).join(" ");
  document.getElementById("counter").textContent=`${c.n} / ${String(COMMANDS.length).padStart(2,"0")}`;
  c.render(tok);
}

/* ---------- keyboard nav ---------- */
document.addEventListener("keydown",e=>{
  if(e.key==="ArrowDown"||e.key==="ArrowRight"){select((current+1)%COMMANDS.length);e.preventDefault();}
  if(e.key==="ArrowUp"||e.key==="ArrowLeft"){select((current-1+COMMANDS.length)%COMMANDS.length);e.preventDefault();}
});

buildList();
select(0);
</script>
@endverbatim
</div>
