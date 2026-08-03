<div class="visualizer-guide">
{{--
    Concept Visualizer guide, ported from project-overview/4 http-lifecycle.html.

    Guides are hand-authored and self-contained. Only <style> and <script>
    sit inside verbatim — the markup is plain Blade so the masthead reads
    its labels from App\Tools\Visualizer\GuideLibrary.

    Rendered full-bleed by layouts/canvas.blade.php.
--}}

@verbatim
<style>
:root{
  --inset:#161617;
  --panel-lift:#27272C;
  --bg:#1A1A1D;             /* platform page colour */
  --panel:#222226;          /* raised */
  --panel-2:#1c1c20;
  --line:rgba(255,255,255,.08);           /* borders */
  --line-soft:rgba(255,255,255,.05);
  --ink:#ffffff;            /* primary */
  --ink-dim:rgba(255,255,255,.45);        /* muted */
  --ink-faint:rgba(255,255,255,.28);
  --primary:#9d8cff;        /* signal violet — this guide's accent */
  --primary-hot:#b9adff;
  --primary-soft:rgba(157,140,255,.14);
  --green:#2e9c6b;          /* success / 200 */
  --amber:#c98a2e;
  --secure:#35d0e6;         /* secure / TLS */
  --pink:#ff6ba6;
  --red:#ff5d5d;
  --radius:16px;
}
/* Scoped: an unlayered `*` reset would out-rank Tailwind's utility
   layer and strip the platform header's spacing. */
