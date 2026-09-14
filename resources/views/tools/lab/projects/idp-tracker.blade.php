<div class="lab-project" data-csrf="{{ csrf_token() }}">
<!-- {{--
    Lab Vault project: IDP Tracker.

    A full Individual Development Plan tracker: dashboard, 70-20-10 learning
    mix, countdown to deadline, a progress-over-time chart, a
    filterable/searchable competency list with per-activity notes/sources/
    attachments, and a quarterly review log.

    Graduated from a localStorage prototype to a real database — see
    App\Tools\Lab\Http\Controllers\IdpTrackerController and the `idp_*`
    tables. The markup, styling, and rendering logic below are unchanged
    from that prototype; only the persistence layer (the functions that
    used to read/write localStorage, plus the old File System Access
    "Connect Assets Folder" flow) was replaced with fetch() calls against
    that controller.

    CSS is scoped under .lab-project — left unscoped, its `*`, `body`, `a`
    and `header` rules would otherwise bleed onto the platform header this
    page shares via layouts/canvas.blade.php. Everything is wrapped in
    @ verbatim so Blade leaves the CSS at-rules (@media, @keyframes) and JS
    template literals alone — the CSRF token above is the one thing that
    has to come from Blade, so it's a data attribute on this wrapper,
    outside the verbatim block.

    Rendered full-bleed by layouts/canvas.blade.php; registered in
    App\Tools\Lab\ProjectLibrary.
--}} -->

