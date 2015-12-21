<?php
/**
 * LuaToken.php
 *
 * @author Koen Vlaswinkel <koen@vlaswinkel.info>
 * @since  20/12/2015 18:46
 */

namespace Vlaswinkel\Lua;

/**
 * Class LuaToken
 *
 * @package Vlaswinkel\Lua
 */
class LuaToken {
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
     * LuaToken constructor.
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