<?php
/**
 * ASTNode.php
 *
 * @author Koen Vlaswinkel <koen@vlaswinkel.info>
 * @since  21/12/2015 10:28
 */

namespace Vlaswinkel\Lua\AST;

/**
 * Class ASTNode
 *
 * @package Vlaswinkel\Lua\AST
 */
abstract class ASTNode {
    /**
     * @return string
     */
    public abstract function getName();
}