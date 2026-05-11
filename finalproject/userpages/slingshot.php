<?php

// Slingshot - gameId=2

$histStmt = DBHandler::getPDO()->prepare("
    SELECT score, playedAt
    FROM matches
    WHERE userId = :uid AND gameId = 2
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
.game-area { display:flex; flex-direction:column; align-items:center; width:100%; max-width:520px; padding:0 1rem; }
#canvasWrapper { position:relative; display:inline-block; }
canvas { display:block; border-radius:12px; border:1px solid var(--border); }
#gameOverMsg {
  display:none; position:absolute; top:50%; left:50%;
  transform:translate(-50%,-50%);
  background:rgba(13,17,23,.92); border:1px solid var(--gold);
  border-radius:12px; padding:1.5rem 2.5rem; text-align:center;
  z-index:10; white-space:nowrap;
}
#gameOverMsg h3 { color:var(--gold); margin:0 0 .3rem; font-size:1.2rem; }
#gameOverMsg p  { color:var(--text2); font-size:.85rem; margin:.25rem 0; }
#gameOverMsg .score-big { color:var(--text); font-size:1.6rem; font-weight:700; }

.game-deco-left { position:fixed; bottom:0; left:0; width:420px; opacity:0.85; pointer-events:none; z-index:0; }
</style>

<img src="../assets/slingshot.png" class="game-deco-left" alt="">
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
                    <span class="history-score"><?= number_format((int)$h['score']) ?>s</span>
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
      <span>Tempo</span>
      <strong id="scoreDisplay">0s</strong>
    </div>
    <div class="hud-divider"></div>
    <div class="hud-stat">
      <span>Record</span>
      <strong id="bestDisplay">—</strong>
    </div>

  </div>

  <p class="instructions">Trascina il dado e rilascia per lanciarlo - Evita di toccare il suolo rosso</p>

  <div id="canvasWrapper">
    <div id="canvasContainer"></div>
    <div id="gameOverMsg">
      <h3>Game Over</h3>
      <p>Tempo sopravvissuto</p>
      <div class="score-big"><span id="finalScore">0</span>s</div>
      <p id="newRecordMsg" style="color:var(--gold);display:none;font-weight:600;">Nuovo record!</p>
      <button onclick="restartGame()" class="btn btn-gold" style="margin-top:1rem;font-size:.9rem;">Rigioca</button>
    </div>
  </div>
</div>

<script>
const GAME_ID = 2;
const GW = 500;
const GH = Math.min(window.innerHeight - 220, 620);

let platform = [];
let gameInstance = null;
let prevBest = 0;

const C = {
    bgTop:      '#0d1117',
    bgBot:      '#161b22',
    platFill:   '#1c3a5e',
    platTop:    '#2d6a9f',
    platShad:   '#0a1a2e',
    playerBody: '#e03030',
    sling:      '#c89a20',
    ground:     'rgba(224,80,80,0.15)'
};

// BASE 
class GameObject {
    constructor(x, y, w, h) {
        this.x = x;
        this.y = y;
        this.width  = w;
        this.height = h;
    }
}

// PLATFORM
class Platform extends GameObject {
    constructor(x, y, w) {
        super(x, y, w, 16);
    }

    falling() {
        this.y += 0.08;
    }

    draw(ctx) {
        // Shadow
        ctx.fillStyle = C.platShad;
        ctx.fillRect(this.x + 2, this.y + 4, this.width, this.height);

        // Body
        ctx.fillStyle = C.platFill;
        ctx.beginPath();
        ctx.roundRect(this.x, this.y, this.width, this.height, 4);
        ctx.fill();

        // Top line
        ctx.fillStyle = C.platTop;
        ctx.fillRect(this.x + 4, this.y + 2, this.width - 8, 3);
    }
}

