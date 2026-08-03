<div class="visualizer-guide">
{{--
    Concept Visualizer guide — SQL, authored directly as a Blade view.

    Unlike the first four this one has no counterpart in project-overview/;
    it was written here, so this file is the original rather than a port.

    Guides are hand-authored and self-contained. Only <style> and <script>
    sit inside verbatim — the markup is plain Blade so the masthead reads
    its labels from App\Tools\Visualizer\GuideLibrary.

    Rendered full-bleed by layouts/canvas.blade.php.
--}}

@verbatim
<style>
:root{
  --inset:#161617;
  --bg:#1A1A1D;             /* platform page colour */
  --panel:#222226;          /* raised */
  --panel-lift:#27272C;
  --panel-2:#1c1c20;
  --line:rgba(255,255,255,.08);
  --line-soft:rgba(255,255,255,.05);
  --ink:#ffffff;
  --ink-dim:rgba(255,255,255,.45);
  --ink-faint:rgba(255,255,255,.28);
  --sql:#3FCF8E;            /* emerald — this guide's accent */
  --sql-hot:#5ce3a6;
  --sql-soft:rgba(63,207,142,.14);
  --amber:#c98a2e;
  --red:#ff5d5d;
  --radius:16px;
}
/* Scoped: an unlayered `*` reset would out-rank Tailwind's utility
   layer and strip the platform header's spacing. */
