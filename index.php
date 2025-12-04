<?php
// index.php - dashboard one-page layout
session_start();
require_once __DIR__ . "/core/User.php";
require_once __DIR__ . "/core/QuestManager.php";
// deklarasi variabel dan objek (modul 1)
$user = new User();
$quests = new QuestManager();

$msg = "";

// username (ini menggunakan pengkondisian di modul 2)
if (isset($_POST['set_username'])) {
    $name = trim($_POST['username'] ?? '');
    if ($name !== '') { // bagian ini pengkondisian + setter
        $user->setUsername($name);
        $msg = "Welcome, " . htmlspecialchars($name) . "!";
    } else $msg = "Please enter a username.";
}

// rename username
if(isset($_POST['rename_user'])){
    $new = trim($_POST['new_username']);
    if($new !== ""){ 
        $user->rename($new);
        $feedback = "📜 Your name has been rewritten in the ancient records, <b>$new</b>!";
    }
}


// add quest
if (isset($_POST['add_quest'])) {
    $title = trim($_POST['title'] ?? '');
    $diff = $_POST['difficulty'] ?? 'normal';
    if ($title !== '') {
        $quests->add($title, $diff);
        $msg = "Quest added: " . htmlspecialchars($title);
    }
}

// complete quest
if (isset($_POST['complete_id'])) {
    $id = (int)$_POST['complete_id'];
    $reward = $quests->complete($id);
    if ($reward > 0) {
        $user->addXp($reward);
        $user->addGold(5);
        $user->updateStreakIfToday();
        $msg = "Quest completed! +{$reward} EXP and +5 gold.";
    } else $msg = "Quest not found or already done.";
}

// delete quest
if (isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    if ($quests->delete($id)) $msg = "Quest deleted.";
}

// start session
if (isset($_POST['start_session'])) {
    $id = (int)($_POST['quest_id']);
    $q = $quests->getAll();
// perulangan
    foreach ($q as $quest) {
        if ($quest['id'] == $id && $quest['status'] === 'pending') {
            $_SESSION['active_session'] = [
                'task' => $quest['title'],
                'start' => time(),
                'quest_id' => $id
            ];
            $msg = "You begin working on <strong>" . htmlspecialchars($quest['title']) . "</strong>...";
            break;
        }
    }
}


// finish session
if (isset($_POST['finish_session'])) {
    if (isset($_SESSION['active_session'])) {
        $a = $_SESSION['active_session'];

        // auto-complete quest
        $reward = $quests->complete($a['quest_id']);
        if ($reward > 0) {
            $user->addXp($reward);
            $user->addGold(10);
            $user->updateStreakIfToday();
            $msg = "Quest completed! +{$reward} XP and +10 gold.";
        } else {
            $msg = "Quest could not be completed or already finished.";
        }

        unset($_SESSION['active_session']);
    } else {
        $msg = "No active timer.";
    }
}


// cancel session
if (isset($_POST['cancel_session'])) {
    unset($_SESSION['active_session']);
    $msg = "Session canceled.";
}

// purchase reward
if (isset($_POST['buy_reward'])) {
    if ($user->buyReward(50)) {
        $msg = "🎁 Reward redeemed: +10 Gold!";
    } else {
        $msg = "❌ Not enough XP to buy this reward. (Need 50)";
    }
}



// data for display
$username = $user->getUsername();
$level = $user->getLevel();
$xp = $user->getXp();
$xpNext = 150;
$allQuests = $quests->getAll();
$active = $_SESSION['active_session'] ?? null;
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>QuestRPG - Dashboard</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <div class="header">
    <h1>QuestForge</h1>
    <nav>
        <a href="index.php">🏠 Dashboard</a>
        <a href="profile.php">👤 Profile</a>
    </nav>
</div>


  <div class="wrapper" id="dashboard">
    <div class="status-bar">

  <!-- LEFT: Username -->
  <div class="user-info">
    <span class="username"><?= htmlspecialchars($username ?: 'Guest') ?></span>

    <?php if (!$username): ?>
      <form method="post" class="inline-setname">
        <input type="text" name="username" placeholder="Enter name" required>
        <button class="button" name="set_username">Set</button>
      </form>
    <?php endif; ?>
  </div>

  <!-- RIGHT: Level + XP Bar -->
  <div class="level-section">
      <div class="level-text">Lv <?= $level ?></div>
      <div class="xpbar">
        <div class="xpfill" style="width:<?= min(100, (int) round($xp/$xpNext*100)) ?>%;"></div>
      </div>
      <div class="xp-num"><?= $xp ?>/<?= $xpNext ?> XP</div>
  </div>

