<?php

namespace Vlaswinkel\Lua\AST;

/**
 * Class NumberASTNode
 *
 * @author  Koen Vlaswinkel <koen@vlaswinkel.info>
 * @package Vlaswinkel\Lua\AST
 */
class NumberASTNode extends LiteralASTNode {
    const NAME = 'number';

    /**
     * @var integer|float
     */
    private $value;

    /**
     * NumberASTNode constructor.
     *
     * @param float|int $value
     */
    public function __construct($value) {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getName() {
        return self::NAME;
    }

    /**
     * @return float|int
     */
    public function getValue() {
        return $this->value;
    }
}