// PLAYER 
class Player extends GameObject {
    constructor(x, y) {
        super(x, y, 28, 28);
        this.speedX = 0;
        this.speedY = 0;
        this.gravity = 0.05;
        this.gravitySpeed = 0;
        this.turn = 1;
        this.jumpStatus = true;
        this.onGround = false;
        this.prevY = y;
        this.angle = 0;
    }

    launch(vx, vy) {
        this.speedX = vx;
        this.gravitySpeed = vy;
        this.jumpStatus = true;
        this.onGround = false;
    }

    updatePosition() {
        this.gravitySpeed += this.gravity;
        this.x += Math.round(this.speedX);
        this.prevY = this.y;
        this.y += this.speedY + this.gravitySpeed;

        // Rotating speed
        if (!this.onGround) {
            this.angle += this.speedX * 0.05;
        }

        this.border();
        this.floor();
    }

    border() {
        if (this.x >= GW - this.width || this.x <= 0) {
            this.speedX *= -1;
            this.turn *= -1;
            return;
        }

        for (let i = 0; i < platform.length; i++) {
            const el = platform[i];
            const overlapX = this.x + this.width > el.x && this.x < el.x + el.width;
            const overlapY = this.y + this.height > el.y && this.y < el.y + el.height;

            if (!overlapX || !overlapY) {
                continue;
            }

            const oL = (this.x + this.width) - el.x;
            const oR = (el.x + el.width) - this.x;
            const oT = (this.y + this.height) - el.y;
            const oB = (el.y + el.height) - this.y;

            if (Math.min(oL, oR) < Math.min(oT, oB)) {
                if (oL < oR) {
                    this.x = el.x - this.width;
                } else {
                    this.x = el.x + el.width;
                }
                this.speedX *= -1;
                this.turn *= -1;
            }
        }
    }

    floor() {
        const rock = GH - this.height;

        if (this.y > rock) {
            this.y = rock;
            this.gravitySpeed = 0;
            this.onGround = true;
            this.angle = 0;
            //this.friction();
            if (gameInstance !== null) {
                gameInstance.triggerGameOver();
            }
            return;
        }

        this.onGround = false;

        for (let i = 0; i < platform.length; i++) {
            const el = platform[i];

            if (this.x + this.width <= el.x || this.x >= el.x + el.width) {
                continue;
            }

            const prevBottom = this.prevY + this.height;
            const currBottom = this.y + this.height;

            if (prevBottom <= el.y && currBottom >= el.y) {
                this.y = el.y - this.height;
                this.gravitySpeed = 0;
                this.onGround = true;
                this.angle = 0;
                this.friction();
            } else if (this.prevY >= el.y + el.height && this.y < el.y + el.height) {
                this.y = el.y + el.height;
                this.gravitySpeed = 0;
            }
        }
    }

    friction() {
        if (!this.jumpStatus) {
            if (this.speedX > 0) {
                this.speedX -= 0.1;
            } else if (this.speedX < 0) {
                this.speedX += 0.1;
            }
        } else {
            this.jumpStatus = false;
        }
    }

    draw(ctx) {
        ctx.save();
        const cx = this.x + this.width  / 2;
        const cy = this.y + this.height / 2;
        ctx.translate(cx, cy);
        ctx.rotate(this.angle);
        ctx.translate(-this.width / 2, -this.height / 2);

        // Body
        ctx.fillStyle = C.playerBody;
        ctx.beginPath();
        ctx.roundRect(0, 0, this.width, this.height, 5);
        ctx.fill();

        ctx.restore();
    }
}

//  GAME 
class Game {
    constructor() {
        this.canvas = document.createElement('canvas');
        this.canvas.width = GW;
        this.canvas.height = GH;
        this.ctx = this.canvas.getContext('2d');
        this.running = false;
        this.gameOver = false;
        this.startTime = null;
        this.dragging = false;
        this.dragCurrent = null;

        document.getElementById('canvasContainer').innerHTML = '';
        document.getElementById('canvasContainer').appendChild(this.canvas);

        this.stars = [];
        for (let i = 0; i < 40; i++) {
            this.stars.push({
                x: Math.random() * GW,
                y: Math.random() * GH * 0.6,
                r: Math.random() * 0.8 + 0.3
            });
        }
    }