.visualizer-guide *{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{
  background:
    radial-gradient(1100px 560px at 84% -10%, rgba(63,207,142,.10), transparent 60%),
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

/* ---------- header ---------- */
.eyebrow{display:flex;justify-content:space-between;align-items:center;
  font-family:'JetBrains Mono',monospace;font-size:12px;letter-spacing:.18em;
  color:var(--sql);text-transform:uppercase;margin-bottom:18px}
.eyebrow .right{color:var(--ink-faint)}
.masthead{display:flex;align-items:center;gap:18px;flex-wrap:wrap}
/* Drawn, not a brand mark — SQL is a language, not a product. Tile-free so
   it sits the same way the other guides' real marks do. */
.glyph{width:58px;height:58px;flex:none;display:grid;place-items:center;
  filter:drop-shadow(0 6px 20px rgba(63,207,142,.38))}
.glyph svg{width:100%;height:100%}
h1{font-family:'League Spartan','JetBrains Mono',sans-serif;font-weight:700;
  font-size:clamp(28px,5.5vw,48px);line-height:1;letter-spacing:-.01em}
h1 .accent{color:var(--sql)}
.sub{font-family:'JetBrains Mono',monospace;letter-spacing:.26em;text-transform:uppercase;
  color:var(--ink-dim);font-size:clamp(11px,2vw,14px);margin-top:8px}
.rule{height:2px;background:linear-gradient(90deg,var(--sql),transparent);margin:22px 0 24px;border-radius:2px}

/* ---------- the statement, full width ---------- */
/* The query is the hero here, so it gets the top of the page rather than a
   column — the other guides put a terminal or a spine in this slot. */
.stmt-card{border:1px solid var(--line);border-radius:var(--radius);background:var(--panel);
  overflow:hidden;margin-bottom:22px}
.stmt-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;
  padding:9px 14px;border-bottom:1px solid var(--line);background:var(--panel-2)}
.stmt-head .s-label{font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:.16em;
  text-transform:uppercase;color:var(--ink-faint)}
.stmt-head .s-dot{width:9px;height:9px;border-radius:50%;background:var(--sql);display:inline-block;
  margin-right:8px;vertical-align:middle}
.stmt-body{padding:14px 16px;font-family:'JetBrains Mono',monospace;font-size:14.5px;overflow-x:auto}

.q-line{display:flex;gap:14px;align-items:baseline;padding:3px 10px;border-radius:7px;
  border-left:2px solid transparent;white-space:nowrap}
.q-kw{color:var(--sql);font-weight:700;min-width:86px;flex:none}
.q-body{color:var(--ink)}
.q-body .lit{color:var(--sql-hot)}
.q-body .fn{color:var(--ink)}
.q-body .cmt{color:var(--ink-faint)}
/* a clause already applied by an earlier step */
.q-line.done .q-kw{color:var(--ink-dim)}
.q-line.done .q-body{color:var(--ink-dim)}
/* the clause this step introduces */
.q-line.current{background:var(--sql-soft);border-left-color:var(--sql)}
/* clauses still to come — the shape of the statement, before it exists */
.q-line.ghost{opacity:.26}
.q-line.ghost .q-kw,.q-line.ghost .q-body{color:var(--ink-faint)}

/* ---------- execution order ---------- */
/* The point of this strip: you write SELECT first, the engine runs it fifth. */
.exec{display:flex;align-items:center;gap:8px;flex-wrap:wrap;
  padding:11px 16px;border-top:1px solid var(--line-soft);background:var(--inset)}
.exec .e-label{font-family:'JetBrains Mono',monospace;font-size:9px;letter-spacing:.16em;
  text-transform:uppercase;color:var(--ink-faint);margin-right:2px}
.e-pill{font-family:'JetBrains Mono',monospace;font-size:11px;font-weight:700;
  border:1px solid var(--line);border-radius:7px;padding:4px 9px;color:var(--ink-faint);
  background:var(--panel-2);transition:color .2s,border-color .2s,background .2s}
.e-pill.done{color:var(--ink-dim)}
.e-pill.on{color:#0d2b1e;background:var(--sql);border-color:var(--sql);
  box-shadow:0 0 14px rgba(63,207,142,.35)}
.e-arrow{color:var(--ink-faint);font-size:11px}
.exec.na{opacity:.3}
.exec .e-note{font-family:'JetBrains Mono',monospace;font-size:10.5px;color:var(--ink-faint)}

/* ---------- layout ---------- */
.grid{display:grid;grid-template-columns:minmax(260px,.8fr) minmax(340px,1.2fr);gap:24px;align-items:start}
@media(max-width:900px){.grid{grid-template-columns:1fr}}

/* ---------- step list ---------- */
.list{display:flex;flex-direction:column;gap:7px}
.cmd{position:relative;text-align:left;width:100%;cursor:pointer;
  background:var(--panel);border:1px solid var(--line-soft);border-radius:11px;
  padding:12px 14px 12px 16px;color:var(--ink);transition:border-color .2s,background .2s;
  display:grid;grid-template-columns:auto 1fr;gap:13px;align-items:center;font-family:inherit}
.cmd:hover{border-color:var(--line);background:var(--panel-2)}
.cmd:focus-visible{outline:2px solid var(--sql);outline-offset:2px}
.cmd.active{border-color:var(--sql);background:linear-gradient(90deg,var(--sql-soft),transparent 70%)}
.cmd.active::before{content:"";position:absolute;left:0;top:8px;bottom:8px;width:3px;border-radius:3px;
  background:var(--sql);box-shadow:0 0 12px var(--sql-hot)}
.badge{font-family:'JetBrains Mono',monospace;font-weight:700;font-size:11px;color:var(--sql);
  border:1px solid var(--line);border-radius:6px;padding:3px 6px;min-width:26px;text-align:center;
  background:var(--inset)}
.cmd.active .badge{border-color:var(--sql);color:#0d2b1e;background:var(--sql-hot)}
/* the three statements that change data, marked apart from the seven that read */
.cmd.write .badge{color:var(--amber)}
.cmd.write.active .badge{border-color:var(--amber);background:var(--amber);color:#1A1A1D}
.cmd.write.active{border-color:var(--amber);background:linear-gradient(90deg,rgba(201,138,46,.14),transparent 70%)}
.cmd.write.active::before{background:var(--amber);box-shadow:0 0 12px var(--amber)}
.cmd-main{display:flex;flex-direction:column;gap:3px;min-width:0}
.cmd-code{font-family:'JetBrains Mono',monospace;font-weight:700;font-size:15px}
.cmd.active .cmd-code .kw{color:var(--sql)}
.cmd.write.active .cmd-code .kw{color:var(--amber)}
.cmd-code .arg{color:var(--ink-faint);font-weight:500;font-size:13px}
.cmd-desc{font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:var(--ink-faint)}

/* ---------- stage ---------- */
.stage{position:sticky;top:24px;background:var(--panel);border:1px solid var(--line);
  border-radius:var(--radius);overflow:hidden}
.stage-inner{padding:16px;display:flex;flex-direction:column;gap:14px}

/* a table, source or result — the signature element */
.tbl-zone{border:1px solid var(--line);border-radius:10px;background:var(--inset);overflow:hidden}
.tbl-h{display:flex;align-items:center;justify-content:space-between;gap:10px;
  padding:8px 12px;border-bottom:1px solid var(--line-soft);
  font-family:'JetBrains Mono',monospace;font-size:9px;letter-spacing:.16em;
  text-transform:uppercase;color:var(--ink-faint)}
.tbl-h .t-name{color:var(--ink-dim);font-weight:700}
.tbl-scroll{overflow-x:auto}
table.tbl{border-collapse:collapse;width:100%;font-family:'JetBrains Mono',monospace;font-size:13px}
table.tbl th{text-align:left;font-weight:700;font-size:10px;letter-spacing:.12em;text-transform:uppercase;
  color:var(--ink-faint);padding:8px 12px;border-bottom:1px solid var(--line);white-space:nowrap}
table.tbl td{padding:7px 12px;color:var(--ink);white-space:nowrap;
  border-bottom:1px solid var(--line-soft)}
table.tbl tr:last-child td{border-bottom:none}
table.tbl .num{text-align:right}
/* a column the statement did not ask for */
table.tbl th.dim,table.tbl td.dim{color:var(--ink-faint);opacity:.45}

/* row states — the whole point of the visual */
tr.kept td{box-shadow:inset 2px 0 0 var(--sql)}
tr.kept td:first-child{position:relative}
tr.dropped td{opacity:.3;text-decoration:line-through;text-decoration-color:var(--ink-faint)}
tr.added td{box-shadow:inset 2px 0 0 var(--sql);background:var(--sql-soft)}
tr.changed td{box-shadow:inset 2px 0 0 var(--amber);background:rgba(201,138,46,.12)}
tr.removed td{opacity:.34;text-decoration:line-through;text-decoration-color:var(--red);
  box-shadow:inset 2px 0 0 var(--red)}
td .was{color:var(--ink-faint);text-decoration:line-through;margin-right:7px}
td .now{color:var(--amber);font-weight:700}

.caption{font-family:'JetBrains Mono',monospace;font-size:11px;color:var(--ink-dim);
  padding:8px 12px;border-top:1px solid var(--line-soft);background:var(--panel-2)}
.caption b{color:var(--sql)}
.caption.warn b{color:var(--amber)}
.caption.danger b{color:var(--red)}

/* legend for the row states */
.legend{display:flex;gap:14px;flex-wrap:wrap;font-family:'JetBrains Mono',monospace;
  font-size:10px;letter-spacing:.08em;text-transform:uppercase;color:var(--ink-faint)}
.legend span{display:flex;align-items:center;gap:6px}
.legend i{width:9px;height:9px;border-radius:3px;display:inline-block;font-style:normal}
.legend i.k{background:var(--sql)}
.legend i.d{background:var(--ink-faint)}
.legend i.c{background:var(--amber)}
.legend i.r{background:var(--red)}

/* explanation */
.explain{border-top:1px solid var(--line);padding:16px;background:var(--panel-lift)}
.explain h3{font-family:'JetBrains Mono',monospace;font-size:13px;letter-spacing:.08em;
  color:var(--sql);text-transform:uppercase;margin-bottom:6px}
.explain.write h3{color:var(--amber)}
.explain p{font-size:14px;color:var(--ink)}

/* ---------- footer ---------- */
footer{margin-top:26px;border-top:1px solid var(--line);padding-top:16px;display:flex;
  justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap}
.foot-flow{font-family:'JetBrains Mono',monospace;font-size:12px;letter-spacing:.1em;color:var(--ink-dim)}
.foot-flow b{color:var(--sql)}
.counter{font-family:'JetBrains Mono',monospace;font-size:13px;color:var(--sql);
  border:1px solid var(--line);border-radius:8px;padding:6px 12px}
.tagline{font-family:'JetBrains Mono',monospace;font-weight:700;color:var(--ink);
  font-size:clamp(14px,3vw,18px);margin-top:14px}

@media(prefers-reduced-motion:reduce){*{animation:none!important;transition:none!important}}
</style>
@endverbatim

<div class="wrap">

  <div class="eyebrow"><span>&gt;_ coding chops</span><span class="right">{{ $guide['eyebrow'] }}</span></div>

  <div class="masthead">
    <div class="glyph" aria-hidden="true">
      <svg viewBox="0 0 24 24" fill="none">
        <ellipse cx="12" cy="5.5" rx="7.5" ry="2.8" stroke="var(--sql)" stroke-width="1.5"/>
        <path d="M4.5 5.5v6c0 1.55 3.36 2.8 7.5 2.8s7.5-1.25 7.5-2.8v-6" stroke="var(--sql)" stroke-width="1.5"/>
        <path d="M4.5 11.5v6c0 1.55 3.36 2.8 7.5 2.8s7.5-1.25 7.5-2.8v-6" stroke="var(--sql)" stroke-width="1.5"/>
      </svg>
    </div>
    <div>
      <h1>10 <span class="accent">SQL</span> Statements</h1>
      <div class="sub">{{ $guide['subtitle'] }}</div>
    </div>
  </div>

  <div class="rule"></div>

  <div class="stmt-card">
    <div class="stmt-head">
      <span class="s-label"><span class="s-dot"></span><span id="stmtLabel">statement</span></span>
      <span class="s-label" id="stmtHint"></span>
    </div>
    <div class="stmt-body" id="stmtBody"></div>
    <div class="exec" id="exec">
      <span class="e-label">runs in this order</span>
      <span id="execPills"></span>
    </div>
  </div>

  <div class="grid">
    <div class="list" id="list" role="tablist" aria-label="SQL statements"></div>

    <div class="stage">
      <div class="stage-inner">
        <div id="sourceWrap"></div>
        <div class="tbl-zone">
          <div class="tbl-h">
            <span class="t-name" id="resultName">result</span>
            <span id="resultShape"></span>
          </div>
          <div class="tbl-scroll"><table class="tbl" id="resultTable"></table></div>
          <div class="caption" id="resultCaption"></div>
        </div>
        <div class="legend">
          <span><i class="k"></i>kept</span>
          <span><i class="d"></i>filtered out</span>
          <span><i class="c"></i>changed</span>
          <span><i class="r"></i>removed</span>
        </div>
      </div>
      <div class="explain" id="explain">
        <h3 id="exTitle"></h3>
        <p id="exBody"></p>
      </div>
    </div>
  </div>

  <footer>
    <div class="foot-flow">select <b>&rsaquo;</b> filter <b>&rsaquo;</b> sort <b>&rsaquo;</b> join <b>&rsaquo;</b> group <b>&rsaquo;</b> write</div>
    <div class="counter" id="counter">01 / 10</div>
  </footer>
  <div class="tagline">ONE DATASET. TEN STATEMENTS. EVERY ANSWER.</div>

</div>

@verbatim
<script>
/* ============================================================
   SQL, taught against one small dataset.

   Every step renders three things: the statement as it stands, where the
   engine runs the new clause, and the rows that survive it. Nothing here
   executes SQL — each step declares the result it is teaching, which keeps
   the guide honest about what it is (a diagram, not a database).
   ============================================================ */
(function(){

/* ---------- the dataset ---------- */
const CUSTOMERS=[
  {id:1,name:"Ada",    city:"Manila", spend:420},
  {id:2,name:"Linus",  city:"Cebu",   spend:180},
  {id:3,name:"Grace",  city:"Manila", spend:960},
  {id:4,name:"Alan",   city:"Davao",  spend:75},
  {id:5,name:"Edsger", city:"Cebu",   spend:610},
  {id:6,name:"Barbara",city:"Manila", spend:240}
];

const ORDERS=[
  {id:101,customer_id:1,item:"Keyboard",total:120},
  {id:102,customer_id:3,item:"Monitor", total:340},
  {id:103,customer_id:1,item:"Mouse",   total:45},
  {id:104,customer_id:5,item:"Desk",    total:260},
  {id:105,customer_id:3,item:"Lamp",    total:60}
];

const CUST_COLS=[
  {k:"id",label:"id",num:true},
  {k:"name",label:"name"},
  {k:"city",label:"city"},
  {k:"spend",label:"spend",num:true}
];

/* rows(list, stateFn) — wraps plain records as renderable rows. */
function rows(list, stateFn){
  return list.map(r=>({c:r, state: stateFn ? stateFn(r) : ""}));
}

/* ---------- execution order ---------- */
/* Written order and run order are different, which is the single most useful
   thing to know about SELECT. The strip below is the run order. */
const EXEC=["FROM","WHERE","GROUP BY","HAVING","SELECT","ORDER BY","LIMIT"];

/* ---------- steps ---------- */
const STEPS=[
{ n:"01", kw:"SELECT", arg:"… FROM", desc:"Read a table",
  label:"statement a", hint:"pick the columns",
  sql:[["SELECT","name, city, spend","current"],["FROM","customers","current"],
       ["WHERE","…","ghost"],["ORDER BY","…","ghost"],["LIMIT","…","ghost"]],
  exec:"SELECT",
  title:"SELECT chooses columns, FROM chooses the table",
  body:"SELECT is a vertical cut: it decides which columns come back, never which rows. Every row is still here — id is simply not on the list, so it is greyed out rather than gone.",
  view:()=>({
    name:"customers",
    cols:CUST_COLS.map(c=>c.k==="id"?{...c,dim:true}:c),
    rows:rows(CUSTOMERS),
    shape:"6 rows × 3 columns",
    caption:"<b>6 rows</b> returned — id was not selected"
  })},

{ n:"02", kw:"WHERE", arg:"city = 'Manila'", desc:"Filter rows",
  label:"statement a", hint:"cut rows, not columns",
  sql:[["SELECT","name, city, spend","done"],["FROM","customers","done"],
       ["WHERE","city = <span class='lit'>'Manila'</span>","current"],
       ["ORDER BY","…","ghost"],["LIMIT","…","ghost"]],
  exec:"WHERE",
  title:"WHERE is the horizontal cut",
  body:"Each row is tested on its own. Pass and it stays, fail and it never reaches SELECT — which is why WHERE runs before SELECT even though you write it after.",
  view:()=>({
    name:"customers",
    cols:CUST_COLS.map(c=>c.k==="id"?{...c,dim:true}:c),
    rows:rows(CUSTOMERS, r=>r.city==="Manila"?"kept":"dropped"),
    shape:"3 of 6 rows",
    caption:"<b>3 rows</b> passed the test, 3 were filtered out"
  })},

{ n:"03", kw:"ORDER BY", arg:"spend DESC", desc:"Sort the result",
  label:"statement a", hint:"arrange what survived",
  sql:[["SELECT","name, city, spend","done"],["FROM","customers","done"],
       ["WHERE","city = <span class='lit'>'Manila'</span>","done"],
       ["ORDER BY","spend <span class='lit'>DESC</span>","current"],["LIMIT","…","ghost"]],
  exec:"ORDER BY",
  title:"Sorting happens last, on the rows that survived",
  body:"Without ORDER BY a database is free to hand rows back in any order it likes — including a different order next time. If the sequence matters, say so.",
  view:()=>({
    name:"result",
    cols:[CUST_COLS[1],CUST_COLS[2],CUST_COLS[3]],
    rows:rows([CUSTOMERS[2],CUSTOMERS[0],CUSTOMERS[5]], ()=>"kept"),
    shape:"3 rows, sorted",
    caption:"<b>3 rows</b> — highest spend first"
  })},

{ n:"04", kw:"LIMIT", arg:"2", desc:"Take the top N",
  label:"statement a", hint:"the last thing to run",
  sql:[["SELECT","name, city, spend","done"],["FROM","customers","done"],
       ["WHERE","city = <span class='lit'>'Manila'</span>","done"],
       ["ORDER BY","spend <span class='lit'>DESC</span>","done"],
       ["LIMIT","<span class='lit'>2</span>","current"]],
  exec:"LIMIT",
  title:"LIMIT slices the sorted list",
  body:"LIMIT without ORDER BY gives you two arbitrary rows, not the top two. The pair only means “biggest spenders” because the sort ran first.",
  view:()=>({
    name:"result",
    cols:[CUST_COLS[1],CUST_COLS[2],CUST_COLS[3]],
    rows:[{c:CUSTOMERS[2],state:"kept"},{c:CUSTOMERS[0],state:"kept"},
          {c:CUSTOMERS[5],state:"dropped"}],
    shape:"2 rows",
    caption:"<b>2 rows</b> returned — the third was cut by the limit"
  })},

{ n:"05", kw:"JOIN", arg:"orders ON …", desc:"Bring in a second table",
  label:"statement b", hint:"a new statement",
  sql:[["SELECT","c.name, o.item, o.total","done"],["FROM","customers c","current"],
       ["JOIN","orders o <span class='cmt'>ON o.customer_id = c.id</span>","current"]],
  exec:"FROM",
  title:"A join happens at FROM, before anything is filtered",
  body:"The engine builds the combined table first, then WHERE and SELECT work on that. An INNER JOIN keeps only rows that matched — Linus, Alan and Barbara have no orders, so they disappear entirely.",
  view:()=>({
    name:"customers ⋈ orders",
    cols:[{k:"name",label:"c.name"},{k:"item",label:"o.item"},{k:"total",label:"o.total",num:true}],
    rows:rows([
      {name:"Ada",   item:"Keyboard",total:120},
      {name:"Ada",   item:"Mouse",   total:45},
      {name:"Grace", item:"Monitor", total:340},
      {name:"Grace", item:"Lamp",    total:60},
      {name:"Edsger",item:"Desk",    total:260}
    ], ()=>"kept"),
    shape:"5 rows",
    caption:"<b>5 rows</b> — one per matching order; 3 customers matched nothing and dropped out",
    source:{
      name:"orders",
      cols:[{k:"id",label:"id",num:true},{k:"customer_id",label:"customer_id",num:true},
            {k:"item",label:"item"},{k:"total",label:"total",num:true}],
      rows:rows(ORDERS),
      shape:"5 rows"
    }
  })},

{ n:"06", kw:"GROUP BY", arg:"city", desc:"Collapse into buckets",
  label:"statement c", hint:"many rows become one",
  sql:[["SELECT","city, <span class='fn'>COUNT(*)</span>, <span class='fn'>SUM(spend)</span>","done"],
       ["FROM","customers","done"],["GROUP BY","city","current"],
       ["HAVING","…","ghost"]],
  exec:"GROUP BY",
  title:"GROUP BY turns rows into groups",
  body:"Six customers become three cities. Once you group, every column in SELECT must either be the thing you grouped by or an aggregate over the group — there is no single “name” for a bucket of three people.",
  view:()=>({
    name:"result",
    cols:[{k:"city",label:"city"},{k:"customers",label:"count(*)",num:true},
          {k:"total",label:"sum(spend)",num:true}],
    rows:rows([
      {city:"Manila",customers:3,total:1620},
      {city:"Cebu",  customers:2,total:790},
      {city:"Davao", customers:1,total:75}
    ], ()=>"kept"),
    shape:"3 groups",
    caption:"<b>6 rows</b> collapsed into <b>3 groups</b>"
  })},

{ n:"07", kw:"HAVING", arg:"SUM(spend) > 500", desc:"Filter the buckets",
  label:"statement c", hint:"where, but for groups",
  sql:[["SELECT","city, <span class='fn'>COUNT(*)</span>, <span class='fn'>SUM(spend)</span>","done"],
       ["FROM","customers","done"],["GROUP BY","city","done"],
       ["HAVING","<span class='fn'>SUM(spend)</span> &gt; <span class='lit'>500</span>","current"]],
  exec:"HAVING",
  title:"WHERE filters rows, HAVING filters groups",
  body:"This is the distinction people trip on. WHERE runs before grouping, so it cannot see a SUM — the total does not exist yet. HAVING runs after, which is the only place a condition on an aggregate can live.",
  view:()=>({
    name:"result",
    cols:[{k:"city",label:"city"},{k:"customers",label:"count(*)",num:true},
          {k:"total",label:"sum(spend)",num:true}],
    rows:[
      {c:{city:"Manila",customers:3,total:1620},state:"kept"},
      {c:{city:"Cebu",  customers:2,total:790}, state:"kept"},
      {c:{city:"Davao", customers:1,total:75},  state:"dropped"}
    ],
    shape:"2 of 3 groups",
    caption:"<b>2 groups</b> cleared the threshold"
  })},

{ n:"08", kw:"INSERT", arg:"INTO customers", desc:"Add a row", write:true,
  label:"statement d", hint:"changes the table",
  sql:[["INSERT INTO","customers <span class='cmt'>(name, city, spend)</span>","current"],
       ["VALUES","(<span class='lit'>'Tim'</span>, <span class='lit'>'Davao'</span>, <span class='lit'>0</span>)","current"]],
  exec:null,
  title:"INSERT adds rows and returns none",
  body:"A write statement answers with a count, not a result set. Columns you leave out take their default — id is generated, so it is not in the list.",
  view:()=>({
    name:"customers",
    cols:CUST_COLS,
    rows:[...rows(CUSTOMERS), {c:{id:7,name:"Tim",city:"Davao",spend:0},state:"added"}],
    shape:"7 rows",
    caption:"<b>1 row</b> added — the table now holds 7",
    captionTone:"warn"
  })},

{ n:"09", kw:"UPDATE", arg:"SET … WHERE", desc:"Change rows in place", write:true,
  label:"statement e", hint:"the WHERE is the whole safety net",
  sql:[["UPDATE","customers","current"],
       ["SET","spend = spend + <span class='lit'>100</span>","current"],
       ["WHERE","city = <span class='lit'>'Davao'</span>","current"]],
  exec:null,
  title:"UPDATE rewrites the rows WHERE matches",
  body:"Same WHERE, same meaning — but now it decides what gets overwritten rather than what gets returned. Run this without the WHERE and every customer in the table gets the raise.",
  view:()=>({
    name:"customers",
    cols:CUST_COLS,
    rows:[
      {c:CUSTOMERS[0],state:""},{c:CUSTOMERS[1],state:""},{c:CUSTOMERS[2],state:""},
      {c:{id:4,name:"Alan",city:"Davao",spend:175,_was:{spend:75}},state:"changed"},
      {c:CUSTOMERS[4],state:""},{c:CUSTOMERS[5],state:""},
      {c:{id:7,name:"Tim",city:"Davao",spend:100,_was:{spend:0}},state:"changed"}
    ],
    shape:"7 rows",
    caption:"<b>2 rows</b> changed — both of the Davao customers",
    captionTone:"warn"
  })},

{ n:"10", kw:"DELETE", arg:"FROM … WHERE", desc:"Remove rows", write:true,
  label:"statement f", hint:"no undo",
  sql:[["DELETE FROM","customers","current"],
       ["WHERE","spend &lt; <span class='lit'>200</span>","current"]],
  exec:null,
  title:"DELETE takes rows out for good",
  body:"The rows below are struck through, not hidden — after this runs they are gone, and no ORDER BY or LIMIT will bring them back. DELETE with no WHERE empties the table, which is the most expensive typo in SQL.",
  view:()=>({
    name:"customers",
    cols:CUST_COLS,
    rows:[
      {c:CUSTOMERS[0],state:""},
      {c:CUSTOMERS[1],state:"removed"},
      {c:CUSTOMERS[2],state:""},
      {c:{id:4,name:"Alan",city:"Davao",spend:175},state:"removed"},
      {c:CUSTOMERS[4],state:""},{c:CUSTOMERS[5],state:""},
      {c:{id:7,name:"Tim",city:"Davao",spend:100},state:"removed"}
    ],
    shape:"4 rows remain",
    caption:"<b>3 rows</b> removed — 4 left in the table",
    captionTone:"danger"
  })}
];

/* ---------- rendering ---------- */
const $=id=>document.getElementById(id);

function renderStatement(step){
  $("stmtLabel").textContent=step.label;
  $("stmtHint").textContent=step.hint||"";
  $("stmtBody").innerHTML=step.sql.map(([kw,body,state])=>
    `<div class="q-line ${state}"><span class="q-kw">${kw}</span><span class="q-body">${body}</span></div>`
  ).join("");
}

function renderExec(step){
  const exec=$("exec");
  if(!step.exec){
    exec.classList.add("na");
    $("execPills").innerHTML=`<span class="e-note">not a query — this statement writes to the table</span>`;
    return;
  }
  exec.classList.remove("na");
  const at=EXEC.indexOf(step.exec);
  $("execPills").innerHTML=EXEC.map((name,i)=>{
    const cls=i===at?"on":(i<at?"done":"");
    return `<span class="e-pill ${cls}">${name}</span>`;
  }).join(`<span class="e-arrow">›</span>`);
}

function cell(row,col){
  const was=row._was&&row._was[col.k]!==undefined;
  const v=row[col.k]===undefined?"":row[col.k];
  const inner=was?`<span class="was">${row._was[col.k]}</span><span class="now">${v}</span>`:v;
  return `<td class="${col.num?"num":""} ${col.dim?"dim":""}">${inner}</td>`;
}

function renderTable(spec){
  const head=`<thead><tr>${spec.cols.map(c=>
    `<th class="${c.num?"num":""} ${c.dim?"dim":""}">${c.label}</th>`).join("")}</tr></thead>`;
  const body=`<tbody>${spec.rows.map(r=>
    `<tr class="${r.state}">${spec.cols.map(c=>cell(r.c,c)).join("")}</tr>`).join("")}</tbody>`;
  return head+body;
}

function renderSource(spec){
  const wrap=$("sourceWrap");
  if(!spec.source){ wrap.innerHTML=""; return; }
  const s=spec.source;
  wrap.innerHTML=
    `<div class="tbl-zone">
       <div class="tbl-h"><span class="t-name">${s.name}</span><span>${s.shape||""}</span></div>
       <div class="tbl-scroll"><table class="tbl">${renderTable(s)}</table></div>
     </div>`;
}

function select(i){
  const step=STEPS[i];
  const spec=step.view();

  document.querySelectorAll(".cmd").forEach((el,j)=>
    el.classList.toggle("active", j===i));

  renderStatement(step);
  renderExec(step);
  renderSource(spec);

  $("resultName").textContent=spec.name;
  $("resultShape").textContent=spec.shape||"";
  $("resultTable").innerHTML=renderTable(spec);
  const cap=$("resultCaption");
  cap.className="caption"+(spec.captionTone?" "+spec.captionTone:"");
  cap.innerHTML=spec.caption||"";

  $("explain").classList.toggle("write", !!step.write);
  $("exTitle").textContent=step.title;
  $("exBody").textContent=step.body;
  $("counter").textContent=`${step.n} / ${String(STEPS.length).padStart(2,"0")}`;

  current=i;
}

let current=0;

STEPS.forEach((s,i)=>{
  const b=document.createElement("button");
  b.className="cmd"+(s.write?" write":"");
  b.type="button";
  b.setAttribute("role","tab");
  b.innerHTML=
    `<span class="badge">${s.n}</span>
     <span class="cmd-main">
       <span class="cmd-code"><span class="kw">${s.kw}</span> <span class="arg">${s.arg}</span></span>
       <span class="cmd-desc">${s.desc}</span>
     </span>`;
  b.addEventListener("click",()=>select(i));
  $("list").appendChild(b);
});

document.addEventListener("keydown",e=>{
  if(e.key==="ArrowDown"||e.key==="ArrowRight"){select((current+1)%STEPS.length);e.preventDefault();}
  if(e.key==="ArrowUp"||e.key==="ArrowLeft"){select((current-1+STEPS.length)%STEPS.length);e.preventDefault();}
});

select(0);

})();
</script>
@endverbatim
</div>
