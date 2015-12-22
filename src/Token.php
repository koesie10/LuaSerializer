<?php

namespace Vlaswinkel\Lua;

/**
 * Class Token
 *
 * @author  Koen Vlaswinkel <koen@vlaswinkel.info>
 * @package Vlaswinkel\Lua
 */
class Token {
    const TYPE_STRING = 1;
    const TYPE_NUMBER = 2;
    const TYPE_PUNCTUATION = 3;
    const TYPE_IDENTIFIER = 4;
    const TYPE_KEYWORD = 5;

    /**
     * @var int
     */
    private $type;
    /**
     * @var string
     */
    private $value;

    /**
     * Token constructor.
     *
     * @param int    $type
     * @param string $value
     */
    public function __construct($type, $value) {
        $this->type  = $type;
        $this->value = $value;
    }

    /**
     * @return int
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getValue() {
        return $this->value;
    }
}