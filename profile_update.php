<?php
session_start();
require_once "core/User.php";

if (!isset($_SESSION['user'])) {
    header("Location: profile.php");
    exit;
}

$user = unserialize($_SESSION['user']);

$newName = trim($_POST['new_username'] ?? '');

if ($newName !== "") {
    $user->rename($newName);
}

// Save back to session
$_SESSION['user'] = serialize($user);

header("Location: profile.php");
exit;
