<?php

namespace Vlaswinkel\Lua;

/**
 * Class InputStream
 *
 * @see     http://lisperator.net/pltut/parser/input-stream
 *
 * @author  Koen Vlaswinkel <koen@vlaswinkel.info>
 * @package Vlaswinkel\Lua
 */
class InputStream {
    /**
     * @var string
     */
    private $input;

    /**
     * @var int
     */
    private $position = 0;
    /**
     * @var int
     */
    private $line = 1;
    /**
     * @var int
     */
    private $column = 0;

    /**
     * InputStream constructor.
     *
     * @param string $input
     */
    public function __construct($input) {
        $this->input = $input;
    }

    public function next() {
        $char = $this->input[$this->position++];
        if ($char == "\n") {
            $this->line++;
            $this->column = 0;
        } else {
            $this->column++;
        }
        return $char;
    }

    public function peek() {
        return $this->input[$this->position];
    }

    public function eof() {
        return $this->position >= strlen($this->input);
    }

    public function error($msg) {
        throw new ParseException($msg . ' (' . $this->line . ':' . $this->column . ')');
    }
}