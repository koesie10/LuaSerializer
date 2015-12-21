<?php
/**
 * NilASTNode.php
 *
 * @author Koen Vlaswinkel <koen@vlaswinkel.info>
 * @since  21/12/2015 10:49
 */

namespace Vlaswinkel\Lua\AST;

/**
 * Class NilASTNode
 *
 * @package Vlaswinkel\Lua\AST
 */
class NilASTNode extends ASTNode {
    const NAME = 'nil';

    /**
     * @return string
     */
    public function getName() {
        return self::NAME;
    }
}