    start() {
        platform = [];
        this.gameOver = false;
        this.player = new Player(80, GH - 160);

        // Starting platforms
        const step = GH / 5;
        platform.push(new Platform(30,  step * 4,        130));
        platform.push(new Platform(270, step * 3 + 20,   110));
        platform.push(new Platform(100, step * 2 - 10,   100));
        platform.push(new Platform(320, step * 1 + 15,   120));
        platform.push(new Platform(60,  -step,           110));
        platform.push(new Platform(250, -step * 2 - 30,  100));

        this.startTime = Date.now();
        this.running = true;
        document.getElementById('gameOverMsg').style.display = 'none';

        // Mouse in dice
        this._od = function(e) {
            const pos = gameInstance.getPos(e);
            if (gameInstance.player.onGround || gameInstance.player.speedX == 0) {
                if (gameInstance.hitPlayer(pos)) {
                    gameInstance.dragging = true;
                    gameInstance.dragCurrent = pos;
                }
            }
        };

        // Pulling position
        this._om = function(e) {
            if (gameInstance.dragging) {
                gameInstance.dragCurrent = gameInstance.getPos(e);
            }
        };

        // Pulling distance requirement
        this._ou = function(e) {
            if (gameInstance.dragging) {
                const pull = gameInstance.getPull();
                if (pull !== null && pull.len > 5) {
                    const power = pull.len / 250;
                    gameInstance.player.launch(pull.dx * power * 0.22, pull.dy * power * 0.22);
                }
                gameInstance.dragging = false;
                gameInstance.dragCurrent = null;
            }
        };

        this._ts = function(e) { 
            e.preventDefault(); 
            gameInstance._od(e.touches[0]); 
        };
        this._tm = function(e) { 
            e.preventDefault();
             gameInstance._om(e.touches[0]); 
        };
        this._te = function(e) { 
            e.preventDefault(); 
            gameInstance._ou(e.changedTouches[0]); 
        };

        window.addEventListener('mousedown', this._od);
        window.addEventListener('mousemove', this._om);
        window.addEventListener('mouseup', this._ou);
        this.canvas.addEventListener('touchstart', this._ts, { passive: false });
        this.canvas.addEventListener('touchmove', this._tm, { passive: false });
        this.canvas.addEventListener('touchend', this._te, { passive: false });

        this.loop();
    }

    stop() {
        this.running = false;
        window.removeEventListener('mousedown', this._od);
        window.removeEventListener('mousemove', this._om);
        window.removeEventListener('mouseup', this._ou);
        this.canvas.removeEventListener('touchstart', this._ts);
        this.canvas.removeEventListener('touchmove', this._tm);
        this.canvas.removeEventListener('touchend', this._te);
    }

    triggerGameOver() {
        if (this.gameOver) {
            return;
        }
        this.gameOver = true;
        this.stop();

        const s = Math.floor((Date.now() - this.startTime) / 1000);
        document.getElementById('finalScore').textContent = s;

        const nr = document.getElementById('newRecordMsg');
        if (s > prevBest) {
            nr.style.display = 'block';
        } else {
            nr.style.display = 'none';
        }

        document.getElementById('gameOverMsg').style.display = 'block';

        // History card
        loadHistory();

        fetch('saveScore.php?gameId=' + GAME_ID, {
            method:  'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body:    'score=' + s + '&gameId=' + GAME_ID
        })
        .then(function(r) { 
            return r.json(); 
        })
        .then(function(d) {
            if (d.bestScore !== undefined) {
                prevBest = d.bestScore;
                document.getElementById('bestDisplay').textContent = d.bestScore + 's';
            }
        }).catch(function() {});
    }

    getPos(e) {
        const r = this.canvas.getBoundingClientRect();
        const scaleX = GW / r.width;
        const scaleY = GH / r.height;
        return {
            x: (e.clientX - r.left) * scaleX,
            y: (e.clientY - r.top)  * scaleY
        };
    }

