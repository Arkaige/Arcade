<?php

// Runner - gameId=1

$histStmt = DBHandler::getPDO()->prepare("
    SELECT score, playedAt
    FROM matches
    WHERE userId = :uid AND gameId = 1
    ORDER BY playedAt DESC
    LIMIT 5
");
$histStmt->bindParam(':uid', $_SESSION['userId'], PDO::PARAM_INT);
$histStmt->execute();
$history = $histStmt->fetchAll();
?>

<style>
body { display:flex; flex-direction:column; align-items:center; padding-bottom:2rem; }
.page-layout {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
}
.game-area { display:flex; flex-direction:column; align-items:center; width:100%; }
.side-panel {
    position: absolute;
    left: calc(50% - 530px);
    top: 5.5rem;
    width: 190px;
}
@media (max-width: 900px) {
    .side-panel { position: static; width: 100%; max-width: 600px; margin-bottom: 1rem; }
    .page-layout { align-items: center; }
}
.side-title {
    font-size: .72rem; text-transform: uppercase;
    letter-spacing: .7px; color: var(--text2);
    font-weight: 600; margin-bottom: .75rem;
}
.side-card {
    background: var(--bg2); border: 1px solid var(--border);
    border-radius: var(--radius); padding: 1rem; margin-bottom: .75rem;
}
.history-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: .4rem 0; border-bottom: 1px solid rgba(48,54,61,.4); font-size: .85rem;
}
.history-row:last-child { border-bottom: none; }
.history-score { color: var(--gold); font-weight: 700; }
.history-date  { color: var(--text2); font-size: .75rem; }
.no-history    { color: var(--text2); font-size: .82rem; font-style: italic; }
.game-area { display:flex; flex-direction:column; align-items:center; width:100%; max-width:620px; padding:0 1rem; }
canvas { border-radius:12px; display:block; width:100%; max-width:600px; border:1px solid var(--border); }
#canvasWrapper { position:relative; display:inline-block; width:100%; max-width:600px; }
#gameOverMsg {
  display:none; position:absolute; top:50%; left:50%;
  transform:translate(-50%,-50%);
  background:rgba(13,17,23,.92); border:1px solid var(--gold);
  border-radius:12px; padding:1.5rem 2.5rem; text-align:center;
  z-index:10; white-space:nowrap; backdrop-filter:blur(4px);
}
#gameOverMsg h3 { color:var(--gold); margin:0 0 .3rem; font-size:1.2rem; }
#gameOverMsg p  { color:var(--text2); font-size:.85rem; margin:.25rem 0; }
#gameOverMsg .score-big { color:var(--text); font-size:1.6rem; font-weight:700; }

.game-deco-left { position:fixed; bottom:0; left:0; width:420px; opacity:0.85; pointer-events:none; z-index:0; }
</style>

<img src="../assets/runner.png" class="game-deco-left" alt="">
<div class="page-layout">

<div class="side-panel">
    <div class="side-title">📋 Ultime partite</div>
    <div class="side-card">
        <div id="historyList">
        <?php if (empty($history)): ?>
            <div class="no-history">Nessuna partita registrata.</div>
        <?php else: ?>
            <?php foreach ($history as $h): ?>
              <div class="history-row">
                  <span class="history-score"><?= number_format((int) $h['score']) ?></span>
                  <span class="history-date"><?= date('d/m H:i', strtotime($h['playedAt'])) ?></span>
              </div>
            <?php endforeach; ?>
        <?php endif; ?>
        </div>
    </div>
</div>

<div class="game-area">
  <div class="hud">
    <div class="hud-stat">
      <span>Punteggio</span>
      <strong id="scoreDisplay">0</strong>
    </div>
    <div class="hud-divider"></div>
    <div class="hud-stat">
      <span>Record</span>
      <strong id="bestDisplay">—</strong>
    </div>

  </div>

  <p class="instructions">SPAZIO o click per saltare • evita gli ostacoli</p>

  <div id="canvasWrapper">
    <canvas id="gameCanvas" width="600" height="220"></canvas>
    <div id="gameOverMsg">
      <h3>Game Over</h3>
      <p>Punteggio</p>
      <div class="score-big" id="finalScore">0</div>
      <p id="newRecordMsg" style="color:var(--gold);display:none;font-weight:600;">🎉 Nuovo record!</p>
      <button onclick="startGame()" class="btn btn-gold mt-3 px-4" style="font-size:.9rem;">▶ Rigioca</button>
    </div>
  </div>
