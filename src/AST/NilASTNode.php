<?php

namespace Vlaswinkel\Lua\AST;

/**
 * Class NilASTNode
 *
 * @author  Koen Vlaswinkel <koen@vlaswinkel.info>
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