    hitPlayer(pos) {
        const cx = this.player.x + this.player.width / 2;
        const cy = this.player.y + this.player.height / 2;
        return Math.hypot(pos.x - cx, pos.y - cy) < 34;
    }

    // Pulling logic
    getPull() {
        if (!this.dragging || this.dragCurrent === null) {
            return null;
        }


        const cx = this.player.x + this.player.width / 2;
        const cy = this.player.y + this.player.height / 2;
        let dx = cx - this.dragCurrent.x;
        let dy = cy - this.dragCurrent.y;
        const len = Math.hypot(dx, dy);
        const cap = Math.min(len, 100);
        if (len > 100) {
            dx = dx / len * 100;
            dy = dy / len * 100;
        }

        return { 
            dx: dx,
            dy: dy, 
            len: cap 
        };
    }

    drawSlingshot() {
        if (!this.dragging) { 
            return; 
        }

        const pull = this.getPull();
        if (pull === null) { 
            return; 
        }


        const ctx = this.ctx;
        const cx = this.player.x + this.player.width  / 2;
        const cy = this.player.y + this.player.height / 2;
        const bx = cx - pull.dx;
        const by = cy - pull.dy;

        // Rubber
        ctx.beginPath();
        ctx.moveTo(cx, cy);
        ctx.lineTo(bx, by);
        ctx.strokeStyle = C.sling;
        ctx.lineWidth = 2.5;
        ctx.stroke();

        // Trajectory
        const power = pull.len / 100;
        let px = bx;
        let py = by;
        let pvx = pull.dx * power * 0.22;
        let pvy = pull.dy * power * 0.22;

        for (let i = 0; i < 22; i++) {
            pvy += this.player.gravity;
            px += pvx;
            py += pvy;
            if (i % 2 === 0) {
                const alpha = 0.5 - i * 0.02;
                ctx.beginPath();
                ctx.arc(px, py, 2.5, 0, Math.PI * 2);
                ctx.fillStyle = 'rgba(240,192,64,' + alpha + ')';
                ctx.fill();
            }
        }
    }

    generatePlatform() {
        const x = 30 + Math.floor(Math.random() * (GW - 160));
        const w = 80 + Math.floor(Math.random() * 80);
        platform.push(new Platform(x, -20, w));
        if (platform.length > 10) {
            platform.shift();
        }
    }

    // Background
    drawBg(ctx) {
        const grad = ctx.createLinearGradient(0, 0, 0, GH);
        grad.addColorStop(0, C.bgTop);
        grad.addColorStop(1, C.bgBot);
        ctx.fillStyle = grad;
        ctx.fillRect(0, 0, GW, GH);

        // Stars
        ctx.fillStyle = 'rgba(255,255,255,0.6)';
        for (let i = 0; i < this.stars.length; i++) {
            const s = this.stars[i];
            ctx.beginPath();
            ctx.arc(s.x, s.y, s.r, 0, Math.PI * 2);
            ctx.fill();
        }

        // Death zone
        ctx.fillStyle = C.ground;
        ctx.fillRect(0, GH - 6, GW, 6);
        ctx.strokeStyle = 'rgba(224,80,80,0.4)';
        ctx.lineWidth = 1;
        ctx.setLineDash([6, 4]);
        ctx.beginPath();
        ctx.moveTo(0, GH - 6);
        ctx.lineTo(GW, GH - 6);
        ctx.stroke();
        ctx.setLineDash([]);
    }

