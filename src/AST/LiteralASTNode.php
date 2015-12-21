<?php
/**
 * LiteralASTNode.php
 *
 * @author Koen Vlaswinkel <koen@vlaswinkel.info>
 * @since  21/12/2015 10:30
 */

namespace Vlaswinkel\Lua\AST;

/**
 * Class LiteralASTNode
 *
 * @package Vlaswinkel\Lua\AST
 */
abstract class LiteralASTNode extends ASTNode {
    public abstract function getValue();
}