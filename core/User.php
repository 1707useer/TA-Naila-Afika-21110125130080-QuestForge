<?php
// OOP encapsulation
class User {
    private $username;
    private $xp;
    private $level;
    private $gold;
    private $streak;
    private $last_active;
    private $file;

    public function __construct() {
        $this->file = __DIR__ . "/../data/user.json";
        if (file_exists($this->file)) {
            $d = json_decode(file_get_contents($this->file), true);
            $this->username = $d['username'] ?? '';
            $this->xp = $d['xp'] ?? 0;
            $this->level = $d['level'] ?? 1;
            $this->gold = $d['gold'] ?? 0;
            $this->streak = $d['streak'] ?? 0;
            $this->last_active = $d['last_active'] ?? '';
        } else {
            $this->username = '';
            $this->xp = 0;
            $this->level = 1;
            $this->gold = 0;
            $this->streak = 0;
            $this->last_active = '';
            $this->save();
        }
    }
// getter setter
    public function getUsername() { return $this->username; }
    public function getXp() { return $this->xp; }
    public function getLevel() { return $this->level; }
    public function getGold() { return $this->gold; }
    public function getStreak() { return $this->streak; }
    public function getLastActive() { return $this->last_active; }

    public function setUsername($name) { $this->username = $name; $this->save(); }

    public function rename($newName) {
    if(trim($newName) !== "") {
        $this->setUsername($newName);
    }
}

    public function addGold($n) { $this->gold += (int)$n; $this->save(); }
    public function spendGold($n) { if ($this->gold >= $n) { $this->gold -= $n; $this->save(); return true; } return false; }
// method perhitungan  (loop/while + conditional/if)
    public function addXp($amount) {
        $this->xp += (int)$amount;
        $threshold = 150; // XP per level
        while ($this->xp >= $threshold) {
            $this->xp -= $threshold;
            $this->level++;
        }
        $this->save();
    }

    public function updateStreakIfToday() {
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        if ($this->last_active === $today) return;
        if ($this->last_active === $yesterday) $this->streak += 1;
        else $this->streak = 1;
        $this->last_active = $today;
        $this->save();
    }

    public function buyReward($cost) {
    if ($this->xp >= $cost) {
        $this->xp -= $cost;
        $this->addGold(10); // reward sederhana: convert XP → gold
        $this->save();
        return true;
    }
    return false;
}

    public function save() {
        $data = [
            'username' => $this->username,
            'xp' => $this->xp,
            'level' => $this->level,
            'gold' => $this->gold,
            'streak' => $this->streak,
            'last_active' => $this->last_active
        ];
        file_put_contents($this->file, json_encode($data, JSON_PRETTY_PRINT));
    }
}