</div>

<script>
const GAME_ID = 1,
      canvas = document.getElementById('gameCanvas'),
      ctx = canvas.getContext('2d');

const W = canvas.width,
      H = canvas.height,
      GY = 170;
  
let player, obstacles, score, speed, frame, animId, running, prevBest = 0;

function startGame(){
  player = {
    x:60,
    y:GY,
    w:32,
    h:32,
    vy:0,
    onGround:true
  };

  obstacles = [];
  score = 0;
  speed = 4; 
  frame = 0; 
  running = true;

  document.getElementById('gameOverMsg').style.display = 'none';
  document.getElementById('scoreDisplay').textContent = '0';
  cancelAnimationFrame(animId);

  loop();
}

function jump(){
  if(player && player.onGround){
    player.vy = -11;
    player.onGround = false;
  } 
}

document.addEventListener('keydown', e => {
  if(e.code === 'Space'){
    e.preventDefault();
    jump();
  }
});

canvas.addEventListener('click',jump);
canvas.addEventListener('touchstart',e => {
  e.preventDefault();
  jump();
},{passive:false});

function loop(){
  if(!running){
    return;
  }
  
  update();
  draw();
  animId = requestAnimationFrame(loop);
}

function update(){
  frame++;
  score++;

  if(frame % 400 === 0){
    speed += 0.75;
  }

  // Gravity
  player.vy += 0.55;
  player.y += player.vy;
  if(player.y >= GY){
    player.y = GY;
    player.vy = 0;
    player.onGround = true;
  }

  // Obstacle distance
  const iv = Math.max(55, Math.floor(110 - speed * 4));
  if(frame % iv === 0){
    const h = 18 + Math.floor(Math.random() * 38);
    obstacles.push({
      x: W,
      y: GY + 32 - h,
      w:18,
      h: h
    });
  }

  // Obstacle LOS
  obstacles = obstacles.filter(o => {
    o.x -= speed;
    return o.x + o.w > 0;
  });

  // Losing condition
  const m = 5;
  for(const o of obstacles){
    if(player.x + player.w - m > o.x + m && player.x + m < o.x + o.w - m && player.y + player.h - m > o.y + m && player.y + m < o.y + o.h - m){
      gameOver();
      return;
    }
  }
  document.getElementById('scoreDisplay').textContent = score;
}

function draw(){
  ctx.clearRect(0,0,W,H);

  const sky = ctx.createLinearGradient(0,0,0,GY);
  sky.addColorStop(0,'#0d1117');
  sky.addColorStop(1,'#161b22');

  ctx.fillStyle = sky;
  ctx.fillRect(0,0,W,GY+32);

  // Stars
  ctx.fillStyle = 'rgba(255,255,255,0.5)';
  [40,120,200,330,460,550,80,280,410].forEach((x,i) => {
    const y = [20,35,12,45,18,30,55,8,42][i];
    ctx.fillRect(x + (frame / 30 % 1) * - 0.5, y, 1, 1);
  });

  // Ground
  ctx.fillStyle = '#1c2a3a';
  ctx.fillRect(0, GY + 32, W, H - GY - 32);
  // Top line
  ctx.fillStyle = '#2d4a6a';
  ctx.fillRect(0, GY + 30, W, 4);
  ctx.fillStyle = 'rgba(45,74,106,0.4)';
  ctx.fillRect(0, GY + 32, W, 2);

  // Player body
  ctx.fillStyle = '#f0c040';
  roundRect(ctx, player.x, player.y, player.w, player.h, 5);
  ctx.fill();

  // Obstacles
  obstacles.forEach(o => {
    ctx.fillStyle = '#c0392b';
    roundRect(ctx, o.x, o.y, o.w, o.h, 3);
    ctx.fill();
    ctx.fillStyle = '#e74c3c';
    ctx.fillRect(o.x + 2, o.y + 2, o.w - 4, 4);
  });
}

function roundRect(c,x,y,w,h,r){
  c.beginPath();
  c.roundRect(x,y,w,h,r);
}


