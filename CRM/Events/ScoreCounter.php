<?php

class CRM_Events_ScoreCounter {
  public $score = 0;
  public $numEntries = 0;

  public function add($score) {
    if (is_numeric($score) && $score > 0 && $score <= 100) {
      $this->score += $score;
      $this->numEntries++;
    }
  }

  public function getAvgScore() {
    if ($this->numEntries > 0) {
      return round($this->score / $this->numEntries);
    }
    else {
      return '';
    }
  }
}
