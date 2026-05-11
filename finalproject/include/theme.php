<style>
/*  RESET & BASE */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
:root {
  --bg:     #0d1117;
  --bg2:    #161b22;
  --bg3:    #21262d;
  --border: #30363d;
  --gold:   #f0c040;
  --gold2:  #c89a20;
  --red:    #e05050;
  --blue:   #58a6ff;
  --green:  #56d364;
  --text:   #e6edf3;
  --text2:  #8b949e;
  --radius: 10px;
}
html { height: 100%; }
body {
  background: var(--bg);
  color: var(--text);
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
  font-size: 15px;
  line-height: 1.5;
  min-height: 100vh;
  /* Gradient bianco->bg nella parte bassa */
  background-image: linear-gradient(
    to top,
    rgba(255,255,255,0.18) 0%,
    rgba(255,255,255,0.07) 35%,
    transparent 60%
  );
  background-attachment: fixed;
}

/* NAVBAR */
.navbar { 
  background: var(--bg2);
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  width: 100%;
  padding: 0 1.5rem;
  height: 52px;
  position: relative;
  z-index: 50;
}
.navbar-brand {
  color: var(--gold);
  font-weight: 700;
  font-size: 1.05rem;
  text-decoration: none;
  letter-spacing: .4px;
  white-space: nowrap;
}
.nav-collapse {
  display: flex;
  align-items: center;
  flex: 1;
}
.navbar-links {
  display: flex;
  align-items: center;
  gap: .1rem;
  list-style: none;
}
.navbar-links a {
  color: var(--text2);
  text-decoration: none;
  font-size: .88rem;
  padding: .45rem .85rem;
  border-radius: 6px;
  transition: color .15s, background .15s;
  display: block;
  white-space: nowrap;
}
.navbar-links a:hover { color: var(--text); background: var(--bg3); }
.navbar-links a.active { color: var(--text); border-bottom: 2px solid var(--gold); border-radius: 0; }
.navbar-links a.admin-link { color: var(--blue) !important; }
.navbar-right {
  display: flex;
  align-items: center;
  gap: .75rem;
  margin-left: auto;
  white-space: nowrap;
}
.navbar-user { color: var(--text2); font-size: .85rem; }
.nav-sep { width: 1px; height: 20px; background: var(--border); margin: 0 .25rem; flex-shrink: 0; }
/* Hamburger */
.nav-toggle {
  display: none;
  background: none;
  border: 1px solid var(--border);
  border-radius: 6px;
  color: var(--text2);
  padding: .3rem .6rem;
  cursor: pointer;
  font-size: 1.1rem;
  line-height: 1;
}
@media (max-width: 640px) {
  .nav-toggle { display: block; }
  .nav-collapse {
    display: none;
    position: absolute;
    top: 52px; left: 0; right: 0;
    background: var(--bg2);
    border-bottom: 1px solid var(--border);
    padding: .75rem 1rem 1rem;
    flex-direction: column;
    align-items: flex-start;
    gap: .5rem;
  }
  .nav-collapse.open { display: flex; }
  .navbar-links { flex-direction: column; align-items: flex-start; width: 100%; }
  .navbar-right { margin-left: 0; width: 100%; padding-top: .5rem; border-top: 1px solid var(--border); }
}

