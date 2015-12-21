<?php
/**
 * StringASTNode.php
 *
 * @author Koen Vlaswinkel <koen@vlaswinkel.info>
 * @since  21/12/2015 10:32
 */

namespace Vlaswinkel\Lua\AST;

/**
 * Class StringASTNode
 *
 * @package Vlaswinkel\Lua\AST
 */
class StringASTNode extends LiteralASTNode {
    const NAME = 'string';

    /**
     * @var string
     */
    private $value;

    /**
     * StringASTNode constructor.
     *
     * @param string $value
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
     * @return string
     */
    public function getValue() {
        return $this->value;
    }
}