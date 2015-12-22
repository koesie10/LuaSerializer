<?php

namespace Vlaswinkel\Lua\AST;

/**
 * Class ASTNode
 *
 * @author  Koen Vlaswinkel <koen@vlaswinkel.info>
 * @package Vlaswinkel\Lua\AST
 */
abstract class ASTNode {
    /**
     * @return string
     */
    public abstract function getName();
}