@verbatim
<style>
  @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=League+Gothic&family=League+Spartan:wght@400;600;700&family=JetBrains+Mono:wght@400;500&display=swap');

  .lab-project{
    --bg:#161617; --panel:#222226; --panel2:#2a2a2f; --line:rgba(255,255,255,0.1);
    --ink:#ffffff; --muted:rgba(255,255,255,0.52); --accent:#C3073F; --accent2:#6F2232;
    --exp:#f59e0b; --soc:#4ADE80; --form:#38bdf8;
    --done:#4ADE80; --prog:#C3073F; --kick:rgba(255,255,255,0.4); --hold:#ef4444;
    --shadow:0 6px 24px rgba(0,0,0,.4);
  }
  .lab-project *{box-sizing:border-box;margin:0;padding:0}
  .lab-project{font-family:'Roboto',sans-serif;
    background:var(--bg);color:var(--ink);line-height:1.5;padding-bottom:60px;
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='28' height='28'%3E%3Ccircle cx='14' cy='14' r='1' fill='white' fill-opacity='0.07'/%3E%3C/svg%3E");
    background-size:28px 28px;}
  .lab-project a{color:var(--accent)}
  .lab-project header{background:linear-gradient(135deg,#1c1c20,#111113);border-bottom:1px solid var(--line);
    padding:22px 28px;position:sticky;top:56px;z-index:50;box-shadow:var(--shadow)}
  .lab-project .head-row{display:flex;justify-content:space-between;align-items:flex-start;gap:20px;flex-wrap:wrap}
  .lab-project h1{font-family:'League Gothic',sans-serif;font-weight:400;font-size:30px;letter-spacing:.5px;text-transform:uppercase}
  .lab-project .sub{color:var(--muted);font-size:13px;margin-top:3px;font-weight:300}
  .lab-project .toolbar{display:flex;gap:8px;flex-wrap:wrap}
  .lab-project button,.lab-project select{font-family:'Roboto',sans-serif;font-size:13px;cursor:pointer;border:1px solid var(--line);
    background:var(--panel2);color:var(--ink);padding:8px 13px;border-radius:6px;transition:.15s}
  .lab-project button{font-family:'League Spartan',sans-serif;font-size:11.5px;font-weight:700;letter-spacing:1.4px;text-transform:uppercase}
  .lab-project button:hover{border-color:var(--accent);background:#2f2f34}
  .lab-project button.primary{background:var(--accent);border-color:var(--accent);color:#fff;font-weight:700}
  .lab-project button.primary:hover{background:#a0062f}
  .lab-project .wrap{max-width:1200px;margin:0 auto;padding:24px 28px}

  /* dashboard */
  .lab-project .dash{display:grid;grid-template-columns:1.3fr 1fr;gap:18px;margin-bottom:26px}
  @media(max-width:820px){.lab-project .dash{grid-template-columns:1fr}}
  .lab-project .card{background:var(--panel);border:1px solid var(--line);border-radius:14px;padding:20px;box-shadow:var(--shadow)}
  .lab-project .card h3{font-family:'JetBrains Mono',monospace;font-size:11px;text-transform:uppercase;letter-spacing:2px;color:var(--accent);margin-bottom:16px;display:flex;align-items:center;gap:10px}
  .lab-project .card h3::before{content:'';display:block;width:20px;height:1px;background:var(--accent);flex-shrink:0}
  .lab-project .big-num{font-family:'League Gothic',sans-serif;font-size:56px;font-weight:400;letter-spacing:-1px;line-height:1}
  .lab-project .big-num small{font-size:18px;color:var(--muted);font-weight:400}
  .lab-project .bar{height:12px;border-radius:8px;background:var(--panel2);overflow:hidden;margin-top:14px}
  .lab-project .bar > span{display:block;height:100%;background:linear-gradient(90deg,var(--accent),#ff4d6d);transition:width .5s}
  .lab-project .statgrid{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-top:18px}
  .lab-project .stat{background:var(--panel2);border-radius:10px;padding:12px;text-align:center;border:1px solid var(--line)}
  .lab-project .stat .n{font-family:'League Gothic',sans-serif;font-size:28px;font-weight:400}
  .lab-project .stat .l{font-family:'JetBrains Mono',monospace;font-size:10px;text-transform:uppercase;letter-spacing:1px;color:var(--muted);margin-top:2px}
  .lab-project .stat.done .n{color:var(--done)} .lab-project .stat.prog .n{color:var(--prog)}
  .lab-project .stat.kick .n{color:var(--kick)} .lab-project .stat.hold .n{color:var(--hold)}
  .lab-project .mix{display:flex;flex-direction:column;gap:14px}
  .lab-project .mixrow{display:flex;align-items:center;gap:12px}
  .lab-project .mixrow .lbl{width:32px;font-family:'JetBrains Mono',monospace;font-weight:500;font-size:12.5px}
  .lab-project .mixrow .lbl.exp{color:var(--exp)} .lab-project .mixrow .lbl.soc{color:var(--soc)} .lab-project .mixrow .lbl.form{color:var(--form)}
  .lab-project .mixrow .track{flex:1;height:9px;background:var(--panel2);border-radius:6px;overflow:hidden}
  .lab-project .mixrow .track > span{display:block;height:100%}
  .lab-project .mixrow.exp .track>span{background:var(--exp)} .lab-project .mixrow.soc .track>span{background:var(--soc)} .lab-project .mixrow.form .track>span{background:var(--form)}
  .lab-project .mixrow .pct{width:42px;text-align:right;font-family:'JetBrains Mono',monospace;font-size:11.5px;color:var(--muted)}

  /* filters */
  .lab-project .filters{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:18px}
  .lab-project .filters .grow{flex:1;min-width:180px}
  .lab-project input[type=search]{width:100%;font-family:'Roboto',sans-serif;font-size:13px;padding:9px 13px;border-radius:8px;
    border:1px solid var(--line);background:var(--panel2);color:var(--ink)}

  /* competency block */
  .lab-project .comp{background:var(--panel);border:1px solid var(--line);border-radius:14px;margin-bottom:14px;overflow:hidden;box-shadow:var(--shadow)}
  .lab-project .comp-head{display:flex;align-items:center;gap:14px;padding:16px 20px;cursor:pointer;user-select:none}
  .lab-project .comp-head:hover{background:var(--panel2)}
  .lab-project .comp-head .title{flex:1;font-family:'League Gothic',sans-serif;font-weight:400;font-size:20px;letter-spacing:.3px;text-transform:uppercase}
  .lab-project .comp-head .mini{width:120px;height:7px;background:var(--panel2);border-radius:5px;overflow:hidden}
  .lab-project .comp-head .mini>span{display:block;height:100%;background:linear-gradient(90deg,var(--accent),#ff4d6d)}
  .lab-project .comp-head .cpct{font-family:'JetBrains Mono',monospace;font-size:12px;color:var(--muted);width:38px;text-align:right}
  .lab-project .comp-head .chev{color:var(--accent);transition:.2s}
  .lab-project .comp.open .chev{transform:rotate(90deg)}
  .lab-project .comp-body{display:none;border-top:1px solid var(--line)}
  .lab-project .comp.open .comp-body{display:block}

  .lab-project .act{padding:16px 20px;border-bottom:1px solid var(--line)}
  .lab-project .act:last-child{border-bottom:none}
  .lab-project .act-top{display:flex;gap:12px;align-items:flex-start}
  .lab-project .tag{font-family:'JetBrains Mono',monospace;font-size:10px;font-weight:500;padding:3px 8px;border-radius:6px;white-space:nowrap;margin-top:2px}
  .lab-project .tag.exp{background:rgba(245,158,11,.15);color:var(--exp)}
  .lab-project .tag.soc{background:rgba(74,222,128,.15);color:var(--soc)}
  .lab-project .tag.form{background:rgba(56,189,248,.15);color:var(--form)}
  .lab-project .act-main{flex:1;min-width:0}
  .lab-project .obj{font-weight:500;font-size:13.5px}
  .lab-project .desc{color:var(--muted);font-size:12.5px;margin-top:3px;font-weight:300}
  .lab-project .act-ctl{display:flex;gap:18px;flex-wrap:wrap;align-items:flex-end;margin-top:14px;padding-top:13px;border-top:1px solid var(--line)}
  .lab-project .ctl-field{display:flex;flex-direction:column;gap:5px}
  .lab-project .ctl-label{font-family:'JetBrains Mono',monospace;font-size:9.5px;letter-spacing:.8px;text-transform:uppercase;color:var(--muted)}
  .lab-project .act-ctl select{padding:6px 10px;font-size:12px}
  .lab-project .status-select{min-width:136px;font-weight:500;border-width:1px;border-style:solid}
  .lab-project .status-select.done{background:rgba(74,222,128,.1);border-color:rgba(74,222,128,.35);color:var(--done)}
  .lab-project .status-select.prog{background:rgba(195,7,63,.1);border-color:rgba(195,7,63,.4);color:var(--prog)}
  .lab-project .status-select.kick{background:var(--panel2);border-color:var(--line);color:var(--ink)}
  .lab-project .status-select.hold{background:rgba(239,68,68,.1);border-color:rgba(239,68,68,.35);color:var(--hold)}
  .lab-project .act-ctl input[type=date]{font-family:'Roboto',sans-serif;font-size:12px;padding:5px 8px;border-radius:7px;
    border:1px solid var(--line);background:var(--panel2);color:var(--ink)}
  .lab-project .ctl-badges{display:flex;align-items:center;gap:8px;padding-bottom:1px}
  .lab-project .overdue{color:var(--hold);font-size:11px;font-weight:600}
  .lab-project .notes-toggle{font-family:'League Spartan',sans-serif;font-size:11px;font-weight:600;letter-spacing:.3px;color:var(--accent);cursor:pointer;background:none;border:none;padding:4px 0;margin-left:auto}
  .lab-project .notes-box{display:none;margin-top:10px}
  .lab-project .notes-box.open{display:block}
  .lab-project .notes-box textarea{width:100%;min-height:56px;font-family:'Roboto',sans-serif;font-size:12.5px;padding:9px;
    border-radius:8px;border:1px solid var(--line);background:var(--panel2);color:var(--ink);resize:vertical}
  .lab-project .due{font-family:'JetBrains Mono',monospace;font-size:10.5px;font-weight:500;padding:3px 9px;border-radius:20px}
  .lab-project .due.od{background:rgba(239,68,68,.15);color:var(--hold)}
  .lab-project .due.soon{background:rgba(245,158,11,.18);color:var(--exp)}
  .lab-project .due.near{background:rgba(195,7,63,.14);color:var(--accent)}
  .lab-project .due.far{background:var(--panel2);color:var(--muted)}
  .lab-project .due.ok{background:rgba(74,222,128,.15);color:var(--done)}
  .lab-project .src-head{font-family:'JetBrains Mono',monospace;font-size:10px;text-transform:uppercase;letter-spacing:1px;color:var(--muted);margin:12px 0 8px}
  .lab-project .src-list{display:flex;flex-direction:column;gap:6px}
  .lab-project .src-item{display:flex;align-items:center;gap:8px;background:var(--panel2);border:1px solid var(--line);
    border-radius:8px;padding:7px 10px}
  .lab-project .src-item a{flex:1;font-size:12.5px;text-decoration:none;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
  .lab-project .src-item a:hover{text-decoration:underline}
  .lab-project .src-del{background:none;border:none;color:var(--muted);cursor:pointer;font-size:13px;padding:2px 6px}
  .lab-project .src-del:hover{color:var(--hold)}
  .lab-project .src-empty{font-size:12px;color:var(--muted);font-style:italic;font-weight:300}
  .lab-project .src-add{display:flex;gap:6px;margin-top:8px;flex-wrap:wrap}
  .lab-project .src-add input{font-family:'Roboto',sans-serif;font-size:12px;padding:7px 9px;border-radius:8px;
    border:1px solid var(--line);background:var(--panel2);color:var(--ink)}
  .lab-project .src-add input[type=text]{flex:0 0 200px} .lab-project .src-add input[type=url]{flex:1;min-width:160px}
  .lab-project .src-add button{font-size:11px;padding:7px 14px}
  .lab-project .img-head{font-family:'JetBrains Mono',monospace;font-size:10px;text-transform:uppercase;letter-spacing:1px;color:var(--muted);margin:16px 0 8px}
  .lab-project .img-grid{display:flex;flex-direction:column;gap:8px}
  .lab-project .att-row{display:flex;flex-wrap:wrap;gap:8px;align-items:center}
  .lab-project .img-thumb{position:relative;width:88px;height:88px;border-radius:8px;overflow:hidden;background:var(--panel2);border:1px solid var(--line);cursor:pointer}
  .lab-project .img-thumb img{width:100%;height:100%;object-fit:cover;display:block}
  .lab-project .img-del{position:absolute;top:3px;right:3px;background:rgba(0,0,0,.6);border:none;color:#fff;
    width:18px;height:18px;border-radius:50%;font-size:10px;line-height:1;cursor:pointer;padding:0}
  .lab-project .img-del:hover{background:var(--hold)}
  .lab-project .file-chip{display:flex;align-items:center;gap:8px;background:var(--panel2);border:1px solid var(--line);
    border-radius:8px;padding:8px 8px 8px 12px;cursor:pointer;}
  .lab-project .file-chip:hover{border-color:rgba(195,7,63,.4)}
  .lab-project .file-chip .fc-ic{font-size:16px;flex-shrink:0}
  .lab-project .file-chip .fc-name{font-size:12px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;min-width:0;flex:1}
  .lab-project .file-chip .img-del{position:static;background:none;color:var(--muted);width:auto;height:auto;font-size:12px;flex-shrink:0}
  .lab-project .file-chip .img-del:hover{color:var(--hold);background:none}
  .lab-project .countdown{display:flex;align-items:baseline;gap:8px;margin-top:6px}
  .lab-project .countdown .cd-num{font-family:'League Gothic',sans-serif;font-size:34px;font-weight:400;color:var(--accent)}
  .lab-project .countdown .cd-lbl{font-size:12.5px;color:var(--muted);font-weight:300}
  .lab-project .nudges{background:linear-gradient(135deg,rgba(245,158,11,.12),rgba(239,68,68,.08));
    border:1px solid var(--line);border-left:3px solid var(--exp);border-radius:14px;padding:18px 20px;margin-bottom:26px;box-shadow:var(--shadow)}
  .lab-project .nudges.clear{border-left-color:var(--done);background:linear-gradient(135deg,rgba(74,222,128,.1),rgba(74,222,128,.03))}
  .lab-project .nudges h3{font-family:'JetBrains Mono',monospace;font-size:11px;text-transform:uppercase;letter-spacing:2px;color:var(--muted);margin-bottom:12px}
  .lab-project .nudge{display:flex;gap:12px;align-items:flex-start;padding:9px 0;border-bottom:1px solid var(--line)}
  .lab-project .nudge:last-child{border-bottom:none}
  .lab-project .nudge .ic{font-size:15px;margin-top:1px}
  .lab-project .nudge .body{flex:1;font-size:13px}
  .lab-project .nudge .body .a{font-weight:600}
  .lab-project .nudge .body .m{color:var(--muted);font-size:12px;margin-top:1px;font-weight:300}
  .lab-project .nudge .body .m b{color:var(--ink);font-weight:600}
  .lab-project .nudge .jump{font-family:'League Spartan',sans-serif;font-size:10.5px;font-weight:700;letter-spacing:1px;color:var(--accent);background:none;border:1px solid var(--line);
    border-radius:7px;padding:5px 10px;cursor:pointer;white-space:nowrap}
  .lab-project .nudge .jump:hover{border-color:var(--accent)}
  .lab-project #chartWrap{width:100%;overflow:hidden}
  .lab-project #chart{width:100%;height:220px;display:block}
  .lab-project .flash-row{animation:hl 1.6s ease}
  @keyframes hl{0%,100%{background:transparent}25%{background:rgba(195,7,63,.18)}}

  /* review log */
  .lab-project .review-card textarea{width:100%;font-family:'Roboto',sans-serif;font-size:13px;padding:10px;border-radius:8px;
    border:1px solid var(--line);background:var(--panel2);color:var(--ink);resize:vertical;min-height:70px;margin-bottom:8px}
  .lab-project .rev-entry{background:var(--panel2);border-radius:10px;padding:14px;margin-bottom:10px;border:1px solid var(--line)}
  .lab-project .rev-entry .date{font-family:'JetBrains Mono',monospace;font-size:11.5px;color:var(--accent);font-weight:500;margin-bottom:6px}
  .lab-project .rev-entry p{font-size:12.5px;margin:4px 0;font-weight:300}
  .lab-project .rev-entry .k{color:var(--muted);font-size:11px;text-transform:uppercase;letter-spacing:.4px}
  .lab-project .rev-form{display:grid;gap:8px}
  .lab-project .rev-form input[type=date]{width:170px;font-family:'Roboto',sans-serif;font-size:13px;padding:8px;border-radius:8px;border:1px solid var(--line);background:var(--panel2);color:var(--ink)}
  .lab-project .section-title{font-family:'JetBrains Mono',monospace;font-size:11px;text-transform:uppercase;letter-spacing:2px;color:var(--accent);margin:30px 0 14px;display:flex;align-items:center;gap:10px}
  .lab-project .section-title::before{content:'';display:block;width:20px;height:1px;background:var(--accent);flex-shrink:0}
  .lab-project .saved-flash{position:fixed;bottom:20px;right:20px;background:var(--accent);color:#fff;font-weight:600;
    font-size:13px;padding:10px 16px;border-radius:10px;opacity:0;transition:.3s;pointer-events:none;z-index:100}
  .lab-project .saved-flash.show{opacity:1}
  .lab-project .del{color:var(--hold);background:none;border:none;font-size:11px;cursor:pointer;letter-spacing:0;text-transform:none;font-family:'Roboto',sans-serif;font-weight:400}
</style>

<header>
  <div class="head-row">
    <div>
      <h1>Individual Development Plan Tracker</h1>
      <div class="sub">Iverson Craig G. Guno · Web Developer → Senior Web Developer · Started Sep 1, 2025</div>
    </div>
    <div class="toolbar">
      <button onclick="exportJSON()">↓ Export</button>
      <input type="file" id="fileUploadInput" multiple style="display:none">
      <button class="primary" onclick="openPrint()">🖨 Print Summary</button>
      <button onclick="resetAll()">↺ Reset</button>
    </div>
  </div>
</header>

<div class="wrap">
  <!-- DASHBOARD -->
  <div class="dash">
    <div class="card">
      <h3>Overall Progress</h3>
      <div class="big-num" id="overallPct">0<small>%</small></div>
      <div class="bar"><span id="overallBar" style="width:0%"></span></div>
      <div class="statgrid">
        <div class="stat done"><div class="n" id="sDone">0</div><div class="l">Completed</div></div>
        <div class="stat prog"><div class="n" id="sProg">0</div><div class="l">In Progress</div></div>
        <div class="stat kick"><div class="n" id="sKick">0</div><div class="l">To Kick-off</div></div>
        <div class="stat hold"><div class="n" id="sHold">0</div><div class="l">On Hold</div></div>
      </div>
    </div>
    <div class="card">
      <h3>70-20-10 Learning Mix</h3>
      <div class="mix">
        <div class="mixrow exp"><span class="lbl exp">70</span><div class="track"><span id="mixExp" style="width:0%"></span></div><span class="pct" id="pctExp">0%</span></div>
        <div class="mixrow soc"><span class="lbl soc">20</span><div class="track"><span id="mixSoc" style="width:0%"></span></div><span class="pct" id="pctSoc">0%</span></div>
        <div class="mixrow form"><span class="lbl form">10</span><div class="track"><span id="mixForm" style="width:0%"></span></div><span class="pct" id="pctForm">0%</span></div>
      </div>
      <h3 style="margin-top:20px">Target Completion — First Week of December</h3>
      <div class="countdown"><span class="cd-num" id="cdNum">—</span><span class="cd-lbl" id="cdLbl">days to go (deadline Dec 7, 2026)</span></div>
      <div class="sub" id="paceNote" style="font-size:12px;margin-top:8px"></div>
    </div>
  </div>

  <!-- ACT-NOW NUDGES -->
  <div id="nudges"></div>

  <!-- PROGRESS OVER TIME -->
  <div class="card" style="margin-bottom:26px">
    <h3>Progress Over Time</h3>
    <div id="chartWrap"><svg id="chart" viewBox="0 0 720 220" preserveAspectRatio="none"></svg></div>
    <div class="sub" id="chartNote" style="font-size:12px;margin-top:8px"></div>
  </div>

  <!-- FILTERS -->
  <div class="filters">
    <div class="grow"><input type="search" id="search" placeholder="Search objectives or activities…" oninput="render()"></div>
    <select id="fStatus" onchange="render()">
      <option value="">All statuses</option>
      <option value="To Kick-off">To Kick-off</option>
      <option value="In Progress">In Progress</option>
      <option value="Completed">Completed</option>
      <option value="On Hold">On Hold</option>
    </select>
    <select id="fType" onchange="render()">
      <option value="">All types</option>
      <option value="70">70 · Experiential</option>
      <option value="20">20 · Social</option>
      <option value="10">10 · Formal</option>
    </select>
    <button onclick="toggleAll(true)">Expand all</button>
    <button onclick="toggleAll(false)">Collapse all</button>
  </div>

  <div id="comps"></div>

  <!-- REVIEW LOG -->
  <div class="section-title">Quarterly Review Log</div>
  <div class="card review-card">
    <div class="rev-form">
      <input type="date" id="revDate">
      <textarea id="revProgress" placeholder="Key progress since last review…"></textarea>
      <textarea id="revChallenge" placeholder="Challenges encountered…"></textarea>
      <textarea id="revAdjust" placeholder="Adjustments made…"></textarea>
      <div><button class="primary" onclick="addReview()">+ Add review entry</button></div>
    </div>
    <div id="reviewList" style="margin-top:16px"></div>
  </div>
</div>

<div class="saved-flash" id="flash">Saved ✓</div>


<script>
const STATUSES=['To Kick-off','In Progress','Completed','On Hold'];
const pillClass={'Completed':'done','In Progress':'prog','To Kick-off':'kick','On Hold':'hold'};
const typeMeta={'70':{cls:'exp',lbl:'70 · Experiential'},'20':{cls:'soc',lbl:'20 · Social'},'10':{cls:'form',lbl:'10 · Formal'}};
const DEADLINE="2026-12-07";

// ---- server API ----
// The wrapper div carries the CSRF token as a data attribute since this
// whole file is wrapped in @verbatim and can't use {{ csrf_token() }} here.
const ROOT=document.currentScript.closest('.lab-project');
const CSRF=ROOT.dataset.csrf;
const API='/lab/idp-tracker';

async function api(method,url,body){
  const opts={method,headers:{'X-CSRF-TOKEN':CSRF,'Accept':'application/json'}};
  if(body instanceof FormData) opts.body=body;
  else if(body!==undefined){ opts.headers['Content-Type']='application/json'; opts.body=JSON.stringify(body); }
  const res=await fetch(url,opts);
  if(!res.ok) throw new Error('Request failed ('+res.status+')');
  return res.status===204 ? null : res.json();
}

let state;
let openAreas={}; // accordion open/closed — purely client-side, not persisted

async function load(){
  state=await api('GET',`${API}/state`);
}

function flash(){const f=document.getElementById('flash');f.classList.add('show');clearTimeout(window._ft);window._ft=setTimeout(()=>f.classList.remove('show'),1200);}

const weight={'70':.5,'20':.3,'10':.2}; // effort weight per activity type for a nicer % feel
function progressOf(a){return a.status==='Completed'?1:a.status==='In Progress'?0.5:0;}

function computeOverall(){
  let num=0,den=0;
  state.activities.forEach(a=>{const w=weight[a.type];num+=progressOf(a)*w;den+=w;});
  return den?Math.round(num/den*100):0;
}
function isOverdue(a){return a.target && a.status!=='Completed' && new Date(a.target) < new Date(new Date().toDateString());}

function render(){
  // dashboard counts
  const c={'Completed':0,'In Progress':0,'To Kick-off':0,'On Hold':0};
  state.activities.forEach(a=>c[a.status]++);
  document.getElementById('sDone').textContent=c['Completed'];
  document.getElementById('sProg').textContent=c['In Progress'];
  document.getElementById('sKick').textContent=c['To Kick-off'];
  document.getElementById('sHold').textContent=c['On Hold'];
  const ov=computeOverall();
  document.getElementById('overallPct').innerHTML=ov+'<small>%</small>';
  document.getElementById('overallBar').style.width=ov+'%';
  // countdown to deadline
  const dleft=daysUntil(DEADLINE);
  document.getElementById('cdNum').textContent=dleft;
  const overdueCount=state.activities.filter(a=>isOverdue(a)).length;
  const pn=document.getElementById('paceNote');
  if(overdueCount>0)pn.innerHTML=`<span style="color:var(--hold)">⚠ ${overdueCount} activit${overdueCount>1?'ies':'y'} past target</span> — reprioritise these first.`;
  else if(c['Completed']===state.activities.length)pn.innerHTML='<span style="color:var(--done)">🎉 All activities complete — ahead of the December goal.</span>';
  else pn.textContent='On track. Next targets are highlighted amber when due within a week.';
  // mix
  ['70','20','10'].forEach(t=>{
    const items=state.activities.filter(a=>a.type===t);
    const p=items.length?Math.round(items.reduce((s,a)=>s+progressOf(a),0)/items.length*100):0;
    const map={'70':'Exp','20':'Soc','10':'Form'};
    document.getElementById('mix'+map[t]).style.width=p+'%';
    document.getElementById('pct'+map[t]).textContent=p+'%';
  });

  // filters
  const q=document.getElementById('search').value.toLowerCase();
  const fs=document.getElementById('fStatus').value;
  const ft=document.getElementById('fType').value;

  const areas=[...new Set(state.activities.map(a=>a.area))];
  const host=document.getElementById('comps');host.innerHTML='';
  areas.forEach(area=>{
    let items=state.activities.filter(a=>a.area===area);
    let visible=items.filter(a=>
      (!q||a.obj.toLowerCase().includes(q)||a.desc.toLowerCase().includes(q))&&
      (!fs||a.status===fs)&&(!ft||a.type===ft));
    if(!visible.length)return;
    const cp=Math.round(items.reduce((s,a)=>s+progressOf(a),0)/items.length*100);
    const openClass=(openAreas[area]===true||q||fs||ft)?'open':'';
    const div=document.createElement('div');
    div.className='comp '+openClass;
    div.innerHTML=`
      <div class="comp-head" onclick="toggleComp('${esc(area)}')">
        <span class="chev">▶</span>
        <span class="title">${area}</span>
        <span class="mini"><span style="width:${cp}%"></span></span>
        <span class="cpct">${cp}%</span>
      </div>
      <div class="comp-body">
        ${visible.map(a=>actHTML(a)).join('')}
      </div>`;
    host.appendChild(div);
  });
  renderNudges();
  renderChart();
  renderReviews();
}

// ---- Act-now nudges ----
function renderNudges(){
  const host=document.getElementById('nudges');
  const items=[];
  state.activities.forEach(a=>{
    if(a.status==='Completed')return;
    const n=daysUntil(a.target);
    if(n===null)return;
    if(n<0) items.push({a,pr:0,ic:'⚠',cls:'od',
      msg:`<b>${Math.abs(n)} days overdue</b> — was due ${fmtNice(a.target)}. ${a.status==='On Hold'?'Currently on hold.':'Move on this now.'}`});
    else if(a.status==='To Kick-off'&&n<=21) items.push({a,pr:1,ic:'▶',cls:'soon',
      msg:`Target ${fmtNice(a.target)} (<b>${n} days</b>) and not started yet — <b>kick this off now</b> to stay on pace.`});
    else if(n<=7) items.push({a,pr:2,ic:'◔',cls:'soon',
      msg:`Due in <b>${n} day${n===1?'':'s'}</b> (${fmtNice(a.target)}) — wrap it up this week.`});
  });
  items.sort((x,y)=>x.pr-y.pr || daysUntil(x.a.target)-daysUntil(y.a.target));

  if(!items.length){
    host.innerHTML=`<div class="nudges clear"><h3>⏱ Act Now</h3>
      <div class="nudge"><span class="ic">✅</span><div class="body"><div class="a">Nothing needs attention right now</div>
      <div class="m">No overdue items and nothing due within the week. Next targets will surface here as they approach.</div></div></div></div>`;
    return;
  }
  const shown=items.slice(0,6);
  host.innerHTML=`<div class="nudges"><h3>⏱ Act Now — ${items.length} item${items.length>1?'s':''} need${items.length>1?'':'s'} attention</h3>
    ${shown.map(n=>`<div class="nudge">
      <span class="ic">${n.ic}</span>
      <div class="body">
        <div class="a">${n.a.type} · ${n.a.area}</div>
        <div class="m">${n.a.obj} — ${n.msg}</div>
      </div>
      <button class="jump" onclick="jumpTo(${n.a.id},'${esc(n.a.area)}')">Go →</button>
    </div>`).join('')}
    ${items.length>shown.length?`<div class="nudge"><span class="ic">…</span><div class="body"><div class="m">+${items.length-shown.length} more — see amber/red badges below.</div></div></div>`:''}
  </div>`;
}
function fmtNice(d){return d?new Date(d).toLocaleDateString('en-US',{month:'short',day:'numeric'}):'';}
function jumpTo(id,area){
  openAreas[area]=true;render();
  setTimeout(()=>{
    const el=document.getElementById('act'+id);
    if(el){el.scrollIntoView({behavior:'smooth',block:'center'});el.classList.add('flash-row');}
  },60);
}

// ---- Progress-over-time chart (inline SVG, no libs) ----
function buildChartSVG(h,forPrint){
  const W=720,H=220,pad={l:34,r:14,t:14,b:26};
  const iw=W-pad.l-pad.r, ih=H-pad.t-pad.b;
  const start=new Date('2026-07-07'), end=new Date(DEADLINE);
  const span=end-start;
  const x=d=>pad.l+((new Date(d)-start)/span)*iw;
  const y=p=>pad.t+(1-p/100)*ih;
  const stroke=forPrint?'#C3073F':'var(--accent)';
  const grid=forPrint?'#e5e5e5':'var(--line)';
  const txt=forPrint?'#888':'var(--muted)';
  const goalCol=forPrint?'#16a34a':'var(--done)';

  let g='';
  [0,25,50,75,100].forEach(p=>{g+=`<line x1="${pad.l}" y1="${y(p)}" x2="${W-pad.r}" y2="${y(p)}" stroke="${grid}" stroke-width="1"/><text x="${pad.l-6}" y="${y(p)+3}" text-anchor="end" font-size="9" fill="${txt}">${p}</text>`;});
  // month ticks
  ['2026-08-01','2026-09-01','2026-10-01','2026-11-01','2026-12-01'].forEach(m=>{
    const lbl=new Date(m).toLocaleDateString('en-US',{month:'short'});
    g+=`<text x="${x(m)}" y="${H-8}" text-anchor="middle" font-size="9" fill="${txt}">${lbl}</text>`;
  });
  // ideal pace line (0% today-ish -> 100% at deadline)
  const firstPt=h.length?h[0]:{date:'2026-07-07',pct:0};
  g+=`<line x1="${x(firstPt.date)}" y1="${y(firstPt.pct)}" x2="${x(DEADLINE)}" y2="${y(100)}" stroke="${goalCol}" stroke-width="1.5" stroke-dasharray="4 4" opacity="0.7"/>`;
  g+=`<text x="${W-pad.r}" y="${y(100)-4}" text-anchor="end" font-size="9" fill="${goalCol}">ideal pace → 100%</text>`;

  if(h.length){
    const pts=h.map(pt=>`${x(pt.date).toFixed(1)},${y(pt.pct).toFixed(1)}`);
    // area fill
    const area=`${pad.l},${y(0)} `+pts.join(' ')+` ${x(h[h.length-1].date)},${y(0)}`;
    g+=`<polyline points="${area}" fill="${stroke}" opacity="0.08"/>`;
    g+=`<polyline points="${pts.join(' ')}" fill="none" stroke="${stroke}" stroke-width="2.5" stroke-linejoin="round"/>`;
    h.forEach(pt=>{g+=`<circle cx="${x(pt.date).toFixed(1)}" cy="${y(pt.pct).toFixed(1)}" r="3" fill="${stroke}"/>`;});
    // last value label
    const last=h[h.length-1];
    g+=`<text x="${x(last.date).toFixed(1)}" y="${(y(last.pct)-8).toFixed(1)}" text-anchor="middle" font-size="11" font-weight="700" fill="${stroke}">${last.pct}%</text>`;
  }
  return `<svg viewBox="0 0 ${W} ${H}" preserveAspectRatio="none" style="width:100%;height:220px;display:block">${g}</svg>`;
}
function renderChart(){
  const h=(state.history||[]).slice();
  document.getElementById('chart').outerHTML=buildChartSVG(h,false).replace('<svg','<svg id="chart"');
  const note=document.getElementById('chartNote');
  if(h.length<2)note.textContent='One data point so far. Each time you change a status, today\'s overall % is recorded — the trend line grows as you go. The green dashed line is the pace needed to hit 100% by the deadline.';
  else{
    const first=h[0],last=h[h.length-1];
    const gained=last.pct-first.pct;
    note.innerHTML=`${h.length} snapshots recorded · ${gained>=0?'+':''}${gained}% since ${fmtNice(first.date)}. Green dashed line = pace needed for the December deadline.`;
  }
}

function actHTML(a){
  const tm=typeMeta[a.type];
  return `<div class="act" id="act${a.id}">
    <div class="act-top">
      <span class="tag ${tm.cls}">${a.type}</span>
      <div class="act-main">
        <div class="obj">${a.obj}</div>
        <div class="desc">${a.desc}</div>
        <div class="act-ctl">
          <div class="ctl-field">
            <span class="ctl-label">Status</span>
            <select class="status-select ${pillClass[a.status]}" onchange="setField(${a.id},'status',this.value)">
              ${STATUSES.map(s=>`<option ${s===a.status?'selected':''}>${s}</option>`).join('')}
            </select>
          </div>
          <div class="ctl-field">
            <span class="ctl-label">Target date</span>
            <input type="date" value="${a.target||''}" onchange="setField(${a.id},'target',this.value)">
          </div>
          <div class="ctl-badges">${dueBadge(a)}</div>
          <button class="notes-toggle" onclick="toggleNotes(${a.id})">📝 Notes & sources${a.notes||(a.sources&&a.sources.length)||(a.files&&a.files.length)?' •':''}</button>
        </div>
        <div class="notes-box" id="nb${a.id}">
          <textarea placeholder="Progress notes, blockers, reflections…" onchange="setField(${a.id},'notes',this.value)">${a.notes||''}</textarea>
          <div class="src-head">🔗 Sources & references</div>
          <div class="src-list">${sourcesHTML(a)}</div>
          <div class="src-add">
            <input type="text" id="sl${a.id}" placeholder="Label (e.g. Udemy course, docs, PR)">
            <input type="url" id="su${a.id}" placeholder="https://…">
            <button onclick="addSource(${a.id})">+ Add</button>
          </div>
          <div class="img-head">📎 Attachments</div>
          <div class="img-grid" id="ig${a.id}">${attachmentsHTML(a)}</div>
          <div class="src-add">
            <button onclick="triggerFileUpload(${a.id})">📎 Upload file</button>
          </div>
        </div>
      </div>
    </div>
  </div>`;
}

function daysUntil(d){if(!d)return null;const ms=new Date(d)-new Date(new Date().toDateString());return Math.round(ms/86400000);}
function dueBadge(a){
  if(a.status==='Completed')return `<span class="due ok">✓ Done</span>`;
  const n=daysUntil(a.target);if(n===null)return'';
  if(n<0)return `<span class="due od">⚠ ${Math.abs(n)}d overdue</span>`;
  if(n<=7)return `<span class="due soon">◔ due in ${n}d</span>`;
  if(n<=21)return `<span class="due near">due in ${n}d</span>`;
  return `<span class="due far">due in ${n}d</span>`;
}
function sourcesHTML(a){
  if(!a.sources||!a.sources.length)return '<div class="src-empty">No sources yet — attach a link so you don\'t lose it.</div>';
  return a.sources.map(s=>`<div class="src-item">
    <a href="${s.url}" target="_blank" rel="noopener">🔗 ${s.label||s.url}</a>
    <button class="src-del" onclick="delSource(${a.id},${s.id})">✕</button>
  </div>`).join('');
}
function escAttr(s){return s.replace(/&/g,'&amp;').replace(/"/g,'&quot;');}
const FILE_ICONS = {
  pdf:'📕', doc:'📄', docx:'📄', txt:'📃', md:'📃', rtf:'📃',
  xls:'📊', xlsx:'📊', csv:'📊',
  ppt:'📽', pptx:'📽',
  zip:'🗜', rar:'🗜', '7z':'🗜'
};
function fileIcon(name){
  const ext = name.slice(name.lastIndexOf('.')+1).toLowerCase();
  return FILE_ICONS[ext] || '📎';
}
function fileChipHTML(a,f){
  return `<div class="file-chip" title="${escAttr(f.name)}" onclick="window.open('${f.url}','_blank')">
    <span class="fc-ic">${fileIcon(f.name)}</span>
    <span class="fc-name">${f.name}</span>
    <button class="img-del" onclick="event.stopPropagation();deleteAttachment(${a.id},${f.id})">✕</button>
  </div>`;
}
function imgThumbHTML(a,f){
  return `<div class="img-thumb" onclick="window.open('${f.url}','_blank')">
    <img src="${f.url}" alt="">
    <button class="img-del" onclick="event.stopPropagation();deleteAttachment(${a.id},${f.id})">✕</button>
  </div>`;
}
function attachmentsHTML(a){
  if(!a.files||!a.files.length)return '<div class="src-empty">No attachments yet.</div>';
  // docs/PDFs get their own row above images, since fixed-square thumbnails
  // and variable-width file chips don't align cleanly in one wrapped row
  const files=a.files.filter(f=>!f.isImage);
  const images=a.files.filter(f=>f.isImage);
  let out='';
  if(files.length) out+=`<div class="att-row">${files.map(f=>fileChipHTML(a,f)).join('')}</div>`;
  if(images.length) out+=`<div class="att-row">${images.map(f=>imgThumbHTML(a,f)).join('')}</div>`;
  return out;
}

async function addSource(id){
  const l=document.getElementById('sl'+id).value.trim();
  let u=document.getElementById('su'+id).value.trim();
  if(!u)return;
  if(!/^https?:\/\//i.test(u))u='https://'+u;
  try{
    const source=await api('POST',`${API}/activities/${id}/sources`,{label:l||null,url:u});
    state.activities.find(a=>a.id===id).sources.push(source);
    render();
    flash();
    setTimeout(()=>{const b=document.getElementById('nb'+id);if(b)b.classList.add('open');},0);
  }catch(e){ alert('Could not add source: '+e.message); }
}
async function delSource(id,sourceId){
  try{
    await api('DELETE',`${API}/sources/${sourceId}`);
    const a=state.activities.find(a=>a.id===id);
    a.sources=a.sources.filter(s=>s.id!==sourceId);
    render();
    flash();
    setTimeout(()=>{const b=document.getElementById('nb'+id);if(b)b.classList.add('open');},0);
  }catch(e){ alert('Could not delete source: '+e.message); }
}
async function setField(id,f,v){
  try{
    const key = f==='target' ? 'target_date' : f;
    const result=await api('PATCH',`${API}/activities/${id}`,{[key]: v===''?null:v});
    const idx=state.activities.findIndex(a=>a.id===id);
    state.activities[idx]=result.activity;
    state.history=result.history;
    render();
    flash();
  }catch(e){ alert('Could not save: '+e.message); }
}
function toggleComp(area){openAreas[area]=!(openAreas[area]===true);render();}
function toggleNotes(id){document.getElementById('nb'+id).classList.toggle('open');}
function toggleAll(open){
  [...new Set(state.activities.map(a=>a.area))].forEach(a=>openAreas[a]=open);
  render();
}
function esc(s){return s.replace(/'/g,"\\'");}

function triggerFileUpload(id){
  document.getElementById('fileUploadInput').dataset.forId=id;
  document.getElementById('fileUploadInput').click();
}
document.getElementById('fileUploadInput').addEventListener('change', async e=>{
  const input=e.target;
  const files=Array.from(input.files||[]);
  const id=Number(input.dataset.forId);
  input.value='';
  if(!files.length || Number.isNaN(id)) return;
  const a=state.activities.find(a=>a.id===id);
  try{
    for(const file of files){
      const fd=new FormData();
      fd.append('file',file);
      const attachment=await api('POST',`${API}/activities/${id}/attachments`,fd);
      a.files.push(attachment);
    }
    render();
    flash();
    setTimeout(()=>{const b=document.getElementById('nb'+id);if(b)b.classList.add('open');},0);
  }catch(err){ alert('Could not upload file(s): '+err.message); }
});
async function deleteAttachment(id,attachmentId){
  try{
    await api('DELETE',`${API}/attachments/${attachmentId}`);
    const a=state.activities.find(a=>a.id===id);
    a.files=a.files.filter(f=>f.id!==attachmentId);
    render();
    flash();
    setTimeout(()=>{const b=document.getElementById('nb'+id);if(b)b.classList.add('open');},0);
  }catch(e){ alert('Could not delete attachment: '+e.message); }
}

// reviews
async function addReview(){
  const d=document.getElementById('revDate').value||new Date().toISOString().slice(0,10);
  const p=document.getElementById('revProgress').value.trim();
  const c=document.getElementById('revChallenge').value.trim();
  const adj=document.getElementById('revAdjust').value.trim();
  if(!p&&!c&&!adj)return;
  try{
    const review=await api('POST',`${API}/reviews`,{date:d,progress:p||null,challenge:c||null,adjust:adj||null});
    state.reviews.unshift(review);
    document.getElementById('revProgress').value='';
    document.getElementById('revChallenge').value='';
    document.getElementById('revAdjust').value='';
    document.getElementById('revDate').value='';
    render();
    flash();
  }catch(e){ alert('Could not save review: '+e.message); }
}
async function delReview(reviewId){
  try{
    await api('DELETE',`${API}/reviews/${reviewId}`);
    state.reviews=state.reviews.filter(r=>r.id!==reviewId);
    render();
    flash();
  }catch(e){ alert('Could not delete review: '+e.message); }
}
function renderReviews(){
  const host=document.getElementById('reviewList');
  if(!state.reviews.length){host.innerHTML='<div class="sub" style="font-size:12.5px">No review entries yet. Log one each quarter to build your history.</div>';return;}
  host.innerHTML=state.reviews.map(r=>`<div class="rev-entry">
    <div class="date">${r.date} <button class="del" style="float:right" onclick="delReview(${r.id})">delete</button></div>
    ${r.progress?`<p><span class="k">Progress</span><br>${r.progress}</p>`:''}
    ${r.challenge?`<p><span class="k">Challenges</span><br>${r.challenge}</p>`:''}
    ${r.adjust?`<p><span class="k">Adjustments</span><br>${r.adjust}</p>`:''}
  </div>`).join('');
}

// export / reset
function exportJSON(){
  const blob=new Blob([JSON.stringify(state,null,2)],{type:'application/json'});
  const u=URL.createObjectURL(blob);const a=document.createElement('a');
  a.href=u;a.download='idp-progress-'+new Date().toISOString().slice(0,10)+'.json';a.click();URL.revokeObjectURL(u);
}
async function resetAll(){
  if(!confirm('Reset every activity\'s status, notes, and target date back to the original IDP? Sources, attachments, and the review log are kept.'))return;
  try{
    await api('POST',`${API}/reset`);
    await load();
    render();
    flash();
  }catch(e){ alert('Could not reset: '+e.message); }
}

function openPrint(){
  const ov=computeOverall();
  const c={'Completed':0,'In Progress':0,'To Kick-off':0,'On Hold':0};
  state.activities.forEach(a=>c[a.status]++);
  const areas=[...new Set(state.activities.map(a=>a.area))];
  const today=new Date().toLocaleDateString('en-US',{year:'numeric',month:'long',day:'numeric'});
  const mix=t=>{const it=state.activities.filter(a=>a.type===t);return it.length?Math.round(it.reduce((s,a)=>s+progressOf(a),0)/it.length*100):0;};
  const fmtDate=d=>d?new Date(d).toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'}):'—';

  let rows='';
  areas.forEach(area=>{
    const items=state.activities.filter(a=>a.area===area);
    const cp=Math.round(items.reduce((s,a)=>s+progressOf(a),0)/items.length*100);
    rows+=`<tr class="area-row"><td colspan="5"><strong>${area}</strong> — ${cp}% complete</td></tr>`;
    items.forEach(a=>{
      const srcs=(a.sources&&a.sources.length)?a.sources.map(s=>`<a href="${s.url}">${s.label||s.url}</a>`).join(' · '):'';
      rows+=`<tr>
        <td class="ty">${a.type}</td>
        <td>${a.obj}<div class="d">${a.desc}</div>${a.notes?`<div class="nt">📝 ${a.notes}</div>`:''}${srcs?`<div class="sc">🔗 ${srcs}</div>`:''}</td>
        <td class="st st-${pillClass[a.status]}">${a.status}</td>
        <td class="dt">${fmtDate(a.target)}</td>
      </tr>`;
    });
  });

  let reviews='';
  if(state.reviews.length){
    reviews=`<h2>Quarterly Review Log</h2>`+state.reviews.map(r=>`<div class="rev">
      <div class="rd">${r.date}</div>
      ${r.progress?`<p><b>Progress:</b> ${r.progress}</p>`:''}
      ${r.challenge?`<p><b>Challenges:</b> ${r.challenge}</p>`:''}
      ${r.adjust?`<p><b>Adjustments:</b> ${r.adjust}</p>`:''}
    </div>`).join('');
  }

  const html=`<!DOCTYPE html><html><head><meta charset="utf-8"><title>IDP Progress Summary — Iverson Guno</title>
  <style>
    *{box-sizing:border-box}body{font-family:Arial,Helvetica,sans-serif;color:#1a1a1a;max-width:820px;margin:0 auto;padding:36px 30px;font-size:12px;line-height:1.45}
    h1{font-size:20px;margin:0 0 2px}.meta{color:#555;font-size:12px;margin-bottom:18px}
    .kpis{display:flex;gap:10px;margin:16px 0 22px}
    .kpi{flex:1;border:1px solid #ddd;border-radius:8px;padding:10px 12px;text-align:center}
    .kpi .n{font-size:22px;font-weight:700}.kpi .l{font-size:10px;text-transform:uppercase;letter-spacing:.4px;color:#666;margin-top:2px}
    .kpi.big .n{color:#C3073F}
    .mixline{font-size:12px;color:#444;margin-bottom:18px}
    .mixline b{color:#111}
    .chartbox{border:1px solid #e5e5e5;border-radius:8px;padding:12px 8px;margin-bottom:20px}
    table{width:100%;border-collapse:collapse;margin-bottom:8px}
    td{border-bottom:1px solid #eee;padding:7px 8px;vertical-align:top}
    .area-row td{background:#f3f4f6;border-bottom:1px solid #ccc;font-size:13px;padding:8px}
    .ty{width:32px;font-weight:700;color:#666;text-align:center}
    .d{color:#666;font-size:11px;margin-top:2px}
    .nt{color:#333;font-size:11px;margin-top:4px;background:#fffbe6;padding:4px 6px;border-radius:4px}
    .sc{font-size:11px;margin-top:4px}.sc a{color:#C3073F;text-decoration:none}
    .st{width:82px;font-weight:600;font-size:11px;text-align:center;white-space:nowrap}
    .st-done{color:#16a34a}.st-prog{color:#C3073F}.st-kick{color:#777}.st-hold{color:#dc2626}
    .dt{width:88px;font-size:11px;text-align:center;white-space:nowrap}
    h2{font-size:15px;margin:24px 0 10px;border-bottom:2px solid #333;padding-bottom:4px}
    .rev{border:1px solid #e5e5e5;border-radius:6px;padding:10px;margin-bottom:8px}
    .rd{font-weight:700;color:#C3073F;margin-bottom:4px}.rev p{margin:3px 0}
    .signoff{display:flex;gap:20px;margin-top:36px}
    .sig{flex:1;border-top:1px solid #333;padding-top:5px;font-size:11px;color:#555}
    .foot{margin-top:28px;font-size:10px;color:#999;text-align:center;border-top:1px solid #eee;padding-top:10px}
    @media print{body{padding:0}.noprint{display:none}.area-row{break-inside:avoid}tr{break-inside:avoid}}
    .pbtn{background:#C3073F;color:#fff;border:none;padding:10px 20px;border-radius:8px;font-size:14px;cursor:pointer;margin-bottom:16px}
  </style></head><body>
  <button class="pbtn noprint" onclick="window.print()">🖨 Print / Save as PDF</button>
  <h1>Individual Development Plan — Progress Summary</h1>
  <div class="meta">Iverson Craig G. Guno · Web Developer → Senior Web Developer<br>
  IT and Security / IT · Supervisor: Michael Adam E. Trinidad · Report generated ${today}</div>
  <div class="kpis">
    <div class="kpi big"><div class="n">${ov}%</div><div class="l">Overall</div></div>
    <div class="kpi"><div class="n">${c['Completed']}</div><div class="l">Completed</div></div>
    <div class="kpi"><div class="n">${c['In Progress']}</div><div class="l">In Progress</div></div>
    <div class="kpi"><div class="n">${c['To Kick-off']}</div><div class="l">To Kick-off</div></div>
    <div class="kpi"><div class="n">${c['On Hold']}</div><div class="l">On Hold</div></div>
  </div>
  <div class="mixline"><b>70-20-10 mix:</b> Experiential ${mix('70')}% · Social ${mix('20')}% · Formal ${mix('10')}% &nbsp;|&nbsp; <b>Target completion:</b> first week of December 2026</div>
  <div class="chartbox">${buildChartSVG((state.history||[]).slice(),true)}</div>
  <table><tbody>${rows}</tbody></table>
  ${reviews}
  <div class="signoff">
    <div class="sig">Employee — Iverson Craig G. Guno<br>Date: __________</div>
    <div class="sig">Supervisor — Michael Adam E. Trinidad<br>Date: __________</div>
    <div class="sig">HR Representative<br>Date: __________</div>
  </div>
  <div class="foot">CONFIDENTIAL · Property of Brookside Group of Companies</div>
  </body></html>`;

  const w=window.open('','_blank');
  w.document.write(html);w.document.close();
}

(async function init(){
  await load();
  render();
})();
</script>
@endverbatim
</div>