/* BUTTONS */
.btn {
  display: inline-block;
  padding: .5rem 1.1rem;
  border-radius: var(--radius);
  border: none;
  font-size: .88rem;
  font-weight: 500;
  cursor: pointer;
  text-decoration: none;
  transition: background .15s, transform .1s;
  line-height: 1.4;
  font-family: inherit;
}
.btn:active { transform: translateY(1px); }
.btn-gold { background: var(--gold); color: #0d1117; font-weight: 700; }
.btn-gold:hover { background: var(--gold2); color: #0d1117; }
.btn-outline-gold { background: transparent; color: var(--gold); border: 1px solid var(--gold); }
.btn-outline-gold:hover { background: var(--gold); color: #0d1117; }
.btn-outline-muted { background: transparent; color: var(--text2); border: 1px solid var(--border); }
.btn-outline-muted:hover { color: var(--text); border-color: var(--text2); }
.btn-danger { background: rgba(224,80,80,.15); color: #ff7b7b; border: 1px solid rgba(224,80,80,.3); }
.btn-danger:hover { background: rgba(224,80,80,.28); }
.btn-blue { background: rgba(88,166,255,.15); color: var(--blue); border: 1px solid rgba(88,166,255,.3); }
.btn-blue:hover { background: rgba(88,166,255,.28); }
.btn-sm { padding: .3rem .75rem; font-size: .8rem; border-radius: 7px; }

/* BADGES */
.badge { display: inline-block; border-radius: 20px; padding: 2px 10px; font-size: .72rem; font-weight: 600; }
.badge-gold  { background: rgba(240,192,64,.15); color: var(--gold); border: 1px solid rgba(240,192,64,.3); }
.badge-admin { background: rgba(88,166,255,.15); color: var(--blue); border: 1px solid rgba(88,166,255,.3); }
.badge-user  { background: rgba(139,148,158,.1); color: var(--text2); border: 1px solid var(--border); }

/* CARDS */
.card, .card-dark {
  background: var(--bg2);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 1.5rem;
}

/* FORMS */
.form-group { margin-bottom: 1rem; }
.form-label { display: block; color: var(--text2); font-size: .82rem; margin-bottom: .35rem; }
.form-control {
  width: 100%;
  background: var(--bg3);
  border: 1px solid var(--border);
  color: var(--text);
  border-radius: var(--radius);
  padding: .6rem .9rem;
  font-size: .92rem;
  outline: none;
  font-family: inherit;
  transition: border-color .15s, box-shadow .15s;
}
.form-control:focus { border-color: var(--gold); box-shadow: 0 0 0 2px rgba(240,192,64,.18); }

/* TABLES */
.table-game { width: 100%; border-collapse: collapse; }
.table-game th {
  color: var(--text2); font-size: .75rem; font-weight: 600;
  text-transform: uppercase; letter-spacing: .7px;
  padding: .55rem 1rem; border-bottom: 1px solid var(--border); text-align: left;
}
.table-game td { padding: .7rem 1rem; border-bottom: 1px solid rgba(48,54,61,.5); font-size: .9rem; vertical-align: middle; }
.table-game tr:last-child td { border-bottom: none; }
.table-game tr:hover td { background: rgba(255,255,255,.02); }
.table-game tr.me td { background: rgba(240,192,64,.06); border-left: 2px solid var(--gold); }
.text-end { text-align: right; }
.text-center { text-align: center; }

/* ALERTS */
.alert, .alert-game {
  border-radius: var(--radius); padding: .7rem 1rem;
  font-size: .88rem; border: 1px solid; margin-bottom: 1rem;
}
.alert-danger  { background: rgba(224,80,80,.1);  border-color: rgba(224,80,80,.3);  color: #ff7b7b; }
.alert-success { background: rgba(86,211,100,.1); border-color: rgba(86,211,100,.3); color: var(--green); }
.alert-info    { background: rgba(88,166,255,.1); border-color: rgba(88,166,255,.3); color: var(--blue); }

/* HUD */
.hud {
  display: flex; align-items: center; gap: 1.25rem;
  padding: .65rem 1.25rem; margin: .9rem 0 .4rem;
  background: var(--bg2); border: 1px solid var(--border);
  border-radius: var(--radius); font-size: .88rem; flex-wrap: wrap;
}
.hud-stat { display: flex; flex-direction: column; align-items: center; line-height: 1.2; }
.hud-stat span { color: var(--text2); font-size: .68rem; text-transform: uppercase; letter-spacing: .6px; }
.hud-stat strong { color: var(--gold); font-size: 1.1rem; font-weight: 700; }
.hud-divider { width: 1px; height: 1.8rem; background: var(--border); }
.hud-actions { margin-left: auto; display: flex; gap: .5rem; }

/* GAME OVER DISPLAY */
.gameover-overlay {
  display: none; position: absolute; top: 50%; left: 50%;
  transform: translate(-50%,-50%);
  background: rgba(13,17,23,.93); border: 1px solid var(--gold);
  border-radius: 12px; padding: 1.5rem 2.5rem;
  text-align: center; z-index: 10; white-space: nowrap;
}
.gameover-overlay h3 { color: var(--gold); font-size: 1.2rem; margin-bottom: .25rem; }
.gameover-overlay p  { color: var(--text2); font-size: .85rem; margin: .2rem 0; }
.gameover-overlay .score-big { color: var(--text); font-size: 1.6rem; font-weight: 700; }

/* LAYOUT UTILS */
.container    { max-width: 960px; margin: 0 auto; padding: 2rem 1rem; }
.container-sm { max-width: 760px; margin: 0 auto; padding: 2rem 1rem; }
.d-flex { display: flex; }
.align-center { align-items: center; }
.justify-between { justify-content: space-between; }
.gap-sm { gap: .5rem; } .gap-md { gap: 1rem; }
.mt-1{margin-top:.5rem} .mt-2{margin-top:1rem} .mt-3{margin-top:1.5rem}
.mb-1{margin-bottom:.5rem} .mb-2{margin-bottom:1rem} .mb-3{margin-bottom:1.5rem}
.ms-auto{margin-left:auto} .w-100{width:100%}
.grid-auto { display: grid; grid-template-columns: repeat(auto-fit,minmax(200px,1fr)); gap: 1rem; }
.grid-2 { display: grid; grid-template-columns: 1fr 2fr; gap: 1rem; }
@media (max-width:640px) { .grid-2 { grid-template-columns: 1fr; } }

/* PAGE TITLE & MISC */
.page-title { color: var(--gold); font-weight: 700; font-size: 1.5rem; margin-bottom: 1.5rem; }
.instructions { color: var(--text2); font-size: .8rem; margin-bottom: .5rem; text-align: center; }
.stat-card { background: var(--bg2); border: 1px solid var(--border); border-radius: var(--radius); padding: 1.25rem 1.5rem; }
.stat-num { font-size: 2rem; font-weight: 700; color: var(--gold); line-height: 1; }
.stat-lbl { color: var(--text2); font-size: .75rem; text-transform: uppercase; letter-spacing: .6px; margin-top: .25rem; }

/* ARCADE DECORATION */
.arcade-deco {
  position: fixed;
  bottom: 0;
  right: 0;
  width: 420px;
  opacity: 0.5;
  pointer-events: none;
  z-index: 0;
}
</style>