function gameOver(){
  running = false;
  cancelAnimationFrame(animId);
  const s = score;
  document.getElementById('finalScore').textContent = s;
  const nr = document.getElementById('newRecordMsg');
  if (s > prevBest) {
    nr.style.display = 'block';
  } else {
    nr.style.display = 'none';
  }
  document.getElementById('gameOverMsg').style.display = 'block';
  loadHistory();
  fetch('saveScore.php?gameId=' + GAME_ID, {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'score=' + s + '&gameId=' + GAME_ID})
    .then(function(r) {
      return r.json(); 
    })
    .then(function(d) {
      if (d.bestScore !== undefined) {
        prevBest = d.bestScore;
        document.getElementById('bestDisplay').textContent = d.bestScore;
      }
    }).catch(function() {});
}
fetch('saveScore.php?getBest=1&gameId=' + GAME_ID)
  .then(function(r) { 
    return r.json(); 
  })
  .then(function(d) {
    if (d.bestScore !== undefined) {
      prevBest = d.bestScore;
      document.getElementById('bestDisplay').textContent = d.bestScore;
    }
  }).catch(function() {});



// Initial drawing
(function drawInitial() {
    var p = {
      x:60,
      y:170, 
      w:32, 
      h:32
    };
    ctx.clearRect(0,0,W,H);

    var sky = ctx.createLinearGradient(0,0,0,170);
    sky.addColorStop(0,'#0d1117');
    sky.addColorStop(1,'#161b22');
    ctx.fillStyle=sky;
    ctx.fillRect(0,0,W,202);

    ctx.fillStyle='#2a2a4a';
    ctx.fillRect(0,202,W,H-202);
    ctx.fillStyle='#f0c040';
    ctx.fillRect(0,199,W,2);


    ctx.fillStyle='#f0c040';
    ctx.beginPath();
    ctx.roundRect(p.x,p.y,p.w,p.h,5);
    ctx.fill();

    // Starting text
    ctx.fillStyle='rgba(13,17,23,0.75)'; 
    ctx.fillRect(0,0,W,H);
    ctx.fillStyle='#f0c040'; 
    ctx.font='bold 16px sans-serif'; 
    ctx.textAlign='center';
    ctx.fillText('🏃 Runner', W / 2, H / 2 - 16);
    ctx.fillStyle='#8b949e'; 
    ctx.font='13px sans-serif';
    ctx.fillText('Premi SPAZIO o clicca per iniziare', W/2, H/2 + 12);
})();

// Starting display input
function waitForStart() {
    var started = false;
    function begin() {
        if (started) { 
          return;
        }
        started = true;
        window.removeEventListener('keydown', onKey);
        canvas.removeEventListener('click', onClick);
        startGame();
    }
    function onKey(e) { 
      if (e.code === 'Space') { 
        e.preventDefault(); 
        begin(); 
      } 
    }
    function onClick() { 
      begin(); 
    }
    window.addEventListener('keydown', onKey);
    canvas.addEventListener('click', onClick);
}

waitForStart();


function loadHistory() {
    fetch('getHistory.php?gameId=1')
    .then(function(r) { 
      return r.json(); 
    })
    .then(function(d) {
        var container = document.getElementById('historyList');
        if (!container) { 
          return; 
        }
        if (d.length === 0) {
            container.innerHTML = '<div class="no-history">Nessuna partita registrata.</div>';
            return;
        }
        var html = '';
        for (var i = 0; i < d.length; i++) {
            var date = new Date(d[i].playedAt.replace(' ', 'T'));
            var pad = function(n) { 
                        return n < 10 ? '0' + n : '' + n; 
                      };
            var dateStr = pad(date.getDate()) + '/' + pad(date.getMonth() + 1) + ' ' + pad(date.getHours()) + ':' + pad(date.getMinutes());
            html += '<div class="history-row">';
            html += '<span class="history-score">' + d[i].score.toLocaleString() + '</span>';
            html += '<span class="history-date">' + dateStr + '</span>';
            html += '</div>';
        }
        container.innerHTML = html;
    })
    .catch(function() {});
}
loadHistory();
</script>
</div><!-- game area -->
</div><!-- page layout -->
</body></html>