.visualizer-guide *{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{
  background:
    radial-gradient(1100px 560px at 84% -10%, rgba(157,140,255,.11), transparent 60%),
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

/* header */
.eyebrow{display:flex;justify-content:space-between;align-items:center;
  font-family:'JetBrains Mono',monospace;font-size:12px;letter-spacing:.18em;
  color:var(--primary);text-transform:uppercase;margin-bottom:18px}
.eyebrow .right{color:var(--ink-faint)}
.masthead{display:flex;align-items:center;gap:18px;flex-wrap:wrap}
/* The mark carries its own colour, so no tile behind it — the glow is a
   drop-shadow that follows the logo shape rather than a square. */
.glyph{width:58px;height:58px;flex:none;display:grid;place-items:center;
  filter:drop-shadow(0 6px 20px rgba(157,140,255,.40))}
.glyph img{width:100%;height:100%;object-fit:contain}
h1{font-family:'League Spartan','JetBrains Mono',sans-serif;font-weight:700;font-size:clamp(26px,5vw,46px);line-height:1.02;letter-spacing:-.01em}
h1 .accent{color:var(--primary)}
.sub{font-family:'JetBrains Mono',monospace;letter-spacing:.24em;text-transform:uppercase;
  color:var(--ink-dim);font-size:clamp(11px,2vw,13px);margin-top:8px}
.rule{height:2px;background:linear-gradient(90deg,var(--primary),transparent);margin:22px 0 22px;border-radius:2px}

/* run bar */
.runbar{display:flex;align-items:center;gap:14px;margin-bottom:22px;flex-wrap:wrap}
.url-box{flex:1;min-width:220px;display:flex;align-items:center;gap:10px;
  border:1px solid var(--line);border-radius:10px;background:var(--inset);padding:10px 14px;
  font-family:'JetBrains Mono',monospace;font-size:14px}
.url-box .lock{color:var(--green)}
.url-box .url{color:var(--ink)}
.url-box .url .scheme{color:var(--primary)}
.run-btn{display:flex;align-items:center;gap:9px;cursor:pointer;border:none;border-radius:10px;
  background:linear-gradient(135deg,var(--primary),var(--primary-hot));color:#12081f;
  font-family:'JetBrains Mono',monospace;font-weight:700;font-size:13px;letter-spacing:.08em;
  text-transform:uppercase;padding:12px 18px;transition:transform .15s,box-shadow .2s;
  box-shadow:0 4px 16px rgba(157,140,255,.3)}
.run-btn:hover{transform:translateY(-1px);box-shadow:0 6px 22px rgba(157,140,255,.45)}
.run-btn:active{transform:translateY(0)}
.run-btn svg{width:14px;height:14px}
.run-btn.running{background:var(--panel-2);color:var(--primary);box-shadow:none;cursor:default}

/* expected-outcome selector */
.outcome-select{display:flex;align-items:center;gap:8px;border:1px solid var(--line);border-radius:10px;
  background:var(--inset);padding:8px 12px}
.outcome-select label{font-family:'JetBrains Mono',monospace;font-size:9px;letter-spacing:.14em;
  text-transform:uppercase;color:var(--ink-faint)}
.outcome-select select{background:transparent;border:none;color:var(--ink);font-family:'JetBrains Mono',monospace;
  font-size:13px;font-weight:700;cursor:pointer;outline:none;padding-right:4px}
.outcome-select select option{background:var(--panel);color:var(--ink)}

/* hop where the request FAILS — flashes red and stays marked */
.hop.failed{border-color:var(--red)!important;box-shadow:0 0 0 1px var(--red),0 0 22px rgba(255,93,93,.28) inset!important;
  animation:failFlash .5s ease}
@keyframes failFlash{0%{background:rgba(255,93,93,.35)}100%{background:var(--inset)}}
.hop.failed .hop-ico{border-color:var(--red)}
.hop.failed .hop-ico svg *{stroke:var(--red)!important}
.hop.failed .hop-note{opacity:1;color:var(--red)}
/* hops never reached because the request died earlier */
.hop.unreached{opacity:.32;filter:grayscale(.4)}

/* layout */
.grid{display:grid;grid-template-columns:minmax(280px,.85fr) minmax(340px,1.15fr);gap:24px;align-items:start}
@media(max-width:860px){.grid{grid-template-columns:1fr}}

/* stage list */
.stages{display:flex;flex-direction:column;gap:7px}
.stage-item{position:relative;text-align:left;width:100%;cursor:pointer;
  background:var(--panel);border:1px solid var(--line-soft);border-radius:11px;
  padding:12px 14px 12px 16px;color:var(--ink);transition:border-color .2s,background .2s;
  display:grid;grid-template-columns:auto 1fr;gap:13px;align-items:center;font-family:inherit}
.stage-item:hover{border-color:var(--line);background:var(--panel-2)}
.stage-item:focus-visible{outline:2px solid var(--primary);outline-offset:2px}
.stage-item.active{border-color:var(--primary);background:linear-gradient(90deg,var(--primary-soft),transparent 70%)}
.stage-item.active::before{content:"";position:absolute;left:0;top:8px;bottom:8px;width:3px;border-radius:3px;
  background:var(--primary);box-shadow:0 0 12px var(--primary-hot)}
.stage-item.done .num{background:var(--green);border-color:var(--green);color:#04220f}
.num{font-family:'JetBrains Mono',monospace;font-weight:700;font-size:11px;color:var(--primary);
  border:1px solid var(--line);border-radius:50%;width:26px;height:26px;display:grid;place-items:center;background:var(--inset);transition:.3s}
.stage-item.active .num{border-color:var(--primary);color:#12081f;background:var(--primary-hot)}
.stage-main{display:flex;flex-direction:column;gap:2px;min-width:0}
.stage-name{font-family:'JetBrains Mono',monospace;font-weight:700;font-size:15px}
.stage-tag{font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:var(--ink-faint)}

/* stage panel (right) */
.panel{position:sticky;top:24px;background:var(--panel);border:1px solid var(--line);
  border-radius:var(--radius);overflow:hidden}
.panel-inner{padding:16px;display:flex;flex-direction:column;gap:0}

/* the network spine */
.spine{position:relative;padding:6px 0}
.hop{display:flex;align-items:center;gap:14px;padding:10px 12px;border:1px solid var(--line);
  border-radius:11px;background:var(--inset);transition:border-color .3s,box-shadow .3s,background .3s;position:relative;z-index:2}
.hop.lit{border-color:var(--primary);box-shadow:0 0 0 1px var(--primary),0 0 20px rgba(157,140,255,.18) inset}
.hop.secure.lit{border-color:var(--secure);box-shadow:0 0 0 1px var(--secure),0 0 20px rgba(53,208,230,.18) inset}
.hop-ico{width:34px;height:34px;flex:none;border-radius:9px;background:var(--panel-2);
  display:grid;place-items:center;border:1px solid var(--line)}
.hop-ico svg{width:19px;height:19px}
.hop-body{flex:1;min-width:0}
.hop-title{font-family:'JetBrains Mono',monospace;font-weight:700;font-size:14px;color:var(--ink)}
.hop-sub{font-family:'JetBrains Mono',monospace;font-size:11px;color:var(--ink-faint);margin-top:1px}
.hop-note{font-family:'JetBrains Mono',monospace;font-size:11px;color:var(--primary);opacity:0;transition:opacity .3s;text-align:right;white-space:nowrap}
.hop.lit .hop-note{opacity:1}

/* connector rail behind hops */
.rail{position:absolute;left:29px;top:16px;bottom:16px;width:2px;background:var(--line-soft);z-index:1}
.rail-fill{position:absolute;left:0;top:0;width:100%;height:0;background:linear-gradient(180deg,var(--primary),var(--secure));
  transition:height .3s ease;box-shadow:0 0 8px var(--primary)}

.hop-gap{height:8px}

/* travelling packet */
.packet{position:fixed;width:15px;height:15px;border-radius:4px;z-index:80;pointer-events:none;
  background:var(--primary);box-shadow:0 0 14px 3px rgba(157,140,255,.7);
  transform:translate(-50%,-50%) rotate(45deg);display:grid;place-items:center}
.packet.resp{background:var(--green);box-shadow:0 0 14px 3px rgba(46,156,107,.7)}
.packet.moving{transition:left .5s cubic-bezier(.5,0,.2,1),top .5s cubic-bezier(.5,0,.2,1)}
/* labeled handshake packet (SYN, ACK, etc.) — a pill instead of a diamond */
.packet.labeled{width:auto;height:auto;border-radius:6px;transform:translate(-50%,-50%);
  padding:3px 8px;font-family:'JetBrains Mono',monospace;font-size:10px;font-weight:700;
  color:#12081f;white-space:nowrap}
.packet.labeled.violet{background:var(--secure);box-shadow:0 0 12px 2px rgba(53,208,230,.6);color:#04222a}

/* detail card */
.detail{margin-top:14px;border:1px solid var(--line);border-radius:11px;background:var(--inset);overflow:hidden}
.detail-head{display:flex;align-items:center;gap:8px;padding:9px 12px;border-bottom:1px solid var(--line);background:var(--panel)}
.detail-head .d-dot{width:9px;height:9px;border-radius:50%;background:var(--primary)}
.detail-head .d-label{font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:.16em;
  text-transform:uppercase;color:var(--ink-faint)}
.detail-body{padding:11px 13px;font-family:'JetBrains Mono',monospace;font-size:12.5px;line-height:1.7;min-height:74px}
.detail-body .k{color:var(--primary)}
.detail-body .v{color:var(--ink-dim)}
.detail-body .status{color:var(--green);font-weight:700}
.detail-body .redir-s{color:var(--amber);font-weight:700}
.detail-body .err-s{color:var(--pink);font-weight:700}
.detail-body .muted{color:var(--ink-faint)}

/* status-code chips (response stage only) */
.status-chips{display:flex;flex-wrap:wrap;gap:7px;margin-top:12px}
.status-chips .sc-label{width:100%;font-family:'JetBrains Mono',monospace;font-size:9px;
  letter-spacing:.16em;text-transform:uppercase;color:var(--ink-faint);margin-bottom:2px}
.chip{cursor:pointer;font-family:'JetBrains Mono',monospace;font-size:12px;font-weight:700;
  border:1px solid var(--line);border-radius:8px;padding:6px 11px;background:var(--inset);color:var(--ink-dim);
  transition:border-color .2s,color .2s,background .2s}
.chip:hover{border-color:var(--ink-faint);color:var(--ink)}
.chip.active{color:#1A1A1D}
.chip[data-cls="ok"].active{background:var(--green);border-color:var(--green)}
.chip[data-cls="redir"].active{background:var(--amber);border-color:var(--amber)}
.chip[data-cls="clienterr"].active{background:var(--pink);border-color:var(--pink)}
.chip[data-cls="servererr"].active{background:var(--red);border-color:var(--red)}

/* explanation */
.explain{border-top:1px solid var(--line);padding:16px;background:var(--panel-lift)}
.explain h3{font-family:'JetBrains Mono',monospace;font-size:13px;letter-spacing:.08em;color:var(--primary);text-transform:uppercase;margin-bottom:6px}
.explain p{font-size:14px;color:var(--ink)}

/* footer */
footer{margin-top:26px;border-top:1px solid var(--line);padding-top:16px;display:flex;
  justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap}
.foot-flow{font-family:'JetBrains Mono',monospace;font-size:12px;letter-spacing:.1em;color:var(--ink-dim)}
.foot-flow b{color:var(--primary)}
.counter{font-family:'JetBrains Mono',monospace;font-size:13px;color:var(--primary);border:1px solid var(--line);border-radius:8px;padding:6px 12px}
.tagline{font-family:'JetBrains Mono',monospace;font-weight:700;color:var(--ink);font-size:clamp(14px,3vw,18px);margin-top:14px}

@media(prefers-reduced-motion:reduce){*{animation:none!important;transition:none!important}}
</style>
@endverbatim

<div class="wrap">

  <div class="eyebrow"><span>&gt;_ coding chops</span><span class="right">{{ $guide['eyebrow'] }}</span></div>

  <div class="masthead">
    <div class="glyph">
      <img src="{{ asset($guide['logo']) }}" alt="" />
    </div>
    <div>
      <h1>The HTTP <span class="accent">Request</span> Lifecycle</h1>
      <div class="sub">{{ $guide['subtitle'] }}</div>
    </div>
  </div>

  <div class="rule"></div>

  <div class="runbar">
    <div class="url-box">
      <span class="lock">🔒</span>
      <span class="url"><span class="scheme">https://</span>api.example.com<span class="muted">/users</span></span>
    </div>
    <div class="outcome-select">
      <label for="outcome">Outcome</label>
      <select id="outcome">
        <option value="200">200 OK — success</option>
        <option value="404">404 Not Found</option>
        <option value="500">500 Server Error</option>
        <option value="dns">DNS failure</option>
        <option value="tcp">Connection refused</option>
        <option value="tls">TLS / cert error</option>
      </select>
    </div>
    <button class="run-btn" id="runBtn">
      <svg viewBox="0 0 24 24" fill="currentColor"><path d="M6 4l14 8-14 8V4z"/></svg>
      Run request
    </button>
  </div>

  <div class="grid">
    <div class="stages" id="stages" role="tablist" aria-label="Request stages"></div>

    <div class="panel">
      <div class="panel-inner">
        <div class="spine" id="spine">
          <div class="rail"><div class="rail-fill" id="railFill"></div></div>
          <!-- hops injected here -->
          <div id="hops"></div>
        </div>

        <div class="detail">
          <div class="detail-head"><span class="d-dot"></span><span class="d-label" id="detailLabel">request</span></div>
          <div class="detail-body" id="detailBody"></div>
        </div>
      </div>

      <div class="explain">
        <h3 id="exTitle"></h3>
        <p id="exBody"></p>
      </div>
    </div>
  </div>

  <footer>
    <div class="foot-flow">browser <b>›</b> dns <b>›</b> tcp <b>›</b> tls <b>›</b> server <b>›</b> response</div>
    <div class="counter" id="counter">01 / 08</div>
  </footer>
  <div class="tagline">ONE URL. EIGHT STEPS. MILLISECONDS.</div>

</div>

@verbatim
<script>
/* ============================================================
   The HTTP request lifecycle.
   State model: a vertical NETWORK SPINE of hops
   (Browser → DNS → TCP → TLS → Server → Response). A request
   packet travels down and a response travels back. Click a
   stage to see that moment, or Run to play the whole journey.
   Engine mirrors prior explainers: seqToken cancellation guard.
   ============================================================ */

let seqToken=0;
const step=(fn,delay,tok)=>setTimeout(()=>{ if(tok===seqToken) fn(); },delay);

/* icons for hops */
const ICO={
  browser:`<svg viewBox="0 0 24 24" fill="none"><rect x="3" y="4" width="18" height="16" rx="2" stroke="#9d8cff" stroke-width="1.6"/><path d="M3 8h18" stroke="#9d8cff" stroke-width="1.6"/><circle cx="6" cy="6" r=".8" fill="#9d8cff"/></svg>`,
  dns:`<svg viewBox="0 0 24 24" fill="none"><path d="M12 3v18M4 8l8-5 8 5-8 5-8-5Z" stroke="#9d8cff" stroke-width="1.5"/></svg>`,
  tcp:`<svg viewBox="0 0 24 24" fill="none"><path d="M4 8h11a4 4 0 0 1 0 8H9M8 4 4 8l4 4M16 12l4 4-4 4" stroke="#9d8cff" stroke-width="1.5"/></svg>`,
  tls:`<svg viewBox="0 0 24 24" fill="none"><rect x="5" y="10" width="14" height="10" rx="2" stroke="#35d0e6" stroke-width="1.6"/><path d="M8 10V7a4 4 0 0 1 8 0v3" stroke="#35d0e6" stroke-width="1.6"/></svg>`,
  server:`<svg viewBox="0 0 24 24" fill="none"><rect x="4" y="4" width="16" height="7" rx="1.5" stroke="#9d8cff" stroke-width="1.5"/><rect x="4" y="13" width="16" height="7" rx="1.5" stroke="#9d8cff" stroke-width="1.5"/><circle cx="8" cy="7.5" r=".9" fill="#9d8cff"/><circle cx="8" cy="16.5" r=".9" fill="#9d8cff"/></svg>`,
  response:`<svg viewBox="0 0 24 24" fill="none"><path d="M20 12a8 8 0 1 1-3-6.2M20 5v4h-4" stroke="#2e9c6b" stroke-width="1.6"/></svg>`,
};

/* The hops shown on the spine (fixed rail). */
const HOPS=[
  {id:"browser", title:"Browser",        sub:"you@localhost",     ico:"browser"},
  {id:"dns",     title:"DNS resolver",   sub:"→ 93.184.216.34",   ico:"dns"},
  {id:"tcp",     title:"TCP connection", sub:"3-way handshake",   ico:"tcp"},
  {id:"tls",     title:"TLS handshake",  sub:"secure channel",    ico:"tls", secure:true},
  {id:"server",  title:"Server",         sub:"api.example.com",   ico:"server"},
];

/* Expected outcomes. Each defines how far the request gets:
   stopAt = index into STAGES where the journey ends. For network failures the
   packet dies at a hop (fail:true, failHop) and later hops are greyed. For HTTP
   responses (200/404/500) the full round trip completes with a coloured result. */
const OUTCOMES={
  "200":{ kind:"http", stopAt:7, color:"var(--green)", statusClass:"status",
    line:"HTTP/1.1 200 OK", detailLabel:"← 200 response",
    extra:[`<span class="k">Content-Type:</span> <span class="v">application/json</span>`,
           `<span class="v">[ { "id": 1, "name": "Ada" }, … ]</span>`],
    body:"The full round trip succeeds. The request reaches the server and a 200 response with your data travels all the way back — the happy path." },
  "404":{ kind:"http", stopAt:7, color:"var(--pink)", statusClass:"err-s",
    line:"HTTP/1.1 404 Not Found", detailLabel:"← 404 response",
    extra:[`<span class="k">Content-Type:</span> <span class="v">application/json</span>`,
           `<span class="v">{ "error": "user not found" }</span>`],
    body:"Notice the request COMPLETES — it reaches the server and comes all the way back. A 404 isn't a network failure; it's a successful round trip whose answer is \"that resource isn't here.\" The problem is the path, not the connection." },
  "500":{ kind:"http", stopAt:7, color:"var(--red)", statusClass:"err-s",
    line:"HTTP/1.1 500 Internal Server Error", detailLabel:"← 500 response",
    extra:[`<span class="muted">the request was fine — the server failed</span>`],
    body:"Also a completed round trip: the request arrived fine, but the server hit a bug or crash while handling it. 5xx means the server's fault, not yours — and unlike a DNS or TCP failure, you did reach it." },
  "dns":{ kind:"fail", failHop:"dns", stopStage:1, color:"var(--red)", note:"ENOTFOUND",
    detailLabel:"✕ dns failure", statusClass:"err-s", line:"Error: getaddrinfo ENOTFOUND",
    extra:[`<span class="muted">the domain didn't resolve to any IP</span>`,
           `<span class="muted">the request never left your machine</span>`],
    body:"The journey dies at DNS. The domain name couldn't be resolved to an IP address, so there's nowhere to send the request — it never even reaches the network. Everything below is never attempted." },
  "tcp":{ kind:"fail", failHop:"tcp", stopStage:2, color:"var(--red)", note:"ECONNREFUSED",
    detailLabel:"✕ connection refused", statusClass:"err-s", line:"Error: connect ECONNREFUSED",
    extra:[`<span class="muted">the IP exists, but nothing accepted the connection</span>`,
           `<span class="muted">server down, wrong port, or firewalled</span>`],
    body:"DNS worked — we have an IP — but the TCP handshake fails: nothing answers on the other end. The server may be down, on a different port, or blocked by a firewall. No secure channel, no request." },
  "tls":{ kind:"fail", failHop:"tls", stopStage:3, color:"var(--red)", note:"CERT_INVALID",
    detailLabel:"✕ tls error", statusClass:"err-s", line:"Error: CERT_HAS_EXPIRED",
    extra:[`<span class="muted">connected, but the certificate didn't verify</span>`,
           `<span class="muted">expired, self-signed, or wrong host</span>`],
    body:"We connected, but the TLS handshake fails — the server's certificate couldn't be trusted (expired, self-signed, or for the wrong host). The browser refuses to send anything over an unverified channel. This is the padlock protecting you." },
};
let currentOutcome="200";

/* The 8 stages of the journey. Each: which hop(s) light, packet motion,
   detail card content, and explanation. */
const STAGES=[
  { n:"01", name:"URL entered", tag:"you hit enter", lit:["browser"],
    title:"You hit enter",
    body:"You type a URL and press enter. The browser parses it into a scheme (https), a host (api.example.com), and a path (/users) — then begins the journey to fetch it.",
    detail:{label:"url", lines:[
      `<span class="k">scheme</span> <span class="v">https</span>`,
      `<span class="k">host</span>   <span class="v">api.example.com</span>`,
      `<span class="k">path</span>   <span class="v">/users</span>`]},
    play:(tok)=>{ litHops(["browser"]); fillRail(0); } },

  { n:"02", name:"DNS lookup", tag:"domain → IP", lit:["dns"],
    title:"DNS lookup",
    body:"The host name means nothing to the network — it needs an IP address. The browser asks a DNS resolver \"where is api.example.com?\" and gets back an address like 93.184.216.34.",
    detail:{label:"dns", lines:[
      `<span class="k">query</span>  <span class="v">api.example.com</span>`,
      `<span class="k">answer</span> <span class="v">93.184.216.34</span>`,
      `<span class="muted">cached for next time (TTL)</span>`]},
    play:(tok)=>{ litHops(["browser","dns"]); fillRail(1); flyPacket("hop-browser","hop-dns",tok); } },

  { n:"03", name:"TCP handshake", tag:"connect", lit:["tcp"],
    title:"TCP handshake",
    body:"Before any data flows, browser and server agree to talk. The 3-way handshake: the browser says SYN, the server replies SYN-ACK, the browser confirms ACK. Now there's a reliable connection.",
    detail:{label:"tcp · 3-way", lines:[
      `<span class="v">→ SYN</span>`,
      `<span class="v">← SYN-ACK</span>`,
      `<span class="v">→ ACK</span> <span class="muted">connected</span>`]},
    progressive:true,
    play:(tok)=>{ litHops(["dns","tcp"]); fillRail(2);
      handshake("hop-dns","hop-tcp",[
        {label:"SYN",dir:"down"},
        {label:"SYN-ACK",dir:"up"},
        {label:"ACK",dir:"down"},
      ],tok); } },

  { n:"04", name:"TLS handshake", tag:"go secure", lit:["tls"],
    title:"TLS handshake",
    body:"Because it's https, the connection is encrypted. Browser and server exchange certificates and keys, verify identity, and agree on a secret. From here, everything is private — that's the padlock.",
    detail:{label:"tls · secure", lines:[
      `<span class="v">→ ClientHello</span>`,
      `<span class="v">← Certificate + ServerHello</span>`,
      `<span class="status">🔒 encrypted channel ready</span>`]},
    progressive:true,
    play:(tok)=>{ litHops(["tcp","tls"]); fillRail(3);
      handshake("hop-tcp","hop-tls",[
        {label:"ClientHello",dir:"down"},
        {label:"Certificate",dir:"up"},
        {label:"Finished 🔒",dir:"down"},
      ],tok,true); } },

  { n:"05", name:"HTTP request", tag:"GET /users", lit:["tls","server"],
    title:"The request is sent",
    body:"Now the actual HTTP request travels to the server: a method (GET), the path, and headers describing the browser and what it accepts. It flows through the secure channel to the server.",
    detail:{label:"→ request", lines:[
      `<span class="k">GET</span> <span class="v">/users HTTP/1.1</span>`,
      `<span class="k">Host:</span> <span class="v">api.example.com</span>`,
      `<span class="k">Accept:</span> <span class="v">application/json</span>`]},
    play:(tok)=>{ litHops(["tls","server"]); fillRail(4); flyPacket("hop-tls","hop-server",tok); } },

  { n:"06", name:"Server processing", tag:"build response", lit:["server"],
    title:"The server works",
    body:"The server receives the request, runs your application code, maybe queries a database, and assembles a response. This is where the actual work happens — everything else is transport.",
    detail:{label:"server", lines:[
      `<span class="muted">routing GET /users…</span>`,
      `<span class="muted">querying database…</span>`,
      `<span class="v">200 OK — 24 users</span>`]},
    play:(tok)=>{ litHops(["server"]); fillRail(4); pulseHop("hop-server",tok); } },

  { n:"07", name:"HTTP response", tag:"the answer comes back", lit:["server","browser"],
    title:"The response comes back",
    body:"The server sends back a status code, headers, and body. The status code is the headline — it tells the browser what happened.",
    isResponse:true,
    play:(tok)=>{ litHops(["server","browser"]); fillRail(4);
      const o=OUTCOMES[currentOutcome];
      flyPacket("hop-server","hop-browser",tok,true,o.color||"var(--green)"); } },

  { n:"08", name:"Browser renders", tag:"paint", lit:["browser"],
    title:"The page renders",
    body:"The browser parses the response and paints it to the screen — data becomes pixels. The whole round trip took milliseconds, and you see your page.",
    detail:{label:"render", lines:[
      `<span class="muted">parse json…</span>`,
      `<span class="muted">build DOM…</span>`,
      `<span class="status">✓ painted in 142ms</span>`]},
    play:(tok)=>{ litHops(["browser"]); fillRail(0); pulseHop("hop-browser",tok); } },
];

/* ---------- render spine hops ---------- */
function buildHops(){
  const host=document.getElementById("hops");
  host.innerHTML="";
  HOPS.forEach((h,i)=>{
    const d=document.createElement("div");
    d.className="hop"+(h.secure?" secure":"");
    d.id="hop-"+h.id;
    d.innerHTML=`<div class="hop-ico">${ICO[h.ico]}</div>
      <div class="hop-body"><div class="hop-title">${h.title}</div><div class="hop-sub">${h.sub}</div></div>
      <div class="hop-note" id="note-${h.id}"></div>`;
    host.appendChild(d);
    if(i<HOPS.length-1){ const g=document.createElement("div");g.className="hop-gap";host.appendChild(g); }
  });
}
function litHops(ids){
  HOPS.forEach(h=>{ const el=document.getElementById("hop-"+h.id); if(el) el.classList.toggle("lit", ids.includes(h.id)); });
}
function fillRail(hopIndex){
  // fill the rail down to a given hop index (0..4)
  const fill=document.getElementById("railFill");
  const frac=HOPS.length<=1?0:hopIndex/(HOPS.length-1);
  fill.style.height=(frac*100)+"%";
}
function pulseHop(hopId,tok){
  const el=document.getElementById(hopId); if(!el) return;
  el.classList.add("lit");
}

/* travelling packet between two hop elements */
function flyPacket(fromId,toId,tok,isResp,color){
  const from=document.getElementById(fromId), to=document.getElementById(toId);
  if(!from||!to) return;
  const a=from.getBoundingClientRect(), b=to.getBoundingClientRect();
  const p=document.createElement("div");
  p.className="packet"+(isResp?" resp":"");
  if(color){ p.style.background=color; p.style.boxShadow=`0 0 14px 3px ${color}`; }
  p.style.left=(a.left+29)+"px"; p.style.top=(a.top+a.height/2)+"px";
  document.body.appendChild(p);
  requestAnimationFrame(()=>requestAnimationFrame(()=>{
    if(tok!==seqToken){ p.remove(); return; }
    p.classList.add("moving");
    p.style.left=(b.left+29)+"px"; p.style.top=(b.top+b.height/2)+"px";
  }));
  setTimeout(()=>{ if(tok!==seqToken){ p.remove(); return; }
    p.style.opacity="0"; setTimeout(()=>p.remove(),200); }, 520);
}

/* Fire a single labeled packet between two hops (one leg of a handshake).
   dir 'down' = fromId→toId, 'up' = toId→fromId. Returns nothing. */
function flyLabeled(fromId,toId,label,tok,violet){
  const from=document.getElementById(fromId), to=document.getElementById(toId);
  if(!from||!to) return;
  const a=from.getBoundingClientRect(), b=to.getBoundingClientRect();
  const p=document.createElement("div");
  p.className="packet labeled"+(violet?" violet":"");
  p.textContent=label;
  p.style.left=(a.left+29)+"px"; p.style.top=(a.top+a.height/2)+"px";
  document.body.appendChild(p);
  requestAnimationFrame(()=>requestAnimationFrame(()=>{
    if(tok!==seqToken){ p.remove(); return; }
    p.classList.add("moving");
    p.style.left=(b.left+29)+"px"; p.style.top=(b.top+b.height/2)+"px";
  }));
  setTimeout(()=>{ if(tok!==seqToken){ p.remove(); return; }
    p.style.opacity="0"; setTimeout(()=>p.remove(),200); }, 520);
}

/* Play a multi-leg handshake between two hops. legs = [{label, dir}] where
   dir is 'down' (a→b) or 'up' (b→a). Each leg fires ~640ms apart, and the
   detail card reveals each line as its packet flies. */
function handshake(hopA, hopB, legs, tok, violet){
  legs.forEach((leg,i)=>{
    step(()=>{
      if(leg.dir==="down") flyLabeled(hopA,hopB,leg.label,tok,violet);
      else flyLabeled(hopB,hopA,leg.label,tok,violet);
      revealDetailLine(i);
    }, i*680, tok);
  });
}
/* reveal detail card lines progressively (used by handshakes) */
function revealDetailLine(idx){
  const lines=document.querySelectorAll("#detailBody > div");
  if(lines[idx]){ lines[idx].style.opacity="1"; }
}

/* ---------- detail + explanation ---------- */
function setDetail(d, progressive){
  document.getElementById("detailLabel").textContent=d.label;
  document.getElementById("detailBody").innerHTML=d.lines
    .map(l=>`<div${progressive?' style="opacity:0;transition:opacity .3s"':''}>${l}</div>`).join("");
}

/* ---------- stage list ---------- */
const stagesEl=document.getElementById("stages");
function buildStages(){
  stagesEl.innerHTML="";
  STAGES.forEach((s,i)=>{
    const b=document.createElement("button");
    b.className="stage-item";b.setAttribute("role","tab");
    b.innerHTML=`<span class="num">${s.n}</span>
      <span class="stage-main"><span class="stage-name">${s.name}</span><span class="stage-tag">${s.tag}</span></span>`;
    b.addEventListener("click",()=>{ stopRun(); select(i); });
    stagesEl.appendChild(b);
  });
}

/* ---------- select a stage ---------- */
let current=0;
function select(i){
  current=i;const s=STAGES[i];
  seqToken++;const tok=seqToken;
  resetHopStates();
  [...stagesEl.querySelectorAll(".stage-item")].forEach((el,idx)=>{
    el.classList.toggle("active",idx===i);
    el.classList.toggle("done",idx<i && running);
  });
  document.getElementById("counter").textContent=`${s.n} / ${String(STAGES.length).padStart(2,"0")}`;
  if(s.isResponse){
    const o=OUTCOMES[currentOutcome];
    document.getElementById("exTitle").textContent=s.title;
    document.getElementById("exBody").textContent=o.body||s.body;
    setDetail({label:o.detailLabel||"← response",
      lines:[`<span class="${o.statusClass}">${o.line}</span>`].concat(o.extra||[])});
  }else{
    document.getElementById("exTitle").textContent=s.title;
    document.getElementById("exBody").textContent=s.body;
    setDetail(s.detail, s.progressive);
  }
  s.play(tok);
}
/* clear any failure/unreached marks from the hops */
function resetHopStates(){
  HOPS.forEach(h=>{ const el=document.getElementById("hop-"+h.id);
    if(el){ el.classList.remove("failed","unreached"); const note=document.getElementById("note-"+h.id); if(note) note.textContent=""; } });
}

/* ---------- run the whole request ---------- */
let running=false, runTimers=[];
function stopRun(){ running=false; runTimers.forEach(clearTimeout); runTimers=[];
  document.getElementById("runBtn").classList.remove("running");
  document.getElementById("runBtn").innerHTML=`<svg viewBox="0 0 24 24" fill="currentColor"><path d="M6 4l14 8-14 8V4z"/></svg> Run request`; }
function runAll(){
  stopRun(); running=true;
  currentOutcome=document.getElementById("outcome").value;
  const o=OUTCOMES[currentOutcome];
  const btn=document.getElementById("runBtn");
  btn.classList.add("running");
  btn.innerHTML=`<svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="8"/></svg> Running…`;

  // how far does this outcome get?
  const lastStage = o.kind==="fail" ? o.stopStage : STAGES.length-1;
  let t=0;
  for(let i=0;i<=lastStage;i++){
    const idx=i;
    runTimers.push(setTimeout(()=>{ if(!running) return;
      select(idx);
      [...stagesEl.querySelectorAll(".stage-item")].forEach((el,k)=>el.classList.toggle("done",k<idx));
    }, t));
    // handshake stages (TCP=2, TLS=3) need longer to finish their bounces
    t += (STAGES[i].progressive ? 2300 : 1300);
  }
  if(o.kind==="fail"){
    // at the failing stage, mark the hop as failed and grey out the rest
    runTimers.push(setTimeout(()=>{ if(!running) return;
      failAtHop(o.failHop, o.note);
      markStageFailed(o.stopStage);
      stopRun();
    }, t));
  }else{
    runTimers.push(setTimeout(()=>{ if(!running) return; stopRun();
      [...stagesEl.querySelectorAll(".stage-item")].forEach(el=>el.classList.add("done"));
    }, t));
  }
}
/* mark a hop as the failure point; grey every hop below it */
function failAtHop(hopId, note){
  seqToken++;                     // cancel any in-flight packet
  const idx=HOPS.findIndex(h=>h.id===hopId);
  HOPS.forEach((h,i)=>{
    const el=document.getElementById("hop-"+h.id); if(!el) return;
    el.classList.remove("lit");
    if(i===idx){ el.classList.add("failed");
      const n=document.getElementById("note-"+h.id); if(n) n.textContent=note||"failed"; }
    else if(i>idx){ el.classList.add("unreached"); }
  });
  // stop the rail fill at the failing hop
  fillRail(idx);
}
/* mark the failing stage item red-ish (done up to it, active on it) */
function markStageFailed(stageIdx){
  [...stagesEl.querySelectorAll(".stage-item")].forEach((el,idx)=>{
    el.classList.toggle("done", idx<stageIdx);
    el.classList.toggle("active", idx===stageIdx);
  });
}
document.getElementById("runBtn").addEventListener("click",runAll);
/* re-render current stage if the outcome changes (so the response preview updates) */
document.getElementById("outcome").addEventListener("change",()=>{
  currentOutcome=document.getElementById("outcome").value;
  stopRun();
  // jump to a sensible stage: response for http outcomes, the failing hop's stage for failures
  const o=OUTCOMES[currentOutcome];
  select(o.kind==="fail" ? o.stopStage : 6);
});

/* keyboard nav */
document.addEventListener("keydown",e=>{
  if(e.key==="ArrowDown"||e.key==="ArrowRight"){ stopRun(); select((current+1)%STAGES.length);e.preventDefault(); }
  if(e.key==="ArrowUp"||e.key==="ArrowLeft"){ stopRun(); select((current-1+STAGES.length)%STAGES.length);e.preventDefault(); }
});

/* init */
buildHops();
buildStages();
select(0);
</script>
@endverbatim
</div>
