<?php

declare(strict_types=1);

class StringIterator implements Iterator {
  private $str;
  private $current = 0;
  private $last;

  public function __construct($str) {
    $this->str = $str;
    $this->last = strlen($this->str) - 1;
  }

  public function valid() {
    return $this->current < $this->last;
  }

  public function next() {
    return $this->str[$this->current++];
  }

  public function current() {
    return $this->str[$this->current];
  }

  public function key() {
    return $this->current;
  }

  public function rewind() {
    $this->current--;
  }
}

class Tape {
  private $pos = 0;
  private $tape = [0];

  public function inc(int $x) {
    $this->tape[$this->pos] += $x;
  }

  public function move($x) {
    $this->pos += $x;
    while ($this->pos >= count($this->tape)) {
      array_push($this->tape, 0);
    }
  }

  public function get() {
    return $this->tape[$this->pos];
  }
}

const OP_INC = 1;
const OP_MOVE = 2;
const OP_LOOP = 3;
const OP_PRINT = 4;

class Op {
  public $op;
  public $v;

  public function __construct($op, $v) {
    $this->op = $op;
    $this->v = $v;
  }
}

class Brainfuck {
  private $parse;

  public function __construct($text) {
    $this->text = $text;
  }

  private function parse($it) {
    $res = [];
    while ($it->valid()) {
      switch ($it->next()) {
        case '+': array_push($res, new Op(OP_INC, 1)); break;
        case '-': array_push($res, new Op(OP_INC, -1)); break;
        case '>': array_push($res, new Op(OP_MOVE, 1)); break;
        case '<': array_push($res, new Op(OP_MOVE, -1)); break;
        case '.': array_push($res, new Op(OP_PRINT, 0)); break;
        case '[': array_push($res, new Op(OP_LOOP, $this->parse($it))); break;
        case ']': return $res;
      }
    }
    return $res;
  }

  private function _run($ops, $tape) {
    foreach ($ops as $op) {
      switch ($op->op) {
        case OP_INC: $tape->inc($op->v); break;
        case OP_MOVE: $tape->move($op->v); break;
        case OP_LOOP: while ($tape->get() > 0) $this->_run($op->v, $tape); break;
        case OP_PRINT: echo chr($tape->get()); break;
      }
    }
  }

  public function run() {
    $this->_run($this->parse(new StringIterator($this->text)), new Tape());
  }
}

$text = file_get_contents($_SERVER['argv'][1]);
$brainfuck = new Brainfuck($text);
$brainfuck->run();
