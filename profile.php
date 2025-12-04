<?php
session_start();
require_once "core/User.php";

// Load user from session or create a default one
$user = isset($_SESSION['user']) ? unserialize($_SESSION['user']) : new User();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Profile - QuestForge</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="header">
    <h1>QuestForge</h1>
    <nav>
        <a href="index.php">🏠 Dashboard</a>
        <a href="profile.php" class="active">👤 Profile</a>
    </nav>
</div>

<div class="wrapper">
    <div class="card" style="max-width:450px;margin:auto;">
        <h2>👤 Profile</h2>

        <p><strong>Name:</strong> <?= htmlspecialchars($user->getUsername() ?: "Traveler") ?></p>
        <p><strong>Level:</strong> <?= $user->getLevel() ?></p>
        <p><strong>EXP:</strong> <?= $user->getXp() ?> / 150</p>
        <p><strong>Gold:</strong> <?= $user->getGold() ?></p>
        <p><strong>Streak:</strong> <?= $user->getStreak() ?> days</p>

        <!-- Rename Feature -->
        <form method="post" action="profile_update.php" style="margin-top:16px;">
            <input type="text" name="new_username" placeholder="Rename Yourself..."
                style="padding:8px;width:70%;">
            <button class="button">Save</button>
        </form>
    </div>
</div>

</body>
</html>
