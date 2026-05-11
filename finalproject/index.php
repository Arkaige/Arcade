<?php include 'include/login_style.php'; ?>
<style>
.welcome-card {
    background: var(--bg2);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 2.5rem 2rem;
    width: 100%;
    max-width: 480px;
    text-align: center;
}
.welcome-title {
    color: var(--gold);
    font-size: 1.6rem;
    font-weight: 700;
    margin-bottom: .3rem;
}
.welcome-sub {
    color: var(--text2);
    font-size: .85rem;
    margin-bottom: 2rem;
}
.game-cards {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 2rem;
}
.game-card {
    background: var(--bg3);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 1.5rem 1rem;
    cursor: pointer;
    transition: border-color .15s, background .15s, transform .1s;
    text-decoration: none;
    display: block;
}
.game-card:hover {
    border-color: var(--gold);
    background: rgba(240,192,64,.07);
    transform: translateY(-2px);
}
.game-card.selected {
    border-color: var(--gold);
    background: rgba(240,192,64,.12);
}
.game-card-icon { font-size: 2.2rem; margin-bottom: .5rem; }
.game-card-name {
    color: var(--text);
    font-weight: 600;
    font-size: .95rem;
    margin-bottom: .25rem;
}
.game-card-desc { color: var(--text2); font-size: .78rem; line-height: 1.4; }
.auth-sep { border: none; border-top: 1px solid var(--border); margin: 0 0 1.5rem; }
.auth-links { display: flex; gap: .75rem; justify-content: center; }
.btn-play {
    width: 100%;
    background: var(--gold);
    color: #0d1117;
    font-weight: 700;
    border: none;
    border-radius: var(--radius);
    padding: .7rem;
    font-size: .95rem;
    cursor: pointer;
    margin-bottom: 1.5rem;
    transition: background .15s;
    font-family: inherit;
}
.btn-play:hover { background: var(--gold2); }
.btn-ghost {
    background: transparent;
    color: var(--text2);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: .55rem 1.2rem;
    font-size: .88rem;
    cursor: pointer;
    text-decoration: none;
    transition: all .15s;
    font-family: inherit;
    display: inline-block;
}

.btn-ghost:hover { 
    color: var(--text);
    border-color: var(--text2);
}
</style>

<img id="gamePreviewImg" src="assets/runner.png" style="position:fixed;bottom:0;left:0;width:420px;opacity:0.85;pointer-events:none;z-index:0;" alt="">
<img src="assets/arcade.png" style="position:fixed;bottom:0;right:0;width:480px;opacity:0.5;pointer-events:none;z-index:0;" alt="">

<div class="welcome-card">
    <div class="auth-logo"><img src="assets/logo.png" alt="Arcade"></div>
    <div class="welcome-title">Arcade</div>
    <div class="welcome-sub">Scegli un gioco per iniziare</div>

    <div class="game-cards">
        <div class="game-card" id="card-runner" onclick="selectGame('runner')">
            <div class="game-card-icon">🏃</div>
            <div class="game-card-name">Runner</div>
            <div class="game-card-desc">Salta gli ostacoli e percorri la distanza più lunga</div>
        </div>
        <div class="game-card" id="card-slingshot" onclick="selectGame('slingshot')">
            <div class="game-card-icon">🎯</div>
            <div class="game-card-name">Slingshot</div>
            <div class="game-card-desc">Lancia il dado tra le piattaforme</div>
        </div>
    </div>

    <button class="btn-play" id="playBtn" onclick="goPlay()">▶ Gioca</button>


</div>

<script>
var selected = 'runner';
document.getElementById('card-runner').classList.add('selected');

function selectGame(game) {
    selected = game;
    document.getElementById('card-runner').classList.remove('selected');
    document.getElementById('card-slingshot').classList.remove('selected');
    document.getElementById('card-' + game).classList.add('selected');

    var img = document.getElementById('gamePreviewImg');
    if (game == 'runner') {
        img.src = 'assets/runner.png';
    } else {
        img.src = 'assets/slingshot.png';
    }
}

function goPlay() {
    if (selected == 'runner') {
        window.location.href = 'userpages/runner.php';
    } else {
        window.location.href = 'userpages/slingshot.php';
    }
}
</script>

</body></html>
