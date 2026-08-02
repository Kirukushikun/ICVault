<div class="visualizer-guide">
{{--
    Concept Visualizer guide, ported from project-overview/3 docker-commands.html.

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
  --bg:#1A1A1D;             /* deep navy-black */
  --panel:#222226;         /* raised surface */
  --panel-2:#1c1c20;
  --line:rgba(255,255,255,.08);          /* borders */
  --line-soft:rgba(255,255,255,.05);
  --ink:#ffffff;           /* primary text */
  --ink-dim:rgba(255,255,255,.45);       /* muted */
  --ink-faint:rgba(255,255,255,.28);
  --cyan:#2ab6e6;          /* docker blue */
  --cyan-hot:#0db7ed;      /* docker's actual brand blue */
  --cyan-soft:rgba(13,183,237,.14);
  --green:#2e9c6b;         /* running / success */
  --amber:#c98a2e;         /* exited / warning */
  --amber-hot:#e0a94a;
  --violet:#9b5fcf;
  --radius:16px;
}
/* Scoped: an unlayered `*` reset would out-rank Tailwind's utility
   layer and strip the platform header's spacing. */
.visualizer-guide *{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{
  background:
    radial-gradient(1100px 560px at 82% -8%, rgba(13,183,237,.10), transparent 60%),
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
.wrap{max-width:1200px;margin:0 auto}

/* ---------- Header ---------- */
.eyebrow{display:flex;justify-content:space-between;align-items:center;
  font-family:'JetBrains Mono',monospace;font-size:12px;letter-spacing:.18em;
  color:var(--cyan);text-transform:uppercase;margin-bottom:18px}
.eyebrow .right{color:var(--ink-faint)}
.masthead{display:flex;align-items:center;gap:18px;flex-wrap:wrap}
/* The mark carries its own colour, so no tile behind it — the glow is a
   drop-shadow that follows the logo shape rather than a square. */
.glyph{width:58px;height:58px;flex:none;display:grid;place-items:center;
  filter:drop-shadow(0 6px 20px rgba(13,183,237,.40))}
.glyph img{width:100%;height:100%;object-fit:contain}
h1{font-family:'League Spartan','JetBrains Mono',sans-serif;font-weight:800;
  font-size:clamp(30px,6vw,52px);line-height:1;letter-spacing:-.02em}
h1 .num{color:var(--cyan)}
.sub{font-family:'JetBrains Mono',monospace;letter-spacing:.28em;text-transform:uppercase;
  color:var(--ink-dim);font-size:clamp(11px,2vw,14px);margin-top:8px}
.rule{height:2px;background:linear-gradient(90deg,var(--cyan),transparent);margin:22px 0 26px;border-radius:2px}

/* ---------- Layout ---------- */
.grid{display:grid;grid-template-columns:minmax(320px,1fr) minmax(340px,1.1fr);gap:24px;align-items:start}
@media(max-width:860px){.grid{grid-template-columns:1fr}}

/* ---------- Command list ---------- */
.list{display:flex;flex-direction:column;gap:8px}
.cmd{position:relative;text-align:left;width:100%;cursor:pointer;
  background:var(--panel);border:1px solid var(--line-soft);border-radius:12px;
  padding:14px 16px 14px 18px;color:var(--ink);
  transition:border-color .2s,background .2s;
  display:grid;grid-template-columns:auto 1fr auto;gap:14px;align-items:center;font-family:inherit}
.cmd:hover{border-color:var(--line);background:var(--panel-2)}
.cmd:focus-visible{outline:2px solid var(--cyan);outline-offset:2px}
.cmd.active{border-color:var(--cyan);background:linear-gradient(90deg,var(--cyan-soft),transparent 70%)}
.cmd.active::before{content:"";position:absolute;left:0;top:8px;bottom:8px;width:3px;border-radius:3px;
  background:var(--cyan);box-shadow:0 0 12px var(--cyan-hot)}
.badge{font-family:'JetBrains Mono',monospace;font-weight:700;font-size:11px;color:var(--cyan);
  border:1px solid var(--line);border-radius:6px;padding:3px 6px;min-width:26px;text-align:center;background:var(--inset)}
.cmd.active .badge{border-color:var(--cyan);color:#1A1A1D;background:var(--cyan-hot)}
.cmd-main{display:flex;flex-direction:column;gap:3px;min-width:0}
.cmd-code{font-family:'JetBrains Mono',monospace;font-weight:700;font-size:16px}
.cmd-code .kw{color:var(--ink-dim)}
.cmd-code .verb{color:var(--ink)}
.cmd.active .cmd-code .verb{color:var(--cyan)}
.cmd-flag{font-family:'JetBrains Mono',monospace;font-size:11px;color:var(--ink-faint)}
.cmd-desc{font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:var(--ink-faint);margin-top:2px}
.freq{font-family:'JetBrains Mono',monospace;font-size:9px;letter-spacing:.1em;text-transform:uppercase;
  color:var(--ink-faint);border:1px solid var(--line);border-radius:5px;padding:3px 6px;align-self:start}

/* ---------- Stage ---------- */
.stage{position:sticky;top:24px;background:var(--panel);border:1px solid var(--line);
  border-radius:var(--radius);overflow:hidden}
.stage-inner{padding:18px 16px 20px;display:flex;flex-direction:column;gap:14px}

/* zone base */
.zone{border:1px solid var(--line);border-radius:10px;background:var(--inset);padding:12px;
  transition:border-color .3s,box-shadow .3s,opacity .3s;position:relative}
.zone.lit{border-color:var(--cyan);box-shadow:0 0 0 1px var(--cyan),0 0 18px rgba(13,183,237,.2) inset}
.zone-h{font-family:'JetBrains Mono',monospace;font-size:9px;letter-spacing:.16em;
  color:var(--ink-faint);text-transform:uppercase;margin-bottom:8px;display:flex;justify-content:space-between}
.zone-h .meta{color:var(--cyan)}

/* registry */
.registry .reg-name{font-family:'JetBrains Mono',monospace;font-weight:700;font-size:18px;color:var(--ink)}

/* dockerfile layers (image) */
.layers{display:flex;flex-direction:column;gap:6px}
.layer{font-family:'JetBrains Mono',monospace;font-size:13px;color:var(--ink-dim);
  border:1px solid var(--line);border-radius:7px;padding:8px 10px;background:#0d1420;
  transition:border-color .35s,color .35s,background .35s,transform .35s;position:relative}
.layer .k{color:var(--cyan)}
.layer.built{border-color:var(--cyan);color:var(--ink);background:rgba(13,183,237,.06)}
/* a layer being assembled: cyan sweep + brief lift */
.layer.building{border-color:var(--cyan);color:var(--ink);
  animation:layerBuild .5s cubic-bezier(.34,1.56,.64,1)}
@keyframes layerBuild{
  0%{transform:translateX(-6px);opacity:.4;box-shadow:0 0 0 rgba(13,183,237,0)}
  50%{transform:translateX(0);box-shadow:-8px 0 16px rgba(13,183,237,.4)}
  100%{transform:none;box-shadow:0 0 0 rgba(13,183,237,0)}}
/* layers waiting to be built are dimmed */
.layer.pending{opacity:.28}
/* a subtle cached tick that appears when built */
.layer .tick{position:absolute;right:10px;top:50%;transform:translateY(-50%) scale(0);
  color:var(--cyan);font-size:12px;transition:transform .3s cubic-bezier(.34,1.56,.64,1)}
.layer.built .tick{transform:translateY(-50%) scale(1)}

/* containers */
.containers{display:flex;gap:10px;flex-wrap:wrap;min-height:20px}
.container-box{width:96px;border:1px solid var(--amber);border-radius:8px;background:rgba(224,168,63,.06);
  padding:10px;text-align:center;position:relative;
  transition:border-color .6s ease,background .6s ease,opacity .6s ease,transform .3s ease}
.container-box.running{border-color:var(--green);background:rgba(46,156,107,.07)}
.container-box .c-name{font-family:'JetBrains Mono',monospace;font-weight:700;font-size:14px;color:var(--ink)}
.container-box .c-state{font-family:'JetBrains Mono',monospace;font-size:9px;letter-spacing:.14em;
  text-transform:uppercase;margin-top:3px;transition:color .6s ease}
.container-box.running .c-state{color:var(--green)}
.container-box.exited .c-state{color:var(--amber)}
.container-box.stopping{border-color:var(--amber);background:rgba(224,168,63,.05)}
.container-box.stopping .c-state{color:var(--amber)}
/* gentle pulse while shutting down, then settle */
.container-box.stopping{animation:stopping 1s ease}
@keyframes stopping{0%{opacity:1}50%{opacity:.55}100%{opacity:1}}
.container-box .c-port{font-family:'JetBrains Mono',monospace;font-size:10px;color:var(--ink-faint);margin-top:4px}
/* container springing into existence */
.container-box.spawn{animation:cspawn .5s cubic-bezier(.34,1.56,.64,1)}
@keyframes cspawn{0%{transform:scale(.3);opacity:0}60%{transform:scale(1.1)}100%{transform:none;opacity:1}}
/* glowing pulse that travels down the spine (image → container) */
.fly-pulse{position:fixed;width:16px;height:16px;border-radius:50%;
  background:var(--cyan);pointer-events:none;z-index:60;
  box-shadow:0 0 16px 5px rgba(13,183,237,.7);transform:translate(-50%,-50%)}
.fly-pulse.moving{transition:left .55s cubic-bezier(.5,0,.2,1),
  top .55s cubic-bezier(.5,0,.2,1),transform .55s cubic-bezier(.5,0,.2,1)}

/* terminal */
.terminal{border:1px solid var(--line);border-radius:10px;background:#060a0f;padding:0;overflow:hidden}
.term-bar{display:flex;align-items:center;gap:7px;padding:10px 12px;border-bottom:1px solid var(--line);background:var(--inset)}
.dot{width:11px;height:11px;border-radius:50%}
.dot.r{background:#ff5f57}.dot.y{background:#febc2e}.dot.g{background:#28c840}
.term-label{margin-left:8px;font-family:'JetBrains Mono',monospace;font-size:10px;
  letter-spacing:.16em;color:var(--ink-faint);text-transform:uppercase}
.term-body{padding:12px 14px;min-height:96px;font-family:'JetBrains Mono',monospace;font-size:13px;line-height:1.7}
.term-line{white-space:pre-wrap;word-break:break-word}
.term-line .p{color:var(--cyan)}
.term-line .out{color:var(--ink-dim)}
.caret{display:inline-block;width:8px;height:15px;background:var(--cyan);vertical-align:-2px;
  margin-left:2px;animation:blink 1s steps(1) infinite}
@keyframes blink{50%{opacity:0}}

/* flow footer inside stage */
.flowbar{display:flex;justify-content:space-between;align-items:center;
  border:1px solid var(--line);border-radius:10px;padding:10px 14px;background:var(--inset)}
.flowbar .path{font-family:'JetBrains Mono',monospace;font-size:11px;letter-spacing:.12em;
  color:var(--ink-dim);text-transform:uppercase}
.flowbar .path b{color:var(--cyan)}
.flowbar .status{font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:.14em;
  text-transform:uppercase;color:var(--green);border:1px solid var(--line);border-radius:5px;padding:4px 8px}

/* connectors between zones */
.connector{height:22px;display:flex;justify-content:center;align-items:center;position:relative}
.connector svg{width:16px;height:22px;overflow:visible}
.connector .arrow-line{stroke:var(--line);stroke-width:2;stroke-dasharray:3 3;transition:stroke .3s}
.connector.lit .arrow-line{stroke:var(--cyan)}
/* animated flow: dashes march along the line + a travelling glow dot */
.connector.flowing .arrow-line{stroke:var(--cyan);stroke-dasharray:4 4;
  animation:dashFlow .6s linear infinite}
@keyframes dashFlow{to{stroke-dashoffset:-8}}
.connector .flow-dot{opacity:0}
.connector.flowing .flow-dot{opacity:1;animation:dotFlow .6s ease-in-out}
@keyframes dotFlow{0%{transform:translateY(-11px);opacity:0}
  20%{opacity:1}80%{opacity:1}100%{transform:translateY(11px);opacity:0}}
.connector .flow-dot circle{fill:var(--cyan);filter:drop-shadow(0 0 4px var(--cyan))}

/* explanation */
.explain{border-top:1px solid var(--line);padding:16px;background:var(--panel-lift)}
.explain h3{font-family:'JetBrains Mono',monospace;font-size:13px;letter-spacing:.1em;
  color:var(--cyan);text-transform:uppercase;margin-bottom:6px}
.explain p{font-size:14px;color:var(--ink);margin-bottom:10px}
.explain .flow{display:flex;align-items:center;gap:6px;flex-wrap:wrap;font-family:'JetBrains Mono',monospace;
  font-size:10px;letter-spacing:.1em;text-transform:uppercase;color:var(--ink-faint)}
.explain .flow .on{color:var(--cyan)}

/* footer */
footer{margin-top:28px;border-top:1px solid var(--line);padding-top:16px;
  display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap}
.foot-flow{font-family:'JetBrains Mono',monospace;font-size:12px;letter-spacing:.12em;
  color:var(--ink-dim);text-transform:uppercase}
.foot-flow b{color:var(--cyan)}
.counter{font-family:'JetBrains Mono',monospace;font-size:13px;color:var(--cyan);
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
    <div class="glyph">
      <img src="{{ asset($guide['logo']) }}" alt="" />
    </div>
    <div>
      <h1><span class="num">10</span> DOCKER COMMANDS</h1>
      <div class="sub">{{ $guide['subtitle'] }}</div>
    </div>
  </div>

  <div class="rule"></div>

  <div class="grid">
    <!-- LEFT: command list -->
    <div class="list" id="list" role="tablist" aria-label="Docker commands"></div>

    <!-- RIGHT: stage -->
    <div class="stage">
      <div class="stage-inner">

        <div class="zone registry" id="zRegistry">
          <div class="zone-h"><span>registry</span></div>
          <div class="reg-name">docker.io</div>
        </div>

        <div class="connector" id="conn1"><svg viewBox="0 0 16 22"><line class="arrow-line" x1="8" y1="0" x2="8" y2="22"/><g class="flow-dot"><circle cx="8" cy="11" r="2.5"/></g></svg></div>

        <div class="zone image" id="zImage">
          <div class="zone-h"><span>image</span><span class="meta" id="imageTag">app:latest</span></div>
          <div class="layers" id="layers"></div>
        </div>

        <div class="connector" id="conn2"><svg viewBox="0 0 16 22"><line class="arrow-line" x1="8" y1="0" x2="8" y2="22"/><g class="flow-dot"><circle cx="8" cy="11" r="2.5"/></g></svg></div>

        <div class="zone containers-zone" id="zContainers">
          <div class="zone-h"><span>containers</span></div>
          <div class="containers" id="containers"></div>
        </div>

        <div class="connector" id="conn3"><svg viewBox="0 0 16 22"><line class="arrow-line" x1="8" y1="0" x2="8" y2="22"/><g class="flow-dot"><circle cx="8" cy="11" r="2.5"/></g></svg></div>

        <div class="terminal" id="zTerminal">
          <div class="term-bar">
            <span class="dot r"></span><span class="dot y"></span><span class="dot g"></span>
            <span class="term-label">terminal</span>
          </div>
          <div class="term-body" id="termBody"></div>
        </div>

        <div class="flowbar">
          <span class="path">registry <b>›</b> image <b>›</b> container</span>
          <span class="status" id="flowStatus">READY</span>
        </div>

      </div>

      <div class="explain">
        <h3 id="exTitle">docker pull</h3>
        <p id="exBody">Downloads an image from a registry to your machine.</p>
        <div class="flow" id="exFlow"></div>
      </div>
    </div>
  </div>

  <footer>
    <div class="foot-flow">dockerfile <b>›</b> image <b>›</b> container <b>›</b> registry</div>
    <div class="counter" id="counter">01 / 10</div>
  </footer>
  <div class="tagline">ONE IMAGE. ONE CONTAINER. TEN COMMANDS.</div>

</div>

@verbatim
<script>
/* ============================================================
   Docker commands — interactive explainer.
   State model (vertical spine): Registry → Image (Dockerfile
   layers) → Containers → Terminal. Each command animates what
   it does to these zones. Engine mirrors the Git explainer:
   data-driven commands + a seqToken cancellation guard.
   ============================================================ */

const DOCKERFILE = [
  {k:"FROM",     v:"node:20"},
  {k:"WORKDIR",  v:"/app"},
  {k:"COPY",     v:". ."},
  {k:"RUN",      v:"npm ci"},
  {k:"CMD",      v:"start"},
];

let seqToken = 0;
const step = (fn,delay,tok)=>setTimeout(()=>{ if(tok===seqToken) fn(); }, delay);

/* ---------- zone helpers ---------- */
function renderLayers(builtCount, pending){
  const host=document.getElementById("layers");
  host.innerHTML="";
  DOCKERFILE.forEach((l,i)=>{
    const d=document.createElement("div");
    let cls="layer";
    if(i<builtCount) cls+=" built";
    else if(pending) cls+=" pending";
    d.className=cls;
    d.innerHTML=`<span class="k">${l.k}</span> ${l.v}<span class="tick">✓</span>`;
    host.appendChild(d);
  });
}
function buildLayer(i){
  const els=document.querySelectorAll("#layers .layer");
  const el=els[i]; if(!el) return;
  el.classList.remove("pending");
  el.classList.add("building");
  setTimeout(()=>{ el.classList.add("built"); el.classList.remove("building"); }, 260);
}
function setContainers(list){
  const host=document.getElementById("containers");
  host.innerHTML="";
  list.forEach(c=>{
    const d=document.createElement("div");
    d.className="container-box "+(c.state==="running"?"running":"exited");
    d.innerHTML=`<div class="c-name">${c.name}</div>
      <div class="c-state">${c.state}</div>
      ${c.port?`<div class="c-port">:${c.port}</div>`:""}`;
    host.appendChild(d);
  });
}
function setTerminal(lines){
  const host=document.getElementById("termBody");
  host.innerHTML="";
  lines.forEach(ln=>host.appendChild(termLineEl(ln)));
  host.appendChild(caretEl());
}
function termLineEl(ln){
  const d=document.createElement("div");d.className="term-line";
  if(ln.cmd!==undefined) d.innerHTML=`<span class="p">$</span> ${ln.cmd}`;
  else d.innerHTML=`<span class="out">${ln.out}</span>`;
  return d;
}
function caretEl(){ const c=document.createElement("span");c.className="caret";return c; }
function appendTerminal(ln){
  const host=document.getElementById("termBody");
  const caret=host.querySelector(".caret"); if(caret) caret.remove();
  host.appendChild(termLineEl(ln));
  host.appendChild(caretEl());
}

/* Type a command into the terminal character-by-character, then call done().
   Clears the terminal first. Cancels cleanly if the command changes. */
function typeCommand(cmd, done, tok){
  const host=document.getElementById("termBody");
  host.innerHTML="";
  const line=document.createElement("div");line.className="term-line";
  const prompt=document.createElement("span");prompt.className="p";prompt.textContent="$";
  const text=document.createElement("span");
  line.appendChild(prompt);line.appendChild(document.createTextNode(" "));line.appendChild(text);
  const caret=caretEl();
  line.appendChild(caret);
  host.appendChild(line);
  let i=0;
  const speed=38;                       // ms per character
  const tick=()=>{
    if(tok!==seqToken) return;
    text.textContent=cmd.slice(0,i);
    if(i<=cmd.length){ i++; setTimeout(tick,speed); }
    else { if(done) done(); }
  };
  tick();
}
/* Type the command, then after a beat run the effect, then stream output lines. */
function runSequence(cmd, effect, outputs, tok, opts={}){
  const afterType=opts.afterType||250;   // pause after typing before effect
  const afterEffect=opts.afterEffect||0; // extra delay before output lines
  typeCommand(cmd, ()=>{
    step(()=>{ if(effect) effect(); }, afterType, tok);
    if(outputs&&outputs.length){
      outputs.forEach((o,k)=>{
        step(()=>appendTerminal(o), afterType+afterEffect+150+k*140, tok);
      });
    }
  }, tok);
}

/* Fly a glowing pulse from the center of one zone element to another,
   then run onArrive(). Cancels cleanly if the command changes. */
function flyPulse(fromId, toId, onArrive, tok, color){
  const from=document.getElementById(fromId), to=document.getElementById(toId);
  if(!from||!to){ if(onArrive) onArrive(); return; }
  const a=from.getBoundingClientRect(), b=to.getBoundingClientRect();
  const ox=a.left+a.width/2, oy=a.top+a.height/2;
  const dx=b.left+b.width/2, dy=b.top+b.height/2;
  const p=document.createElement("div");
  p.className="fly-pulse";
  if(color) p.style.background=color;
  p.style.left=ox+"px"; p.style.top=oy+"px";
  document.body.appendChild(p);
  requestAnimationFrame(()=>requestAnimationFrame(()=>{
    if(tok!==seqToken){ p.remove(); return; }
    p.classList.add("moving");
    p.style.left=dx+"px"; p.style.top=dy+"px";
    p.style.transform="translate(-50%,-50%) scale(.7)";
  }));
  setTimeout(()=>{
    if(tok!==seqToken){ p.remove(); return; }
    if(onArrive) onArrive();
    p.style.transform="translate(-50%,-50%) scale(1.7)";
    p.style.opacity="0";
    setTimeout(()=>p.remove(),240);
  }, 600);
}

/* Set containers and spring-in the newest one. */
function spawnContainers(list){
  setContainers(list);
  const boxes=document.querySelectorAll("#containers .container-box");
  const last=boxes[boxes.length-1];
  if(last) last.classList.add("spawn");
}

/* Gracefully stop the named container IN PLACE (no DOM rebuild) so the
   running→exited change eases smoothly: brief "stopping…" beat, then settle
   to exited. Mutates the existing box's classes and state label. */
function gracefulStop(name, tok){
  const boxes=[...document.querySelectorAll("#containers .container-box")];
  const box=boxes.find(b=>b.querySelector(".c-name")?.textContent===name);
  if(!box){ setContainers([{name,state:"exited",port:3000}]); return; }
  const stateEl=box.querySelector(".c-state");
  box.classList.remove("running");
  box.classList.add("stopping");
  if(stateEl) stateEl.textContent="stopping";
  setTimeout(()=>{
    if(tok!==seqToken) return;
    box.classList.remove("stopping");
    box.classList.add("exited");
    if(stateEl) stateEl.textContent="exited";
  }, 900);
}
function lightZones(ids){
  ["zRegistry","zImage","zContainers"].forEach(z=>{
    document.getElementById(z).classList.toggle("lit", ids.includes(z));
  });
  ["conn1","conn2","conn3"].forEach(c=>document.getElementById(c).classList.remove("lit"));
}
/* Flow cyan energy through a connector (conn1: reg→image, conn2: image→container,
   conn3: container→terminal). Runs a short burst, then leaves it lit. */
function flowConnector(id, tok, keepLit=true){
  const el=document.getElementById(id); if(!el) return;
  el.classList.add("flowing");
  setTimeout(()=>{
    if(tok!==seqToken) return;
    el.classList.remove("flowing");
    if(keepLit) el.classList.add("lit");
  }, 620);
}

/* ---------- COMMANDS (add one by pushing an object) ---------- */
const COMMANDS = [
  { n:"01", verb:"pull", flag:"node:20", freq:"daily", cmd:"docker pull", title:"Fetch an image",
    body:"Downloads a prebuilt image from a registry (like Docker Hub) to your local machine, ready to run.",
    flow:["<span class='on'>registry</span>","→","image"],
    render:(tok)=>{ renderLayers(5); setContainers([]); lightZones(["zRegistry"]);
      runSequence("docker pull node:20",
        ()=>{ lightZones(["zRegistry","zImage"]); flowConnector("conn1",tok); },
        [{out:"Status: Downloaded newer image for node:20"}], tok); } },

  { n:"02", verb:"build", flag:"-t app .", freq:"daily", cmd:"docker build", title:"Image from Dockerfile",
    body:"Reads your Dockerfile top to bottom and assembles an image layer by layer. Each instruction becomes a cached layer, so unchanged steps are reused on the next build.",
    flow:["dockerfile","→","<span class='on'>image</span>"],
    render:(tok)=>{
      renderLayers(0, true);              // all layers pending (dimmed)
      setContainers([]);
      lightZones(["zImage"]);
      typeCommand("docker build -t app .", ()=>{
        const n=DOCKERFILE.length;
        DOCKERFILE.forEach((l,i)=>{
          step(()=>{
            buildLayer(i);
            appendTerminal({out:`Step ${i+1}/${n} : ${l.k} ${l.v}`});
          }, 300 + i*520, tok);
        });
        step(()=>appendTerminal({out:"Successfully tagged app:latest"}), 300 + n*520 + 200, tok);
      }, tok);
    } },

  { n:"03", verb:"images", flag:"", freq:"weekly", cmd:"docker images", title:"List local images",
    body:"Shows the images stored on your machine — their repository, tag, size, and when they were created.",
    flow:["<span class='on'>image</span>"],
    render:(tok)=>{ renderLayers(5); setContainers([]); lightZones(["zImage"]);
      runSequence("docker images", null,
        [{out:"REPOSITORY   TAG      SIZE"},{out:"app          latest   142MB"}], tok); } },

  { n:"04", verb:"run", flag:"-p 3000:3000 app", freq:"daily", cmd:"docker run", title:"Start a container",
    body:"Creates a running container from an image — a live, isolated instance of your app. The image is the template; the container is the thing that actually runs. Ports map the container to your machine.",
    flow:["image","→","<span class='on'>container</span>"],
    render:(tok)=>{
      renderLayers(5);
      setContainers([]);
      lightZones(["zImage"]);
      typeCommand("docker run -p 3000:3000 app", ()=>{
        step(()=>{
          flowConnector("conn2",tok);
          flyPulse("zImage","zContainers",()=>{
            lightZones(["zImage","zContainers"]);
            spawnContainers([{name:"app",state:"running",port:3000}]);
            appendTerminal({out:"a1b2c3  app  Up (running)"});
          }, tok);
        }, 200, tok);
      }, tok);
    } },

  { n:"05", verb:"ps", flag:"-a", freq:"daily", cmd:"docker ps", title:"List containers",
    body:"Lists your containers and their status. With -a it shows stopped ones too, not just the running ones.",
    flow:["<span class='on'>container</span>"],
    render:(tok)=>{ renderLayers(5); setContainers([{name:"app",state:"running",port:3000}]); lightZones(["zContainers"]);
      runSequence("docker ps -a", null,
        [{out:"CONTAINER   IMAGE   STATUS"},{out:"app         app     Up 4s"}], tok); } },

  { n:"06", verb:"logs", flag:"-f app", freq:"daily", cmd:"docker logs", title:"Stream output",
    body:"Prints (and with -f, follows) whatever your container writes to stdout — the app's live logs.",
    flow:["<span class='on'>container</span>","→","output"],
    render:(tok)=>{ renderLayers(5); setContainers([{name:"app",state:"running",port:3000}]); lightZones(["zContainers"]);
      runSequence("docker logs -f app",
        ()=>flowConnector("conn3",tok),
        [{out:"listening on :3000"},{out:"GET / 200 12ms"}], tok); } },

  { n:"07", verb:"exec", flag:"-it app sh", freq:"weekly", cmd:"docker exec", title:"Shell inside it",
    body:"Runs a command inside a running container. With -it sh you get an interactive shell — like stepping inside the box.",
    flow:["you","→","<span class='on'>container</span>"],
    render:(tok)=>{ renderLayers(5); setContainers([{name:"app",state:"running",port:3000}]); lightZones(["zContainers"]);
      runSequence("docker exec -it app sh",
        ()=>flowConnector("conn3",tok),
        [{out:"/app # "}], tok); } },

  { n:"08", verb:"stop", flag:"app", freq:"daily", cmd:"docker stop", title:"Graceful shutdown",
    body:"Sends the container a stop signal so it can shut down cleanly — finishing in-flight work before it halts. It moves from running to exited but still exists (you can start it again).",
    flow:["running","→","<span class='on'>exited</span>"],
    render:(tok)=>{ renderLayers(5); setContainers([{name:"app",state:"running",port:3000}]); lightZones(["zContainers"]);
      runSequence("docker stop app",
        ()=>gracefulStop("app",tok),
        [{out:"app"}], tok, {afterEffect:900}); } },

  { n:"09", verb:"system prune", flag:"", freq:"rarely", cmd:"docker system prune", title:"Reclaim disk space",
    body:"Deletes stopped containers, unused networks, and dangling images to free up disk. Use with care.",
    flow:["stopped","→","<span class='on'>removed</span>"],
    render:(tok)=>{ renderLayers(5); setContainers([{name:"app",state:"exited",port:3000}]); lightZones(["zContainers"]);
      runSequence("docker system prune",
        ()=>setContainers([]),
        [{out:"Deleted Containers: app"},{out:"Total reclaimed space: 142MB"}], tok); } },

  { n:"10", verb:"compose up", flag:"-d", freq:"daily", cmd:"docker compose up", title:"The whole stack",
    body:"Reads a compose file and starts all your services at once — app, database, cache — wired together with one command.",
    flow:["compose.yml","→","<span class='on'>stack</span>"],
    render:(tok)=>{ renderLayers(5); setContainers([]); lightZones(["zImage","zContainers"]);
      runSequence("docker compose up -d",
        ()=>{ flowConnector("conn2",tok); spawnContainers([{name:"app",state:"running",port:3000},{name:"db",state:"running",port:5432}]); },
        [{out:"Creating app  ... done"},{out:"Creating db   ... done"}], tok); } },
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
        <span class="cmd-code"><span class="kw">$ docker</span> <span class="verb">${c.verb}</span>${c.flag?` <span class="cmd-flag">${c.flag}</span>`:""}</span>
        <span class="cmd-desc">${c.title}</span>
      </span>
      <span class="freq">${c.freq}</span>`;
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
  document.getElementById("exTitle").textContent=c.cmd;
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

/* ---------- init ---------- */
buildList();
select(0);
</script>
@endverbatim
</div>