</div>


    <?php if ($msg): ?><div class="card msg"><?= $msg ?></div><?php endif; ?>

<div class="grid">

  <!-- LEFT SIDE -->
  <div>

    <!-- QUESTS CARD -->
    <div class="card">
        <h2>🗡 Quests</h2>

        <form method="post" style="margin-bottom:10px;">
          <input type="text" name="title" placeholder="New quest title" required style="padding:8px;width:60%;">
          <select name="difficulty" style="padding:8px;">
            <option value="easy">Easy (+10 XP)</option>
            <option value="normal">Normal (+20 XP)</option>
            <option value="hard">Hard (+40 XP)</option>
          </select>
          <button class="button" name="add_quest">Add</button>
        </form>

        <?php if (empty($allQuests)): ?>
          <p>No quests yet.</p>
        <?php else: ?>

        <?php 
          $pending = array_filter($allQuests, fn($q) => $q['status'] === 'pending');
          $completed = array_filter($allQuests, fn($q) => $q['status'] === 'completed');
        ?>

        <!-- ACTIVE QUESTS -->
        <div class="card" style="margin-top:10px;">
          <h3>🔥 Active Quests</h3>
          <?php if (empty($pending)): ?>
            <p>No active quests — you're free!</p>
          <?php else: ?>
            <ul class="quest-list">
              <?php foreach ($pending as $q): ?>
                <li class="quest-item">
                  <div>
                    <div class="quest-title"><?= htmlspecialchars($q['title']) ?></div>
                    <div class="quest-meta"><?= ucfirst($q['difficulty']) ?> • +<?= $q['reward'] ?> XP</div>
                  </div>
                  <div>
                    <form method="post" style="display:inline;">
                      <input type="hidden" name="complete_id" value="<?= $q['id'] ?>">
                      <button class="button">Complete</button>
                    </form>
                    <form method="post" style="display:inline;">
                      <input type="hidden" name="delete_id" value="<?= $q['id'] ?>">
                      <button class="button alt">Delete</button>
                    </form>
                  </div>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>

        <!-- COMPLETED QUESTS -->
        <div class="card" style="margin-top:16px;opacity:0.7;">
          <h3>✔ Completed Quests</h3>
          <?php if (empty($completed)): ?>
            <p>No quests completed yet.</p>
          <?php else: ?>
            <ul class="quest-list">
              <?php foreach ($completed as $q): ?>
                <li class="quest-item">
                  <div>
                    <div class="quest-title" style="text-decoration: line-through; opacity:0.6;">
                      <?= htmlspecialchars($q['title']) ?>
                    </div>
                    <div class="quest-meta">Completed • +<?= $q['reward'] ?> XP</div>
                  </div>
                  <form method="post">
                    <input type="hidden" name="delete_id" value="<?= $q['id'] ?>">
                    <button class="button alt">Delete</button>
                  </form>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>

        <?php endif; ?>
    </div>
    </div>

  <!-- RIGHT SIDE -->
  <div class="right-stack">
    <!-- SHOP / REWARD SECTION -->
     <div class="card" style="margin-top:16px;">
  <h2>🏆 Rewards Shop</h2>

  <p>Exchange 50 XP for <strong>+10 Gold</strong></p>
  
  <form method="post">
     <button class="button" name="buy_reward">Buy XP → Gold</button>
  </form>
</div>


    <!-- TIMER -->
    <div class="card">
      <h2>⏳ Work Timer</h2>

      <?php if (!$active): ?>

        <?php if (empty($pending)): ?>
          <p>No active quests available.</p>
        <?php else: ?>
          <form method="post">
            <label><strong>Select Quest:</strong></label><br>
            <select name="quest_id" required style="padding:8px;width:100%;margin-bottom:8px;">
              <?php foreach ($pending as $q): ?>
                <option value="<?= $q['id'] ?>">
                  <?= htmlspecialchars($q['title']) ?> (+<?= $q['reward'] ?> XP)
                </option>
              <?php endforeach; ?>
            </select>

            <button class="button" name="start_session">Start Working</button>
          </form>
        <?php endif; ?>

      <?php else: ?>

        <p><strong>Working on:</strong> <?= htmlspecialchars($active['task']) ?></p>
        <p>Started: <?= date('Y-m-d H:i:s', $active['start']) ?></p>
        <form method="post" style="margin-top:8px;">
          <button class="button" name="finish_session">Finish</button>
          <button class="button alt" name="cancel_session">Cancel</button>
        </form>

      <?php endif; ?>
    </div>

    <div class="footer" style="margin-top:16px;">“You must strive to find your own voice because the longer you wait to begin, the less likely you are to find it at all.”
    </div>
  </div>
</body>
</html>