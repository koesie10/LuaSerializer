<?php
/**
 * NumberASTNode.php
 *
 * @author Koen Vlaswinkel <koen@vlaswinkel.info>
 * @since  21/12/2015 10:33
 */

namespace Vlaswinkel\Lua\AST;

/**
 * Class NumberASTNode
 *
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