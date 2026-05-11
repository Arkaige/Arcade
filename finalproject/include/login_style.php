<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Arcade</title>
  <style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root { --bg:#0d1117;--bg2:#161b22;--bg3:#21262d;--border:#30363d;--gold:#f0c040;--gold2:#c89a20;--text:#e6edf3;--text2:#8b949e;--radius:10px; }
  body {
    background: var(--bg);
    background-image: linear-gradient(
      to top,
      rgba(255,255,255,0.18) 0%,
      rgba(255,255,255,0.07) 35%,
      transparent 60%
    );
    background-attachment: fixed;
    color: var(--text);
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .auth-card {
    background: var(--bg2);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 2.5rem 2rem;
    width: 100%;
    max-width: 380px;
  }
  .auth-logo  { text-align: center; margin-bottom: .5rem; display: block; }
  .auth-logo img { width: 72px; height: 72px; object-fit: contain; display: inline-block; }
  .deco-left { position: fixed; bottom: 0; left: 0; width: 420px; opacity: 0.85; pointer-events: none; z-index: 0; }
  .auth-title { color: var(--text2); font-size: .8rem; text-align: center; margin-bottom: 2rem; text-transform: uppercase; letter-spacing: 1px; }
  .form-group { margin-bottom: 1rem; }
  .form-label { display: block; color: var(--text2); font-size: .82rem; margin-bottom: .3rem; }
  .form-control {
    display: block; width: 100%;
    background: var(--bg3); border: 1px solid var(--border);
    color: var(--text); border-radius: var(--radius);
    padding: .65rem .9rem; font-size: .92rem;
    font-family: inherit; outline: none;
    transition: border-color .15s, box-shadow .15s;
  }
  .form-control:focus { border-color: var(--gold); box-shadow: 0 0 0 2px rgba(240,192,64,.2); }
  .btn-gold {
    display: block; width: 100%;
    background: var(--gold); color: #0d1117; font-weight: 700;
    border: none; border-radius: var(--radius);
    padding: .7rem; font-size: .95rem;
    cursor: pointer; margin-top: .5rem;
    transition: background .15s;
  }
  .btn-gold:hover { background: var(--gold2); }
  .auth-sep { border: none; border-top: 1px solid var(--border); margin: 1.5rem 0; }
  .auth-footer { text-align: center; font-size: .86rem; color: var(--text2); }
  .auth-link { color: var(--gold); text-decoration: none; }
  .auth-link:hover { text-decoration: underline; }
  .alert { border-radius: var(--radius); padding: .65rem .9rem; font-size: .88rem; border: 1px solid; margin-bottom: 1rem; }
  .alert-danger  { background: rgba(224,80,80,.1);  border-color: rgba(224,80,80,.3);  color: #ff7b7b; }
  .alert-success { background: rgba(63,185,80,.1);  border-color: rgba(63,185,80,.3);  color: #56d364; }
  .arcade-deco {
    position: fixed;
    bottom: 0;
    right: 0;
    width: 480px;
    opacity: 0.50;
    pointer-events: none;
    z-index: 0;
  }
  </style>
</head>
<body>
<img src="../assets/user.png" class="deco-left" alt="">
<img src="../assets/arcade.png" class="arcade-deco" alt="">
