<?php
// class dan public function merupakan class dan constructor nya (berupa encapsulation data private)
class QuestManager {
    private $file;
    private $quests;

    public function __construct() {
        $this->file = __DIR__ . "/../data/quests.json";
        if (file_exists($this->file)) {
            $this->quests = json_decode(file_get_contents($this->file), true) ?: [];
        } else {
            $this->quests = [];
            $this->save();
        }
    }
// di bawah ini merupakan meethod getter
    public function getAll() { return $this->quests; }

    public function add($title, $difficulty) {
        $id = $this->generateId();
        $reward = $this->rewardByDifficulty($difficulty);
        $this->quests[] = [
            'id' => $id,
            'title' => $title,
            'difficulty' => $difficulty,
            'reward' => $reward,
            'status' => 'pending'
        ];
        $this->save();
    }

    private function generateId() {
        $max = 0;
        foreach ($this->quests as $q) if ($q['id'] > $max) $max = $q['id'];
        return $max + 1; // array + loop
    }
// basic polymorphism (behavior changes depends on input)
    private function rewardByDifficulty($diff) {
        if ($diff === 'easy') return 10;
        if ($diff === 'normal') return 20;
        return 40;
    }

    public function complete($id) {
        foreach ($this->quests as &$q) {
            if ($q['id'] == $id && $q['status'] === 'pending') {
                $q['status'] = 'completed';
                $this->save();
                return $q['reward'];
            }
        }
        return 0;
    }

    public function delete($id) {
        foreach ($this->quests as $i => $q) {
            if ($q['id'] == $id) {
                array_splice($this->quests, $i, 1);
                $this->save();
                return true;
            }
        }
        return false;
    }

    public function countDone() {
        $c = 0;
        foreach ($this->quests as $q) if ($q['status'] === 'done') $c++;
        return $c;
    }

    private function save() {
        file_put_contents($this->file, json_encode($this->quests, JSON_PRETTY_PRINT));
    }
}