    loop() {
        if (!this.running) { 
            return; 
        }

        // Generate platform timing
        let highest = GH;
        for (let i = 0; i < platform.length; i++) {
            if (platform[i].y < highest) {
                highest = platform[i].y;
            }
        }
        if (highest > GH / 5) {
            this.generatePlatform();
        }

        this.player.updatePosition();

        const ctx = this.ctx;
        ctx.clearRect(0, 0, GW, GH);
        this.drawBg(ctx);

        for (let i = 0; i < platform.length; i++) {
            platform[i].draw(ctx);
            platform[i].falling();
        }

        this.player.draw(ctx);
        this.drawSlingshot();

        if (this.startTime !== null) {
            const elapsed = Math.floor((Date.now() - this.startTime) / 1000);
            document.getElementById('scoreDisplay').textContent = elapsed + 's';
        }

        requestAnimationFrame(function() { 
            gameInstance.loop(); 
        });
    }
}

function restartGame() {
    if (gameInstance !== null) {
        gameInstance.stop();
    }
    gameInstance = new Game();
    gameInstance.start();
}

window.onload = function() {
    gameInstance = new Game();
    gameInstance.start();
    
    // Pauses the display
    gameInstance.running = false;
    cancelAnimationFrame(gameInstance._animId);

    // Starting display
    gameInstance.drawBg(gameInstance.ctx);
    for (var i = 0; i < platform.length; i++) {
        platform[i].draw(gameInstance.ctx);
    }
    gameInstance.player.draw(gameInstance.ctx);

    // Overlay text
    var ctx2 = gameInstance.ctx;
    ctx2.fillStyle = 'rgba(13,17,23,0.75)';
    ctx2.fillRect(0, 0, gameInstance.canvas.width, gameInstance.canvas.height);
    ctx2.fillStyle = '#f0c040';
    ctx2.font = 'bold 16px sans-serif';
    ctx2.textAlign = 'center';
    ctx2.fillText('🎯 Slingshot', gameInstance.canvas.width / 2, gameInstance.canvas.height / 2 - 16);
    ctx2.fillStyle = '#8b949e';
    ctx2.font = '13px sans-serif';
    ctx2.fillText('Clicca lo schermo per iniziare', gameInstance.canvas.width / 2, gameInstance.canvas.height / 2 + 12);

    function beginOnFirstDrag(e) {
        gameInstance.startTime = Date.now();
        gameInstance.running = true;
        gameInstance.loop();
        gameInstance.canvas.removeEventListener('mousedown', beginOnFirstDrag);
        gameInstance.canvas.removeEventListener('touchstart', beginOnFirstDrag);
    }
    gameInstance.canvas.addEventListener('mousedown', beginOnFirstDrag);
    gameInstance.canvas.addEventListener('touchstart', beginOnFirstDrag, { passive: false });

    fetch('saveScore.php?getBest=1&gameId=' + GAME_ID)
    .then(function(r) { 
        return r.json(); 
    })
    .then(function(d) {
        if (d.bestScore !== undefined) {
            prevBest = d.bestScore;
            document.getElementById('bestDisplay').textContent = d.bestScore + 's';
        }
    }).catch(function() {});
};

// History card
function loadHistory() {
    fetch('getHistory.php?gameId=2')
    .then(function(r) { 
        return r.json(); 
    })
    .then(function(d) {
        var container = document.getElementById('historyList');
        if (!container) { return; }
        if (d.length === 0) {
            container.innerHTML = '<div class="no-history">Nessuna partita registrata.</div>';
            return;
        }
        var html = '';
        for (var i = 0; i < d.length; i++) {
            var date = new Date(d[i].playedAt.replace(' ', 'T'));
            var pad = function(n) { return n < 10 ? '0' + n : '' + n; };
            var dateStr = pad(date.getDate()) + '/' + pad(date.getMonth()+1) + ' ' + pad(date.getHours()) + ':' + pad(date.getMinutes());
            html += '<div class="history-row">';
            html += '<span class="history-score">' + d[i].score + 's' + '</span>';
            html += '<span class="history-date">' + dateStr + '</span>';
            html += '</div>';
        }
        container.innerHTML = html;
    }).catch(function() {});
}

loadHistory();
</script>
</div><!-- game area -->
</div><!-- pagelayout -->
</body></html>
