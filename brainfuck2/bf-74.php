<?php

declare(strict_types=1);

class StringIterator implements Iterator {
  private string $str;
  private int $current = 0;
  private int $last;

  public function __construct(string $str) {
    $this->str = $str;
    $this->last = strlen($this->str) - 1;
  }

  public function valid(): bool {
    return $this->current < $this->last;
  }

  public function next(): string {
    return $this->str[$this->current++];
  }

  public function current(): int {
    return $this->str[$this->current];
  }

  public function key(): int {
    return $this->current;
  }

  public function rewind(): void {
    $this->current--;
  }
}

class Tape {
  private int $pos = 0;
  private array $tape = [0];

  public function inc(int $x): void {
    $this->tape[$this->pos] += $x;
  }

  public function move(int $x): void {
    $this->pos += $x;
    while ($this->pos >= count($this->tape)) {
      array_push($this->tape, 0);
    }
  }

  public function get(): int {
    return $this->tape[$this->pos];
  }
}

const OP_INC = 1;
const OP_MOVE = 2;
const OP_LOOP = 3;
const OP_PRINT = 4;

class Op {
  public int $op;
  public int $v;
  public ?array $loop;

  public function __construct(int $op, int $v = 0, ?array $loop = null) {
    $this->op = $op;
    $this->v = $v;
    $this->loop = $loop;
  }
}

class Brainfuck {
  private $parse;

  public function __construct($text) {
    $this->text = $text;
  }

  private function parse(StringIterator $it): array {
    $res = [];
    while ($it->valid()) {
      switch ($it->next()) {
        case '+': array_push($res, new Op(OP_INC, 1)); break;
        case '-': array_push($res, new Op(OP_INC, -1)); break;
        case '>': array_push($res, new Op(OP_MOVE, 1)); break;
        case '<': array_push($res, new Op(OP_MOVE, -1)); break;
        case '.': array_push($res, new Op(OP_PRINT)); break;
        case '[': array_push($res, new Op(OP_LOOP, 0, $this->parse($it))); break;
        case ']': return $res;
      }
    }
    return $res;
  }

  private function _run(array &$ops, Tape &$tape) {
    foreach ($ops as &$op) {
      switch ($op->op) {
        case OP_INC: $tape->inc($op->v); break;
        case OP_MOVE: $tape->move($op->v); break;
        case OP_LOOP: while ($tape->get() > 0) $this->_run($op->loop, $tape); break;
        case OP_PRINT: echo chr($tape->get()); break;
      }
    }
  }

  public function run(): void {
    $this->_run($this->parse(new StringIterator($this->text)), new Tape());
  }
}

$text = file_get_contents($_SERVER['argv'][1]);
$brainfuck = new Brainfuck($text);
$brainfuck->run();
