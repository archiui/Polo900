<?php 

class Stack {
    public $stack = array();

    public function push($elem) {
        array_push($this->stack, $elem);
    }

    public function pop()   {
        return array_pop($this->stack);
    }

    public function isEmpty()   {
        return count($this->stack) == 0;
    }
}