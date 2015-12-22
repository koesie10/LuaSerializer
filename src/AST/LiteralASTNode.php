<?php

namespace Vlaswinkel\Lua\AST;

/**
 * Class LiteralASTNode
 *
 * @author  Koen Vlaswinkel <koen@vlaswinkel.info>
 * @package Vlaswinkel\Lua\AST
 */
abstract class LiteralASTNode extends ASTNode {
    public abstract function getValue();
}